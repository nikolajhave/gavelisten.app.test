<?php

use App\Models\Changelog;
use App\Services\ChangelogSyncService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'changelog_test_'.uniqid();
    File::makeDirectory($this->tempDir, 0755, true);
});

afterEach(function () {
    if (File::exists($this->tempDir)) {
        File::deleteDirectory($this->tempDir);
    }
});

test('changelog:sync imports markdown files with front matter into database', function () {
    $file1 = $this->tempDir.DIRECTORY_SEPARATOR.'2026-09-01-v1.0.0.md';
    $content1 = <<<'MD'
---
title: "Første version"
version: v1.0.0
published_at: 2026-09-01 10:00:00
slug: v1-0-0
---

- Første funktion
- Anden funktion
MD;
    File::put($file1, $content1);

    $file2 = $this->tempDir.DIRECTORY_SEPARATOR.'2026-09-15-v1.1.0.md';
    $content2 = <<<'MD'
---
title: "Anden version"
version: v1.1.0
published_at: 2026-09-15 12:00:00
slug: v1-1-0
---

- Tredje funktion
MD;
    File::put($file2, $content2);

    $this->artisan('changelog:sync', ['--path' => $this->tempDir])
        ->expectsOutputToContain('Synced 2 changelog(s): 2 created, 0 updated.')
        ->assertSuccessful();

    $this->assertDatabaseCount('changelogs', 2);

    $this->assertDatabaseHas('changelogs', [
        'slug' => 'v1-0-0',
        'title' => 'Første version',
        'version' => 'v1.0.0',
    ]);

    $this->assertDatabaseHas('changelogs', [
        'slug' => 'v1-1-0',
        'title' => 'Anden version',
        'version' => 'v1.1.0',
    ]);
});

test('changelog:sync is idempotent and updates existing records', function () {
    $filePath = $this->tempDir.DIRECTORY_SEPARATOR.'2026-09-01-v1.0.0.md';
    $contentInitial = <<<'MD'
---
title: "Initial Titel"
version: v1.0.0
published_at: 2026-09-01 10:00:00
slug: my-changelog
---

Gammelt indhold
MD;
    File::put($filePath, $contentInitial);

    $this->artisan('changelog:sync', ['--path' => $this->tempDir])
        ->expectsOutputToContain('Synced 1 changelog(s): 1 created, 0 updated.')
        ->assertSuccessful();

    $changelog = Changelog::where('slug', 'my-changelog')->first();
    expect($changelog->title)->toBe('Initial Titel');
    $originalId = $changelog->id;

    // Update the file
    $contentUpdated = <<<'MD'
---
title: "Opdateret Titel"
version: v1.0.0
published_at: 2026-09-01 10:00:00
slug: my-changelog
---

Nyt opdateret indhold
MD;
    File::put($filePath, $contentUpdated);

    $this->artisan('changelog:sync', ['--path' => $this->tempDir])
        ->expectsOutputToContain('Synced 1 changelog(s): 0 created, 1 updated.')
        ->assertSuccessful();

    $this->assertDatabaseCount('changelogs', 1);
    $changelog->refresh();
    expect($changelog->id)->toBe($originalId)
        ->and($changelog->title)->toBe('Opdateret Titel')
        ->and($changelog->content)->toBe('Nyt opdateret indhold');
});

test('changelog:sync parses draft changelogs with draft: true', function () {
    $filePath = $this->tempDir.DIRECTORY_SEPARATOR.'draft.md';
    $content = <<<'MD'
---
title: "Kommende funktion"
version: v2.0.0
draft: true
slug: upcoming-feature
---

Dette er en kladde
MD;
    File::put($filePath, $content);

    $this->artisan('changelog:sync', ['--path' => $this->tempDir])
        ->assertSuccessful();

    $changelog = Changelog::where('slug', 'upcoming-feature')->first();
    expect($changelog)->not->toBeNull()
        ->and($changelog->published_at)->toBeNull()
        ->and($changelog->isPublished())->toBeFalse();
});

test('changelog:sync handles markdown files without front matter using heading', function () {
    $filePath = $this->tempDir.DIRECTORY_SEPARATOR.'2026-09-20-plain-update.md';
    $content = <<<'MD'
# Ny funktion uden front matter (v1.5.0)

Her er teksten til opdateringen.
MD;
    File::put($filePath, $content);

    $this->artisan('changelog:sync', ['--path' => $this->tempDir])
        ->assertSuccessful();

    $changelog = Changelog::where('title', 'Ny funktion uden front matter (v1.5.0)')->first();
    expect($changelog)->not->toBeNull()
        ->and($changelog->version)->toBe('v1.5.0')
        ->and($changelog->content)->toBe('Her er teksten til opdateringen.')
        ->and($changelog->published_at->format('Y-m-d'))->toBe('2026-09-20');
});

test('changelog:sync aliases work', function () {
    $this->artisan('changelogs:sync', ['--path' => $this->tempDir])
        ->assertSuccessful();

    $this->artisan('changelog:import', ['--path' => $this->tempDir])
        ->assertSuccessful();
});

test('changelog:sync warns when directory is empty', function () {
    $this->artisan('changelog:sync', ['--path' => $this->tempDir])
        ->expectsOutputToContain('No changelog markdown files found to sync.')
        ->assertSuccessful();
});

test('ChangelogSyncService can create a changelog markdown file', function () {
    $service = app(ChangelogSyncService::class);

    $filePath = $service->createChangelogFile(
        title: 'Programmatisk oprettet',
        content: "- Punkt 1\n- Punkt 2",
        version: 'v4.0.0',
        publishedAt: Carbon::parse('2026-09-28 14:00:00'),
        slug: 'custom-slug',
        directory: $this->tempDir
    );

    expect(File::exists($filePath))->toBeTrue();

    $raw = File::get($filePath);
    expect($raw)->toContain('title: "Programmatisk oprettet"')
        ->and($raw)->toContain('version: v4.0.0')
        ->and($raw)->toContain('published_at: 2026-09-28 14:00:00')
        ->and($raw)->toContain('slug: custom-slug')
        ->and($raw)->toContain("- Punkt 1\n- Punkt 2");

    $parsed = $service->parseFile($filePath);
    expect($parsed)->not->toBeNull()
        ->and($parsed['title'])->toBe('Programmatisk oprettet')
        ->and($parsed['version'])->toBe('v4.0.0')
        ->and($parsed['slug'])->toBe('custom-slug');
});
