<?php

namespace App\Modules\ShuleYetu\Inventory\Services;

use App\Core\Finance\LedgerService;
use App\Modules\ShuleYetu\Models\ShuleStockEntry;
use App\Modules\ShuleYetu\Models\ShuleStockIssue;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    private const ACCOUNT_CASH = '1000-CASH';
    private const ACCOUNT_INVENTORY = '1200-INVENTORY';
    private const ACCOUNT_INVENTORY_EXPENSE = '5200-INVENTORY-EXPENSE';

    public function __construct(private readonly LedgerService $ledger)
    {
    }

    public function currentStock(string $itemId): float
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $this->assertItemInSchool($itemId, $schoolId);

        return $this->currentStockForItem($schoolId, $itemId);
    }

    public function postStockEntry(string $entryId): ShuleStockEntry
    {
        $schoolId = app(SchoolContext::class)->requireId();

        return DB::transaction(function () use ($entryId, $schoolId): ShuleStockEntry {
            $entry = ShuleStockEntry::query()
                ->where('school_id', $schoolId)
                ->where('id', $entryId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($entry->status === 'posted') {
                throw new RuntimeException('Stock entry is already posted.');
            }

            $qty = (float) $entry->qty;
            $unitCost = (float) $entry->unit_cost;
            if ($qty <= 0 || $unitCost < 0) {
                throw new RuntimeException('Stock entry quantity and cost must be valid.');
            }

            $amount = round($qty * $unitCost, 2);

            $this->ledger->postEvent(
                $schoolId,
                'stock_entry',
                (string) $entry->id,
                sprintf('Stock entry posted for item %s', (string) $entry->item_id),
                [
                    [
                        'account_code' => self::ACCOUNT_INVENTORY,
                        'debit' => $amount,
                        'credit' => null,
                    ],
                    [
                        'account_code' => self::ACCOUNT_CASH,
                        'debit' => null,
                        'credit' => $amount,
                    ],
                ]
            );

            $entry->status = 'posted';
            $entry->posted_at = now();
            $entry->save();

            return $entry;
        });
    }

    public function postStockIssue(string $issueId): ShuleStockIssue
    {
        $schoolId = app(SchoolContext::class)->requireId();

        return DB::transaction(function () use ($issueId, $schoolId): ShuleStockIssue {
            $issue = ShuleStockIssue::query()
                ->where('school_id', $schoolId)
                ->where('id', $issueId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($issue->status === 'posted') {
                throw new RuntimeException('Stock issue is already posted.');
            }

            $issueQty = (float) $issue->qty;
            if ($issueQty <= 0) {
                throw new RuntimeException('Stock issue quantity must be greater than zero.');
            }

            // Lock inventory movements for deterministic valuation and quantity checks.
            $entryRows = DB::table('shule_stock_entries')
                ->where('school_id', $schoolId)
                ->where('item_id', $issue->item_id)
                ->where('status', 'posted')
                ->lockForUpdate()
                ->get(['qty', 'unit_cost']);
            $issueRows = DB::table('shule_stock_issues')
                ->where('school_id', $schoolId)
                ->where('item_id', $issue->item_id)
                ->where('status', 'posted')
                ->lockForUpdate()
                ->get(['qty', 'unit_cost', 'total_cost']);

            $entryQty = (float) $entryRows->sum('qty');
            $postedIssueQty = (float) $issueRows->sum('qty');
            $availableQty = round($entryQty - $postedIssueQty, 3);
            if ($availableQty < round($issueQty, 3)) {
                throw new RuntimeException('Insufficient stock for posting stock issue.');
            }

            $entryValue = (float) $entryRows->sum(fn ($row) => (float) $row->qty * (float) $row->unit_cost);
            $issuedValue = (float) $issueRows->sum(function ($row): float {
                if ($row->total_cost !== null) {
                    return (float) $row->total_cost;
                }

                return (float) $row->qty * (float) ($row->unit_cost ?? 0);
            });
            $remainingValue = $entryValue - $issuedValue;
            $averageCost = $availableQty > 0 ? round($remainingValue / $availableQty, 6) : 0.0;
            $totalCost = round($issueQty * $averageCost, 2);

            $this->ledger->postEvent(
                $schoolId,
                'stock_issue',
                (string) $issue->id,
                sprintf('Stock issue posted for item %s', (string) $issue->item_id),
                [
                    [
                        'account_code' => self::ACCOUNT_INVENTORY_EXPENSE,
                        'debit' => $totalCost,
                        'credit' => null,
                    ],
                    [
                        'account_code' => self::ACCOUNT_INVENTORY,
                        'debit' => null,
                        'credit' => $totalCost,
                    ],
                ]
            );

            $issue->unit_cost = round($averageCost, 2);
            $issue->total_cost = $totalCost;
            $issue->status = 'posted';
            $issue->posted_at = now();
            $issue->save();

            return $issue;
        });
    }

    private function assertItemInSchool(string $itemId, string $schoolId): void
    {
        $itemSchoolId = DB::table('shule_items')
            ->where('id', $itemId)
            ->value('school_id');
        if (!$itemSchoolId || $itemSchoolId !== $schoolId) {
            throw new RuntimeException('Item must belong to active school.');
        }
    }

    private function currentStockForItem(string $schoolId, string $itemId): float
    {
        $totalIn = (float) DB::table('shule_stock_entries')
            ->where('school_id', $schoolId)
            ->where('item_id', $itemId)
            ->where('status', 'posted')
            ->sum('qty');

        $totalOut = (float) DB::table('shule_stock_issues')
            ->where('school_id', $schoolId)
            ->where('item_id', $itemId)
            ->where('status', 'posted')
            ->sum('qty');

        return round($totalIn - $totalOut, 3);
    }
}
