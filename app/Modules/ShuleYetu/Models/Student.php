<?php

namespace App\Modules\ShuleYetu\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'shule_class_id',
        'shule_stream_id',
        'admission_number',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'guardian_name',
        'guardian_phone',
        'address',
        'photo',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    public function shuleClass()
    {
        return $this->belongsTo(ShuleClass::class);
    }

    public function stream()
    {
        return $this->belongsTo(ShuleStream::class, 'shule_stream_id');
    }
}
