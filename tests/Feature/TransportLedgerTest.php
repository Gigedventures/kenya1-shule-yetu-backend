<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleRoute;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleVehicle;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use App\Modules\ShuleYetu\Transport\Services\TransportService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransportLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_fee_applied_when_amount_not_provided(): void
    {
        [$school, $student] = $this->seedSchoolWithStudent();
        $route = ShuleRoute::query()->create([
            'name' => 'Morning Route',
            'description' => 'North wing pickup',
            'fee_amount' => 3200,
        ]);

        $service = app(TransportService::class);
        $actor = $this->makeUser();
        $payment = $service->recordTransportPayment($student->id, $route->id, null, 'cash', $actor);

        $this->assertSame((string) $school->id, (string) $payment->school_id);
        $this->assertSame('3200.00', $payment->amount);
        $this->assertSame('posted', $payment->status);
    }

    public function test_transport_payment_creates_balanced_ledger_entries(): void
    {
        [$school, $student] = $this->seedSchoolWithStudent();
        $route = ShuleRoute::query()->create([
            'name' => 'Evening Route',
            'fee_amount' => 1500,
        ]);

        $payment = app(TransportService::class)->recordTransportPayment(
            $student->id,
            $route->id,
            null,
            'mobile_money',
            $this->makeUser()
        );

        $debits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $school->id)
            ->where('reference_type', 'transport_payment')
            ->where('reference_id', $payment->id)
            ->sum('debit');
        $credits = (float) DB::table('k1_ledger_entries')
            ->where('tenant_id', $school->id)
            ->where('reference_type', 'transport_payment')
            ->where('reference_id', $payment->id)
            ->sum('credit');

        $this->assertSame(1500.0, $debits);
        $this->assertSame(1500.0, $credits);
    }

    public function test_vehicle_plate_no_is_unique_per_school(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'Vehicle School A',
            'code' => 'VSA-' . Str::upper(Str::random(4)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($schoolA->id);

        ShuleVehicle::query()->create([
            'plate_no' => 'KDA-001A',
            'capacity' => 45,
            'status' => 'active',
        ]);

        $schoolB = ShuleSchool::query()->create([
            'name' => 'Vehicle School B',
            'code' => 'VSB-' . Str::upper(Str::random(4)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($schoolB->id);
        ShuleVehicle::query()->create([
            'plate_no' => 'KDA-001A',
            'capacity' => 40,
            'status' => 'active',
        ]);

        app(SchoolContext::class)->setId($schoolA->id);
        $this->expectException(QueryException::class);
        ShuleVehicle::query()->create([
            'plate_no' => 'KDA-001A',
            'capacity' => 50,
            'status' => 'active',
        ]);
    }

    private function seedSchoolWithStudent(): array
    {
        $school = ShuleSchool::query()->create([
            'name' => 'Transport School',
            'code' => 'TRN-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);

        $class = ShuleClass::query()->create([
            'name' => 'Grade T',
            'level' => 1,
        ]);

        $student = ShuleStudent::query()->create([
            'first_name' => 'Tran',
            'last_name' => 'Sport',
            'current_class_id' => $class->id,
            'status' => 'active',
        ]);

        return [$school, $student];
    }

    private function makeUser(): User
    {
        return User::query()->create([
            'name' => 'Transport User',
            'email' => 'transport+' . Str::random(6) . '@example.com',
            'password' => bcrypt('secret'),
            'is_system_admin' => false,
        ]);
    }
}
