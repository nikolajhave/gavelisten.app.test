<?php

namespace App\Services;

use App\Contracts\SmsService;
use Illuminate\Support\Facades\Log;

class LogSmsService implements SmsService
{
    /**
     * Send an OTP code to the given phone number by logging it.
     */
    public function sendOtp(string $phone, string $code): bool
    {
        Log::info("SMS OTP for {$phone}: {$code}");

        return true;
    }
}
