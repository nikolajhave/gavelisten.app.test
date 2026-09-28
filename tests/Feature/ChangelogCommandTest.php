<?php

use App\Models\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->initialFiles = File::exists(database_path('changelogs'))
        ? array_map(fn ($f) => $f->getRealPath(), File::files(database_path('changelogs')))
        : [];
});

afterEach(function () {
    if (File::exists(database_path('changelogs'))) {
        $currentFiles = array_map(fn ($f) => $f->getRealPath(), File::files(database_path('changelogs')));
        $newFiles = array_diff($currentFiles, $this->initialFiles ?? []);
        foreach ($newFiles as $newFile) {
            File::delete($newFile);
        }
    }
});

test('changelog:new creates a published changelog with options', function () {
    $this->artisan('changelog:new', [
        '--title' => 'Nyt design og hurtigere søgning',
        '--content' => "- Forbedret design\n- Hurtigere søgning\n- Mange fejlrettelser",
        '--ver' => 'v1.2.0',
    ])
        ->expectsOutputToContain('Changelog created successfully!')
        ->expectsOutputToContain('Nyt design og hurtigere søgning')
        ->expectsOutputToContain('v1.2.0')
        ->assertSuccessful();

    $this->assertDatabaseHas('changelogs', [
        'title' => 'Nyt design og hurtigere søgning',
        'version' => 'v1.2.0',
    ]);

    $changelog = Changelog::where('title', 'Nyt design og hurtigere søgning')->first();
    expect($changelog)->not->toBeNull()
        ->and($changelog->isPublished())->toBeTrue()
        ->and($changelog->published_at)->not->toBeNull();
});

test('changelog:new works interactively with prompts', function () {
    $this->artisan('changelog:new')
        ->expectsQuestion('What is the title of this update?', 'Interaktiv titel')
        ->expectsQuestion('Version number (optional, press Enter to skip)', 'v3.0.0')
        ->expectsQuestion("What's new in this update? (Markdown is supported)", "- Interaktiv punkt 1\n- Interaktiv punkt 2")
        ->expectsConfirmation('Publish this update immediately so users will see it?', 'yes')
        ->expectsOutputToContain('Changelog created successfully!')
        ->expectsOutputToContain('Interaktiv titel')
        ->expectsOutputToContain('v3.0.0')
        ->assertSuccessful();

    $this->assertDatabaseHas('changelogs', [
        'title' => 'Interaktiv titel',
        'version' => 'v3.0.0',
    ]);
});

test('changelog:new creates a draft changelog when --draft is specified', function () {
    $this->artisan('changelog:new', [
        '--title' => 'Kommende funktioner',
        '--content' => 'Arbejder på noget fedt',
        '--draft' => true,
    ])
        ->expectsOutputToContain('Changelog created successfully!')
        ->expectsOutputToContain('Draft (Unpublished)')
        ->assertSuccessful();

    $changelog = Changelog::where('title', 'Kommende funktioner')->first();
    expect($changelog)->not->toBeNull()
        ->and($changelog->published_at)->toBeNull()
        ->and($changelog->isPublished())->toBeFalse();
});

test('changelog:new schedules publication with --publish-at', function () {
    $futureDate = now()->addDays(5)->format('Y-m-d H:i:s');

    $this->artisan('changelog:new', [
        '--title' => 'Fremtidig opdatering',
        '--content' => 'Udgives snart',
        '--publish-at' => $futureDate,
    ])
        ->expectsOutputToContain('Changelog created successfully!')
        ->expectsOutputToContain('Scheduled for')
        ->assertSuccessful();

    $changelog = Changelog::where('title', 'Fremtidig opdatering')->first();
    expect($changelog)->not->toBeNull()
        ->and($changelog->published_at)->not->toBeNull()
        ->and($changelog->isPublished())->toBeFalse();
});

test('changelog:new command aliases work', function () {
    $this->artisan('changelog:add', [
        '--title' => 'Alias test',
        '--content' => 'Test indhold',
    ])
        ->expectsOutputToContain('Changelog created successfully!')
        ->assertSuccessful();

    $this->artisan('changelog:create', [
        '--title' => 'Alias test 2',
        '--content' => 'Test indhold 2',
    ])
        ->expectsOutputToContain('Changelog created successfully!')
        ->assertSuccessful();
});

test('changelog:new fails non-interactively when required options are missing', function () {
    $this->artisan('changelog:new', [
        '--content' => 'Noget indhold uden titel',
    ])
        ->expectsOutputToContain('The --title option is required')
        ->assertFailed();

    $this->artisan('changelog:new', [
        '--title' => 'En titel uden indhold',
    ])
        ->expectsOutputToContain('The --content option is required')
        ->assertFailed();
});

test('changelog:list displays table of published changelogs', function () {
    Changelog::factory()->create([
        'title' => 'Udgivet opdatering',
        'version' => '1.0.0',
        'published_at' => now()->subDay(),
    ]);

    Changelog::factory()->draft()->create([
        'title' => 'Kladde opdatering',
        'version' => '2.0.0',
    ]);

    $this->artisan('changelog:list')
        ->expectsOutputToContain('Udgivet opdatering')
        ->doesntExpectOutputToContain('Kladde opdatering')
        ->assertSuccessful();

    $this->artisan('changelog:list', ['--all' => true])
        ->expectsOutputToContain('Udgivet opdatering')
        ->expectsOutputToContain('Kladde opdatering')
        ->assertSuccessful();
});

test('changelog:list displays message when empty', function () {
    $this->artisan('changelog:list')
        ->expectsOutputToContain('No published changelog entries found')
        ->assertSuccessful();
});
