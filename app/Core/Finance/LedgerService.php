<?php

namespace App\Core\Finance;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class LedgerService
{
    public function postEvent(
        string $tenantId,
        string $referenceType,
        string $referenceId,
        string $description,
        array $lines
    ): void {
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($lines as $line) {
            $totalDebit += isset($line['debit']) && $line['debit'] !== null ? round((float) $line['debit'], 2) : 0.0;
            $totalCredit += isset($line['credit']) && $line['credit'] !== null ? round((float) $line['credit'], 2) : 0.0;
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new RuntimeException('Unbalanced ledger event.');
        }

        DB::transaction(function () use ($tenantId, $referenceType, $referenceId, $description, $lines): void {
            foreach ($lines as $line) {
                $account = $this->resolveAccount($tenantId, (string) $line['account_code']);
                $debit = isset($line['debit']) && $line['debit'] !== null ? round((float) $line['debit'], 2) : null;
                $credit = isset($line['credit']) && $line['credit'] !== null ? round((float) $line['credit'], 2) : null;

                K1LedgerEntry::query()->create([
                    'tenant_id' => $tenantId,
                    'account_id' => $account->id,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $description,
                ]);
            }
        });
    }

    public function reverseEvent(
        string $tenantId,
        string $sourceReferenceType,
        string $sourceReferenceId,
        string $targetReferenceType,
        string $targetReferenceId,
        string $description
    ): void {
        $sourceLines = K1LedgerEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('reference_type', $sourceReferenceType)
            ->where('reference_id', $sourceReferenceId)
            ->with('account:id,code')
            ->lockForUpdate()
            ->get();

        if ($sourceLines->isEmpty()) {
            throw new RuntimeException('No source ledger entries found for reversal.');
        }

        $lines = [];
        foreach ($sourceLines as $line) {
            $lines[] = [
                'account_code' => (string) $line->account->code,
                'debit' => $line->credit !== null ? (float) $line->credit : null,
                'credit' => $line->debit !== null ? (float) $line->debit : null,
            ];
        }

        $this->postEvent($tenantId, $targetReferenceType, $targetReferenceId, $description, $lines);
    }

    private function resolveAccount(string $tenantId, string $accountCode): K1Account
    {
        $account = K1Account::query()
            ->where('code', $accountCode)
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->orderByRaw('CASE WHEN tenant_id IS NULL THEN 1 ELSE 0 END')
            ->first();

        return $account ?? $this->createSystemAccountIfMissing($accountCode);
    }

    private function createSystemAccountIfMissing(string $accountCode): K1Account
    {
        $metadata = [
            '1100-AR' => ['name' => 'Accounts Receivable', 'type' => 'asset'],
            '1000-CASH' => ['name' => 'Cash', 'type' => 'asset'],
            '1200-INVENTORY' => ['name' => 'Inventory', 'type' => 'asset'],
            '4000-BILLING-REVENUE' => ['name' => 'Billing Revenue', 'type' => 'revenue'],
            '4100-TRANSPORT-REVENUE' => ['name' => 'Transport Revenue', 'type' => 'revenue'],
            '5000-EXPENSE' => ['name' => 'General Expense', 'type' => 'expense'],
        ];

        if (!isset($metadata[$accountCode])) {
            if (str_starts_with($accountCode, '5')) {
                $metadata[$accountCode] = ['name' => 'Expense ' . $accountCode, 'type' => 'expense'];
            } else {
                throw new RuntimeException("Account {$accountCode} not configured.");
            }
        }

        return K1Account::query()->firstOrCreate(
            [
                'tenant_id' => null,
                'code' => $accountCode,
            ],
            [
                'name' => $metadata[$accountCode]['name'],
                'type' => $metadata[$accountCode]['type'],
                'is_system' => true,
            ]
        );
    }
}
