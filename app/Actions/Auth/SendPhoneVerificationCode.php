<?php

namespace App\Actions\Auth;

use App\Contracts\SmsService;
use App\Models\PhoneVerificationCode;

class SendPhoneVerificationCode
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    /**
     * Generate a 6-digit verification code valid for 10 minutes, persist it, and dispatch via SMS.
     */
    public function execute(string $phone): PhoneVerificationCode
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $dummy = new PhoneVerificationCode(['phone' => $phone]);
        $formattedPhone = $dummy->phone ?? $phone;

        // Invalidate previous verification codes for this phone number
        PhoneVerificationCode::query()->where('phone', $formattedPhone)->delete();

        $verificationCode = PhoneVerificationCode::create([
            'phone' => $formattedPhone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->smsService->sendOtp($verificationCode->phone, $code);

        return $verificationCode;
    }
}
