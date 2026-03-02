<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentReversalTest extends TestCase
{
    use RefreshDatabase;

    public function test_reversal_restores_bill_balances_after_multi_bill_allocation(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School R',
            'code' => 'FN-R-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);

        $year = ShuleAcademicYear::query()->create([
            'name' => '2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $term1 = ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->startOfYear()->addMonths(3),
        ]);
        $term2 = ShuleTerm::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 2',
            'start_date' => now()->startOfYear()->addMonths(4),
            'end_date' => now()->startOfYear()->addMonths(7),
        ]);
        $class = ShuleClass::query()->create([
            'name' => 'Grade 3',
            'level' => 3,
        ]);

        $structureOld = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term1->id,
            'class_id' => $class->id,
            'name' => 'Fees Term 1',
            'is_active' => true,
        ]);
        $structureNew = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term2->id,
            'class_id' => $class->id,
            'name' => 'Fees Term 2',
            'is_active' => true,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Reverse',
            'last_name' => 'Case',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $billOld = new ShuleStudentBill([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $structureOld->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'balance' => 1000,
            'status' => 'unpaid',
        ]);
        $billOld->created_at = Carbon::now()->subDays(10);
        $billOld->updated_at = Carbon::now()->subDays(10);
        $billOld->save();
        ShuleStudentBill::query()->where('id', $billOld->id)->update([
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ]);
        $billOld->refresh();

        $billNew = new ShuleStudentBill([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $structureNew->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'balance' => 1000,
            'status' => 'unpaid',
        ]);
        $billNew->created_at = Carbon::now()->subDays(1);
        $billNew->updated_at = Carbon::now()->subDays(1);
        $billNew->save();
        ShuleStudentBill::query()->where('id', $billNew->id)->update([
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);
        $billNew->refresh();

        $service = app(FeeService::class);
        $actor = $this->fakeUserWithPermission('finance.payments.record');
        $payment = $service->recordPayment($student->id, 1200, 'cash', null, $actor);

        $billOld->refresh();
        $billNew->refresh();
        $this->assertSame('1000.00', $billOld->paid_amount);
        $this->assertSame('0.00', $billOld->balance);
        $this->assertSame('paid', $billOld->status);
        $this->assertSame('200.00', $billNew->paid_amount);
        $this->assertSame('800.00', $billNew->balance);
        $this->assertSame('partial', $billNew->status);

        $reversal = $service->reversePayment($payment->id, 'posted in error', $actor);

        $payment->refresh();
        $billOld->refresh();
        $billNew->refresh();

        $this->assertSame('reversed', $payment->status);
        $this->assertSame((string) $payment->id, (string) $reversal->payment_id);
        $this->assertSame((string) $school->id, (string) $reversal->school_id);
        $this->assertSame((int) $actor->id, (int) $reversal->reversed_by);
        $this->assertSame('posted in error', $reversal->reason);
        $this->assertNotNull($reversal->reversed_at);
        $this->assertDatabaseHas('shule_payment_reversals', [
            'payment_id' => $payment->id,
            'school_id' => $school->id,
        ]);

        $this->assertSame('0.00', $billOld->paid_amount);
        $this->assertSame('1000.00', $billOld->balance);
        $this->assertSame('unpaid', $billOld->status);
        $this->assertSame('0.00', $billNew->paid_amount);
        $this->assertSame('1000.00', $billNew->balance);
        $this->assertSame('unpaid', $billNew->status);
    }

    private function fakeUserWithPermission(string $permission): \App\Models\User
    {
        $user = \App\Models\User::query()->create([
            'name' => 'Tester',
            'email' => 'tester+' . Str::random(5) . '@example.com',
            'password' => bcrypt('secret'),
            'is_system_admin' => false,
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
