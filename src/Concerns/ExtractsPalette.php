<?php

namespace Codekinz\LaravelPalette\Concerns;

use Codekinz\LaravelPalette\Jobs\ExtractColors;
use InvalidArgumentException;

trait ExtractsPalette
{
    public function extractColors(
        ?string $path = null,
        ?string $disk = null,
        int $count = 5,
        string $column = 'dominant_colors'
    ): mixed {
        $path ??= $this->getAttribute('image_path') ?? $this->getAttribute('path');

        if (! is_string($path) || $path === '') {
            throw new InvalidArgumentException('A path is required to extract colors.');
        }

        return ExtractColors::dispatch(
            $path,
            $disk,
            $count,
            static::class,
            $this->getKey(),
            $column
        );
    }
}
