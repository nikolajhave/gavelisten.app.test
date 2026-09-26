<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\SendPhoneVerificationCode;
use App\Actions\Auth\VerifyPhoneVerificationCode;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Gavelisten')]
class PhoneAuth extends Component
{
    public string $phone = '';

    public string $email = '';

    public string $password = '';

    public bool $remember = true;

    public string $authMode = 'phone';

    public string $code = '';

    public string $name = '';

    public string $step = 'phone';

    public ?string $statusMessage = null;

    /**
     * Switch authentication mode between 'phone' and 'email'.
     */
    public function setAuthMode(string $mode): void
    {
        $this->authMode = in_array($mode, ['phone', 'email'], true) ? $mode : 'phone';
        $this->resetErrorBag();
        $this->statusMessage = null;
    }

    /**
     * Check if email exists and advance to login or registration step.
     */
    public function continueWithEmail(): void
    {
        $this->email = trim($this->email);

        $this->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ], [], [
            'email' => __('Email'),
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip().'|check');

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]));

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $user = User::query()->where('email', $this->email)->first();

        $this->password = '';
        $this->resetErrorBag();
        $this->statusMessage = null;

        if ($user && filled($user->password)) {
            $this->step = 'email_password';
        } else {
            if ($user && filled($user->name)) {
                $this->name = $user->name;
            }
            $this->step = 'email_register';
        }
    }

    /**
     * Log in the user using email and password.
     */
    public function loginWithEmail(): mixed
    {
        $this->email = trim($this->email);

        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'password' => __('Password'),
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip().'|login');

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('password', trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]));

            return null;
        }

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials, remember: $this->remember)) {
            RateLimiter::hit($throttleKey);
            $this->addError('password', trans('auth.failed'));

            return null;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        /** @var User|null $user */
        $user = Auth::user();

        if ($user !== null && empty($user->name)) {
            $this->step = 'name';
            $this->statusMessage = null;
            $this->resetErrorBag();

            return null;
        }

        return redirect()->intended('/');
    }

    /**
     * Register a new user with email and password (or set password for passwordless user).
     */
    public function registerWithEmail(): mixed
    {
        $this->email = trim($this->email);

        $this->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [], [
            'password' => __('Password'),
        ]);

        $existingUser = User::query()->where('email', $this->email)->first();

        if ($existingUser && filled($existingUser->password)) {
            $this->step = 'email_password';
            $this->addError('password', __('An account already exists for this email. Please log in.'));

            return null;
        }

        if ($existingUser) {
            $existingUser->update([
                'password' => Hash::make($this->password),
                'email_verified_at' => $existingUser->email_verified_at ?? now(),
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: $this->remember);
        session()->regenerate();

        if (empty($user->name)) {
            $this->step = 'name';
            $this->password = '';
            $this->statusMessage = null;
            $this->resetErrorBag();

            return null;
        }

        return redirect()->intended('/');
    }

    /**
     * Return to the email entry step.
     */
    public function editEmail(): void
    {
        $this->step = 'phone';
        $this->authMode = 'email';
        $this->password = '';
        $this->statusMessage = null;
        $this->resetErrorBag();
    }

    /**
     * Send password reset link to user's email.
     */
    public function sendPasswordResetLink(): void
    {
        $this->email = trim($this->email);

        $this->validate([
            'email' => ['required', 'string', 'email'],
        ], [], [
            'email' => __('Email'),
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip().'|reset-password');

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('password', trans('passwords.throttled'));

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $status = Password::broker()->sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->resetErrorBag();
            $this->password = '';
            $this->statusMessage = trans($status);
        } else {
            $this->addError('password', trans($status));
        }
    }

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
        $this->statusMessage = __('A 6-digit code has been sent to :phone.', ['phone' => $this->phone]);
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

        if (filled($user->name)) {
            return redirect()->intended('/');
        }

        $this->step = 'name';
        $this->statusMessage = null;
        $this->resetErrorBag();

        return null;
    }

    /**
     * Save the user's name and complete login.
     */
    public function saveName(): mixed
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ], [], [
            'name' => __('Your Name'),
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        if ($user !== null) {
            $user->update([
                'name' => trim($this->name),
            ]);
        }

        return redirect()->intended('/');
    }

    /**
     * Resend a new OTP code to the current phone number.
     */
    public function resendOtp(SendPhoneVerificationCode $sender): void
    {
        $sender->execute($this->phone);

        $this->statusMessage = __('A new verification code has been sent to :phone.', ['phone' => $this->phone]);
        $this->code = '';
        $this->resetErrorBag();
    }

    /**
     * Return to the phone entry step.
     */
    public function editPhone(): void
    {
        $this->step = 'phone';
        $this->authMode = 'phone';
        $this->code = '';
        $this->name = '';
        $this->statusMessage = null;
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.auth.phone-auth')
            ->title('Gavelisten');
    }
}
