<?php
declare(strict_types=1);

namespace Gstt\Tests\AchievementChains;

use Gstt\Achievements\AchievementChain;
use Gstt\Tests\Achievements\FiftyPosts;
use Gstt\Tests\Achievements\FirstPost;
use Gstt\Tests\Achievements\TenPosts;

/**
 * Class PostChain
 *
 * @package Gstt\Tests\AchievementChains
 */
class PostChain extends AchievementChain
{
    public function chain(): array
    {
        return [new FirstPost(), new TenPosts(), new FiftyPosts()];
    }
}
