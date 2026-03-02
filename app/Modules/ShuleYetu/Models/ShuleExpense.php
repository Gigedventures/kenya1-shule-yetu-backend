<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuleExpense extends BaseShuleModel
{
    protected $table = 'shule_expenses';

    protected $fillable = [
        'financial_year_id',
        'category_id',
        'vendor_id',
        'amount',
        'description',
        'expense_date',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShuleExpenseCategory::class, 'category_id');
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(ShuleFinancialYear::class, 'financial_year_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ShuleVendor::class, 'vendor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
