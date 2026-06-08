<?php

namespace Codekinz\LaravelPalette\Facades;

use Codekinz\LaravelPalette\PaletteManager;
use Illuminate\Support\Facades\Facade;

class Palette extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaletteManager::class;
    }
}
