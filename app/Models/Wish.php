<?php

namespace App\Models;

use Database\Factories\WishFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wishlist_id', 'legacy_id', 'title', 'description', 'url', 'price', 'sort_order'])]
class Wish extends Model
{
    /** @use HasFactory<WishFactory> */
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
    ];

    /**
     * Get the wishlist that owns the wish.
     *
     * @return BelongsTo<Wishlist, $this>
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * Get the formatted price string (e.g. '260 kr' or '260,50 kr').
     *
     * @return Attribute<string|null, never>
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(function (mixed $value, array $attributes): ?string {
            $rawPrice = $attributes['price'] ?? $this->price;
            if ($rawPrice === null || $rawPrice === '') {
                return null;
            }

            $price = (float) $rawPrice;
            $decimals = (fmod(round($price * 100), 100) == 0.0) ? 0 : 2;

            return number_format($price, $decimals, ',', '.').' '.__('kr');
        });
    }

    /**
     * Get the domain / host string from the URL for clean display.
     *
     * @return Attribute<string|null, never>
     */
    protected function urlDomain(): Attribute
    {
        return Attribute::get(function (mixed $value, array $attributes): ?string {
            $url = $attributes['url'] ?? $this->url;
            if ($url === null || $url === '') {
                return null;
            }

            $host = parse_url((string) $url, PHP_URL_HOST);
            if (! $host) {
                $host = parse_url('https://'.(string) $url, PHP_URL_HOST);
            }

            return $host ?: (string) $url;
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
            'legacy_id' => 'integer',
        ];
    }
}
