<?php

namespace App\K1\Ai\ParentPortal\Trips;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class TripTrackingEngine
{
    public function getTrip(string $tripId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $trip = DB::table('k1_trips')->find($tripId);
        if (!$trip) throw new \RuntimeException('Trip not found');
        return ['trip_id' => $tripId, 'destination' => $trip->destination, 'departure' => now(), 'arrival' => now()->addHours(3), 'students' => 30];
    }

    public function trackCheckpoint(string $tripId, array $checkpoint): array
    {
        DB::table('k1_trip_checkpoints')->insert(['trip_id' => $tripId, 'location' => $checkpoint['location'], 'reached_at' => now()]);
        return ['tracked' => true];
    }

    public function getPhotos(string $tripId): array
    {
        return DB::table('k1_trip_photos')->where('trip_id', $tripId)->orderByDesc('created_at')->get()->toArray();
    }
}