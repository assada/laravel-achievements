<?php
declare(strict_types=1);

namespace Gstt\Tests\Achievements;

use Gstt\Achievements\Achievement;

/**
 * Class FiftyPosts
 *
 * @package Gstt\Tests\Achievements
 */
class FiftyPosts extends Achievement
{
    public $name = 'Fifty Posts';
    public $description = 'You have created 50 posts!';
    public $points = 50;
}
