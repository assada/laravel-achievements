<?php
declare(strict_types=1);

namespace Assada\Achievements;

use Assada\Achievements\Event\Progress as ProgressEvent;
use Assada\Achievements\Event\Unlocked as UnlockedEvent;
use Assada\Achievements\Model\AchievementDetails;
use Assada\Achievements\Model\AchievementProgress;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Achievement
 *
 * @package Assada\Achievements
 */
abstract class Achievement implements CanAchieve
{
    /**
     * The unique identifier for the achievement.
     *
     * @var string
     */
    public $id;

    /*
     * The achievement name
     */
    public $name = 'Achievement';

    /*
     * A small description for the achievement
     */
    public $description = '';

    /**
     * The amount of points required to unlock this achievement.
     */
    public $points = 1;

    /*
     * Whether this is a secret achievement or not.
     */
    public $secret = false;

    /*
     * The model class for this achievement.
     */
    private $modelAttr = null;

    /**
     * Achievement constructor.
     * Should add the achievement to the database.
     */
    public function __construct()
    {
        $this->getModel();
    }

    /**
     * Wrapper for AchievementDetail::all();
     * Conveniently fetches all achievements stored in the database.
     */
    public static function all()
    {
        return AchievementDetails::all();
    }

    /**
     * Gets the full class name.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return static::class;
    }

    /**
     * Gets the amount of points needed to unlock the achievement.
     *
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }

    /**
     * Gets the details class for this achievement.
     *
     * @return AchievementDetails
     */
    public function getModel(): AchievementDetails
    {
        if (!is_null($this->modelAttr)) {
            return $this->modelAttr;
        }

        $model = AchievementDetails::where('class_name', $this->getClassName())->first();

        if (is_null($model)) {
            $model = new AchievementDetails();
            $model->class_name = $this->getClassName();
        }

        if (config('achievements.auto_sync') || is_null($model->name)) {
            $model->name = $this->name;
            $model->description = $this->description;
            $model->points = $this->points;
            $model->secret = $this->secret;

            // Syncs
            $model->save();
        }

        $this->modelAttr = $model;
        return $model;
    }

    /**
     * Adds a specified amount of points to the achievement.
     *
     * @param mixed $achiever The entity that will add progress to this achievement
     * @param int $points The amount of points to be added to this achievement
     */
    public function addProgressToAchiever($achiever, $points = 1): void
    {
        $progress = $this->getOrCreateProgressForAchiever($achiever);
        if (!$progress->isUnlocked()) {
            $progress->points += $points;
            $progress->save();
        }
    }

    /**
     * Sets a specified amount of points to the achievement.
     *
     * @param mixed $achiever The entity that will add progress to this achievement
     * @param int $points The amount of points to be added to this achievement
     */
    public function setProgressToAchiever($achiever, $points): void
    {
        $progress = $this->getOrCreateProgressForAchiever($achiever);

        if (!$progress->isUnlocked()) {
            $progress->points = $points;
            $progress->save();
        }
    }

    /**
     * Gets the achiever's progress data for this achievement, or creates a new one if not existant
     * @param Model $achiever
     *
     * @return AchievementProgress
     */
    public function getOrCreateProgressForAchiever($achiever): AchievementProgress
    {
        $className = $this->getAchieverClassName($achiever);

        $achievementId = $this->getModel()->id;
        $progress = AchievementProgress::where('achiever_type', $className)
            ->where('achievement_id', $achievementId)
            ->where('achiever_id', $achiever->getKey())
            ->first();

        if (is_null($progress)) {
            $progress = new AchievementProgress();
            $progress->details()->associate($this->getModel());
            $progress->achiever()->associate($achiever);

            $progress->save();
        }

        return $progress;
    }

    /**
     * Gets model morph name
     *
     * @param Model $achiever
     * @return string
     */
    protected function getAchieverClassName($achiever): string
    {
        if ($achiever instanceof Model) {
            return $achiever->getMorphClass();
        }

        return get_class($achiever);
    }

    /**
     * Will be called when the achievement is unlocked.
     *
     * @param $progress
     */
    public function whenUnlocked($progress)
    {
    }

    /**
     * Will be called when progress is made on the achievement.
     *
     * @param $progress
     */
    public function whenProgress($progress)
    {
    }

    /**
     * Triggers the AchievementUnlocked Event.
     *
     * @param $progress
     */
    public function triggerUnlocked($progress): void
    {
        event(new UnlockedEvent($progress));
        $this->whenUnlocked($progress);
    }

    /**
     * Triggers the AchievementProgress Event.
     *
     * @param $progress
     */
    public function triggerProgress($progress): void
    {
        event(new ProgressEvent($progress));
        $this->whenProgress($progress);
    }
}
