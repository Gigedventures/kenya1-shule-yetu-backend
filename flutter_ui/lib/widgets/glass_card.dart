import 'package:flutter/material.dart';

class GlassCard extends StatelessWidget {
  const GlassCard({super.key, required this.child, this.radius = 16, this.padding = const EdgeInsets.all(14)});

  final Widget child;
  final double radius;
  final EdgeInsets padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: const Color(0xFF0F1524).withOpacity(0.74),
        borderRadius: BorderRadius.circular(radius),
        border: Border.all(color: const Color(0xFF4A5472), width: 1),
      ),
      child: child,
    );
  }
}
