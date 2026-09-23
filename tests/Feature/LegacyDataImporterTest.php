<?php

use App\Models\User;
use App\Services\LegacyImport\LegacyDataImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('price parser accurately extracts decimal values from diverse formats', function () {
    $importer = new LegacyDataImporter;

    expect($importer->parsePrice(null))->toBeNull()
        ->and($importer->parsePrice(''))->toBeNull()
        ->and($importer->parsePrice(150))->toBe('150.00')
        ->and($importer->parsePrice(19.99))->toBe('19.99')
        ->and($importer->parsePrice('2300 kr'))->toBe('2300.00')
        ->and($importer->parsePrice('699 kr.'))->toBe('699.00')
        ->and($importer->parsePrice('350kr.'))->toBe('350.00')
        ->and($importer->parsePrice('349,97'))->toBe('349.97')
        ->and($importer->parsePrice('8.099 kr'))->toBe('8099.00')
        ->and($importer->parsePrice('2.444,15'))->toBe('2444.15')
        ->and($importer->parsePrice('$99.99'))->toBe('99.99')
        ->and($importer->parsePrice('150,-'))->toBe('150.00')
        ->and($importer->parsePrice('der ved jeg ikke'))->toBeNull()
        ->and($importer->parsePrice('70-150'))->toBeNull();
});

test('description cleaner strips html and normalizes whitespace', function () {
    $importer = new LegacyDataImporter;

    $html = "<ul><li>Item 1\r\n<li>Item 2</li></ul><p>Some text</p>";
    $cleaned = $importer->cleanDescription($html);

    expect($cleaned)->toContain('• Item 1')
        ->and($cleaned)->toContain('Item 2')
        ->and($cleaned)->toContain('Some text')
        ->and($cleaned)->not->toContain('<ul>')
        ->and($cleaned)->not->toContain('<li>');

    expect($importer->cleanDescription(null))->toBeNull()
        ->and($importer->cleanDescription('   '))->toBeNull();
});

test('user configuration resolver matches by name, id, shorthand and arrays', function () {
    $importer = new LegacyDataImporter;

    $config = [
        'Nikolaj' => [
            'name' => 'Nikolaj Have',
            'phone' => '20231120',
            'email' => null,
            'legacy_id' => 8,
        ],
        12 => 'christer@example.com',
        'trine' => '12345678',
    ];

    $nikolaj = $importer->resolveConfiguredUser(['id' => 8, 'name' => 'Nikolaj'], $config);
    expect($nikolaj)->not->toBeNull()
        ->and($nikolaj['name'])->toBe('Nikolaj Have')
        ->and($nikolaj['phone'])->toBe('20231120')
        ->and($nikolaj['legacy_id'])->toBe(8);

    $christer = $importer->resolveConfiguredUser(['id' => 12, 'name' => 'Christer'], $config);
    expect($christer)->not->toBeNull()
        ->and($christer['email'])->toBe('christer@example.com')
        ->and($christer['legacy_id'])->toBe(12);

    $trine = $importer->resolveConfiguredUser(['id' => 80, 'name' => 'Trine'], $config);
    expect($trine)->not->toBeNull()
        ->and($trine['phone'])->toBe('12345678');

    $unconfigured = $importer->resolveConfiguredUser(['id' => 999, 'name' => 'Unknown'], $config);
    expect($unconfigured)->toBeNull();
});

test('legacy importer creates user, wishlist, and wishes from json', function () {
    $importer = new LegacyDataImporter;

    $fakeJson = json_encode([
        [
            'name' => 'Family A',
            'users' => [
                [
                    'id' => 101,
                    'name' => 'Alice',
                    'wishes' => [
                        [
                            'id' => 1001,
                            'name' => 'Lego Star Wars',
                            'text' => 'Set 75192 Falcon',
                            'link' => 'https://lego.com/falcon',
                            'price' => '6500 kr',
                            'deleted' => false,
                        ],
                        [
                            'id' => 1002,
                            'name' => 'Old Book',
                            'text' => 'Old reading book',
                            'link' => null,
                            'price' => '50 kr',
                            'deleted' => true,
                        ],
                    ],
                ],
                [
                    'id' => 102,
                    'name' => 'Bob',
                    'wishes' => [
                        [
                            'id' => 2001,
                            'name' => 'Headphones',
                            'text' => null,
                            'link' => null,
                            'price' => '1200 kr',
                            'deleted' => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $tempFile = storage_path('framework/testing/test_wishes.json');
    File::ensureDirectoryExists(dirname($tempFile));
    File::put($tempFile, $fakeJson);

    $userConfig = [
        'Alice' => [
            'name' => 'Alice Tester',
            'phone' => '+4511223344',
            'email' => 'alice@example.com',
            'legacy_id' => 101,
        ],
    ];

    $result = $importer->import(
        filePath: $tempFile,
        customUserConfig: $userConfig,
    );

    expect($result->hasErrors())->toBeFalse()
        ->and($result->totalUsersInFile)->toBe(2)
        ->and($result->configuredUsersCount)->toBe(1)
        ->and($result->importedUsersCount)->toBe(1)
        ->and($result->wishesCreated)->toBe(1)
        ->and($result->wishesSkipped)->toBe(1)
        ->and($result->unconfiguredUsers)->toHaveCount(1)
        ->and($result->unconfiguredUsers[0]['name'])->toBe('Bob');

    $this->assertDatabaseHas('users', [
        'name' => 'Alice Tester',
        'phone' => '+4511223344',
        'email' => 'alice@example.com',
        'legacy_id' => 101,
    ]);

    $alice = User::where('legacy_id', 101)->first();
    expect($alice->wishlists)->toHaveCount(1);

    $wishlist = $alice->wishlists->first();
    $this->assertDatabaseHas('wishes', [
        'wishlist_id' => $wishlist->id,
        'legacy_id' => 1001,
        'title' => 'Lego Star Wars',
        'price' => '6500.00',
        'url' => 'https://lego.com/falcon',
    ]);

    // Re-running the import is idempotent and updates existing wishes
    $result2 = $importer->import(
        filePath: $tempFile,
        customUserConfig: $userConfig,
    );

    expect($result2->wishesCreated)->toBe(0)
        ->and($result2->wishesUpdated)->toBe(1);

    File::delete($tempFile);
});

test('legacy importer dry run does not persist changes to database', function () {
    $importer = new LegacyDataImporter;

    $fakeJson = json_encode([
        [
            'name' => 'Family Test',
            'users' => [
                [
                    'id' => 777,
                    'name' => 'Charlie',
                    'wishes' => [
                        [
                            'id' => 7771,
                            'name' => 'Boardgame',
                            'text' => 'Catan',
                            'link' => null,
                            'price' => '300 kr',
                            'deleted' => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $tempFile = storage_path('framework/testing/test_dry_run.json');
    File::ensureDirectoryExists(dirname($tempFile));
    File::put($tempFile, $fakeJson);

    $userConfig = [
        'Charlie' => [
            'name' => 'Charlie Dry',
            'phone' => '+4599887766',
            'legacy_id' => 777,
        ],
    ];

    $result = $importer->import(
        filePath: $tempFile,
        dryRun: true,
        customUserConfig: $userConfig,
    );

    expect($result->isDryRun)->toBeTrue()
        ->and($result->wishesCreated)->toBe(1);

    $this->assertDatabaseMissing('users', [
        'legacy_id' => 777,
    ]);

    $this->assertDatabaseMissing('wishes', [
        'legacy_id' => 7771,
    ]);

    File::delete($tempFile);
});

test('legacy importer includes soft-deleted wishes when requested', function () {
    $importer = new LegacyDataImporter;

    $fakeJson = json_encode([
        [
            'name' => 'Family X',
            'users' => [
                [
                    'id' => 888,
                    'name' => 'Dave',
                    'wishes' => [
                        [
                            'id' => 8881,
                            'name' => 'Active Wish',
                            'deleted' => false,
                        ],
                        [
                            'id' => 8882,
                            'name' => 'Deleted Wish',
                            'deleted' => true,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $tempFile = storage_path('framework/testing/test_deleted.json');
    File::ensureDirectoryExists(dirname($tempFile));
    File::put($tempFile, $fakeJson);

    $userConfig = [
        'Dave' => [
            'name' => 'Dave',
            'phone' => '+4555443322',
            'legacy_id' => 888,
        ],
    ];

    $result = $importer->import(
        filePath: $tempFile,
        includeDeleted: true,
        customUserConfig: $userConfig,
    );

    expect($result->wishesCreated)->toBe(2)
        ->and($result->wishesSkipped)->toBe(0);

    File::delete($tempFile);
});

test('legacy importer links to existing user by phone or email without duplicate users', function () {
    $existingUser = User::factory()->create([
        'name' => 'Existing Nikolaj',
        'phone' => '+4520231120',
        'legacy_id' => null,
    ]);

    $importer = new LegacyDataImporter;

    $fakeJson = json_encode([
        [
            'name' => 'Have',
            'users' => [
                [
                    'id' => 8,
                    'name' => 'Nikolaj',
                    'wishes' => [
                        [
                            'id' => 8904,
                            'name' => 'Oplevelser',
                            'deleted' => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $tempFile = storage_path('framework/testing/test_link.json');
    File::ensureDirectoryExists(dirname($tempFile));
    File::put($tempFile, $fakeJson);

    $userConfig = [
        'Nikolaj' => [
            'name' => 'Nikolaj',
            'phone' => '20231120',
            'legacy_id' => 8,
        ],
    ];

    $result = $importer->import(
        filePath: $tempFile,
        customUserConfig: $userConfig,
    );

    expect(User::where('phone', '+4520231120')->count())->toBe(1);

    $existingUser->refresh();
    expect($existingUser->legacy_id)->toBe(8)
        ->and($result->wishesCreated)->toBe(1);

    File::delete($tempFile);
});
