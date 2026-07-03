import 'package:flutter/material.dart';

import '../../models/ai_models.dart';
import '../../theme/k1_colors.dart';

/// At-risk student assessment card — displays risk score, level, reasons, and actions.
class RiskAssessmentCard extends StatelessWidget {
  const RiskAssessmentCard({
    super.key,
    required this.result,
  });

  final AtRiskResult result;

  @override
  Widget build(BuildContext context) {
    final riskColor = switch (result.riskLevel) {
      'low' => const Color(0xFF4CAF50),
      'medium' => const Color(0xFFFF9800),
      'high' => const Color(0xFFF44336),
      _ => const Color(0xFF9E9E9E),
    };

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: K1Colors.cardBg ?? const Color(0xFF1A2438),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: riskColor.withOpacity(0.3),
          width: 1.5,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'At-Risk Score',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 16,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: riskColor.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${result.riskScore}/100',
                  style: TextStyle(
                    color: riskColor,
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          _RiskLevel(level: result.riskLevel),
          const SizedBox(height: 16),
          if (result.isAtRisk) ...[
            const Text(
              'Reasons',
              style: TextStyle(
                color: Color(0xFF91A4C0),
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 6),
            ...result.reasons.map(
              (r) => Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.warning_amber_rounded, size: 14, color: Color(0xFFFF6B6B)),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(r, style: const TextStyle(color: Colors.white, fontSize: 13)),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            const Text(
              'Recommended Actions',
              style: TextStyle(
                color: Color(0xFF5EA3FF),
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 6),
            ...result.recommendedActions.map(
              (a) => Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Row(
                  children: [
                    const Icon(Icons.check_circle_outline, size: 14, color: Color(0xFF5EA3FF)),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(a, style: const TextStyle(color: Color(0xFFC4D2E6), fontSize: 13)),
                    ),
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

class _RiskLevel extends StatelessWidget {
  const _RiskLevel({required this.level});
  final String level;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: switch (level) {
          'low' => Colors.green.withOpacity(0.2),
          'medium' => Colors.orange.withOpacity(0.2),
          'high' => Colors.red.withOpacity(0.2),
          _ => Colors.grey.withOpacity(0.2),
        },
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        'Risk: $level',
        style: TextStyle(
          color: switch (level) {
            'low' => Colors.green,
            'medium' => Colors.orange,
            'high' => Colors.red,
            _ => Colors.grey,
          },
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}