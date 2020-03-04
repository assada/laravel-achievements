<?php
declare(strict_types=1);

namespace Assada\Achievements\Model;

use Carbon\Carbon;
use Exception;
use Assada\Achievements\Achievement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

/**
 * Model for the table that will store the data regarding achievement progress and unlocks.
 *
 * @category Model
 * @package  Assada\Achievements\Model
 * @author   Gabriel Simonetti <simonettigo@gmail.com>
 * @license  MIT License
 * @link     https://github.com/assada/laravel-achievements
 */
class AchievementProgress extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'achievement_progress';

    /**
     * AchievementProgress constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::get('achievements.table_names.progress');
        parent::__construct($attributes);
    }

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    /**
     * Get the notifiable entity that the achievement progress belongs to.
     *
     * @return MorphTo
     */
    public function achiever(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the achievement details.
     *
     * @return BelongsTo
     */
    public function details(): BelongsTo
    {
        return $this->belongsto(AchievementDetails::class, 'achievement_id');
    }

    /**
     * Checks if the achievement has been unlocked.
     *
     * @return bool
     */
    public function isUnlocked(): bool
    {
        if (!is_null($this->unlockedAt)) {
            return true;
        }
        if ($this->points >= $this->details->points) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the achievement is locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return !$this->isUnlocked();
    }

    /**
     * Overloads save method.
     *
     * @param array $options
     *
     * @return bool
     * @throws Exception
     * @throws Exception
     */
    public function save(array $options = []): bool
    {
        if (is_null($this->id)) {
            $this->id = Uuid::uuid4()->toString();
        }
        $recentlyUnlocked = false;
        if (is_null($this->unlockedAt) && $this->isUnlocked()) {
            $recentlyUnlocked = true;
            $this->points = $this->details->points;
            $this->unlocked_at = Carbon::now();
        }

        $result = parent::save($options);

        // Gets the achievement class for this progress
        /** @var Achievement $class */
        $class = $this->details->getClass();

        if ($recentlyUnlocked) {
            // Runs the callback set to run when the achievement is unlocked.
            $class->triggerUnlocked($this);
        } elseif ($this->points >= 0) {
            // Runs the callback set to run when progress has been made on the achievement.
            $class->triggerProgress($this);
        }

        return $result;
    }

    /**
     * Maps to Assada\Achievements\Achievement::$name
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->details->name;
    }

    /**
     * Maps to Assada\Achievements\Achievement::$description
     *
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        return $this->details->description;
    }
}
