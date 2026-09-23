<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialIdentity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again.');
        }

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? $googleUser->getNickname();

        $user = DB::transaction(function () use ($googleId, $email, $name) {
            // Check if social identity already exists
            $socialIdentity = SocialIdentity::query()
                ->where('provider_name', 'google')
                ->where('provider_id', $googleId)
                ->first();

            if ($socialIdentity) {
                $existingUser = $socialIdentity->user;

                if ($name && empty($existingUser->name)) {
                    $existingUser->name = $name;
                }

                if ($email && empty($existingUser->email)) {
                    $existingUser->email = $email;
                    $existingUser->email_verified_at = now();
                }

                $existingUser->save();

                return $existingUser;
            }

            // Check if user already exists by email
            $existingUser = $email ? User::query()->where('email', $email)->first() : null;

            if ($existingUser) {
                if ($name && empty($existingUser->name)) {
                    $existingUser->name = $name;
                }

                if ($existingUser->email_verified_at === null) {
                    $existingUser->email_verified_at = now();
                }

                $existingUser->save();

                $existingUser->socialIdentities()->create([
                    'provider_name' => 'google',
                    'provider_id' => $googleId,
                ]);

                return $existingUser;
            }

            // Create new user and social identity
            $newUser = User::create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
            ]);

            $newUser->socialIdentities()->create([
                'provider_name' => 'google',
                'provider_id' => $googleId,
            ]);

            return $newUser;
        });

        Auth::login($user, remember: true);

        session()->regenerate();

        return redirect()->intended('/');
    }
}
