<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Livewire\Auth\PhoneAuth;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\PublicWishlist;
use App\Livewire\WishlistManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return app()->call(WishlistManager::class);
    }

    return app()->call(PhoneAuth::class);
})->name('home');

Route::get('/w/{share_token}', PublicWishlist::class)->name('wishlist.public');

Route::middleware('guest')->group(function () {
    Route::get('/login', PhoneAuth::class)->name('login');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/wishlist', WishlistManager::class)->name('wishlist');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
