<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Inventory\Services\InventoryService;
use App\Modules\ShuleYetu\Models\ShuleItem;
use App\Modules\ShuleYetu\Models\ShuleItemCategory;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStockEntry;
use App\Modules\ShuleYetu\Models\ShuleStockIssue;
use App\Modules\ShuleYetu\Models\ShuleVendor;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class InventoryLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_levels_update_correctly(): void
    {
        [$item] = $this->seedInventorySetup();
        $service = app(InventoryService::class);

        $entry = ShuleStockEntry::query()->create([
            'item_id' => $item->id,
            'qty' => 10,
            'unit_cost' => 100,
            'entry_date' => now()->toDateString(),
            'status' => 'draft',
        ]);
        $this->assertSame(0.0, $service->currentStock($item->id));

        $service->postStockEntry($entry->id);
        $this->assertSame(10.0, $service->currentStock($item->id));

        $issue = ShuleStockIssue::query()->create([
            'item_id' => $item->id,
            'qty' => 3,
            'issue_date' => now()->toDateString(),
            'issued_to' => 'Kitchen',
            'status' => 'draft',
        ]);

        $postedIssue = $service->postStockIssue($issue->id);
        $this->assertSame(7.0, $service->currentStock($item->id));
        $this->assertSame(100.0, (float) $postedIssue->unit_cost);
        $this->assertSame(300.0, (float) $postedIssue->total_cost);
    }

    public function test_ledger_is_balanced_on_stock_entry_and_issue_post(): void
    {
        [$item] = $this->seedInventorySetup();
        $service = app(InventoryService::class);
        $schoolId = app(SchoolContext::class)->requireId();

        $entry = ShuleStockEntry::query()->create([
            'item_id' => $item->id,
            'qty' => 8,
            'unit_cost' => 75,
            'entry_date' => now()->toDateString(),
            'status' => 'draft',
        ]);
        $service->postStockEntry($entry->id);

        $issue = ShuleStockIssue::query()->create([
            'item_id' => $item->id,
            'qty' => 2,
            'issue_date' => now()->toDateString(),
            'issued_to' => 'Lab',
            'status' => 'draft',
        ]);
        $service->postStockIssue($issue->id);

        $entryDebits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $schoolId)
            ->where('reference_type', 'stock_entry')
            ->where('reference_id', $entry->id)
            ->sum('debit');
        $entryCredits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $schoolId)
            ->where('reference_type', 'stock_entry')
            ->where('reference_id', $entry->id)
            ->sum('credit');

        $issueDebits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $schoolId)
            ->where('reference_type', 'stock_issue')
            ->where('reference_id', $issue->id)
            ->sum('debit');
        $issueCredits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $schoolId)
            ->where('reference_type', 'stock_issue')
            ->where('reference_id', $issue->id)
            ->sum('credit');

        $this->assertSame(600.0, $entryDebits);
        $this->assertSame(600.0, $entryCredits);
        $this->assertSame(150.0, $issueDebits);
        $this->assertSame(150.0, $issueCredits);
    }

    public function test_cannot_post_stock_entry_or_issue_twice(): void
    {
        [$item] = $this->seedInventorySetup();
        $service = app(InventoryService::class);

        $entry = ShuleStockEntry::query()->create([
            'item_id' => $item->id,
            'qty' => 5,
            'unit_cost' => 20,
            'entry_date' => now()->toDateString(),
            'status' => 'draft',
        ]);
        $service->postStockEntry($entry->id);

        try {
            $service->postStockEntry($entry->id);
            $this->fail('Expected duplicate stock entry post to fail.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('already posted', $e->getMessage());
        }

        $issue = ShuleStockIssue::query()->create([
            'item_id' => $item->id,
            'qty' => 1,
            'issue_date' => now()->toDateString(),
            'status' => 'draft',
        ]);
        $service->postStockIssue($issue->id);

        $this->expectException(RuntimeException::class);
        $service->postStockIssue($issue->id);
    }

    private function seedInventorySetup(): array
    {
        $school = ShuleSchool::query()->create([
            'name' => 'Inventory School',
            'code' => 'INV-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);

        $category = ShuleItemCategory::query()->create([
            'name' => 'Supplies',
        ]);
        $item = ShuleItem::query()->create([
            'category_id' => $category->id,
            'sku' => 'SKU-' . Str::upper(Str::random(6)),
            'name' => 'Printer Paper',
            'unit' => 'ream',
            'reorder_level' => 3,
        ]);

        ShuleVendor::query()->create([
            'name' => 'Stationers Ltd',
            'contacts' => ['phone' => '0700000000', 'email' => 'vendor@example.test'],
        ]);

        return [$item];
    }
}
