import 'package:flutter/material.dart';

class ProgressStrip extends StatelessWidget {
  const ProgressStrip({
    super.key,
    required this.subject,
    required this.value,
    required this.color,
    this.trailing,
  });

  final String subject;
  final double value;
  final Color color;
  final String? trailing;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(subject, style: const TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 16)),
              const Spacer(),
              Text('${(value * 100).round()}% ${trailing ?? ''}', style: const TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 16)),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: value,
              minHeight: 8,
              backgroundColor: Colors.white.withOpacity(0.35),
              color: color,
            ),
          ),
        ],
      ),
    );
  }
}
