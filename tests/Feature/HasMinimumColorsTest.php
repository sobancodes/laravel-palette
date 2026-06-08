<?php

use Codekinz\LaravelPalette\Rules\HasMinimumColors;
use Illuminate\Http\UploadedFile;

function uploaded_fixture(string $path, string $mime = 'image/png'): UploadedFile
{
    return new UploadedFile($path, basename($path), $mime, null, true);
}

function uploaded_png(callable $draw): UploadedFile
{
    $path = sys_get_temp_dir().'/palette-validation-'.uniqid().'.png';
    $image = imagecreatetruecolor(10, 10);

    imagesavealpha($image, true);
    $draw($image);
    imagepng($image, $path);
    imagedestroy($image);

    return uploaded_fixture($path);
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
    $file = uploaded_png(function ($image): void {
        $white = imagecolorallocate($image, 255, 255, 255);

        imagefill($image, 0, 0, $white);
    });

    (new HasMinimumColors(2))->validate(
        'avatar',
        $file,
        function (string $error) use (&$message) {
            $message = $error;
        }
    );

    @unlink($file->getRealPath());

    expect($message)->toBe('The avatar must contain at least 2 distinct colors.');
});

it('uses singular color wording when the minimum is one', function () {
    $message = null;
    $file = uploaded_png(function ($image): void {
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

        imagefill($image, 0, 0, $transparent);
    });

    (new HasMinimumColors(1))->validate(
        'avatar',
        $file,
        function (string $error) use (&$message) {
            $message = $error;
        }
    );

    @unlink($file->getRealPath());

    expect($message)->toBe('The avatar must contain at least 1 distinct color.');
});
