import 'package:flutter/material.dart';

import '../theme/app_icons.dart';

class ServiceTile extends StatelessWidget {
  const ServiceTile({
    super.key,
    required this.title,
    required this.iconAsset,
    this.compact = false,
    this.onTap,
  });

  final String title;
  final String iconAsset;
  final bool compact;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(14),
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFFE0E5EF)),
        ),
        padding: EdgeInsets.symmetric(horizontal: compact ? 8 : 10, vertical: compact ? 10 : 12),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AppIcons.svg(iconAsset, width: compact ? 34 : 42, height: compact ? 34 : 42),
            const SizedBox(height: 8),
            Text(
              title,
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF112D59)),
            ),
          ],
        ),
      ),
    );
  }
}
