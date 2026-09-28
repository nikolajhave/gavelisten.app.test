<?php

namespace App\Console\Commands;

use App\Models\Changelog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('changelog:list {--all : Include all entries including drafts}')]
#[Description('List all changelog entries')]
class ListChangelogsCommand extends Command
{
    /**
     * Alternative command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = [
        'changelogs',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $showAll = (bool) $this->option('all');

        $query = $showAll ? Changelog::query() : Changelog::published();
        $changelogs = $query->latest('created_at')->get();

        if ($changelogs->isEmpty()) {
            $this->components->info($showAll ? 'No changelog entries found.' : 'No published changelog entries found (use --all to see drafts).');

            return self::SUCCESS;
        }

        $rows = $changelogs->map(fn (Changelog $c) => [
            $c->id,
            $c->version ?? '-',
            $c->title,
            $c->published_at
                ? ($c->published_at->isFuture()
                    ? 'Scheduled ('.$c->published_at->format('Y-m-d H:i').')'
                    : 'Published ('.$c->published_at->format('Y-m-d H:i').')')
                : 'Draft',
            $c->created_at?->format('Y-m-d H:i') ?? '-',
        ])->toArray();

        $this->table(
            ['ID', 'Version', 'Title', 'Status', 'Created At'],
            $rows
        );

        return self::SUCCESS;
    }
}
