<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewUserFirstLoginNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Notification;

class SendNewUserFirstLoginNotification
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $user = $event->user;

        if ($user->last_login_at === null) {
            $adminEmail = config('mail.admin_address')
                ?? config('services.admin_notifications.email');

            if (! empty($adminEmail)) {
                Notification::route('mail', $adminEmail)
                    ->notify((new NewUserFirstLoginNotification($user))->afterCommit());
            }
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->saveQuietly();
    }
}
