<?php

namespace App\Contracts;

interface SmsService
{
    /**
     * Send an OTP code to the given phone number.
     */
    public function sendOtp(string $phone, string $code): bool;
}
