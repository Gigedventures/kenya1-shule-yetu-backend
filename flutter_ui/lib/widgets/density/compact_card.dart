/// Compact card components for dense SaaS UI.
library compact_card;

import 'package:flutter/material.dart';
import '../../theme/density_tokens.dart';

/// Base compact card with consistent styling.
class CompactCard extends StatelessWidget {
  const CompactCard({
    super.key,
    required this.child,
    this.padding,
    this.margin,
    this.onTap,
    this.onLongPress,
    this.color,
    this.borderColor,
    this.elevation = 0,
    this.hoverElevation = 1,
    this.borderRadius,
    this.constraints,
    this.semanticLabel,
  });

  final Widget child;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;
  final Color? color;
  final Color? borderColor;
  final int elevation;
  final int hoverElevation;
  final BorderRadius? borderRadius;
  final BoxConstraints? constraints;
  final String? semanticLabel;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final isInteractive = onTap != null;

    Widget card = Container(
      constraints: constraints,
      padding: padding ?? EdgeInsets.all(config.sm),
      margin: margin,
      decoration: BoxDecoration(
        color: color ?? config.bgSurface,
        borderRadius: borderRadius ?? BorderRadius.circular(config.radiusMd),
        border: Border.all(
          color: borderColor ?? config.borderLight,
          width: 1,
        ),
        boxShadow: elevation > 0 ? config.shadowCard : config.shadowNone,
      ),
      child: child,
    );

    if (isInteractive) {
      card = _InteractiveCard(
        config: config,
        elevation: elevation,
        hoverElevation: hoverElevation,
        onTap: onTap!,
        semanticLabel: semanticLabel,
        child: card,
      );
    }

    return card;
  }
}

/// Interactive card with hover/tap states.
class _InteractiveCard extends StatefulWidget {
  const _InteractiveCard({
    required this.config,
    required this.elevation,
    required this.hoverElevation,
    required this.onTap,
    required this.child,
    this.semanticLabel,
  });

  final DensityConfig config;
  final int elevation;
  final int hoverElevation;
  final VoidCallback onTap;
  final Widget child;
  final String? semanticLabel;

  @override
  State<_InteractiveCard> createState() => _InteractiveCardState();
}

class _InteractiveCardState extends State<_InteractiveCard> {
  bool _hovering = false;
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    final config = widget.config;
    return MouseRegion(
      onEnter: (_) => setState(() => _hovering = true),
      onExit: (_) => setState(() => _hovering = false),
      child: GestureDetector(
        onTapDown: (_) => setState(() => _pressed = true),
        onTapUp: (_) => setState(() => _pressed = false),
        onTapCancel: () => setState(() => _pressed = false),
        onTap: widget.onTap,
        child: AnimatedContainer(
          duration: config.motionFast,
          curve: config.motionStandard,
          decoration: BoxDecoration(
            color: _pressed
                ? widget.config.bgPressed
                : _hovering
                    ? widget.config.bgHover
                    : widget.config.bgSurface,
            borderRadius: BorderRadius.circular(widget.config.radiusMd),
            border: Border.all(
              color: _hovering ? widget.config.borderMedium : widget.config.borderLight,
              width: 1,
            ),
            boxShadow: (_hovering || _pressed) && widget.hoverElevation > 0
                ? widget.config.shadowCardHover
                : widget.elevation > 0
                    ? widget.config.shadowCard
                    : widget.config.shadowNone,
          ),
          child: widget.child,
        ),
      ),
    );
  }
}

/// Compact stat card for overview layer - shows key metric.
class StatCard extends StatelessWidget {
  const StatCard({
    super.key,
    required this.label,
    required this.value,
    this.icon,
    this.iconColor,
    this.trend,
    this.trendPositive = true,
    this.onTap,
    this.semanticLabel,
  });

  final String label;
  final String value;
  final IconData? icon;
  final Color? iconColor;
  final String? trend;
  final bool trendPositive;
  final VoidCallback? onTap;
  final String? semanticLabel;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final effectiveIconColor = iconColor ?? config.primary;
    final trendColor = trendPositive ? config.success : config.error;

    return CompactCard(
      onTap: onTap,
      semanticLabel: semanticLabel ?? '$label: $value',
      constraints: BoxConstraints(minHeight: config.statCardHeight),
      padding: EdgeInsets.all(config.sm),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              if (icon != null) ...[
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: effectiveIconColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(config.radiusSm),
                  ),
                  child: Icon(icon, size: config.iconSm, color: effectiveIconColor),
                ),
                SizedBox(width: config.xs),
              ],
              Expanded(
                child: Text(
                  label,
                  style: config.metadata.copyWith(color: config.textMuted),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          SizedBox(height: config.xs),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: Text(
                  value,
                  style: config.value.copyWith(color: config.textPrimary),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (trend != null) ...[
                SizedBox(width: config.xs),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      trendPositive ? Icons.trending_up : Icons.trending_down,
                      size: config.iconXs,
                      color: trendColor,
                    ),
                    SizedBox(width: 2),
                    Text(
                      trend!,
                      style: config.caption.copyWith(color: trendColor),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

/// Compact info card with title, value, and optional action.
class InfoCard extends StatelessWidget {
  const InfoCard({
    super.key,
    required this.title,
    required this.value,
    this.subtitle,
    this.icon,
    this.iconColor,
    this.actionLabel,
    this.onAction,
    this.valueStyle,
    this.statusColor,
    this.statusLabel,
  });

  final String title;
  final String value;
  final String? subtitle;
  final IconData? icon;
  final Color? iconColor;
  final String? actionLabel;
  final VoidCallback? onAction;
  final TextStyle? valueStyle;
  final Color? statusColor;
  final String? statusLabel;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final effectiveValueStyle = valueStyle ?? config.value.copyWith(color: config.textPrimary);

    return CompactCard(
      padding: EdgeInsets.all(config.sm),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              if (icon != null) ...[
                Icon(icon, size: config.iconSm, color: iconColor ?? config.textMuted),
                SizedBox(width: config.xs),
              ],
              Expanded(
                child: Text(
                  title,
                  style: config.metadata.copyWith(color: config.textMuted),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (actionLabel != null && onAction != null)
                TextButton(
                  onPressed: onAction,
                  style: TextButton.styleFrom(
                    padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  child: Text(actionLabel!, style: config.interactive.copyWith(color: config.primary)),
                ),
            ],
          ),
          SizedBox(height: config.xs),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: Text(value, style: effectiveValueStyle, maxLines: 1, overflow: TextOverflow.ellipsis),
              ),
              if (statusLabel != null) ...[
                SizedBox(width: config.sm),
                _StatusBadge(label: statusLabel!, color: statusColor ?? config.info),
              ],
            ],
          ),
          if (subtitle != null) ...[
            SizedBox(height: config.xs),
            Text(subtitle!, style: config.caption.copyWith(color: config.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis),
          ],
        ],
      ),
    );
  }
}

/// Small status badge.
class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(config.badgeRadius),
      ),
      child: Text(
        label,
        style: config.caption.copyWith(color: color, fontWeight: FontWeight.w600),
      ),
    );
  }
}

/// Compact list item card for work layer.
class ListCard<T> extends StatelessWidget {
  const ListCard({
    super.key,
    required this.item,
    required this.title,
    this.subtitle,
    this.leading,
    this.trailing,
    this.status,
    this.statusColor,
    this.onTap,
    this.onLongPress,
    this.selected = false,
  });

  final T item;
  final String title;
  final String? subtitle;
  final Widget? leading;
  final Widget? trailing;
  final String? status;
  final Color? statusColor;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;
  final bool selected;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return CompactCard(
      onTap: onTap,
      onLongPress: onLongPress,
      margin: EdgeInsets.only(bottom: config.denseGap),
      color: selected ? config.primaryBg : null,
      borderColor: selected ? config.primary : null,
      padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.sm),
      child: Row(
        children: [
          if (leading != null) ...[leading!, SizedBox(width: config.sm)],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(title, style: config.body.copyWith(color: config.textPrimary, fontWeight: FontWeight.w600), maxLines: 1, overflow: TextOverflow.ellipsis),
                if (subtitle != null) ...[
                  SizedBox(height: 2),
                  Text(subtitle!, style: config.metadata.copyWith(color: config.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis),
                ],
              ],
            ),
          ),
          if (status != null) ...[
            SizedBox(width: config.sm),
            _StatusBadge(label: status!, color: statusColor ?? config.info),
          ],
          if (trailing != null) ...[SizedBox(width: config.sm), trailing!],
        ],
      ),
    );
  }
}

/// Section header for grouped lists.
class SectionHeader extends StatelessWidget {
  const SectionHeader({
    super.key,
    required this.title,
    this.count,
    this.actionLabel,
    this.onAction,
    this.icon,
  });

  final String title;
  final int? count;
  final String? actionLabel;
  final VoidCallback? onAction;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Padding(
      padding: EdgeInsets.only(bottom: config.xs, top: config.xs),
      child: Row(
        children: [
          if (icon != null) ...[
            Icon(icon, size: config.iconSm, color: config.textSecondary),
            SizedBox(width: config.xs),
          ],
          Text(title, style: config.title.copyWith(color: config.textPrimary)),
          if (count != null) ...[
            SizedBox(width: config.xs),
            Container(
              padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 1),
              decoration: BoxDecoration(
                color: config.primaryBg,
                borderRadius: BorderRadius.circular(config.badgeRadius),
              ),
              child: Text(count.toString(), style: config.caption.copyWith(color: config.primary, fontWeight: FontWeight.w600)),
            ),
          ],
          const Spacer(),
          if (actionLabel != null && onAction != null)
            TextButton(
              onPressed: onAction,
              style: TextButton.styleFrom(
                padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              child: Text(actionLabel!, style: config.interactive.copyWith(color: config.primary)),
            ),
        ],
      ),
    );
  }
}