<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuleStudentClassHistory extends BaseShuleModel
{
    protected $table = 'shule_student_class_history';

    protected $fillable = [
        'student_id',
        'from_class_id',
        'from_stream_id',
        'to_class_id',
        'to_stream_id',
        'changed_at',
        'reason',
        'changed_by_user_id',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}

