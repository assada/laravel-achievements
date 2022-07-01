<?php
declare(strict_types=1);

namespace Assada\Achievements;

use Assada\Achievements\Model\AchievementDetails;
use Assada\Achievements\Model\AchievementProgress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait EntityRelationsAchievements
 *
 * @package Assada\Achievements
 */
trait EntityRelationsAchievements
{
    /**
     * Get the entity's Achievements
     *
     * @return MorphMany
     */
    public function achievements(): MorphMany
    {
        if (config('achievements.locked_sync') && !empty($this->id)) {
            $this->syncAchievements();
        }
        return $this->morphMany(AchievementProgress::class, 'achiever')
            ->orderBy('updated_at', 'desc');
    }

    /**
     * Retrieves the status for the specified achievement
     * @param Achievement $achievement
     * @return null|AchievementProgress
     */
    public function achievementStatus(Achievement $achievement): ?AchievementProgress
    {
        return $this->achievements()->where('achievement_id', $achievement->getModel()->id)->first();
    }

    /**
     * Return true if the user has unlocked this achievement, false otherwise.
     * @param Achievement $achievement
     * @return bool
     */
    public function hasUnlocked(Achievement $achievement): bool
    {
        $status = $this->achievementStatus($achievement);

        return !(null === $status || null === $status->unlocked_at);
    }

    /**
     * Get the entity's achievements in progress.
     *
     * @return Collection
     */
    public function inProgressAchievements(): Collection
    {
        return $this->achievements()->whereNull('unlocked_at')->where('points', '>', 0)->get();
    }

    /**
     * Get the entity's achievements not in progress.
     *
     * @return Collection
     */
    public function notInProgressAchievements(): Collection
    {
        return $this->achievements()->whereNull('unlocked_at')->where('points', '=', 0)->get();
    }

    /**
     * Get the entity's unlocked achievements.
     *
     * @return Collection
     */
    public function unlockedAchievements(): Collection
    {
        return $this->achievements()->whereNotNull('unlocked_at')->get();
    }

    /**
     * Get the entity's locked achievements.
     * @return Collection
     */
    public function lockedAchievements(): Collection
    {
        if (config('achievements.locked_sync')) {
            // Relationships should be synced. Just return relationship data.
            return $this->achievements()->whereNull('unlocked_at')->get();
        } else {
            // Query all unSynced
            $unSynced = AchievementDetails::getUnsyncedByAchiever($this)->get();
            $unSynced = $unSynced->map(
                function ($el) {
                    $progress = new AchievementProgress();
                    $progress->details()->associate($el);
                    $progress->achiever()->associate($this);
                    $progress->points = 0;
                    $progress->created_at = null;
                    $progress->updated_at = null;
                    return $progress;
                }
            );

            // Merge with progressed, but not yet unlocked

            return $this->achievements()->whereNull('unlocked_at')->get()->merge($unSynced);
        }
    }

    /**
     * Syncs achievement data.
     */
    public function syncAchievements(): void
    {
        /** @var Collection $locked */
        $locked = AchievementDetails::getUnsyncedByAchiever($this);
        $locked->each(
            function ($el) {
                $progress = new AchievementProgress();
                $progress->details()->associate($el);
                $progress->achiever()->associate($this);
                $progress->points = 0;
                $progress->save();
            }
        );
    }
}
