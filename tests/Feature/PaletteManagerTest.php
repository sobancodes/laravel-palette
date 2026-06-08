<?php

use Codekinz\LaravelPalette\PaletteManager;

it('extracts representative colors from an image', function () {
    $colors = app(PaletteManager::class)->extract($this->fixturePath('test.png'), 3);

    expect($colors)->toHaveCount(3)
        ->and($colors)->each->toMatch('/^#[0-9A-F]{6}$/');
});

it('extracts most used colors from an image', function () {
    $colors = app(PaletteManager::class)->mostUsed($this->fixturePath('test.png'), 2);

    expect($colors)->toBe(['#000000', '#FFFFFF']);
});

it('returns the full palette with pixel counts', function () {
    $palette = app(PaletteManager::class)->palette($this->fixturePath('test.png'));

    expect($palette)->toHaveKey('#000000')
        ->and($palette['#000000'])->toBeInt()
        ->and(count($palette))->toBeGreaterThan(100);
});

it('respects the configured default count', function () {
    config()->set('palette.count', 2);

    $colors = app(PaletteManager::class)->extract($this->fixturePath('google.png'));

    expect($colors)->toHaveCount(2);
});

it('uses the configured default method through colors', function () {
    config()->set('palette.method', 'most_used');

    $colors = app(PaletteManager::class)->colors($this->fixturePath('test.png'), 2);

    expect($colors)->toBe(['#000000', '#FFFFFF']);
});

it('handles transparent images with a background color', function () {
    $colors = app(PaletteManager::class)
        ->withBackground('#FFFFFF')
        ->extract($this->fixturePath('red-transparent-50.png'), 1);

    expect($colors)->toHaveCount(1)
        ->and($colors[0])->toMatch('/^#[0-9A-F]{6}$/');
});

it('loads images from a storage disk', function () {
    $colors = app(PaletteManager::class)
        ->fromDisk('palette-fixtures')
        ->extract('google.png', 2);

    expect($colors)->toHaveCount(2);
});

it('caches extraction results when enabled', function () {
    config()->set('palette.cache.enabled', true);

    $path = sys_get_temp_dir().'/palette-cache-enabled-'.uniqid().'.png';
    copy($this->fixturePath('test.png'), $path);

    try {
        $colors = app(PaletteManager::class)->extract($path, 2);

        unlink($path);

        expect(app(PaletteManager::class)->extract($path, 2))->toBe($colors);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('does not cache extraction results when disabled', function () {
    config()->set('palette.cache.enabled', false);

    $path = sys_get_temp_dir().'/palette-cache-disabled-'.uniqid().'.png';
    copy($this->fixturePath('test.png'), $path);

    try {
        app(PaletteManager::class)->extract($path, 2);

        unlink($path);

        app(PaletteManager::class)->extract($path, 2);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
})->throws(\InvalidArgumentException::class);

it('throws an exception for a missing image', function () {
    app(PaletteManager::class)->extract($this->fixturePath('missing.png'));
})->throws(\InvalidArgumentException::class);
