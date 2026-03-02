<?php

namespace App\Modules\ShuleYetu\Models;

use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleStudentBill extends BaseShuleModel
{
    use HasFactory;

    protected $table = 'shule_student_bills';

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_structure_id',
        'invoice_number',
        'issued_at',
        'due_date',
        'invoice_status',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_date' => 'date',
    ];

    protected static function bootScopedToSchool(): void
    {
        // Intentionally disable implicit tenant scoping for explicit tenancy flows.
    }

    protected static function booted(): void
    {
        static::saving(function (ShuleStudentBill $bill): void {
            $schoolId = $bill->school_id;
            if (!$schoolId) {
                throw new RuntimeException('Student bill school_id is required.');
            }

            $studentSchoolId = DB::table('shule_students')
                ->where('id', $bill->student_id)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Student bill student must belong to active school.');
            }

            $structureSchoolId = DB::table('shule_fee_structures')
                ->where('id', $bill->fee_structure_id)
                ->value('school_id');
            if (!$structureSchoolId || $structureSchoolId !== $schoolId) {
                throw new RuntimeException('Student bill fee structure must belong to active school.');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(ShuleFeeStructure::class, 'fee_structure_id');
    }

    public function paymentsThroughAllocations(): BelongsToMany
    {
        $relation = $this->belongsToMany(
            ShulePayment::class,
            'shule_payment_allocations',
            'student_bill_id',
            'payment_id'
        )->withPivot(['id', 'school_id', 'allocated_amount'])->withTimestamps();

        return $relation;
    }
}
