<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\SendPhoneVerificationCode;
use App\Actions\Auth\VerifyPhoneVerificationCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sign in with Phone')]
class PhoneAuth extends Component
{
    public string $phone = '';

    public string $code = '';

    public string $step = 'phone';

    public ?string $statusMessage = null;

    /**
     * Send the verification OTP code to the phone number.
     */
    public function sendOtp(SendPhoneVerificationCode $sender): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'min:6', 'max:25'],
        ]);

        $sender->execute($this->phone);

        $this->step = 'otp';
        $this->statusMessage = "A 6-digit code has been sent to {$this->phone}.";
        $this->code = '';
        $this->resetErrorBag();
    }

    /**
     * Verify the OTP code and log the user in.
     */
    public function verifyOtp(VerifyPhoneVerificationCode $verifier): mixed
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $verifier->execute($this->phone, $this->code);

        Auth::login($user, remember: true);

        session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Resend a new OTP code to the current phone number.
     */
    public function resendOtp(SendPhoneVerificationCode $sender): void
    {
        $sender->execute($this->phone);

        $this->statusMessage = "A new verification code has been sent to {$this->phone}.";
        $this->code = '';
        $this->resetErrorBag();
    }

    /**
     * Return to the phone entry step.
     */
    public function editPhone(): void
    {
        $this->step = 'phone';
        $this->code = '';
        $this->statusMessage = null;
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.auth.phone-auth');
    }
}
