<?php

namespace Codekinz\LaravelPalette\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;

/**
 * @implements CastsAttributes<array<int, string>|null, array<int, string>|null>
 */
class ColorPalette implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, string>|null
     *
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return array_values($value);
        }

        return json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException('Color palette casts must be set with an array or null.');
        }

        return json_encode(array_values($value), JSON_THROW_ON_ERROR);
    }
}
