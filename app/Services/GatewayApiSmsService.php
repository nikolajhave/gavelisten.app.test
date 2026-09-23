<?php

namespace App\Services;

use App\Contracts\SmsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GatewayApiSmsService implements SmsService
{
    public function __construct(
        protected ?string $token = null,
        protected ?string $sender = null,
        protected ?string $baseUrl = null
    ) {
        $this->token = $token ?? config('services.sms.gatewayapi.token');
        $this->sender = $sender ?? config('services.sms.sender') ?? 'Gavelisten';
        $this->baseUrl = $baseUrl ?? config('services.sms.gatewayapi.endpoint') ?? 'https://messaging.gatewayapi.com/mobile/single';
    }

    /**
     * Send an OTP code to the given phone number via GatewayAPI.
     */
    public function sendOtp(string $phone, string $code): bool
    {
        if (empty($this->token)) {
            Log::error('GatewayAPI SMS token is not configured.');

            return false;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 8) {
            $digits = '45'.$digits;
        }

        $msisdn = (int) $digits;

        if ($msisdn <= 0) {
            Log::error("Invalid recipient phone number for GatewayAPI: {$phone}");

            return false;
        }

        $message = __('Your verification code for Gavelisten is: :code', ['code' => $code]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token '.$this->token,
                'Accept' => 'application/json',
            ])
                ->connectTimeout(3)
                ->timeout(10)
                ->post($this->baseUrl, [
                    'sender' => $this->sender,
                    'message' => $message,
                    'recipient' => $msisdn,
                ]);

            if (! $response->successful()) {
                Log::error('GatewayAPI SMS request failed', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('GatewayAPI SMS exception occurred', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
