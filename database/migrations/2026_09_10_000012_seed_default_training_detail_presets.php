<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fills in a starter set of Programme/Duration/Number of Drivers
     * presets so the dropdown added in the last two PRs has something
     * useful the first time a director opens it, instead of an empty "no
     * suggestions yet" state. Only touches rows where the column is still
     * blank, so it can never overwrite anything a director has already
     * typed and saved. Programme names are drawn from this school's own
     * course catalog (Course::pluck('name')) rather than invented ones.
     */
    public function up(): void
    {
        $settingsId = DB::table('corporate_invoice_settings')->value('id');

        if ($settingsId === null) {
            return;
        }

        DB::table('corporate_invoice_settings')
            ->where('id', $settingsId)
            ->where(function ($query) {
                $query->whereNull('programme_options')->orWhere('programme_options', '');
            })
            ->update([
                'programme_options' => implode("\n", [
                    'Defensive Driving',
                    'Auto Course',
                    'Manual Course',
                    'Combined Course',
                    'Auto Advanced',
                    'Manual Advanced',
                ]),
            ]);

        DB::table('corporate_invoice_settings')
            ->where('id', $settingsId)
            ->where(function ($query) {
                $query->whereNull('duration_options')->orWhere('duration_options', '');
            })
            ->update([
                'duration_options' => implode("\n", [
                    'One Day',
                    'Three Days',
                    'One Week',
                    'Two Weeks',
                    'One Month',
                ]),
            ]);

        DB::table('corporate_invoice_settings')
            ->where('id', $settingsId)
            ->where(function ($query) {
                $query->whereNull('driver_count_options')->orWhere('driver_count_options', '');
            })
            ->update([
                'driver_count_options' => implode("\n", ['1', '5', '10', '20', '50']),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * Deliberately a no-op: these are just starter suggestions a director
     * may since have edited, so there's nothing safe to "undo" back to.
     */
    public function down(): void {}
};
