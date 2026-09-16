<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A flat, catalog-priced service a student can be charged for
 * independently of any course enrollment - e.g. Driver's License
 * Processing or Learner's Permit. Training and course-outline certificate
 * fees are billed through Enrollment instead, since their pricing is
 * course-specific.
 */
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'is_active',
        'processing_days',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'processing_days' => 'integer',
        ];
    }

    public function studentServices(): HasMany
    {
        return $this->hasMany(StudentService::class);
    }

    /**
     * The flat catalog services this app assumes exist by exact name -
     * Driver's License Processing, Learner's Permit, and Online
     * Certificate - with sensible starting price/turnaround defaults.
     * Shared by ServicePriceListSeeder (a manual, deliberate reset), the
     * app:ensure-service-catalog-exists command (automatic, create-only,
     * runs on every boot), and the "this service isn't set up yet" page's
     * suggested defaults, so there's one list instead of several copies
     * that could drift apart.
     *
     * @return list<array{name: string, price: float, processing_days: ?int}>
     */
    public static function defaultCatalog(): array
    {
        return [
            ['name' => "Driver's License Processing", 'price' => 50000, 'processing_days' => 30],
            ['name' => "Learner's Permit", 'price' => 6000, 'processing_days' => null],
            ['name' => 'Online Certificate', 'price' => 20000, 'processing_days' => null],
        ];
    }

    /**
     * The defaultCatalog() entry for one exact service name, or null if
     * that name isn't one of the app's known defaults.
     *
     * @return array{name: string, price: float, processing_days: ?int}|null
     */
    public static function defaultCatalogEntry(string $name): ?array
    {
        return collect(static::defaultCatalog())->firstWhere('name', $name);
    }
}
