<?php

namespace App\Actions\Auth;

use App\Models\PhoneVerificationCode;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class VerifyPhoneVerificationCode
{
    /**
     * Verify the phone code and retrieve/create the verified user.
     *
     * @throws ValidationException
     */
    public function execute(string $phone, string $code): User
    {
        $dummy = new PhoneVerificationCode(['phone' => $phone]);
        $formattedPhone = $dummy->phone ?? $phone;

        $verificationCode = PhoneVerificationCode::query()
            ->where('phone', $formattedPhone)
            ->where('code', trim($code))
            ->active()
            ->latest('id')
            ->first();

        if (! $verificationCode) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or has expired.'),
            ]);
        }

        // Clean up verification codes for this phone
        PhoneVerificationCode::query()->where('phone', $formattedPhone)->delete();

        /** @var User $user */
        $user = User::firstOrCreate(
            ['phone' => $formattedPhone],
            ['phone_verified_at' => now()]
        );

        if ($user->phone_verified_at === null) {
            $user->update(['phone_verified_at' => now()]);
        }

        return $user;
    }
}
