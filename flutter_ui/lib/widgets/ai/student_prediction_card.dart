import 'package:flutter/material.dart';

import '../../models/ai_models.dart';
import '../../theme/k1_colors.dart';

/// Displays a student performance prediction card with risk level and subject analysis.
class StudentPredictionCard extends StatelessWidget {
  const StudentPredictionCard({
    super.key,
    required this.prediction,
  });

  final StudentPerformancePrediction prediction;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: K1Colors.cardBg ?? const Color(0xFF1A2438),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: K1Colors.border ?? const Color(0xFF2D3E5A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Predicted Average: ${prediction.predictedAverage.toStringAsFixed(1)}%',
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w800,
              fontSize: 18,
            ),
          ),
          const SizedBox(height: 8),
          _RiskBadge(level: prediction.riskLevel),
          const SizedBox(height: 12),
          Text(
            'Confidence: ${prediction.confidenceScore.toStringAsFixed(1)}%',
            style: const TextStyle(color: K1Colors.muted ?? Color(0xFF91A4C0)),
          ),
          const SizedBox(height: 16),
          if (prediction.strongestSubjects.isNotEmpty)
            _SubjectList(
              title: 'Strongest',
              subjects: prediction.strongestSubjects,
              color: const Color(0xFF5EA3FF),
            ),
          const SizedBox(height: 8),
          if (prediction.weakestSubjects.isNotEmpty)
            _SubjectList(
              title: 'Needs Improvement',
              subjects: prediction.weakestSubjects,
              color: const Color(0xFFFF6B6B),
            ),
        ],
      ),
    );
  }
}

class _RiskBadge extends StatelessWidget {
  const _RiskBadge({required this.level});
  final String level;

  @override
  Widget build(BuildContext context) {
    final color = switch (level) {
      'low' => const Color(0xFF4CAF50),
      'medium' => const Color(0xFFFF9800),
      'high' => const Color(0xFFF44336),
      _ => const Color(0xFF9E9E9E),
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        'Risk: $level',
        style: TextStyle(
          color: color,
          fontWeight: FontWeight.w700,
          fontSize: 13,
        ),
      ),
    );
  }
}

class _SubjectList extends StatelessWidget {
  const _SubjectList({
    required this.title,
    required this.subjects,
    required this.color,
  });

  final String title;
  final List<String> subjects;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: TextStyle(color: color, fontWeight: FontWeight.w700, fontSize: 13)),
        const SizedBox(height: 4),
        ...subjects.map(
          (s) => Padding(
            padding: const EdgeInsets.only(bottom: 2),
            child: Row(
              children: [
                Icon(Icons.circle, size: 6, color: color),
                const SizedBox(width: 6),
                Text(s, style: const TextStyle(color: Colors.white, fontSize: 13)),
              ],
            ),
          ),
        ),
      ],
    );
  }
}