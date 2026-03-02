<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShuleSubject extends BaseShuleModel
{
    protected $table = 'shule_subjects';

    protected $fillable = [
        'name',
        'code',
        'is_core',
    ];

    protected $casts = [
        'is_core' => 'boolean',
    ];

    public function classes(): BelongsToMany
    {
        $relation = $this->belongsToMany(
            ShuleClass::class,
            'shule_class_subject',
            'subject_id',
            'class_id'
        )->withPivot('school_id')->withTimestamps();

        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return $relation->whereRaw('1 = 0');
        }

        return $relation->wherePivot('school_id', $schoolId);
    }
}

