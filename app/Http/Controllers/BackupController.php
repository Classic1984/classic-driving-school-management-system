<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;

class BackupController extends Controller
{
    /**
     * Manually run the scheduled database backup right now, instead of
     * waiting for its next 2am/2pm run - relaying the command's own
     * console output (success or a caught failure message) back as the
     * flash message, so a misconfigured mailer is immediately visible
     * instead of only showing up as a backup nobody received.
     */
    public function send(): RedirectResponse
    {
        Artisan::call('backup:database');

        return Redirect::route('activity-log.index')->with('status', trim(Artisan::output()));
    }
}
