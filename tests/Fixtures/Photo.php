<?php

namespace Codekinz\LaravelPalette\Tests\Fixtures;

use Codekinz\LaravelPalette\Casts\ColorPalette;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'dominant_colors' => ColorPalette::class,
    ];
}
