<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Gavelisten')]
class ResetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    /**
     * Initialize the component with token and email from request.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(): mixed
    {
        $this->email = trim($this->email);

        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [], [
            'email' => __('Email'),
            'password' => __('Password'),
        ]);

        $status = Password::broker()->reset(
            [
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password,
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = User::query()->where('email', $this->email)->first();

            if ($user !== null) {
                Auth::login($user, remember: true);
                session()->regenerate();
            }

            return redirect()->intended('/');
        }

        $this->addError('email', trans($status));

        return null;
    }

    /**
     * Render the reset password view.
     */
    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}
