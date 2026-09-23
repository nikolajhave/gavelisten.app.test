<?php

use App\Livewire\PublicWishlist;
use App\Models\User;
use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guest can view a public wishlist by share token', function () {
    $user = User::factory()->create([
        'name' => 'Nikolaj',
    ]);
    $wishlist = Wishlist::factory()->for($user)->create([
        'title' => 'Mine juleønsker',
        'share_token' => 'customtoken12',
    ]);

    $wish = Wish::factory()->for($wishlist)->create([
        'title' => 'Lego Star Wars Millennium Falcon',
        'description' => 'Collector edition set 75192',
        'price' => 6499.00,
        'url' => 'https://example.com/lego-falcon',
    ]);

    $response = $this->get('/w/customtoken12');

    $response->assertStatus(200);
    $response->assertSee('Nikolaj');
    $response->assertSee('Mine juleønsker');
    $response->assertSee('Lego Star Wars Millennium Falcon');
    $response->assertSee('Collector edition set 75192');
    $response->assertSee('6.499,00 kr.');
    $response->assertSee('https://example.com/lego-falcon');
    $response->assertSee('target="_blank"', false);
    $response->assertSee('rel="noopener"', false);
});

test('returns 404 when share token does not exist', function () {
    $response = $this->get('/w/nonexistenttoken');

    $response->assertStatus(404);
});

test('public wishes are rendered in ascending sort_order', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->for($user)->create([
        'share_token' => 'orderedtoken1',
    ]);

    Wish::factory()->for($wishlist)->create([
        'title' => 'Third Item in Order',
        'sort_order' => 2,
    ]);
    Wish::factory()->for($wishlist)->create([
        'title' => 'First Item in Order',
        'sort_order' => 0,
    ]);
    Wish::factory()->for($wishlist)->create([
        'title' => 'Second Item in Order',
        'sort_order' => 1,
    ]);

    $response = $this->get('/w/orderedtoken1');

    $response->assertStatus(200);
    $response->assertSeeInOrder(['First Item in Order', 'Second Item in Order', 'Third Item in Order']);
});

test('empty wishlist displays friendly empty state', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->for($user)->create([
        'title' => 'Empty List',
        'share_token' => 'emptylist123',
    ]);

    $response = $this->get('/w/emptylist123');

    $response->assertStatus(200);
    $response->assertSee(__('No wishes on this list yet'));
});

test('wishes without price or url are rendered gracefully without broken elements', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->for($user)->create([
        'share_token' => 'simpletoken1',
    ]);

    Wish::factory()->for($wishlist)->create([
        'title' => 'Cozy Warm Socks',
        'description' => 'Size 42-44, wool',
        'price' => null,
        'url' => null,
    ]);

    $response = $this->get('/w/simpletoken1');

    $response->assertStatus(200);
    $response->assertSee('Cozy Warm Socks');
    $response->assertSee('Size 42-44, wool');
    $response->assertDontSee('kr.');
    $response->assertDontSee(__('See Product'));
});

test('authenticated user can also view public wishlist', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();

    $wishlist = Wishlist::factory()->for($owner)->create([
        'title' => 'Owner Wishlist',
        'share_token' => 'sharedtoken99',
    ]);

    $response = $this->actingAs($viewer)->get('/w/sharedtoken99');

    $response->assertStatus(200);
    $response->assertSee('Owner Wishlist');
});

test('livewire public wishlist component mounts and retrieves data properly', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->for($user)->create([
        'title' => 'Christmas 2026',
        'share_token' => 'christmastoken',
    ]);

    Wish::factory()->for($wishlist)->create([
        'title' => 'Espresso Machine',
        'price' => 4500.00,
        'url' => 'https://example.com/espresso',
    ]);

    Livewire::test(PublicWishlist::class, ['share_token' => 'christmastoken'])
        ->assertStatus(200)
        ->assertSee('Christmas 2026')
        ->assertSee('Espresso Machine')
        ->assertSee('4.500,00 kr.');
});

test('public wishlist displays user name and does not display phone number when owner has phone only', function () {
    $userWithPhone = User::factory()->phoneOnly()->create([
        'name' => null,
        'phone' => '+4520231120',
    ]);

    $wishlist = Wishlist::factory()->for($userWithPhone)->create([
        'title' => 'Min ønskeliste',
        'share_token' => 'phonelisttoken',
    ]);

    $response = $this->get('/w/phonelisttoken');

    $response->assertStatus(200);
    $response->assertSee('Min ønskeliste');
    $response->assertDontSee('+4520231120');
    $response->assertDontSee('20231120');
});

test('public wishlist displays user name when owner has a name', function () {
    $user = User::factory()->create([
        'name' => 'Christer',
        'phone' => '+4520246575',
    ]);

    $wishlist = Wishlist::factory()->for($user)->create([
        'title' => 'Fødselsdag',
        'share_token' => 'namelisttoken',
    ]);

    $response = $this->get('/w/namelisttoken');

    $response->assertStatus(200);
    $response->assertSee('Christer');
    $response->assertSee('Fødselsdag');
    $response->assertDontSee('+4520246575');
    $response->assertDontSee('20246575');
});
