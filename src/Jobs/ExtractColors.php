<?php

namespace Codekinz\LaravelPalette\Jobs;

use Codekinz\LaravelPalette\Events\ColorsExtracted;
use Codekinz\LaravelPalette\PaletteManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractColors implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?string $disk;

    protected mixed $callback = null;

    public function __construct(
        public string $path,
        callable|string|null $disk = null,
        public int $count = 5,
        public ?string $model = null,
        public int|string|null $modelId = null,
        public string $column = 'dominant_colors'
    ) {
        if (is_callable($disk)) {
            $this->callback = $disk;
            $this->disk = null;

            return;
        }

        $this->disk = $disk;
    }

    public static function dispatch(mixed ...$arguments): mixed
    {
        return app(Dispatcher::class)->dispatch(new static(...$arguments));
    }

    /**
     * @return array<int, string>
     */
    public function handle(PaletteManager $palette): array
    {
        $manager = $this->disk === null
            ? $palette
            : $palette->fromDisk($this->disk);

        $colors = $manager->extract($this->path, $this->count);

        if ($this->callback !== null) {
            call_user_func($this->callback, $colors);
        }

        $this->updateModel($colors);

        event(new ColorsExtracted(
            $this->path,
            $colors,
            $this->disk,
            $this->model,
            $this->modelId,
            $this->column
        ));

        return $colors;
    }

    /**
     * @param  array<int, string>  $colors
     */
    protected function updateModel(array $colors): void
    {
        if ($this->model === null || $this->modelId === null) {
            return;
        }

        if (! is_subclass_of($this->model, Model::class)) {
            return;
        }

        $model = $this->model::query()->find($this->modelId);

        if ($model === null) {
            return;
        }

        $model->forceFill([
            $this->column => $colors,
        ])->save();
    }
}
