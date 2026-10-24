<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialIdentity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google and log them in.
     */
    public function callback(): RedirectResponse
    {
        try {
            $driver = Socialite::driver('google');
            if (method_exists($driver, 'stateless')) {
                $driver = $driver->stateless();
            }
            $googleUser = $driver->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('login')->with('error', __('Google authentication failed. Please try again.'));
        }

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? $googleUser->getNickname();

        // 1. Check if social identity already exists
        $socialIdentity = SocialIdentity::query()
            ->where('provider_name', 'google')
            ->where('provider_id', $googleId)
            ->first();

        if ($socialIdentity && $socialIdentity->user) {
            $existingUser = $socialIdentity->user;

            if ($name && empty($existingUser->name)) {
                $existingUser->name = $name;
            }

            if ($email && empty($existingUser->email)) {
                $existingUser->email = $email;
                $existingUser->email_verified_at = now();
            }

            $existingUser->save();

            Auth::login($existingUser, remember: true);

            session()->regenerate();

            return redirect()->intended('/');
        }

        // 2. Check if user already exists by email
        $existingUser = $email ? User::query()->where('email', $email)->first() : null;

        if ($existingUser) {
            if ($name && empty($existingUser->name)) {
                $existingUser->name = $name;
            }

            if ($existingUser->email_verified_at === null) {
                $existingUser->email_verified_at = now();
            }

            $existingUser->save();

            if ($socialIdentity) {
                $socialIdentity->update(['user_id' => $existingUser->id]);
            } else {
                $existingUser->socialIdentities()->create([
                    'provider_name' => 'google',
                    'provider_id' => $googleId,
                ]);
            }

            Auth::login($existingUser, remember: true);

            session()->regenerate();

            return redirect()->intended('/');
        }

        // 3. User does not exist: prompt confirmation before creating profile
        session()->put('google_auth_pending', [
            'google_id' => $googleId,
            'email' => $email,
            'name' => $name,
        ]);

        return redirect()->route('login');
    }
}
