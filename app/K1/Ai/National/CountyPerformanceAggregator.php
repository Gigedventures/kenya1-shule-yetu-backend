<?php

namespace App\K1\Ai\National;

use App\Modules\ShuleYetu\Models\ShuleStudent;
use Illuminate\Support\Facades\DB;

/**
 * CountyPerformanceAggregator — Groups schools by county/region.
 *
 * Produces:
 *   - average performance per county
 *   - subject weakness maps
 *   - regional trend comparisons
 *
 * @package App\K1\Ai\National
 */
class CountyPerformanceAggregator
{
    /**
     * Aggregate school performance by county.
     *
     * @return array{counties: array[], weakest_subjects: string[], strongest_subjects: string[]}
     */
    public function aggregate(): array
    {
        $schools = DB::table('shule_schools')
            ->select(['id', 'name', 'region', 'county'])
            ->get()
            ->keyBy('id');

        $outcomes = DB::table('k1_lesson_outcomes')
            ->select('school_id')
            ->selectRaw('
                AVG(effectiveness) as avg_effectiveness,
                COUNT(*) as lesson_count
            ')
            ->groupBy('school_id')
            ->get();

        $counties = [];
        foreach ($outcomes as $o) {
            $school = $schools->get($o->school_id);
            $county = $school->county ?? 'Unknown';

            if (!isset($counties[$county])) {
                $counties[$county] = ['total_effectiveness' => 0, 'count' => 0];
            }
            $counties[$county]['total_effectiveness'] += (float) ($o->avg_effectiveness ?? 0);
            $counties[$county]['count']++;
        }

        $aggregated = [];
        foreach ($counties as $name => $data) {
            $aggregated[] = [
                'county'              => $name,
                'avg_effectiveness'   => $data['count'] > 0 ? round($data['total_effectiveness'] / $data['count'], 2) : 0,
                'schools_in_county'   => $data['count'],
            ];
        }

        usort($aggregated, fn ($a, $b) => $b['avg_effectiveness'] <=> $a['avg_effectiveness']);

        $subjects = DB::table('k1_lesson_outcomes')
            ->select('subject')
            ->selectRaw('AVG(effectiveness) as avg')
            ->groupBy('subject')
            ->orderByDesc('avg')
            ->get();

        return [
            'counties'          => $aggregated,
            'weakest_subjects'  => array_slice(array_column((array) $subjects, 'subject'), -3),
            'strongest_subjects' => array_slice(array_column((array) $subjects, 'subject'), 0, 3),
        ];
    }
}