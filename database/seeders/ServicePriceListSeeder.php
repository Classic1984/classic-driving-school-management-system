<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicePriceListSeeder extends Seeder
{
    /**
     * Seed the Classic Driving School flat service price list. Safe to run
     * more than once — existing services are matched by name and updated
     * rather than duplicated.
     *
     * Run with: php artisan db:seed --class=ServicePriceListSeeder
     */
    public function run(): void
    {
        foreach (Service::defaultCatalog() as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                ['price' => $service['price'], 'is_active' => true, 'processing_days' => $service['processing_days']]
            );
        }
    }
}
