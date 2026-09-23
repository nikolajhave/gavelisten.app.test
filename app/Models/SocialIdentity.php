<?php

namespace App\Models;

use Database\Factories\SocialIdentityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'provider_name', 'provider_id'])]
class SocialIdentity extends Model
{
    /** @use HasFactory<SocialIdentityFactory> */
    use HasFactory;

    /**
     * Get the user that owns the social identity.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
