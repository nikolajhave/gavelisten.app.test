<?php

namespace App\Models;

use App\Casts\E164PhoneNumberCast;
use Database\Factories\PhoneVerificationCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['phone', 'code', 'expires_at'])]
class PhoneVerificationCode extends Model
{
    /** @use HasFactory<PhoneVerificationCodeFactory> */
    use HasFactory;

    /**
     * Scope a query to only include non-expired verification codes.
     *
     * @param  Builder<PhoneVerificationCode>  $query
     * @return Builder<PhoneVerificationCode>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Determine if the verification code has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine if the verification code is still valid.
     */
    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'phone' => E164PhoneNumberCast::class,
            'expires_at' => 'datetime',
        ];
    }
}
