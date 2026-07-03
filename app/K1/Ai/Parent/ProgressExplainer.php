<?php

namespace App\K1\Ai\Parent;

use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Models\ShuleExamScore;

/**
 * ProgressExplainer — Explains student progress in simple, non-technical terms.
 *
 * @package App\K1\Ai\Parent
 */
class ProgressExplainer
{
    /**
     * Explain a student's academic progress.
     *
     * @param string $studentId
     * @return array{explanation: string, trend_direction: string, benchmark: string}
     */
    public function explain(string $studentId): array
    {
        $results = ShuleTermResult::query()
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();

        $count = count($results);
        $trend = $count > 1 ? ($results[0]['average'] - $results[1]['average']) : 0;

        return [
            'explanation' => $trend > 0
                ? "Your child's performance is improving — keep up the good work!"
                : ($trend < 0 ? "Your child's scores have dropped slightly — let's talk about it." : "Your child's performance is steady — no change this term."),
            'trend_direction' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable'),
            'benchmark' => $results[0]['average'] >= 70
                ? 'Above expected level' : ($results[0]['average'] >= 40 ? 'At expected level' : 'Below expected'),
        ];
    }
}