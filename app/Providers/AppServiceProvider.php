<?php

namespace App\Providers;

use App\Actions\GetTwilioClient;
use App\Services\TwilioService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('twilio', function ($app) {
            return GetTwilioClient::run();
        });

        $this->app->singleton(TwilioService::class, function ($app) {
            return new TwilioService($app->make('twilio'));
        });
    }

    public function boot(): void
    {
        //
    }
}
