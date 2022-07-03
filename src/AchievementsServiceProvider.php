<?php
declare(strict_types=1);

namespace Assada\Achievements;

use Assada\Achievements\Console\AchievementChainMakeCommand;
use Assada\Achievements\Console\AchievementMakeCommand;
use Assada\Achievements\Console\LoadAchievementsCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class AchievementsServiceProvider
 *
 * @package Assada\Achievements
 */
class AchievementsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    AchievementMakeCommand::class,
                    AchievementChainMakeCommand::class,
                    LoadAchievementsCommand::class
                ]
            );
            $this->publishes(
                [
                    __DIR__.'/config/achievements.php' => config_path('achievements.php'),
                ],
                'config'
            );
            $this->publishes(
                [
                    __DIR__.'/Migrations/0000_00_00_000000_create_achievements_tables.php' => database_path('migrations/000_00_00_000000_create_achievements_tables.php')
                ],
                'migrations'
            );
        }

        $this->app[Achievement::class] = static function ($app) {
            return $app['gstt.achievements.achievement'];
        };

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        $this->mergeConfigFrom(__DIR__.'/config/achievements.php', 'achievements');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
