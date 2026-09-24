<?php

namespace App\Services\LegacyImport;

class LegacyImportResult
{
    /**
     * @param  array<int, array{id: int, name: string, phone: ?string, email: ?string, created_wishes: int, updated_wishes: int, skipped_wishes: int}>  $importedUsers
     * @param  array<int, array{id: int, name: string, wishes_count: int}>  $unconfiguredUsers
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public int $totalUsersInFile = 0,
        public int $configuredUsersCount = 0,
        public int $importedUsersCount = 0,
        public int $wishesCreated = 0,
        public int $wishesUpdated = 0,
        public int $wishesSkipped = 0,
        public array $importedUsers = [],
        public array $unconfiguredUsers = [],
        public array $errors = [],
        public bool $isDryRun = false,
    ) {}

    /**
     * Check whether any errors occurred during import.
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
