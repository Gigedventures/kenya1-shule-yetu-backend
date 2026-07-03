<?php

namespace App\K1\Ai\Student\Levels;

use App\Modules\ShuleYetu\Models\ShuleStudent;
use Illuminate\Support\Facades\DB;

/**
 * StudentLevelAdapter — Adapts the student experience per educational level.
 *
 * Pre-Primary:  play-based, simple interface, large cards
 * Lower Primary: visual progress, easy navigation
 * Upper Primary: subject-focused, competency tracking
 * Junior:       revision support, skill development
 * Senior:       advanced analytics, career readiness, university prep
 */
class StudentLevelAdapter
{
    public function adapt(string $studentId): array
    {
        $student = ShuleStudent::findOrFail($studentId);
        $class = $student->current_class_id;

        $className = DB::table('shule_classes')->where('id', $class)->value('name') ?? '';

        if (stripos($className, 'PP') !== false || stripos($className, 'Pre') !== false) {
            return ['level' => 'pre_primary', 'experience' => 'play_based', 'features' => ['games', 'stories', 'coloring'], 'ui' => 'large_cards'];
        }

        if (stripos($className, 'Grade 1') !== false || stripos($className, 'Grade 2') !== false || stripos($className, 'Grade 3') !== false) {
            return ['level' => 'lower_primary', 'experience' => 'guided', 'features' => ['reading', 'writing', 'counting'], 'ui' => 'simple_nav'];
        }

        if (stripos($className, 'Grade 4') !== false || stripos($className, 'Grade 5') !== false || stripos($className, 'Grade 6') !== false) {
            return ['level' => 'upper_primary', 'experience' => 'subject_focused', 'features' => ['subjects', 'competency', 'revision'], 'ui' => 'tabs'];
        }

        if (stripos($className, 'Grade 7') !== false || stripos($className, 'Grade 8') !== false || stripos($className, 'Grade 9') !== false) {
            return ['level' => 'junior', 'experience' => 'skill_based', 'features' => ['skills', 'career', 'projects'], 'ui' => 'compact'];
        }

        if (stripos($className, 'Senior') !== false) {
            return ['level' => 'senior', 'experience' => 'advanced_analytics', 'features' => ['analytics', 'career_path', 'university', 'forecasting'], 'ui' => 'dense'];
        }

        return ['level' => 'unknown', 'experience' => 'standard', 'features' => ['general'], 'ui' => 'default'];
    }
}