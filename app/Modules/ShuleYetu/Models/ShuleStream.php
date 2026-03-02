<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStream extends BaseShuleModel
{
    protected $table = 'shule_streams';

    protected $fillable = [
        'class_id',
        'name',
        'capacity',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShuleStream $stream): void {
            $activeSchoolId = $stream->school_id ?: app(SchoolContext::class)->id();
            if (empty($activeSchoolId)) {
                throw new RuntimeException('No active school context for stream.');
            }

            $classSchoolId = DB::table('shule_classes')
                ->where('id', $stream->class_id)
                ->value('school_id');

            if (empty($classSchoolId) || $classSchoolId !== $activeSchoolId) {
                throw new RuntimeException('Stream and class must belong to same school.');
            }
        });
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ShuleClass::class, 'class_id');
    }
}
