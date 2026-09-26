<?php

namespace App\Models;

use App\Casts\E164PhoneNumberCast;
use App\Notifications\ResetPasswordNotification;
use App\Observers\UserObserver;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[ObservedBy(UserObserver::class)]
#[Fillable(['name', 'email', 'phone', 'legacy_id', 'email_verified_at', 'phone_verified_at', 'last_login_at', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the social identities associated with the user.
     *
     * @return HasMany<SocialIdentity, $this>
     */
    public function socialIdentities(): HasMany
    {
        return $this->hasMany(SocialIdentity::class);
    }

    /**
     * Get the wishlists owned by the user.
     *
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the friends associated with the user.
     *
     * @return BelongsToMany<User, $this>
     */
    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
            ->withTimestamps();
    }

    /**
     * Get the users who added this user as a friend.
     *
     * @return BelongsToMany<User, $this>
     */
    public function friendedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Add a user as a friend.
     */
    public function addFriend(User $friend): void
    {
        if ($this->id !== $friend->id) {
            $this->friends()->syncWithoutDetaching([$friend->id]);
        }
    }

    /**
     * Remove a user from friends.
     */
    public function removeFriend(User $friend): void
    {
        $this->friends()->detach($friend->id);
    }

    /**
     * Check if the user has added another user as a friend.
     */
    public function isFriendWith(User $user): bool
    {
        return $this->friends()->where('friend_id', $user->id)->exists();
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): string
    {
        return (string) ($this->password ?? '');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'phone' => E164PhoneNumberCast::class,
            'password' => 'hashed',
            'legacy_id' => 'integer',
        ];
    }
}
