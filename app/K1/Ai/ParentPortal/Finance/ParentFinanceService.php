<?php

namespace App\K1\Ai\ParentPortal\Finance;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class ParentFinanceService
{
    public function getStatement(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $bills = DB::table('k1_student_bills')->where('student_id', $studentId)->get()->toArray();
        $payments = DB::table('k1_payments')->where('student_id', $studentId)->get()->toArray();

        return [
            'bills' => $bills,
            'payments' => $payments,
            'balance' => collect($bills)->sum('balance') - collect($payments)->sum('amount'),
        ];
    }

    public function getHistory(string $studentId): array
    {
        return DB::table('k1_payments')
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}