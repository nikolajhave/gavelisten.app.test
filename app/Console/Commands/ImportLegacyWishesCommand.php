<?php

namespace App\Console\Commands;

use App\Services\LegacyImport\LegacyDataImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('import:legacy-wishes {--file= : Path to legacy JSON data file} {--user= : Specific user name or legacy ID to import} {--include-deleted : Include soft-deleted wishes} {--dry-run : Simulate the import without saving to database}')]
#[Description('Import users and wishes from legacy wishData.json export')]
class ImportLegacyWishesCommand extends Command
{
    /**
     * Alternative command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = [
        'legacy:import',
    ];

    /**
     * Execute the console command.
     */
    public function handle(LegacyDataImporter $importer): int
    {
        $file = $this->option('file');
        $targetUser = $this->option('user');
        $includeDeleted = (bool) $this->option('include-deleted');
        $dryRun = (bool) $this->option('dry-run');

        $this->components->info(
            $dryRun
                ? 'Starting Legacy Data Import (DRY RUN - No changes will be saved)...'
                : 'Starting Legacy Data Import...'
        );

        $result = $importer->import(
            filePath: is_string($file) ? $file : null,
            targetUser: is_string($targetUser) ? $targetUser : null,
            includeDeleted: $includeDeleted,
            dryRun: $dryRun,
        );

        if ($result->hasErrors()) {
            foreach ($result->errors as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        if (! empty($result->importedUsers)) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=cyan;options=bold>Imported Users</>', '<fg=cyan;options=bold>Wishes (New / Updated / Skipped)</>');

            $userRows = [];
            foreach ($result->importedUsers as $user) {
                $userRows[] = [
                    $user['name'],
                    $user['phone'] ?? '-',
                    $user['email'] ?? '-',
                    $user['legacy_id'] ?? '-',
                    $user['created_wishes'],
                    $user['updated_wishes'],
                    $user['skipped_wishes'],
                ];
            }

            $this->table(
                ['Name', 'Phone', 'Email', 'Legacy ID', 'Created', 'Updated', 'Skipped'],
                $userRows
            );
        } else {
            $this->components->warn('No users were matched for import. Check config/legacy_import.php or use --user flag.');
        }

        if (! empty($result->unconfiguredUsers)) {
            $this->newLine();
            $unconfiguredCount = count($result->unconfiguredUsers);
            $this->components->warn(
                "{$unconfiguredCount} legacy user(s) in the data file are not configured in config/legacy_import.php:"
            );

            $sampleUnconfigured = array_slice($result->unconfiguredUsers, 0, 10);
            $unconfiguredRows = array_map(fn ($u) => [
                $u['name'],
                $u['id'],
                $u['wishes_count'],
            ], $sampleUnconfigured);

            $this->table(['Legacy Name', 'Legacy ID', 'Wishes Count'], $unconfiguredRows);

            if ($unconfiguredCount > 10) {
                $remaining = $unconfiguredCount - 10;
                $this->line("  ... and {$remaining} more unconfigured users.");
            }

            $this->line('  <fg=gray>To import them, add their phone or email to config/legacy_import.php.</>');
        }

        $this->newLine();
        $this->components->twoColumnDetail('Total legacy users in file', (string) $result->totalUsersInFile);
        $this->components->twoColumnDetail('Configured users', (string) $result->configuredUsersCount);
        $this->components->twoColumnDetail('Imported users', (string) $result->importedUsersCount);
        $this->components->twoColumnDetail('Wishes created', (string) $result->wishesCreated);
        $this->components->twoColumnDetail('Wishes updated', (string) $result->wishesUpdated);
        $this->components->twoColumnDetail('Wishes skipped (deleted)', (string) $result->wishesSkipped);

        $this->newLine();
        if ($dryRun) {
            $this->components->info('Dry run completed! No database changes were persisted.');
        } else {
            $this->components->info('Legacy import completed successfully!');
        }

        return self::SUCCESS;
    }
}
