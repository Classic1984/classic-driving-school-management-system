<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Brevo's HTTP API, rather than raw SMTP, since some hosts (Railway
        // included) block outbound SMTP ports entirely.
        Mail::extend('brevo', function (array $config = []) {
            return Transport::fromDsn(sprintf('brevo+api://%s@default', $config['key']));
        });
    }
}
