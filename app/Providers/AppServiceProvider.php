<?php

namespace App\Providers;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        // Payroll's payee is polymorphic (a staff User or a driving
        // Instructor) - an explicit map keeps "user"/"instructor" in the
        // payroll_salaries/payroll_run_items tables instead of a fully
        // qualified class name, so renaming either model later doesn't
        // orphan every payroll row that points to it.
        Relation::enforceMorphMap([
            'user' => User::class,
            'instructor' => Instructor::class,
        ]);

        // Brevo's HTTP API, rather than raw SMTP, since some hosts (Railway
        // included) block outbound SMTP ports entirely.
        Mail::extend('brevo', function (array $config = []) {
            return Transport::fromDsn(sprintf('brevo+api://%s@default', $config['key']));
        });
    }
}
