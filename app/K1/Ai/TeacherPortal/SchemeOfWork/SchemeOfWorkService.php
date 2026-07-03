<?php

namespace App\K1\Ai\TeacherPortal\SchemeOfWork;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class SchemeOfWorkService
{
    private CBCMapper $cbc;

    public function __construct() { $this->cbc = app(CBCMapper::class); }

    public function autoGenerate(string $subject, string $term): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $weeks = [];
        for ($w = 1; $w <= 13; $w++) {
            $isRevision = $w % 4 === 0;
            $topic = "Topic {$w}: " . ($isRevision ? "Revision of {$subject}" : "{$subject} — {$this->resolveWeekCompetency($w)}");
            $weeks[] = [
                'week' => $w, 'topic' => $topic, 'type' => $isRevision ? 'revision' : 'new',
                'competency' => $this->resolveWeekCompetency($w),
            ];
        }
        return ['term' => $term, 'subject' => $subject, 'weeks' => $weeks];
    }

    public function reorder(string $schemeId, array $newOrder): array
    {
        $existing = DB::table('k1_schemes')->find($schemeId);
        if (!$existing) throw new \RuntimeException('Scheme not found');
        $weeks = json_decode($existing->weeks, true);
        $reordered = [];
        foreach ($newOrder as $idx) { $reordered[] = $weeks[$idx] ?? throw new \RuntimeException("Invalid index"); }
        DB::table('k1_schemes')->where('id', $schemeId)->update(['weeks' => json_encode($reordered), 'updated_at' => now()]);
        return ['reordered' => true, 'weeks' => $reordered];
    }

    public function trackProgress(string $schemeId): array
    {
        $s = DB::table('k1_schemes')->find($schemeId);
        $weeks = json_decode($s->weeks, true);
        $completed = collect($weeks)->filter(fn($w) => $w['status'] ?? 'pending' === 'completed')->count();
        return ['total' => count($weeks), 'completed' => $completed, 'progress' => round($completed / count($weeks) * 100, 1)];
    }

    private function resolveWeekCompetency(int $week): string
    {
        return ['Remember', 'Understand', 'Apply', 'Analyze', 'Evaluate', 'Create'][($week - 1) % 6];
    }
}