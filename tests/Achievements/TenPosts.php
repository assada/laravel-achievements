<?php
declare(strict_types=1);

namespace Assada\Tests\Achievements;

use Assada\Achievements\Achievement;

/**
 * Class TenPosts
 *
 * @package Assada\Tests\Achievements
 */
class TenPosts extends Achievement
{
    public $name = '10 Posts';
    public $description = 'You have created 10 posts!';
    public $points = 10;
}
