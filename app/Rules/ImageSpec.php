<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Checks an uploaded image against one of the specs in config/media.php:
 * aspect ratio (within the configured tolerance) and minimum dimensions.
 */
class ImageSpec implements ValidationRule
{
    protected array $spec;

    public function __construct(string $key)
    {
        $this->spec = config("media.$key");
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            return;
        }

        $size = @getimagesize($value->getRealPath());
        if (!$size) {
            $fail('The :attribute must be a valid image.');
            return;
        }

        [$width, $height] = $size;
        $rw = $this->spec['width'];
        $rh = $this->spec['height'];
        $name = $value->getClientOriginalName();

        $expected = $rw / $rh;
        $actual = $width / $height;
        if (abs($actual - $expected) / $expected > config('media.tolerance')) {
            $fail("\"$name\" is {$width} × {$height} px. It must have the same shape as the {$rw} × {$rh} px frame on the website (recommended {$this->spec['recommended']}).");
            return;
        }

        if ($width < $this->spec['min_width'] || $height < $this->spec['min_height']) {
            $fail("\"$name\" is {$width} × {$height} px. It must be at least {$this->spec['min_width']} × {$this->spec['min_height']} px (recommended {$this->spec['recommended']}).");
        }
    }
}
