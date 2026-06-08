<?php

use Codekinz\LaravelPalette\PaletteManager;

if (! function_exists('extract_colors')) {
    /**
     * @return array<int, string>
     */
    function extract_colors(string $path, int $count = 5): array
    {
        return app(PaletteManager::class)->extract($path, $count);
    }
}
