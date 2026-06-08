<?php

namespace Codekinz\LaravelPalette\Tests;

use Codekinz\LaravelPalette\PaletteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PaletteServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('filesystems.disks.palette-fixtures', [
            'driver' => 'local',
            'root' => $this->fixturePath(),
        ]);
    }

    protected function fixturePath(string $file = ''): string
    {
        $path = __DIR__.'/../vendor/league/color-extractor/tests/assets';

        return $file === '' ? $path : $path.'/'.$file;
    }
}
