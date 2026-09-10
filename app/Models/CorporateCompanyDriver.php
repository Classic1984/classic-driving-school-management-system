<?php

namespace App\Models;

use Database\Factories\CorporateCompanyDriverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A named driver/staff member a corporate client has enrolled for
 * training, listed under the company they belong to. Independent of any
 * particular quotation/invoice - a company may enrol drivers before a
 * document naming them ever exists.
 */
class CorporateCompanyDriver extends Model
{
    /** @use HasFactory<CorporateCompanyDriverFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'corporate_company_id',
        'name',
        'phone',
        'license_number',
        'created_by',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CorporateCompany::class, 'corporate_company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
