# Laravel Palette

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codekinz/laravel-palette.svg?style=flat-square)](https://packagist.org/packages/codekinz/laravel-palette)
[![Tests](https://img.shields.io/github/actions/workflow/status/codekinz/laravel-palette/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/codekinz/laravel-palette/actions/workflows/tests.yml)
[![License](https://img.shields.io/packagist/l/codekinz/laravel-palette.svg?style=flat-square)](https://packagist.org/packages/codekinz/laravel-palette)

Extract dominant colors from images in Laravel. A clean wrapper around [league/color-extractor](https://github.com/thephpleague/color-extractor).

```
 ┌─────────────────────┐       ┌──────────────────────────────────────────┐
 │                     │       │                                          │
 │    Input Image      │──────>│  Palette::extract('photo.jpg')           │
 │                     │       │                                          │
 └─────────────────────┘       │  ['#E84C3D', '#2D3436', '#0A74DA',      │
                               │   '#F1C30F', '#1B9B5E']                 │
                               │                                          │
                               │   ██  ██  ██  ██  ██                    │
                               └──────────────────────────────────────────┘
```

## Installation

```bash
composer require codekinz/laravel-palette
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=palette-config
```

## Quick Start

```php
use Codekinz\LaravelPalette\Facades\Palette;

$colors = Palette::extract('/path/to/image.png');
// ['#E84C3D', '#2D3436', '#0A74DA', '#F1C30F', '#1B9B5E']
```

That's it. Five dominant colors as hex strings.

## API

### `extract(string $path, ?int $count = null): array`

Returns the most **representative** (perceptually distinct) colors from the image.

```php
$colors = Palette::extract('/path/to/image.png', 3);
// ['#E84C3D', '#2D3436', '#0A74DA']
```

### `mostUsed(string $path, ?int $count = null): array`

Returns colors sorted by **pixel frequency** (most pixels first).

```php
$colors = Palette::mostUsed('/path/to/image.png', 5);
// ['#FFFFFF', '#000000', '#E84C3D', '#2D3436', '#0A74DA']
```

### `colors(string $path, ?int $count = null): array`

Uses whichever method is set in your config (`representative` or `most_used`).

```php
$colors = Palette::colors('/path/to/image.png');
```

### `palette(string $path): array`

Returns every color in the image with its pixel count.

```php
$palette = Palette::palette('/path/to/image.png');
// ['#E84C3D' => 14500, '#2D3436' => 8200, '#0A74DA' => 6100, ...]
```

### `fromDisk(string $disk): self`

Load images from a Laravel Storage disk instead of an absolute path.

```php
$colors = Palette::fromDisk('s3')->extract('uploads/photo.jpg', 5);
```

### `withBackground(string $hexColor): self`

Set a background color for blending transparent pixels. By default, transparent pixels are discarded.

```php
$colors = Palette::withBackground('#FFFFFF')->extract('logo.png');
```

Both `fromDisk` and `withBackground` return a new instance — they don't mutate the singleton, so they're safe to chain in any order without side effects.

## Configuration

```php
// config/palette.php

return [
    // Default number of colors to extract
    'count' => 5,

    // Default extraction method: 'representative' or 'most_used'
    'method' => 'representative',

    // Background color for transparent images (null = discard transparent pixels)
    'background' => null,

    // Cache settings
    'cache' => [
        'enabled' => false,
        'ttl' => 60 * 24, // minutes (24 hours)
        'prefix' => 'palette:',
    ],

    // Default Storage disk (null = use absolute file paths)
    'disk' => null,
];
```

## Caching

Image processing reads every pixel — it's expensive for large images. Enable caching to avoid re-processing the same image on repeated requests:

```php
// config/palette.php
'cache' => [
    'enabled' => true,
    'ttl' => 60 * 24,
    'prefix' => 'palette:',
],
```

The cache key accounts for the image path, extraction method, color count, disk, and background color, so different calls to the same image don't collide.

If you're extracting once and storing the result (via the Job or the Eloquent cast), you don't need caching.

## Queue Support

Extract colors in the background with the included job:

```php
use Codekinz\LaravelPalette\Jobs\ExtractColors;

// Basic dispatch
ExtractColors::dispatch('/path/to/image.png');

// With all options
ExtractColors::dispatch(
    path: '/path/to/image.png',
    disk: 's3',
    count: 5,
    model: Photo::class,
    modelId: $photo->id,
    column: 'dominant_colors'
);
```

When you pass a model class and ID, the job automatically updates that column with the extracted colors after processing.

The job fires a `ColorsExtracted` event when it finishes:

```php
use Codekinz\LaravelPalette\Events\ColorsExtracted;

// In a listener or EventServiceProvider
Event::listen(ColorsExtracted::class, function (ColorsExtracted $event) {
    // $event->path
    // $event->colors
    // $event->disk
    // $event->model
    // $event->modelId
    // $event->column
});
```

## Eloquent Integration

### Cast

Store color arrays as JSON in the database with the included cast:

```php
use Codekinz\LaravelPalette\Casts\ColorPalette;

class Photo extends Model
{
    protected $casts = [
        'dominant_colors' => ColorPalette::class,
    ];
}
```

```php
$photo->dominant_colors = ['#E84C3D', '#2D3436', '#0A74DA'];
$photo->save();

$photo->dominant_colors; // ['#E84C3D', '#2D3436', '#0A74DA']
```

### Trait

Add the `ExtractsPalette` trait for a convenient `extractColors` method that dispatches the queue job:

```php
use Codekinz\LaravelPalette\Concerns\ExtractsPalette;

class Photo extends Model
{
    use ExtractsPalette;

    protected function paletteImagePath(): ?string
    {
        return $this->image_path;
    }
}
```

```php
// Dispatches ExtractColors job, stores result in 'dominant_colors' column
$photo->extractColors();

// Or override any defaults
$photo->extractColors(
    path: 'custom/path.jpg',
    disk: 's3',
    count: 8,
    column: 'colors'
);
```

Override `paletteImagePath()` to tell the trait which attribute holds your image path.

## Validation

Reject uploads that don't have enough visual variety (e.g. solid-color or near-blank images):

```php
use Codekinz\LaravelPalette\Rules\HasMinimumColors;

$request->validate([
    'avatar' => ['required', 'image', new HasMinimumColors(3)],
]);
```

This extracts representative colors from the uploaded file and fails if fewer than the minimum are found.

## Without the Facade

You can resolve `PaletteManager` directly from the container:

```php
use Codekinz\LaravelPalette\PaletteManager;

$manager = app(PaletteManager::class);
$colors = $manager->extract('/path/to/image.png', 5);
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
