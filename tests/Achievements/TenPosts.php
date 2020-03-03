<?php
declare(strict_types=1);

namespace Gstt\Tests\Achievements;

use Gstt\Achievements\Achievement;

/**
 * Class TenPosts
 *
 * @package Gstt\Tests\Achievements
 */
class TenPosts extends Achievement
{
    public $name = '10 Posts';
    public $description = 'You have created 10 posts!';
    public $points = 10;
}
