<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShulePaymentAllocation extends BaseShuleModel
{
    protected $table = 'shule_payment_allocations';

    protected $fillable = [
        'payment_id',
        'student_bill_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShulePaymentAllocation $allocation): void {
            $schoolId = $allocation->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for payment allocation.');
            }

            $paymentSchoolId = DB::table('shule_payments')
                ->where('id', $allocation->payment_id)
                ->value('school_id');
            if (!$paymentSchoolId || $paymentSchoolId !== $schoolId) {
                throw new RuntimeException('Allocation payment must belong to active school.');
            }

            $billSchoolId = DB::table('shule_student_bills')
                ->where('id', $allocation->student_bill_id)
                ->value('school_id');
            if (!$billSchoolId || $billSchoolId !== $schoolId) {
                throw new RuntimeException('Allocation bill must belong to active school.');
            }
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(ShulePayment::class, 'payment_id');
    }

    public function studentBill(): BelongsTo
    {
        return $this->belongsTo(ShuleStudentBill::class, 'student_bill_id');
    }
}
