import 'package:flutter/material.dart';

import '../../models/ai_models.dart';
import '../../theme/k1_colors.dart';

/// 14-day learning plan card — shows daily schedule with focus/review sessions.
class LearningPlanCard extends StatelessWidget {
  const LearningPlanCard({
    super.key,
    required this.plan,
  });

  final LearningPlanResult plan;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: K1Colors.cardBg ?? const Color(0xFF1A2438),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                '14-Day Learning Plan',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 16,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF5EA3FF).withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${plan.totalSessions} sessions',
                  style: const TextStyle(
                    color: Color(0xFF5EA3FF),
                    fontWeight: FontWeight.w700,
                    fontSize: 13,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            'Est. ${plan.estimatedHours.toStringAsFixed(1)} hours',
            style: const TextStyle(color: Color(0xFF91A4C0), fontSize: 13),
          ),
          const SizedBox(height: 16),
          ...plan.plan.take(7).map(
            (day) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: _DayCard(day: day),
            ),
          ),
          if (plan.plan.length > 7)
            TextButton(
              onPressed: () {
                // TODO: expand full plan
              },
              child: const Text(
                'Show full 14 days →',
                style: TextStyle(color: Color(0xFF5EA3FF)),
              ),
            ),
        ],
      ),
    );
  }
}

class _DayCard extends StatelessWidget {
  const _DayCard({required this.day});
  final LearningPlanDay day;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFF2D3E5A).withValues(alpha: 0.5),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          SizedBox(
            width: 36,
            child: Text(
              'Day ${day.day}',
              style: const TextStyle(
                color: Color(0xFF5EA3FF),
                fontWeight: FontWeight.w800,
                fontSize: 12,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (day.focus.isNotEmpty)
                  Text(
                    'Focus: ${day.focus.join(', ')}',
                    style: const TextStyle(color: Colors.white, fontSize: 12),
                  ),
                if (day.review.isNotEmpty)
                  Text(
                    'Review: ${day.review.join(', ')}',
                    style: const TextStyle(color: Color(0xFFC4D2E6), fontSize: 12),
                  ),
                Text(
                  '${day.hours.toStringAsFixed(1)}h',
                  style: const TextStyle(
                    color: Color(0xFF5EA3FF),
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}