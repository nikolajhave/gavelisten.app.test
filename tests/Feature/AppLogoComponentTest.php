<?php

test('gift icon component renders svg with default and custom classes', function () {
    $defaultView = $this->blade('<x-icons.gift />');
    $defaultView->assertSee('viewBox="0 0 24 24"', false)
        ->assertSee('stroke-width="1.5"', false)
        ->assertSee('w-6 h-6', false);

    $customView = $this->blade('<x-icons.gift class="w-10 h-10 text-red-500" />');
    $customView->assertSee('w-10 h-10', false)
        ->assertSee('text-red-500', false);
});

test('app logo component renders with default large size and link', function () {
    $view = $this->blade('<x-app-logo />');

    $view->assertSee('href="'.route('home').'"', false)
        ->assertSee('w-12 h-12', false)
        ->assertSee('w-7 h-7', false)
        ->assertSee('Gavelisten');
});

test('app logo component renders with small size for navbar', function () {
    $view = $this->blade('<x-app-logo size="sm" />');

    $view->assertSee('href="'.route('home').'"', false)
        ->assertSee('w-9 h-9', false)
        ->assertSee('w-5 h-5', false)
        ->assertSee('text-lg', false)
        ->assertSee('Gavelisten');
});

test('app logo component renders unlinked when linked is false', function () {
    $view = $this->blade('<x-app-logo :linked="false" />');

    $view->assertDontSee('<a', false)
        ->assertSee('Gavelisten');
});
