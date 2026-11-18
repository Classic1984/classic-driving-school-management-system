<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Instructor;
use App\Models\Payment;
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

        // Payroll's payee, and a bank statement line's match, are both
        // polymorphic - an explicit map keeps a short alias
        // ("user"/"instructor"/"payment"/"expense") in the database
        // instead of a fully qualified class name, so renaming any of
        // these models later doesn't orphan every row that points to it.
        Relation::enforceMorphMap([
            'user' => User::class,
            'instructor' => Instructor::class,
            'payment' => Payment::class,
            'expense' => Expense::class,
        ]);

        // Brevo's HTTP API, rather than raw SMTP, since some hosts (Railway
        // included) block outbound SMTP ports entirely.
        Mail::extend('brevo', function (array $config = []) {
            return Transport::fromDsn(sprintf('brevo+api://%s@default', $config['key']));
        });
    }
}
