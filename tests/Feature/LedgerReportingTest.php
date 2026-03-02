<?php

namespace Tests\Feature;

use App\Core\Finance\K1LedgerEntry;
use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeItem;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class LedgerReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ledger_entries_and_reports_are_correct(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School L',
            'code' => 'FN-L-' . Str::upper(Str::random(5)),
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
            'name' => 'Grade 5',
            'level' => 5,
        ]);

        $structure1 = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term1->id,
            'class_id' => $class->id,
            'name' => 'Term 1 Fees',
            'is_active' => true,
        ]);
        $structure2 = ShuleFeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term2->id,
            'class_id' => $class->id,
            'name' => 'Term 2 Fees',
            'is_active' => true,
        ]);

        ShuleFeeItem::query()->create([
            'fee_structure_id' => $structure1->id,
            'name' => 'Tuition T1',
            'amount' => 1000,
            'is_mandatory' => true,
        ]);
        ShuleFeeItem::query()->create([
            'fee_structure_id' => $structure2->id,
            'name' => 'Tuition T2',
            'amount' => 1000,
            'is_mandatory' => true,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Ledger',
            'last_name' => 'Student',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);
        ShuleStudentEnrollment::query()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'status' => 'enrolled',
        ]);

        $service = app(FeeService::class);
        $service->generateBillsForStructure($structure1->id, $school->id);
        $service->generateBillsForStructure($structure2->id, $school->id);

        $actor = $this->fakeUserWithPermission('finance.payments.record');
        $payment1 = $service->recordPayment($student->id, 1200, 'cash', 'PAY-1', $actor);
        $service->reversePayment($payment1->id, 'reversal test', $actor);
        $payment2 = $service->recordPayment($student->id, 900, 'cash', 'PAY-2', $actor);

        $this->assertDatabaseHas('k1_ledger_entries', [
            'tenant_id' => $school->id,
            'reference_type' => 'payment',
            'reference_id' => $payment1->id,
            'credit' => 1200.00,
        ]);
        $this->assertDatabaseHas('k1_ledger_entries', [
            'tenant_id' => $school->id,
            'reference_type' => 'payment',
            'reference_id' => $payment2->id,
            'credit' => 900.00,
        ]);

        $billDebit = (float) K1LedgerEntry::query()
            ->where('tenant_id', $school->id)
            ->where('reference_type', 'bill')
            ->sum('debit');
        $paymentCredit = (float) K1LedgerEntry::query()
            ->where('tenant_id', $school->id)
            ->where('reference_type', 'payment')
            ->sum('credit');
        $reversalDebit = (float) K1LedgerEntry::query()
            ->where('tenant_id', $school->id)
            ->where('reference_type', 'reversal')
            ->sum('debit');

        $this->assertSame(2000.0, $billDebit);
        $this->assertSame(2100.0, $paymentCredit);
        $this->assertSame(1200.0, $reversalDebit);

        $statement = $service->generateStudentStatement($student->id);
        $this->assertSame(2000.0, (float) $statement['summary']['total_billed']);
        $this->assertSame(2100.0, (float) $statement['summary']['total_paid']);
        $this->assertSame(1200.0, (float) $statement['summary']['total_reversed']);
        $this->assertSame(900.0, (float) $statement['summary']['net_paid']);
        $this->assertSame(1100.0, (float) $statement['summary']['outstanding']);

        $revenueSummary = $service->getSchoolRevenueSummaryReport();
        $this->assertSame(2000.0, (float) $revenueSummary['total_billed']);
        $this->assertSame(2100.0, (float) $revenueSummary['total_collected']);
        $this->assertSame(1200.0, (float) $revenueSummary['total_reversed']);
        $this->assertSame(900.0, (float) $revenueSummary['net_revenue']);

        $outstanding = $service->getOutstandingBalancesReport();
        $this->assertSame(1100.0, (float) $outstanding['total_outstanding']);

        $revenueByTerm = $service->getRevenueByTermReport();
        $term1Revenue = collect($revenueByTerm['terms'])->firstWhere('term_id', $term1->id);
        $term2Revenue = collect($revenueByTerm['terms'])->firstWhere('term_id', $term2->id);

        $collectedByTerm = collect([$term1Revenue, $term2Revenue])
            ->filter()
            ->sum(fn ($row) => (float) $row->revenue_collected);
        $this->assertSame(900.0, $collectedByTerm);

        $entry = K1LedgerEntry::query()->where('tenant_id', $school->id)->firstOrFail();
        $this->expectException(RuntimeException::class);
        $entry->update(['description' => 'mutated']);
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
