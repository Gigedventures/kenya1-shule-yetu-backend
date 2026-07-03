<?php

namespace App\K1\Ai\ParentPortal\Transport;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class BusTrackingEngine
{
    public function getBusLocation(string $busId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $route = DB::table('k1_bus_routes')->where('bus_id', $busId)->first();
        if (!$route) throw new \RuntimeException('Bus not found');
        return [
            'bus_id' => $busId,
            'lat' => -1.2921,
            'lng' => 36.8219,
            'speed' => 45,
            'eta' => '15 min',
            'occupied_seats' => 30,
            'capacity' => 50,
            'route' => $route->stops ?? [],
        ];
    }

    public function confirmPickup(string $studentId): array
    {
        DB::table('k1_bus_attendance')->updateOrInsert(
            ['student_id' => $studentId, 'date' => today()->toDateString()],
            ['status' => 'picked_up', 'picked_at' => now()]
        );
        return ['confirmed' => true];
    }

    public function confirmDropoff(string $studentId): array
    {
        DB::table('k1_bus_attendance')->where('student_id', $studentId)->whereDate('date', today())->update(['status' => 'dropped_off', 'dropped_at' => now()]);
        return ['confirmed' => true];
    }

    public function getRouteHistory(string $studentId): array
    {
        return DB::table('k1_bus_attendance')
            ->where('student_id', $studentId)
            ->orderByDesc('date')
            ->get()
            ->toArray();
    }
}