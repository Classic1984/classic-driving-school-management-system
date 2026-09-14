<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;

class EnsureServiceCatalogExists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ensure-service-catalog-exists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create any of the four flat catalog services (Driver\'s License Processing, Learner\'s Permit, Online Certificate, Student Certificate) that are missing, without touching ones that already exist';

    /**
     * Several pages (Driver's License, Learner's Permit, the Services
     * catalog itself) look up these rows by exact name, but a deploy only
     * runs migrations - never ServicePriceListSeeder - so a database that
     * predates a given catalog entry, or one where it was deleted, would
     * otherwise leave those pages broken with nothing pointing at why.
     * firstOrCreate() (not updateOrCreate, unlike the seeder this mirrors)
     * is deliberate: this runs on every boot, so it must never overwrite a
     * price a director has since customized via the Services page.
     */
    public function handle(): int
    {
        $defaults = [
            ['name' => "Driver's License Processing", 'price' => 50000, 'processing_days' => 30],
            ['name' => "Learner's Permit", 'price' => 6000, 'processing_days' => null],
            ['name' => 'Online Certificate', 'price' => 20000, 'processing_days' => null],
            ['name' => 'Student Certificate', 'price' => 1000, 'processing_days' => null],
        ];

        $created = 0;

        foreach ($defaults as $default) {
            $service = Service::firstOrCreate(
                ['name' => $default['name']],
                ['price' => $default['price'], 'is_active' => true, 'processing_days' => $default['processing_days']]
            );

            if ($service->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info($created > 0 ? "Created {$created} missing catalog service(s)." : 'Catalog already had every default service.');

        return self::SUCCESS;
    }
}
