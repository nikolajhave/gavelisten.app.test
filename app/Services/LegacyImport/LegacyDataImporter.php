<?php

namespace App\Services\LegacyImport;

use App\Models\User;
use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class LegacyDataImporter
{
    /**
     * Import legacy wish data from JSON.
     *
     * @param  array<string|int, mixed>|null  $customUserConfig
     */
    public function import(
        ?string $filePath = null,
        ?string $targetUser = null,
        bool $includeDeleted = false,
        bool $dryRun = false,
        ?array $customUserConfig = null,
    ): LegacyImportResult {
        $path = $filePath ?: config('legacy_import.data_path', resource_path('wishData.json'));
        $result = new LegacyImportResult(isDryRun: $dryRun);

        if (! File::exists($path)) {
            $result->errors[] = "Data file not found at: {$path}";

            return $result;
        }

        $rawJson = File::get($path);
        $data = json_decode($rawJson, true);

        if (! is_array($data)) {
            $result->errors[] = "Invalid or unparseable JSON in data file: {$path}";

            return $result;
        }

        $userConfig = $customUserConfig ?? (array) config('legacy_import.users', []);
        $result->configuredUsersCount = count($userConfig);

        // Collect all users across all families
        $allLegacyUsers = [];
        foreach ($data as $family) {
            foreach ($family['users'] ?? [] as $legacyUser) {
                if (is_array($legacyUser)) {
                    $allLegacyUsers[] = $legacyUser;
                }
            }
        }

        $result->totalUsersInFile = count($allLegacyUsers);

        DB::beginTransaction();

        try {
            foreach ($allLegacyUsers as $legacyUser) {
                $legacyId = isset($legacyUser['id']) ? (int) $legacyUser['id'] : null;
                $legacyName = isset($legacyUser['name']) ? trim((string) $legacyUser['name']) : '';

                // Target user filtering if specified
                if ($targetUser !== null && $targetUser !== '') {
                    $matchesTarget = (string) $legacyId === $targetUser
                        || mb_strtolower($legacyName) === mb_strtolower($targetUser);

                    if (! $matchesTarget) {
                        continue;
                    }
                }

                $configured = $this->resolveConfiguredUser($legacyUser, $userConfig);

                if ($configured === null) {
                    $result->unconfiguredUsers[] = [
                        'id' => $legacyId ?? 0,
                        'name' => $legacyName,
                        'wishes_count' => count($legacyUser['wishes'] ?? []),
                    ];

                    continue;
                }

                $user = $this->findOrCreateUser($configured);
                $wishlist = $this->resolveWishlist($user);

                $createdCount = 0;
                $updatedCount = 0;
                $skippedCount = 0;

                $wishes = (array) ($legacyUser['wishes'] ?? []);
                $sortIndex = 0;

                foreach ($wishes as $rawWish) {
                    if (! is_array($rawWish)) {
                        continue;
                    }

                    $isDeleted = ! empty($rawWish['deleted']);
                    if ($isDeleted && ! $includeDeleted) {
                        $skippedCount++;
                        $result->wishesSkipped++;

                        continue;
                    }

                    $legacyWishId = isset($rawWish['id']) ? (int) $rawWish['id'] : null;
                    $title = trim((string) ($rawWish['name'] ?? ''));

                    // Handle empty titles gracefully
                    if ($title === '') {
                        $cleanedDesc = $this->cleanDescription($rawWish['text'] ?? null);
                        if ($cleanedDesc !== null) {
                            $lines = explode("\n", $cleanedDesc);
                            $title = trim($lines[0]);
                        }

                        if ($title === '') {
                            $title = __('Untitled Wish');
                        }
                    }

                    $description = $this->cleanDescription($rawWish['text'] ?? null);
                    $url = ! empty($rawWish['link']) ? trim((string) $rawWish['link']) : null;
                    $price = $this->parsePrice($rawWish['price'] ?? null);

                    $wishData = [
                        'title' => $title,
                        'description' => $description,
                        'url' => $url,
                        'price' => $price,
                        'sort_order' => $sortIndex++,
                    ];

                    $existingWish = null;
                    if ($legacyWishId !== null) {
                        $existingWish = Wish::query()
                            ->where('wishlist_id', $wishlist->id)
                            ->where('legacy_id', $legacyWishId)
                            ->first();
                    }

                    if ($existingWish) {
                        $existingWish->update($wishData);
                        $updatedCount++;
                        $result->wishesUpdated++;
                    } else {
                        $wishlist->wishes()->create(array_merge($wishData, [
                            'legacy_id' => $legacyWishId,
                        ]));
                        $createdCount++;
                        $result->wishesCreated++;
                    }
                }

                $result->importedUsersCount++;
                $result->importedUsers[] = [
                    'id' => $user->id,
                    'legacy_id' => $user->legacy_id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'created_wishes' => $createdCount,
                    'updated_wishes' => $updatedCount,
                    'skipped_wishes' => $skippedCount,
                ];
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Throwable $e) {
            DB::rollBack();
            $result->errors[] = 'Import failed with exception: '.$e->getMessage();
        }

        return $result;
    }

    /**
     * Resolve configuration mapping for a legacy user.
     *
     * @param  array<string, mixed>  $legacyUser
     * @param  array<string|int, mixed>  $userConfig
     * @return array{name: string, phone: ?string, email: ?string, legacy_id: ?int}|null
     */
    public function resolveConfiguredUser(array $legacyUser, array $userConfig): ?array
    {
        $legacyId = isset($legacyUser['id']) ? (int) $legacyUser['id'] : null;
        $legacyName = isset($legacyUser['name']) ? trim((string) $legacyUser['name']) : '';

        $matchedConfig = null;

        // 1. Direct match by key
        if ($legacyId !== null && array_key_exists($legacyId, $userConfig)) {
            $matchedConfig = $userConfig[$legacyId];
        } elseif ($legacyId !== null && array_key_exists((string) $legacyId, $userConfig)) {
            $matchedConfig = $userConfig[(string) $legacyId];
        } else {
            // Match by case-insensitive key name
            foreach ($userConfig as $key => $val) {
                if (is_string($key) && mb_strtolower(trim($key)) === mb_strtolower($legacyName)) {
                    $matchedConfig = $val;
                    break;
                }
            }
        }

        // 2. Value-based match if list of items
        if ($matchedConfig === null) {
            foreach ($userConfig as $entry) {
                if (is_array($entry)) {
                    if (isset($entry['legacy_id']) && (int) $entry['legacy_id'] === $legacyId) {
                        $matchedConfig = $entry;
                        break;
                    }
                    if (isset($entry['name']) && mb_strtolower(trim((string) $entry['name'])) === mb_strtolower($legacyName)) {
                        $matchedConfig = $entry;
                        break;
                    }
                }
            }
        }

        if ($matchedConfig === null) {
            return null;
        }

        // Parse matched configuration into standard format
        $name = $legacyName;
        $phone = null;
        $email = null;

        if (is_string($matchedConfig)) {
            $trimmed = trim($matchedConfig);
            if (str_contains($trimmed, '@')) {
                $email = $trimmed;
            } else {
                $phone = $trimmed;
            }
        } elseif (is_array($matchedConfig)) {
            $name = ! empty($matchedConfig['name']) ? (string) $matchedConfig['name'] : $legacyName;
            $phone = ! empty($matchedConfig['phone']) ? (string) $matchedConfig['phone'] : null;
            $email = ! empty($matchedConfig['email']) ? (string) $matchedConfig['email'] : null;
        }

        return [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'legacy_id' => $legacyId,
        ];
    }

    /**
     * Find or create the user record.
     *
     * @param  array{name: string, phone: ?string, email: ?string, legacy_id: ?int}  $configured
     */
    protected function findOrCreateUser(array $configured): User
    {
        $user = null;
        $normalizedPhone = $this->normalizePhone($configured['phone']);

        if ($configured['legacy_id'] !== null) {
            $user = User::query()->where('legacy_id', $configured['legacy_id'])->first();
        }

        if (! $user && ! empty($normalizedPhone)) {
            $user = User::query()->where('phone', $normalizedPhone)->first();
        }

        if (! $user && ! empty($configured['email'])) {
            $user = User::query()->where('email', $configured['email'])->first();
        }

        if ($user) {
            $updates = [];
            if ($user->legacy_id === null && $configured['legacy_id'] !== null) {
                $updates['legacy_id'] = $configured['legacy_id'];
            }
            if ($user->phone === null && ! empty($normalizedPhone)) {
                $updates['phone'] = $normalizedPhone;
                if ($user->phone_verified_at === null) {
                    $updates['phone_verified_at'] = now();
                }
            }
            if ($user->email === null && ! empty($configured['email'])) {
                $updates['email'] = $configured['email'];
                if ($user->email_verified_at === null) {
                    $updates['email_verified_at'] = now();
                }
            }
            if (! empty($updates)) {
                $user->update($updates);
            }

            return $user;
        }

        return User::create([
            'name' => $configured['name'],
            'phone' => $normalizedPhone,
            'email' => $configured['email'],
            'legacy_id' => $configured['legacy_id'],
            'phone_verified_at' => ! empty($normalizedPhone) ? now() : null,
            'email_verified_at' => ! empty($configured['email']) ? now() : null,
        ]);
    }

    /**
     * Normalize a phone number to standard E.164.
     */
    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+'.substr($cleaned, 2);
        }

        if (str_contains($cleaned, '+')) {
            $cleaned = '+'.str_replace('+', '', $cleaned);
        } elseif (strlen($cleaned) === 8 && ctype_digit($cleaned)) {
            $cleaned = '+45'.$cleaned;
        } elseif ($cleaned !== '') {
            $cleaned = '+'.$cleaned;
        }

        return $cleaned !== '+' ? $cleaned : null;
    }

    /**
     * Resolve the default or primary wishlist for the user.
     */
    protected function resolveWishlist(User $user): Wishlist
    {
        $wishlist = $user->wishlists()->first();

        if (! $wishlist) {
            $wishlist = $user->wishlists()->create([
                'title' => __('My Wishlist'),
            ]);
        }

        return $wishlist;
    }

    /**
     * Parse and clean price input into a decimal string format.
     */
    public function parsePrice(mixed $value): ?string
    {
        if ($value === null || is_bool($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $float = (float) $value;

            return ($float >= 0 && $float < 10000000) ? number_format($float, 2, '.', '') : null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $cleaned = preg_replace('/(?i)\b(kr\.?|dkk|euro|eur|usd|stk|pr|stk\.)\b/u', '', $raw);
        $cleaned = str_replace(['$', '€', '£', 'kr.', 'Kr.', 'kr', 'Kr', 'KR', '.-', ',-', '/stk'], '', (string) $cleaned);
        $cleaned = trim($cleaned, " \t\n\r\0\x0B.,;:/-");

        // European 1.234,56
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $cleaned)) {
            $normalized = str_replace('.', '', $cleaned);
            $normalized = str_replace(',', '.', $normalized);

            return number_format((float) $normalized, 2, '.', '');
        }

        // US 1,234.56
        if (preg_match('/^\d{1,3}(,\d{3})+(\.\d{1,2})?$/', $cleaned)) {
            $normalized = str_replace(',', '', $cleaned);

            return number_format((float) $normalized, 2, '.', '');
        }

        // Decimal comma: 1234,56 or 349,97
        if (preg_match('/^\d+(,\d{1,2})$/', $cleaned)) {
            $normalized = str_replace(',', '.', $cleaned);

            return number_format((float) $normalized, 2, '.', '');
        }

        // Decimal dot: 1234.56
        if (preg_match('/^\d+(\.\d{1,2})$/', $cleaned)) {
            return number_format((float) $cleaned, 2, '.', '');
        }

        // Thousands dot: 8.099
        if (preg_match('/^\d{1,3}\.\d{3}$/', $cleaned)) {
            $normalized = str_replace('.', '', $cleaned);

            return number_format((float) $normalized, 2, '.', '');
        }

        // Pure integer: 150
        if (preg_match('/^\d+$/', $cleaned)) {
            $float = (float) $cleaned;

            return ($float >= 0 && $float < 10000000) ? number_format($float, 2, '.', '') : null;
        }

        return null;
    }

    /**
     * Clean and format legacy description / text field.
     */
    public function cleanDescription(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $cleaned = $text;

        // Convert common HTML elements to markdown/plain text
        $cleaned = preg_replace('/<li[^>]*>/i', '• ', $cleaned);
        $cleaned = preg_replace('/<\/li>|<br\s*\/?>|<\/p>/i', "\n", (string) $cleaned);
        $cleaned = strip_tags((string) $cleaned);
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize carriage returns
        $cleaned = str_replace(["\r\n", "\r"], "\n", $cleaned);

        // Remove excessive empty lines
        $cleaned = preg_replace("/\n{3,}/", "\n\n", $cleaned);
        $cleaned = trim((string) $cleaned);

        return $cleaned !== '' ? $cleaned : null;
    }
}
