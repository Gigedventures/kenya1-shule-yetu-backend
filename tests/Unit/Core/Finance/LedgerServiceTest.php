<?php

namespace Tests\Unit\Core\Finance;

use App\Core\Finance\K1Account;
use App\Core\Finance\K1LedgerEntry;
use App\Core\Finance\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balanced_event_posting_creates_entries(): void
    {
        $service = app(LedgerService::class);
        $tenantId = '00000000-0000-0000-0000-000000000001';

        $service->postEvent($tenantId, 'payment', '00000000-0000-0000-0000-000000000011', 'Balanced post', [
            ['account_code' => '1000-CASH', 'debit' => 500, 'credit' => null],
            ['account_code' => '1100-AR', 'debit' => null, 'credit' => 500],
        ]);

        $debits = (float) K1LedgerEntry::query()->where('tenant_id', $tenantId)->sum('debit');
        $credits = (float) K1LedgerEntry::query()->where('tenant_id', $tenantId)->sum('credit');

        $this->assertSame(500.0, $debits);
        $this->assertSame(500.0, $credits);
        $this->assertSame(2, K1LedgerEntry::query()->where('tenant_id', $tenantId)->count());
    }

    public function test_unbalanced_event_throws_and_rolls_back(): void
    {
        $service = app(LedgerService::class);
        $tenantId = '00000000-0000-0000-0000-000000000002';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unbalanced ledger event.');

        try {
            $service->postEvent($tenantId, 'payment', '00000000-0000-0000-0000-000000000022', 'Unbalanced post', [
                ['account_code' => '1000-CASH', 'debit' => 700, 'credit' => null],
                ['account_code' => '1100-AR', 'debit' => null, 'credit' => 600],
            ]);
        } finally {
            $this->assertSame(0, K1LedgerEntry::query()->where('tenant_id', $tenantId)->count());
        }
    }

    public function test_reverse_event_mirrors_source_entries(): void
    {
        $service = app(LedgerService::class);
        $tenantId = '00000000-0000-0000-0000-000000000003';
        $sourceRef = '00000000-0000-0000-0000-000000000033';
        $reverseRef = '00000000-0000-0000-0000-000000000034';

        $service->postEvent($tenantId, 'payment', $sourceRef, 'Original', [
            ['account_code' => '1000-CASH', 'debit' => 800, 'credit' => null],
            ['account_code' => '1100-AR', 'debit' => null, 'credit' => 800],
        ]);

        $service->reverseEvent($tenantId, 'payment', $sourceRef, 'reversal', $reverseRef, 'Reverse');

        $source = K1LedgerEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('reference_type', 'payment')
            ->where('reference_id', $sourceRef)
            ->orderBy('account_id')
            ->get();
        $reversed = K1LedgerEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('reference_type', 'reversal')
            ->where('reference_id', $reverseRef)
            ->orderBy('account_id')
            ->get();

        $this->assertCount($source->count(), $reversed);

        foreach ($source as $i => $line) {
            $mirror = $reversed[$i];
            $this->assertSame((string) $line->account_id, (string) $mirror->account_id);
            $this->assertSame((float) $line->debit, (float) $mirror->credit);
            $this->assertSame((float) $line->credit, (float) $mirror->debit);
        }
    }

    public function test_tenant_specific_account_resolution_overrides_global(): void
    {
        $tenantId = '00000000-0000-0000-0000-000000000004';

        $global = K1Account::query()->create([
            'tenant_id' => null,
            'code' => '1100-AR',
            'name' => 'Global AR',
            'type' => 'asset',
            'is_system' => true,
        ]);
        K1Account::query()->create([
            'tenant_id' => $tenantId,
            'code' => '1100-AR',
            'name' => 'Tenant AR',
            'type' => 'asset',
            'is_system' => false,
        ]);
        K1Account::query()->create([
            'tenant_id' => $tenantId,
            'code' => '1000-CASH',
            'name' => 'Tenant Cash',
            'type' => 'asset',
            'is_system' => false,
        ]);

        $service = app(LedgerService::class);
        $service->postEvent($tenantId, 'payment', '00000000-0000-0000-0000-000000000044', 'Tenant account resolution', [
            ['account_code' => '1000-CASH', 'debit' => 300, 'credit' => null],
            ['account_code' => '1100-AR', 'debit' => null, 'credit' => 300],
        ]);

        $line = K1LedgerEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('credit', 300)
            ->firstOrFail();
        $this->assertNotSame((string) $global->id, (string) $line->account_id);
    }
}
