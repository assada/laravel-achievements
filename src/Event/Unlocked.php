<?php
declare(strict_types=1);

namespace Gstt\Achievements\Event;

use Gstt\Achievements\Model\AchievementProgress;
use Illuminate\Queue\SerializesModels;

/**
 * Class Unlocked
 *
 * @package Gstt\Achievements\Event
 */
class Unlocked
{
    use SerializesModels;

    /**
     * @var AchievementProgress
     */
    public $progress;

    /**
     * Create a new event instance.
     *
     * @param AchievementProgress $progress
     */
    public function __construct(AchievementProgress $progress)
    {
        $this->progress = $progress;
    }
}
