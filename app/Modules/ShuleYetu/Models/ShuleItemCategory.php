<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuleItemCategory extends BaseShuleModel
{
    protected $table = 'shule_item_categories';

    protected $fillable = [
        'name',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ShuleItem::class, 'category_id');
    }
}
