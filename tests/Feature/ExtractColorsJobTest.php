<?php

use Codekinz\LaravelPalette\Events\ColorsExtracted;
use Codekinz\LaravelPalette\Jobs\ExtractColors;
use Codekinz\LaravelPalette\Tests\Fixtures\Photo;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

beforeEach(function () {
    Schema::create('photos', function (Blueprint $table) {
        $table->id();
        $table->json('dominant_colors')->nullable();
    });
});

it('extracts colors and updates the model', function () {
    $photo = Photo::query()->create();

    ExtractColors::dispatch(
        $this->fixturePath('google.png'),
        null,
        3,
        Photo::class,
        $photo->getKey(),
        'dominant_colors'
    );

    $photo->refresh();

    expect($photo->dominant_colors)->toHaveCount(3)
        ->and($photo->dominant_colors)->each->toMatch('/^#[0-9A-F]{6}$/');
});

it('dispatches an event after extraction', function () {
    Event::fake();

    ExtractColors::dispatch($this->fixturePath('google.png'), null, 2);

    Event::assertDispatched(ColorsExtracted::class, function (ColorsExtracted $event) {
        return $event->path === $this->fixturePath('google.png')
            && $event->colors !== []
            && count($event->colors) === 2;
    });
});
