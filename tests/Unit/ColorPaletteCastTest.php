<?php

use Codekinz\LaravelPalette\Casts\ColorPalette;
use Illuminate\Database\Eloquent\Model;

beforeEach(function () {
    $this->cast = new ColorPalette();
    $this->model = new class () extends Model {
    };
});

it('casts json to an array on get', function () {
    $value = $this->cast->get($this->model, 'dominant_colors', '["#FF0000","#00FF00"]', []);

    expect($value)->toBe(['#FF0000', '#00FF00']);
});

it('casts arrays to json on set', function () {
    $value = $this->cast->set($this->model, 'dominant_colors', ['#FF0000', '#00FF00'], []);

    expect($value)->toBe('["#FF0000","#00FF00"]');
});

it('handles null values', function () {
    expect($this->cast->get($this->model, 'dominant_colors', null, []))->toBeNull()
        ->and($this->cast->set($this->model, 'dominant_colors', null, []))->toBeNull();
});

it('rejects non-array values on set', function () {
    $this->cast->set($this->model, 'dominant_colors', '#FF0000', []);
})->throws(\InvalidArgumentException::class);
