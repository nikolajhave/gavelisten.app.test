<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wishlist_id', 'user_id'])]
class WishlistUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wishlist_user';

    /**
     * Get the wishlist associated with this relation.
     *
     * @return BelongsTo<Wishlist, $this>
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * Get the delegated user associated with this relation.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
