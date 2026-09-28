<?php

namespace App\Console\Commands;

use App\Services\ChangelogSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('changelog:sync {--path= : Custom path to changelogs directory}')]
#[Description('Sync changelog markdown files from the repository into the database')]
class SyncChangelogsCommand extends Command
{
    /**
     * Alternative command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = [
        'changelogs:sync',
        'changelog:import',
    ];

    /**
     * Execute the console command.
     */
    public function handle(ChangelogSyncService $syncService): int
    {
        $customPath = $this->option('path');
        $directory = is_string($customPath) && $customPath !== '' ? $customPath : null;

        $targetDir = $directory ?: $syncService->defaultDirectory();
        $this->components->info("Scanning for changelog files in [{$targetDir}]...");

        $result = $syncService->sync($directory);

        if ($result['synced'] === 0) {
            $this->components->warn('No changelog markdown files found to sync.');

            return self::SUCCESS;
        }

        $rows = array_map(function (array $item): array {
            return [
                $item['file'],
                $item['version'] ?? '-',
                $item['title'],
                $item['published_at'] ?? 'Draft',
                match ($item['action']) {
                    'created' => 'Created',
                    'updated' => 'Updated',
                    default => $item['action'],
                },
            ];
        }, $result['items']);

        $this->table(
            ['File', 'Version', 'Title', 'Published At', 'Action'],
            $rows
        );

        $this->components->info("Synced {$result['synced']} changelog(s): {$result['created']} created, {$result['updated']} updated.");

        return self::SUCCESS;
    }
}
