<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<?string, ?string>
 */
class E164PhoneNumberCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value !== null ? (string) $value : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $cleaned = preg_replace('/[^\d+]/', '', trim((string) $value));

        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+'.substr($cleaned, 2);
        }

        if (str_contains($cleaned, '+')) {
            $cleaned = '+'.str_replace('+', '', $cleaned);
        } elseif (strlen($cleaned) === 8 && ctype_digit($cleaned)) {
            $cleaned = '+45'.$cleaned;
        } elseif ($cleaned !== '') {
            $cleaned = '+'.$cleaned;
        }

        return $cleaned !== '+' ? $cleaned : null;
    }
}
