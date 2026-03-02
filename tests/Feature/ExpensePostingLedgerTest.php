<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleExpense;
use App\Modules\ShuleYetu\Models\ShuleExpenseCategory;
use App\Modules\ShuleYetu\Models\ShuleFinancialYear;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ExpensePostingLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_expense_creates_balanced_ledger_entries_and_cannot_post_twice(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School E',
            'code' => 'EXP-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);

        $fy = ShuleFinancialYear::query()->create([
            'name' => 'FY 2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);
        $category = ShuleExpenseCategory::query()->create([
            'name' => 'Utilities',
            'expense_account_code' => '5100-UTILITIES',
        ]);
        $creator = $this->fakeUserWithPermission('finance.manage');

        $expense = ShuleExpense::query()->create([
            'financial_year_id' => $fy->id,
            'category_id' => $category->id,
            'amount' => 250,
            'description' => 'Water bill',
            'expense_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => $creator->id,
        ]);

        $service = app(FeeService::class);
        $posted = $service->postExpense($expense->id, $creator);
        $this->assertSame('posted', $posted->status);

        $debits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $school->id)
            ->where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->sum('debit');
        $credits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $school->id)
            ->where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->sum('credit');

        $this->assertSame(250.0, $debits);
        $this->assertSame(250.0, $credits);

        $this->expectException(RuntimeException::class);
        $service->postExpense($expense->id, $creator);
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
