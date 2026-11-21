<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /**
     * The expense approval/payment lifecycle. A Director's own direct
     * entry skips straight to 'paid' (see store()/isPaidImmediately()) -
     * only an Accountant's submission actually passes through 'pending'
     * and 'approved' first.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'pending' => 'Pending Director Approval',
        'approved' => 'Approved — Awaiting Payment',
        'rejected' => 'Rejected',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    /**
     * The fixed set of expense categories the Director can record against.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'fuel' => 'Fuel',
        'office_accessory' => 'Office Accessory',
        'salary' => 'Salary',
        'car_repair' => 'Car Repair',
        'car_bodywork' => 'Car Bodywork',
        'new_car' => 'New Car',
        'new_engine' => 'New Engine',
        'vehicle_insurance' => 'Vehicle Insurance',
        'vehicle_registration' => 'Vehicle Registration/Roadworthiness Renewal',
        'office_rent' => 'Office Rent',
        'electricity' => 'Electricity',
        'internet' => 'Internet',
        'office_tools_repair' => 'Office Tools Repair',
        'marketing' => 'Marketing/Advertising',
        'food' => 'Food',
        'clothes_shoes' => 'Clothes & Shoes',
        'house_materials' => 'House Materials',
        'kids' => 'Kids',
        'hospital' => 'Hospital',
        'laboratory' => 'Laboratory',
        'debt' => 'Debt',
        'dssp_payment' => 'DSSP Payment',
        'laundry' => 'Laundry',
        'perfume' => 'Perfume',
        'investment_saving' => 'Investment/Saving',
        'saving' => 'Saving',
        'investment_return' => 'Investment/Saving Return',
        'gift' => 'Gift',
        'miscellaneous' => 'Miscellaneous/Other',
    ];

    /**
     * Categories that represent money coming back in rather than going out -
     * an investment or saving maturing and returning. Unlike every other
     * category, these count toward income (see FinanceController) instead of
     * being subtracted as an expense.
     *
     * @var list<string>
     */
    public const INCOME_CATEGORIES = ['investment_return'];

    /**
     * Whether this entry is money coming back in (an investment/saving
     * return) rather than an outflow.
     */
    public function isIncome(): bool
    {
        return in_array($this->category, self::INCOME_CATEGORIES, true);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category',
        'amount',
        'expense_date',
        'description',
        'receipt_photo_path',
        'requested_by',
        'status',
        'resolved_by',
        'resolved_at',
        'rejection_reason',
        'paid_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date:Y-m-d',
            'resolved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Only a paid expense is a real outflow - a pending or approved one
     * hasn't actually left the business yet, and a rejected/cancelled one
     * never will. Every "how much have we spent" total should use this
     * scope instead of querying the table unfiltered.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }
}
