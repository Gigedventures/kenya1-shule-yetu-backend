<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuleRoute extends BaseShuleModel
{
    protected $table = 'shule_routes';

    protected $fillable = [
        'name',
        'description',
        'fee_amount',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
    ];

    public function transportPayments(): HasMany
    {
        return $this->hasMany(ShuleTransportPayment::class, 'route_id');
    }
}
