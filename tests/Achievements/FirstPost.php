<?php
declare(strict_types=1);

namespace Gstt\Tests\Achievements;

use Gstt\Achievements\Achievement;

/**
 * Class FirstPost
 *
 * @package Gstt\Tests\Achievements
 */
class FirstPost extends Achievement
{
    public $name = 'First Post';
    public $description = 'You made your first post!';
}
