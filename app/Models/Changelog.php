<?php

namespace App\Models;

use Database\Factories\ChangelogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['version', 'title', 'content', 'published_at'])]
class Changelog extends Model
{
    /** @use HasFactory<ChangelogFactory> */
    use HasFactory;

    /**
     * Scope a query to only include published changelogs.
     *
     * @param  Builder<Changelog>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to order by latest published.
     *
     * @param  Builder<Changelog>  $query
     */
    public function scopeLatestPublished(Builder $query): void
    {
        $query->published()
            ->latest('published_at')
            ->latest('id');
    }

    /**
     * Determine if the changelog is published.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Get the formatted HTML content using Markdown.
     *
     * @return Attribute<string, never>
     */
    protected function formattedContent(): Attribute
    {
        return Attribute::get(function (mixed $value, array $attributes): string {
            $rawContent = $attributes['content'] ?? $this->content ?? '';

            return Str::markdown((string) $rawContent);
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
            'published_at' => 'datetime',
        ];
    }
}
