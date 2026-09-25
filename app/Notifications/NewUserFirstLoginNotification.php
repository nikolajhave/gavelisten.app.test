<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserFirstLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $displayName = $this->user->name
            ?: ($this->user->email
            ?: ($this->user->phone
            ?: __('Unknown user')));

        $message = (new MailMessage)
            ->subject(__('New user signup / first login: :name', ['name' => $displayName]))
            ->greeting(__('Hello!'))
            ->line(__('A user has signed up or logged in for the first time on :app.', ['app' => config('app.name', 'Gavelisten')]))
            ->line(__('Name: :name', ['name' => $this->user->name ?: __('Not provided')]))
            ->line(__('Email: :email', ['email' => $this->user->email ?: __('Not provided')]))
            ->line(__('Phone: :phone', ['phone' => $this->user->phone ?: __('Not provided')]));

        if ($this->user->legacy_id !== null) {
            $message->line(__('Legacy ID: :id', ['id' => $this->user->legacy_id]));
        }

        $firstWishlist = $this->user->wishlists()->first();

        if ($firstWishlist !== null && filled($firstWishlist->share_token)) {
            $message->action(__('View Wishlist'), route('wishlist.public', $firstWishlist->share_token));
        } else {
            $message->action(__('Go to :app', ['app' => config('app.name', 'Gavelisten')]), url('/'));
        }

        return $message;
    }
}
