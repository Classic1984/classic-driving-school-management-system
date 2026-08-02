<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

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
        'office_rent' => 'Office Rent',
        'electricity' => 'Electricity',
        'internet' => 'Internet',
        'office_tools_repair' => 'Office Tools Repair',
    ];

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
        ];
    }
}
