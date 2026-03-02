<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuleVendor extends BaseShuleModel
{
    protected $table = 'shule_vendors';

    protected $fillable = [
        'name',
        'contacts',
        'phone',
        'email',
    ];

    protected $casts = [
        'contacts' => 'array',
    ];

    public function stockEntries(): HasMany
    {
        return $this->hasMany(ShuleStockEntry::class, 'vendor_id');
    }
}
