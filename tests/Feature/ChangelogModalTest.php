<?php

use App\Livewire\ChangelogModal;
use App\Models\Changelog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('authenticated user with unread changelog has modal open on mount', function () {
    $user = User::factory()->create();

    $changelog = Changelog::factory()->create([
        'title' => 'Velkommen til den nye version',
        'version' => 'v2.0.0',
        'content' => "- Ny søgefunktion\n- Hurtigere indlæsning",
        'published_at' => now()->subHour(),
    ]);

    $this->actingAs($user);

    Livewire::test(ChangelogModal::class)
        ->assertSet('showModal', true)
        ->assertSee('Velkommen til den nye version')
        ->assertSee('v2.0.0')
        ->assertSee('Ny søgefunktion')
        ->assertSee('Nyt siden sidst');
});

test('dismissing changelog modal marks it as read and closes modal', function () {
    $user = User::factory()->create();

    $changelog = Changelog::factory()->create([
        'title' => 'Første opdatering',
        'published_at' => now()->subHour(),
    ]);

    $this->actingAs($user);

    expect($user->last_seen_changelog_id)->toBeNull();

    Livewire::test(ChangelogModal::class)
        ->assertSet('showModal', true)
        ->call('dismiss')
        ->assertSet('showModal', false);

    $user->refresh();
    expect($user->last_seen_changelog_id)->toBe($changelog->id)
        ->and($user->unreadChangelog())->toBeNull();
});

test('user who has already seen latest changelog does not see modal on mount', function () {
    $changelog = Changelog::factory()->create([
        'title' => 'Gammel opdatering',
        'published_at' => now()->subDays(2),
    ]);

    $user = User::factory()->create([
        'last_seen_changelog_id' => $changelog->id,
    ]);

    $this->actingAs($user);

    Livewire::test(ChangelogModal::class)
        ->assertSet('showModal', false)
        ->assertDontSee('Gammel opdatering');
});

test('user sees newly published changelog after having dismissed previous one', function () {
    $oldChangelog = Changelog::factory()->create([
        'title' => 'Første version',
        'published_at' => now()->subDays(5),
    ]);

    $user = User::factory()->create([
        'last_seen_changelog_id' => $oldChangelog->id,
    ]);

    $newChangelog = Changelog::factory()->create([
        'title' => 'Anden version med nye funktioner',
        'published_at' => now()->subMinute(),
    ]);

    $this->actingAs($user);

    Livewire::test(ChangelogModal::class)
        ->assertSet('showModal', true)
        ->assertSee('Anden version med nye funktioner');
});

test('draft or scheduled changelogs are not shown to users', function () {
    $user = User::factory()->create();

    Changelog::factory()->draft()->create([
        'title' => 'Ikke udgivet kladde',
    ]);

    Changelog::factory()->scheduled()->create([
        'title' => 'Fremtidig planlagt',
    ]);

    $this->actingAs($user);

    Livewire::test(ChangelogModal::class)
        ->assertSet('showModal', false)
        ->assertDontSee('Ikke udgivet kladde')
        ->assertDontSee('Fremtidig planlagt');
});

test('guest user does not see changelog modal on mount', function () {
    Changelog::factory()->create([
        'title' => 'Opdatering for alle',
        'published_at' => now()->subHour(),
    ]);

    Livewire::test(ChangelogModal::class)
        ->assertSet('showModal', false);
});

test('open event allows re-opening changelog modal', function () {
    $changelog = Changelog::factory()->create([
        'title' => 'Genåbn opdatering',
        'published_at' => now()->subDay(),
    ]);

    $user = User::factory()->create([
        'last_seen_changelog_id' => $changelog->id,
    ]);

    $this->actingAs($user);

    Livewire::test(ChangelogModal::class)
        ->assertSet('showModal', false)
        ->dispatch('open-changelog')
        ->assertSet('showModal', true)
        ->assertSee('Genåbn opdatering');
});

test('changelog markdown formatting renders properly', function () {
    $changelog = Changelog::factory()->create([
        'title' => 'Markdown Test',
        'content' => '**Fremhævet tekst** og [et link](https://gavelisten.app)',
        'published_at' => now(),
    ]);

    expect($changelog->formatted_content)->toContain('<strong>Fremhævet tekst</strong>')
        ->and($changelog->formatted_content)->toContain('<a href="https://gavelisten.app">et link</a>');
});
