<?php

namespace App\Modules\ShuleYetu\Support\Traits;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

trait ScopedToSchool
{
    protected static function bootScopedToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder): void {
            $schoolId = app(SchoolContext::class)->id();

            if (!$schoolId) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where($builder->getModel()->qualifyColumn('school_id'), $schoolId);
        });

        static::creating(function ($model): void {
            $schoolId = app(SchoolContext::class)->id();

            if (!$schoolId) {
                throw new RuntimeException('No active school context for create operation.');
            }

            $model->school_id = $schoolId;
        });

        static::updating(function ($model): void {
            if ($model->isDirty('school_id')) {
                throw new RuntimeException('Changing school_id is not allowed.');
            }
        });
    }
}
