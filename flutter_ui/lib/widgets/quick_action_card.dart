import 'package:flutter/material.dart';

import '../models/quick_service.dart';
import '../theme/app_icons.dart';

class QuickActionCard extends StatelessWidget {
  const QuickActionCard({super.key, required this.item, this.highlight = false});

  final QuickService item;
  final bool highlight;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: highlight
              ? const [Color(0xFF0D47A1), Color(0xFF2A72D4)]
              : const [Color(0xFF4A78CC), Color(0xFF77A2EE)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
      ),
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          AppIcons.svg(item.iconAsset, width: 28, height: 28),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              item.title,
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18),
            ),
          ),
        ],
      ),
    );
  }
}
