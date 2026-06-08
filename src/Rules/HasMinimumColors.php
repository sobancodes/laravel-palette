<?php

namespace Codekinz\LaravelPalette\Rules;

use Closure;
use Codekinz\LaravelPalette\PaletteManager;
use Illuminate\Contracts\Validation\ValidationRule;

class HasMinimumColors implements ValidationRule
{
    public function __construct(
        protected int $minimum
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_object($value) || ! method_exists($value, 'getRealPath')) {
            $fail("The {$attribute} must be an uploaded image file.");

            return;
        }

        $path = $value->getRealPath();

        if (! is_string($path) || ! is_file($path)) {
            $fail("The {$attribute} could not be read.");

            return;
        }

        if (count(app(PaletteManager::class)->extract($path, $this->minimum)) < $this->minimum) {
            $color = $this->minimum === 1 ? 'color' : 'colors';

            $fail("The {$attribute} must contain at least {$this->minimum} distinct {$color}.");
        }
    }
}
