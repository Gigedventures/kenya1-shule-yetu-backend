<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShulePayment extends BaseShuleModel
{
    protected $table = 'shule_payments';

    protected $fillable = [
        'student_id',
        'amount',
        'status',
        'payment_method',
        'reference',
        'idempotency_key',
        'received_by_user_id',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShulePayment $payment): void {
            $schoolId = $payment->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for payment.');
            }

            $studentSchoolId = DB::table('shule_students')
                ->where('id', $payment->student_id)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Payment student must belong to active school.');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ShulePaymentAllocation::class, 'payment_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function reversal(): HasOne
    {
        return $this->hasOne(ShulePaymentReversal::class, 'payment_id');
    }
}
