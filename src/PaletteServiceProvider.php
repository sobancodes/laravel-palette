<?php

namespace Codekinz\LaravelPalette;

use Illuminate\Support\ServiceProvider;

class PaletteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/palette.php', 'palette');

        $this->app->singleton(PaletteManager::class, function (): PaletteManager {
            return new PaletteManager(config('palette'));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/palette.php' => config_path('palette.php'),
        ], 'palette-config');
    }
}
