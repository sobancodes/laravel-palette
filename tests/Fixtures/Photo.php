<?php

namespace Codekinz\LaravelPalette\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'dominant_colors' => 'array',
    ];
}
