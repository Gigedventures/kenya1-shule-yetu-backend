<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShulePaymentReversal extends BaseShuleModel
{
    protected $table = 'shule_payment_reversals';

    protected $fillable = [
        'payment_id',
        'school_id',
        'reversed_by',
        'reason',
        'reversed_at',
    ];

    protected $casts = [
        'reversed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShulePaymentReversal $reversal): void {
            $schoolId = $reversal->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for payment reversal.');
            }

            $paymentSchoolId = DB::table('shule_payments')
                ->where('id', $reversal->payment_id)
                ->value('school_id');
            if (!$paymentSchoolId || $paymentSchoolId !== $schoolId) {
                throw new RuntimeException('Payment reversal payment must belong to active school.');
            }
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(ShulePayment::class, 'payment_id');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
