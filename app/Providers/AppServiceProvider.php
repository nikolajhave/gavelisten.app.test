<?php

namespace App\Providers;

use App\Contracts\SmsService;
use App\Services\GatewayApiSmsService;
use App\Services\LogSmsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsService::class, function () {
            return match (config('services.sms.driver')) {
                'gatewayapi' => new GatewayApiSmsService,
                default => new LogSmsService,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
