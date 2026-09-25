<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Resend\Contracts\Client as ResendClientContract;
use Resend\Laravel\Facades\Resend;

test('resend client is bound and accessible through container and facade', function () {
    Config::set('services.resend.key', 're_test_api_key_123');

    $client = app('resend');
    expect($client)->toBeInstanceOf(ResendClientContract::class);

    Resend::fake();
    expect(Resend::getFacadeRoot())->toBeInstanceOf(ResendClientContract::class);
});

test('resend mailer transport can be resolved via mail manager', function () {
    Config::set('services.resend.key', 're_test_api_key_123');

    $mailer = Mail::mailer('resend');

    expect($mailer)->not->toBeNull()
        ->and($mailer->getSymfonyTransport())->not->toBeNull();
});
