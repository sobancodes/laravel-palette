<?php

namespace Codekinz\LaravelPalette\Events;

class ColorsExtracted
{
    /**
     * @param  array<int, string>  $colors
     */
    public function __construct(
        public string $path,
        public array $colors,
        public ?string $disk = null,
        public ?string $model = null,
        public int|string|null $modelId = null,
        public string $column = 'dominant_colors'
    ) {
    }
}
