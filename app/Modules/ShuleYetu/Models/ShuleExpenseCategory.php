<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;

class ShuleExpenseCategory extends BaseShuleModel
{
    protected $table = 'shule_expense_categories';

    protected $fillable = [
        'name',
        'expense_account_code',
    ];
}
