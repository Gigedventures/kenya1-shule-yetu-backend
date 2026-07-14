import 'package:flutter/material.dart';
import '../../theme/k1_colors.dart';
import '../../theme/k1_spacing.dart';
import '../../theme/k1_text_styles.dart';

/// Reusable dark-themed card container — replaces all _DarkPanel instances.
class K1Card extends StatelessWidget {
  final String? title;
  final Widget? child;
  final EdgeInsetsGeometry? padding;
  final Color? backgroundColor;
  final Color? borderColor;
  final double? borderRadius;
  final List<BoxShadow>? shadows;
  final EdgeInsetsGeometry? margin;

  const K1Card({
    super.key,
    this.title,
    this.child,
    this.padding,
    this.backgroundColor,
    this.borderColor,
    this.borderRadius,
    this.shadows,
    this.margin,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: margin ?? EdgeInsets.zero,
      padding: padding ?? const EdgeInsets.all(K1Spacing.card),
      decoration: BoxDecoration(
        color: backgroundColor ?? K1Colors.cardBg,
        borderRadius: BorderRadius.circular(borderRadius ?? K1Spacing.radiusLg),
        border: borderColor != null ? Border.all(color: borderColor!) : null,
        boxShadow: shadows,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (title != null) ...[
            Text(title!, style: K1TextStyles.h4),
            const SizedBox(height: K1Spacing.unit2),
          ],
          if (child != null) child!,
        ],
      ),
    );
  }
}

/// Reusable metric tile — replaces all _DarkTile / _HeroChip / _StatItem instances.
class K1MetricTile extends StatelessWidget {
  final String label;
  final String value;
  final IconData? icon;
  final Color? iconColor;
  final Color? valueColor;

  const K1MetricTile({
    super.key,
    required this.label,
    required this.value,
    this.icon,
    this.iconColor,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(K1Spacing.card),
      decoration: BoxDecoration(
        color: K1Colors.cardBg,
        borderRadius: BorderRadius.circular(K1Spacing.radiusLg),
        border: Border.all(color: K1Colors.cardBorder),
      ),
      child: Row(
        children: [
          if (icon != null) ...[
            Icon(icon, color: iconColor ?? K1Colors.accent, size: K1Spacing.iconMd),
            const SizedBox(width: K1Spacing.unit2),
          ],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: K1TextStyles.metricLabel),
                Text(value, style: K1TextStyles.h4.copyWith(color: valueColor)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Reusable dark panel container — replaces all _DarkPanel / _Panel instances.
class K1Panel extends StatelessWidget {
  final String? title;
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final double? spacing;

  const K1Panel({
    super.key,
    this.title,
    required this.child,
    this.padding,
    this.spacing,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: K1Spacing.unit3),
      padding: padding ?? const EdgeInsets.all(K1Spacing.card),
      decoration: BoxDecoration(
        color: K1Colors.darkBg,
        borderRadius: BorderRadius.circular(K1Spacing.radiusLg),
        border: Border.all(color: K1Colors.cardBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (title != null) ...[
            Text(title!, style: K1TextStyles.h5),
            SizedBox(height: spacing ?? K1Spacing.unit2),
          ],
          child,
        ],
      ),
    );
  }
}