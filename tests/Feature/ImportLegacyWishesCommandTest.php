<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('import legacy wishes command runs successfully with configured users', function () {
    $this->artisan('import:legacy-wishes', ['--dry-run' => true])
        ->expectsOutputToContain('Starting Legacy Data Import (DRY RUN - No changes will be saved)...')
        ->expectsOutputToContain('Nikolaj')
        ->expectsOutputToContain('Total legacy users in file')
        ->expectsOutputToContain('Dry run completed! No database changes were persisted.')
        ->assertSuccessful();
});

test('import legacy wishes command executes actual import and saves to database', function () {
    config()->set('legacy_import.users', [
        'Nikolaj' => [
            'name' => 'Nikolaj',
            'phone' => '20231120',
            'email' => null,
            'legacy_id' => 8,
        ],
    ]);

    $this->artisan('import:legacy-wishes')
        ->expectsOutputToContain('Starting Legacy Data Import...')
        ->expectsOutputToContain('Nikolaj')
        ->expectsOutputToContain('Legacy import completed successfully!')
        ->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'name' => 'Nikolaj',
        'phone' => '+4520231120',
        'legacy_id' => 8,
    ]);

    $user = User::where('legacy_id', 8)->first();
    expect($user)->not->toBeNull()
        ->and($user->wishlists->first()->wishes)->toHaveCount(5);
});

test('import legacy wishes command supports user filter option', function () {
    $this->artisan('import:legacy-wishes', [
        '--user' => 'Nikolaj',
        '--dry-run' => true,
    ])
        ->expectsOutputToContain('Nikolaj')
        ->assertSuccessful();
});

test('legacy:import alias works identically to import:legacy-wishes', function () {
    $this->artisan('legacy:import', ['--dry-run' => true])
        ->expectsOutputToContain('Starting Legacy Data Import')
        ->assertSuccessful();
});

test('import legacy wishes command reports error when file is missing', function () {
    $this->artisan('import:legacy-wishes', [
        '--file' => 'nonexistent/path/data.json',
    ])
        ->expectsOutputToContain('Data file not found at: nonexistent/path/data.json')
        ->assertFailed();
});
