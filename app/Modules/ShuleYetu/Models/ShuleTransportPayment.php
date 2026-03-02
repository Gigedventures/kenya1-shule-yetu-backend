<?php

namespace App\Modules\ShuleYetu\Models;

use App\Models\User;
use App\Modules\ShuleYetu\Support\Models\BaseShuleModel;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShuleTransportPayment extends BaseShuleModel
{
    protected $table = 'shule_transport_payments';

    protected $fillable = [
        'student_id',
        'route_id',
        'amount',
        'method',
        'status',
        'paid_at',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $payment): void {
            $schoolId = $payment->school_id ?: app(SchoolContext::class)->id();
            if (!$schoolId) {
                throw new RuntimeException('No active school context for transport payment.');
            }

            $studentSchoolId = DB::table('shule_students')
                ->where('id', $payment->student_id)
                ->value('school_id');
            if (!$studentSchoolId || $studentSchoolId !== $schoolId) {
                throw new RuntimeException('Transport payment student must belong to active school.');
            }

            $routeSchoolId = DB::table('shule_routes')
                ->where('id', $payment->route_id)
                ->value('school_id');
            if (!$routeSchoolId || $routeSchoolId !== $schoolId) {
                throw new RuntimeException('Transport payment route must belong to active school.');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(ShuleStudent::class, 'student_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(ShuleRoute::class, 'route_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
