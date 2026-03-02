<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuleClass extends BaseShuleModel
{
    protected $table = 'shule_classes';

    protected $fillable = [
        'name',
        'level',
    ];

    public function streams(): HasMany
    {
        return $this->hasMany(ShuleStream::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        $relation = $this->belongsToMany(
            ShuleSubject::class,
            'shule_class_subject',
            'class_id',
            'subject_id'
        )->withPivot('school_id')->withTimestamps();

        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return $relation->whereRaw('1 = 0');
        }

        return $relation->wherePivot('school_id', $schoolId);
    }
}
