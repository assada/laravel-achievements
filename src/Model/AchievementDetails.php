<?php
declare(strict_types=1);

namespace Assada\Achievements\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;

/**
 * Model for the table that will store the details for an Achievement Progress.
 *
 * @category Model
 * @package  Assada\Achievements\Model
 * @author   Gabriel Simonetti <simonettigo@gmail.com>
 * @license  MIT License
 * @link     https://github.com/assada/laravel-achievements
 */
class AchievementDetails extends Model
{
    public $secret = false;

    /**
     * @var string
     */
    protected $table = 'achievement_details';

    /**
     * AchievementDetails constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::get('achievements.table_names.details');
        parent::__construct($attributes);
    }

    /**
     * Return all users that have made progress on this achievement.
     *
     * @return HasMany
     */
    public function progress(): HasMany
    {
        return $this->hasMany(AchievementProgress::class, 'achievement_id');
    }

    /**
     * Return the progress data for achievers that have unlocked this achievement.
     *
     * @return Collection
     */
    public function unlocks(): Collection
    {
        return $this->progress()->whereNotNull('unlocked_at')->get();
    }

    /**
     * Returns the class that defined this achievement.
     *
     * @return object
     */
    public function getClass(): object
    {
        return new $this->class_name();
    }

    /**
     * Gets all AchievementDetails that have no correspondence on the Progress table.
     *
     * @param Model $achiever
     *
     * @return
     */
    public static function getUnsyncedByAchiever($achiever)
    {
        $className = (new static)->getAchieverClassName($achiever);

        $achievements = AchievementProgress::where('achiever_type', $className)
            ->where('achiever_id', $achiever->id)->get();
        $synced_ids = $achievements->map(
            static function ($el) {
                return $el->achievement_id;
            }
        )->toArray();

        return self::whereNotIn('id', $synced_ids);
    }

    /**
     * Gets model morph name
     *
     * @param Model $achiever
     *
     * @return string
     */
    protected function getAchieverClassName($achiever): string
    {
        if ($achiever instanceof Model) {
            return $achiever->getMorphClass();
        }

        return get_class($achiever);
    }
}
