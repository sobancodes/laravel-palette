<?php

use Codekinz\LaravelPalette\Rules\HasMinimumColors;
use Illuminate\Http\UploadedFile;

function uploaded_fixture(string $path, string $mime = 'image/png'): UploadedFile
{
    return new UploadedFile($path, basename($path), $mime, null, true);
}

it('passes when an uploaded image has enough representative colors', function () {
    $failed = false;

    (new HasMinimumColors(3))->validate(
        'avatar',
        uploaded_fixture($this->fixturePath('google.png')),
        function () use (&$failed) {
            $failed = true;
        }
    );

    expect($failed)->toBeFalse();
});

it('fails when an uploaded image has too few representative colors', function () {
    $message = null;

    (new HasMinimumColors(1))->validate(
        'avatar',
        uploaded_fixture($this->fixturePath('empty.png')),
        function (string $error) use (&$message) {
            $message = $error;
        }
    );

    expect($message)->toBe('The avatar must contain at least 1 distinct colors.');
});
