import 'package:flutter/material.dart';

import '../../models/ai_models.dart';
import '../../theme/k1_colors.dart';

/// CBC competency gap analysis card — shows gaps vs expectations with interventions.
class CompetencyGapCard extends StatelessWidget {
  const CompetencyGapCard({
    super.key,
    required this.result,
  });

  final CompetencyGapResult result;

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
          const Text(
            'CBC Competency Gaps',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w800,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 12),
          if (result.competencyGaps.isEmpty) ...[
            const Text(
              'No gaps detected — all subjects at expected level',
              style: TextStyle(color: Color(0xFF4CAF50), fontWeight: FontWeight.w600),
            ),
          ] else ...[
            ...result.competencyGaps.map(
              (gap) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: _GapRow(gap: gap),
              ),
            ),
          ],
          if (result.strengths.isNotEmpty) ...[
            const SizedBox(height: 16),
            const Text(
              'Strengths',
              style: TextStyle(
                color: Color(0xFF5EA3FF),
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 6),
            ...result.strengths.map(
              (s) => Padding(
                padding: const EdgeInsets.only(bottom: 2),
                child: Row(
                  children: [
                    const Icon(Icons.star, size: 14, color: Color(0xFF5EA3FF)),
                    const SizedBox(width: 6),
                    Text(s, style: const TextStyle(color: Colors.white, fontSize: 13)),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _GapRow extends StatelessWidget {
  const _GapRow({required this.gap});
  final CompetencyGap gap;

  @override
  Widget build(BuildContext context) {
    final color = gap.severity == 'critical' ? const Color(0xFFF44336) : const Color(0xFFFF9800);

    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            gap.subject,
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 4),
          Text(
            'Current: ${gap.currentAverage.toStringAsFixed(1)}% vs Expected: ${gap.expected.toStringAsFixed(1)}%',
            style: TextStyle(color: color, fontSize: 12),
          ),
          Text(
            'Gap: ${gap.gap.toStringAsFixed(1)}%',
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w800,
              fontSize: 13,
            ),
          ),
        ],
      ),
    );
  }
}