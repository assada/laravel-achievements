<?php
declare(strict_types=1);

namespace Assada\Tests\Achievements;

use Assada\Achievements\Achievement;

/**
 * Class FiftyPosts
 *
 * @package Assada\Tests\Achievements
 */
class FiftyPosts extends Achievement
{
    public $name = 'Fifty Posts';
    public $description = 'You have created 50 posts!';
    public $points = 50;
}
