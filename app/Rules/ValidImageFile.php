<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidImageFile implements ValidationRule
{
    protected array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg', 'avif', 'heic', 'heif'
    ];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!($value instanceof UploadedFile) || !$value->isValid()) {
            $fail("The {$attribute} must be a valid uploaded file.");
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension() ?: $value->extension());

        if (!in_array($extension, $this->allowedExtensions, true)) {
            $fail("The {$attribute} must be an image of type: " . implode(', ', $this->allowedExtensions) . ".");
        }
    }
}
