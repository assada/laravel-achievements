<?php
declare(strict_types=1);

namespace Assada\Achievements\Console;

use Assada\Achievements\Achievement;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Str;

/**
 * Class LoadAchievements
 *
 * @package Assada\Achievements\Console
 */
class LoadAchievementsCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'achievements:load {--force : Force the operation to run when in production}
                {--path=* : The path(s) to the achievements files to be executed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load all Achievements to database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $paths = $this->option('path');

        if (empty($paths)) {
            $paths = [
                'app/Achievements'
            ];
        }

        $classes = [];

        foreach ($paths as $path) {
            $this->info(sprintf('Load classes in %s...', $path));

            $files = array_diff(scandir($path, SCANDIR_SORT_ASCENDING), array('.', '..'));

            foreach ($files as $file) {
                if (!Str::endsWith($file, '.php')) {
                    $this->warn(sprintf('File %s in %s not an php file', $file, $path));
                    continue;
                }

                $classes[] = [
                    'name' => Str::before($file, '.php'),
                    'namespace' => $this->getNamespace(file_get_contents($path . '/' . $file))
                ];
            }

            $this->info(sprintf('Found %d classes. Instantiating...', count($classes)));
        }

        /** @var Achievement[] $objects */
        $objects = [];

        foreach ($classes as $class) {
            $fullClass = sprintf('%s\%s', $class['namespace'], $class['name']);
            $objects[] = new $fullClass;
        }

        $this->info(sprintf('Created %d objects. Migrating...', count($objects)));

        $bar = $this->output->createProgressBar(count($objects));

        foreach ($objects as $object) {
            $model = $object->getModel();

            $bar->advance();
        }

        $bar->finish();

        $this->line('');
    }

    /**
     * @param $src
     * @return string|null
     */
    private function getNamespace($src): ?string
    {
        if (preg_match('#^namespace\s+(.+?);$#sm', $src, $m)) {
            return $m[1];
        }
        return null;
    }
}
