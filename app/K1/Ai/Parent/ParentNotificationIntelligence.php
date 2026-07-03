<?php

namespace App\K1\Ai\Parent;

use App\K1\Ai\Core\LearningLoop\AdaptiveWeightsEngine;
use Illuminate\Support\Facades\DB;

/**
 * ParentNotificationIntelligence — Sends smart notifications to parents.
 *
 * @package App\K1\Ai\Parent
 */
class ParentNotificationIntelligence
{
    /**
     * Generate a notification for a parent about their child's progress.
     *
     * @param array $input {student_id, subject, score, trend}
     * @return array{notification: string, priority: string, action: string}
     */
    public function notify(array $input): array
    {
        $subject = $input['subject'] ?? 'General';
        $score   = $input['score'] ?? 50;
        $trend   = $input['trend'] ?? 'stable';

        $priority = match (true) {
            $score < 40 => 'urgent',
            $score < 60 => 'important',
            default     => 'normal',
        };

        return [
            'notification' => "Your child scored {$score}% in {$subject} this week.",
            'priority'     => $priority,
            'action'      => match ($priority) {
                'urgent' => 'Please schedule a parent-teacher meeting',
                'important' => 'Review the weekly practice materials',
                default => 'Keep up the good work at home',
            },
        ];
    }
}