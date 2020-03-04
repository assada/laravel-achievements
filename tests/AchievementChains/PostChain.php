<?php
declare(strict_types=1);

namespace Assada\Tests\AchievementChains;

use Assada\Achievements\AchievementChain;
use Assada\Tests\Achievements\FiftyPosts;
use Assada\Tests\Achievements\FirstPost;
use Assada\Tests\Achievements\TenPosts;

/**
 * Class PostChain
 *
 * @package Assada\Tests\AchievementChains
 */
class PostChain extends AchievementChain
{
    public function chain(): array
    {
        return [new FirstPost(), new TenPosts(), new FiftyPosts()];
    }
}
