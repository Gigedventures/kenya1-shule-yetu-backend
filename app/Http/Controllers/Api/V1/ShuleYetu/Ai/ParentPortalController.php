<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\ParentPortal\Dashboard\ParentDashboardService;
use App\K1\Ai\ParentPortal\LiveTracking\StudentPresenceEngine;
use App\K1\Ai\ParentPortal\Transport\BusTrackingEngine;
use App\K1\Ai\ParentPortal\Trips\TripTrackingEngine;
use App\K1\Ai\ParentPortal\Academics\ParentAcademicService;
use App\K1\Ai\ParentPortal\Finance\ParentFinanceService;
use App\K1\Ai\ParentPortal\Communication\ParentCommunicationService;
use App\K1\Ai\ParentPortal\Wellbeing\ParentWellbeingService;
use App\K1\Ai\ParentPortal\Attendance\ParentAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    public function __construct(
        private ParentDashboardService $dashboard,
        private StudentPresenceEngine $presence,
        private BusTrackingEngine $bus,
        private TripTrackingEngine $trips,
        private ParentAcademicService $academics,
        private ParentFinanceService $finance,
        private ParentCommunicationService $comm,
        private ParentWellbeingService $wellbeing,
        private ParentAttendanceService $attendance,
    ) {}

    // ---- DASHBOARD (5 features) ----
    public function home(string $parent): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->dashboard->build($parent)); }

    public function switch(string $parent, string $child): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->dashboard->switchChild($parent, $child)); }

    // ---- LIVE STATUS (8 features) ----
    public function liveStatus(string $student): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->presence->getStatus($student)); }

    public function busLocation(string $bus): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->bus->getBusLocation($bus)); }

    public function confirmPickup(Request $r): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->bus->confirmPickup($r->input('student'))); }

    public function confirmDropoff(Request $r): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->bus->confirmDropoff($r->input('student'))); }

    public function routeHistory(string $student): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->bus->getRouteHistory($student)); }

    // ---- TRIPS (5 features) ----
    public function trip(string $trip): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->trips->getTrip($trip)); }

    public function tripPhotos(string $trip): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->trips->getPhotos($trip)); }

    // ---- ACADEMICS (5 features) ----
    public function insights(string $student): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->academics->getInsights($student)); }

    // ---- FINANCE (4 features) ----
    public function statement(string $student): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->finance->getStatement($student)); }

    public function paymentHistory(string $student): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->finance->getHistory($student)); }

    // ---- COMMUNICATION (4 features) ----
    public function messages(string $parent): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->comm->getThreads($parent)); }

    public function sendMessage(Request $r): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->comm->send($r->input('parent'), $r->input('teacher'), $r->input('message'))); }

    // ---- WELLBEING (2 features) ----
    public function wellness(string $student): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->wellbeing->getDashboard($student)); }

    // ---- ATTENDANCE (4 features) ----
    public function attendanceSummary(string $student): JsonResponse
    { $this->authorize('parent.view'); return response()->json($this->attendance->getSummary($student)); }

    private function authorize(string $permission): void
    { abort_unless(auth()->user()?->hasPermission($permission), 403); }
}