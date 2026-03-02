<?php

namespace App\Modules\ShuleYetu\Support\Traits;

use App\Modules\ShuleYetu\Models\ShuleSchool;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    public function school(): BelongsTo
    {
        return $this->belongsTo(ShuleSchool::class, 'school_id');
    }
}
