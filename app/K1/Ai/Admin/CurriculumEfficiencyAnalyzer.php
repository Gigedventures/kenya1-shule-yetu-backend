<?php

namespace App\K1\Ai\Admin;

use App\K1\Ai\Core\Curriculum\CBCMapper;
use Illuminate\Support\Facades\DB;

/**
 * CurriculumEfficiencyAnalyzer — Measures how effectively the CBC curriculum is being delivered.
 *
 * @package App\K1\Ai\Admin
 */
class CurriculumEfficiencyAnalyzer
{
    private CBCMapper $cbc;

    public function __construct()
    {
        $this->cbc = app(CBCMapper::class);
    }

    /**
     * Analyze curriculum delivery efficiency.
     *
     * @param string $schoolId
     * @return array{efficiency: float, top_subject: string, weak_subject: string, gaps: int}
     */
    public function analyze(string $schoolId): array
    {
        $curriculum = $this->cbc->load();
        $outcomes = DB::table('k1_lesson_outcomes')
            ->where('school_id', $schoolId)
            ->select('subject')
            ->selectRaw('
                AVG(effectiveness) as avg_effectiveness
            ')
            ->groupBy('subject')
            ->orderByDesc('avg_effectiveness')
            ->get()
            ->toArray();

        return [
            'efficiency'   => round(collect($outcomes)->avg('avg_effectiveness') ?? 0, 2),
            'top_subject'  => $outcomes[0]['subject'] ?? 'N/A',
            'weak_subject' => $outcomes[count($outcomes) - 1]['subject'] ?? 'N/A',
            'gaps'         => count($curriculum['subjects']) - count($outcomes),
        ];
    }
}