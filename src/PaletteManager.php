<?php

namespace Codekinz\LaravelPalette;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use Throwable;

class PaletteManager
{
    protected ?string $disk;

    protected ?int $background;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = []
    ) {
        $this->disk = $config['disk'] ?? null;
        $this->background = $this->resolveBackground($config['background'] ?? null);
    }

    /**
     * @return list<string>
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function colors(string $path, ?int $count = null): array
    {
        return $this->resolveMethod() === 'most_used'
            ? $this->mostUsed($path, $count)
            : $this->extract($path, $count);
    }

    /**
     * @return list<string>
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function extract(string $path, ?int $count = null): array
    {
        $count = $this->resolveCount($count);

        return $this->remember($path, 'representative', $count, function () use ($path, $count): array {
            $extractor = new ColorExtractor($this->buildPalette($path));

            return $this->formatColors($extractor->extract($count));
        });
    }

    /**
     * @return list<string>
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function mostUsed(string $path, ?int $count = null): array
    {
        $count = $this->resolveCount($count);

        return $this->remember($path, 'most_used', $count, function () use ($path, $count): array {
            return $this->formatColorMap($this->buildPalette($path)->getMostUsedColors($count));
        });
    }

    /**
     * @return array<string, int>
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function palette(string $path): array
    {
        return $this->remember($path, 'palette', 0, function () use ($path): array {
            $colors = [];

            foreach ($this->buildPalette($path)->getMostUsedColors() as $color => $count) {
                $colors[Color::fromIntToHex($color)] = $count;
            }

            return $colors;
        });
    }

    public function fromDisk(string $disk): self
    {
        $manager = clone $this;
        $manager->disk = $disk;

        return $manager;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withBackground(string $hexColor): self
    {
        $manager = clone $this;
        $manager->background = Color::fromHexToInt($hexColor);

        return $manager;
    }

    protected function buildPalette(string $path): Palette
    {
        $resolvedPath = $this->resolveFilePath($path);

        if ($this->background === null) {
            return Palette::fromFilename($resolvedPath);
        }

        return Palette::fromFilename($resolvedPath, $this->background);
    }

    protected function resolveFilePath(string $path): string
    {
        $resolvedPath = $this->disk === null
            ? $path
            : Storage::disk($this->disk)->path($path);

        if (! is_file($resolvedPath)) {
            throw new InvalidArgumentException("Image file does not exist at path [{$path}].");
        }

        return $resolvedPath;
    }

    protected function resolveCount(?int $count): int
    {
        return $count ?? (int) ($this->config['count'] ?? 5);
    }

    protected function resolveMethod(): string
    {
        return (string) ($this->config['method'] ?? 'representative');
    }

    protected function resolveBackground(mixed $background): ?int
    {
        if ($background === null) {
            return null;
        }

        return Color::fromHexToInt((string) $background);
    }

    /**
     * @param  list<int>  $colors
     * @return list<string>
     */
    protected function formatColors(array $colors): array
    {
        return array_values(array_map(
            fn (int $color): string => Color::fromIntToHex($color),
            $colors
        ));
    }

    /**
     * @param  array<int, int>  $colors
     * @return list<string>
     */
    protected function formatColorMap(array $colors): array
    {
        return array_values(array_map(
            fn (int $color): string => Color::fromIntToHex($color),
            array_keys($colors)
        ));
    }

    /**
     * @template TValue
     *
     * @param  callable(): TValue  $callback
     * @return TValue
     */
    protected function remember(string $path, string $method, int $count, callable $callback): mixed
    {
        $cache = $this->config['cache'] ?? [];

        if (! ($cache['enabled'] ?? false)) {
            return $callback();
        }

        return Cache::remember(
            $this->getCacheKey($path, $method, $count),
            now()->addMinutes((int) ($cache['ttl'] ?? 1440)),
            $callback
        );
    }

    protected function getCacheKey(string $path, string $method, int $count): string
    {
        $cache = $this->config['cache'] ?? [];
        $prefix = (string) ($cache['prefix'] ?? 'palette:');

        return $prefix.sha1(implode('|', [
            $this->disk ?? '',
            $path,
            $method,
            $count,
            $this->background ?? '',
        ]));
    }
}
