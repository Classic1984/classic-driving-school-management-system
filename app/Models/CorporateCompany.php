<?php

namespace App\Models;

use Database\Factories\CorporateCompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateCompany extends Model
{
    /** @use HasFactory<CorporateCompanyFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'contact_person',
        'phone',
        'email',
        'notes',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(CorporateQuotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CorporateInvoice::class);
    }

    /**
     * company_reference is deliberately not fillable: it's a permanent,
     * system-assigned identifier derived from the row's own auto-increment
     * id (like Student::student_id_number), so it's unique without a
     * collision-check loop, in the form COMP-00001.
     */
    protected static function booted(): void
    {
        static::created(function (CorporateCompany $company) {
            $company->forceFill([
                'company_reference' => 'COMP-'.str_pad((string) $company->id, 5, '0', STR_PAD_LEFT),
            ])->save();
        });
    }
}
