<?php

namespace App\K1\Ai\ParentPortal\Dashboard;

use App\K1\Ai\ParentPortal\Academics\ParentAcademicService;
use App\K1\Ai\ParentPortal\Finance\ParentFinanceService;
use App\K1\Ai\ParentPortal\Communication\ParentCommunicationService;
use App\K1\Ai\ParentPortal\Attendance\ParentAttendanceService;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class ParentDashboardService
{
    public function __construct(
        private ParentAcademicService $academics,
        private ParentFinanceService $finance,
        private ParentCommunicationService $comm,
        private ParentAttendanceService $attendance,
    ) {}

    public function build(string $parentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $children = DB::table('shule_student_guardian')->where('guardian_id', $parentId)->get()->toArray();
        $childSummaries = [];
        foreach ($children as $child) {
            $childSummaries[] = [
                'student_id' => $child->student_id,
                'academic' => $this->academics->getInsights($child->student_id),
                'finance' => $this->finance->getStatement($child->student_id),
                'attendance' => $this->attendance->getSummary($child->student_id),
            ];
        }
        return ['parent_id' => $parentId, 'children' => $childSummaries];
    }

    public function switchChild(string $parentId, string $childId): array
    {
        return $this->build($parentId);
    }
}