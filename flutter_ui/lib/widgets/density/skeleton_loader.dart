/// Skeleton loading components for dense UI.
library skeleton_loader;

import 'package:flutter/material.dart';
import '../../theme/density_tokens.dart';

/// Base skeleton loader with shimmer animation.
class SkeletonLoader extends StatefulWidget {
  const SkeletonLoader({
    super.key,
    required this.width,
    required this.height,
    this.borderRadius,
    this.baseColor,
    this.highlightColor,
  });

  final double width;
  final double height;
  final double? borderRadius;
  final Color? baseColor;
  final Color? highlightColor;

  @override
  State<SkeletonLoader> createState() => _SkeletonLoaderState();
}

class _SkeletonLoaderState extends State<SkeletonLoader> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    )..repeat();
    _animation = Tween<double>(begin: -1, end: 2).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final base = widget.baseColor ?? config.bgHover;
    final highlight = widget.highlightColor ?? config.bgPressed;

    return AnimatedBuilder(
      animation: _animation,
      builder: (context, child) {
        return Container(
          width: widget.width,
          height: widget.height,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(widget.borderRadius ?? config.radiusSm),
            gradient: LinearGradient(
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
              colors: [base, highlight, base],
              stops: [
                (_animation.value - 0.3).clamp(0.0, 1.0),
                _animation.value.clamp(0.0, 1.0),
                (_animation.value + 0.3).clamp(0.0, 1.0),
              ],
            ),
          ),
        );
      },
    );
  }
}

/// Skeleton for stat card.
class StatCardSkeleton extends StatelessWidget {
  const StatCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Container(
      height: config.statCardHeight,
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        color: config.bgSurface,
        borderRadius: BorderRadius.circular(config.radiusMd),
        border: Border.all(color: config.borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              SkeletonLoader(width: 32, height: 32, borderRadius: config.radiusSm),
              SizedBox(width: config.xs),
              Expanded(child: SkeletonLoader(width: 80, height: 12)),
            ],
          ),
          SizedBox(height: config.xs),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(child: SkeletonLoader(width: 100, height: 20)),
              SkeletonLoader(width: 60, height: 14),
            ],
          ),
        ],
      ),
    );
  }
}

/// Skeleton for list card.
class ListCardSkeleton extends StatelessWidget {
  const ListCardSkeleton({super.key, this.hasLeading = false, this.hasTrailing = false});

  final bool hasLeading;
  final bool hasTrailing;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Container(
      margin: EdgeInsets.only(bottom: config.denseGap),
      padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.sm),
      decoration: BoxDecoration(
        color: config.bgSurface,
        borderRadius: BorderRadius.circular(config.radiusMd),
        border: Border.all(color: config.borderLight),
      ),
      child: Row(
        children: [
          if (hasLeading) ...[
            SkeletonLoader(width: 40, height: 40, borderRadius: config.radiusPill),
            SizedBox(width: config.sm),
          ],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                SkeletonLoader(width: 120, height: 14),
                SizedBox(height: 4),
                SkeletonLoader(width: 80, height: 12),
              ],
            ),
          ),
          if (hasTrailing) ...[
            SizedBox(width: config.sm),
            SkeletonLoader(width: 60, height: 20, borderRadius: config.badgeRadius),
          ],
        ],
      ),
    );
  }
}

/// Skeleton for table row.
class TableRowSkeleton extends StatelessWidget {
  const TableRowSkeleton({super.key, required this.columnCount, this.compact = false});

  final int columnCount;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final rowHeight = compact ? config.tableRowHeightCompact : config.tableRowHeight;

    return Container(
      height: rowHeight,
      padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
      child: Row(
        children: List.generate(columnCount, (index) {
          return Expanded(
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: config.xs),
              child: SkeletonLoader(
                width: double.infinity,
                height: compact ? 12 : 14,
              ),
            ),
          );
        }),
      ),
    );
  }
}

/// Skeleton for detail view.
class DetailSkeleton extends StatelessWidget {
  const DetailSkeleton({super.key, this.sections = 3});

  final int sections;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header skeleton
        Container(
          width: double.infinity,
          padding: EdgeInsets.all(config.lg),
          decoration: BoxDecoration(
            color: config.bgSurface,
            borderRadius: BorderRadius.circular(config.radiusMd),
            border: Border.all(color: config.borderLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SkeletonLoader(width: 200, height: 24),
              SizedBox(height: config.sm),
              SkeletonLoader(width: 150, height: 16),
            ],
          ),
        ),
        SizedBox(height: config.lg),
        // Section skeletons
        ...List.generate(sections, (index) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SkeletonLoader(width: 120, height: 16),
              SizedBox(height: config.sm),
              Container(
                width: double.infinity,
                padding: EdgeInsets.all(config.sm),
                decoration: BoxDecoration(
                  color: config.bgSurface,
                  borderRadius: BorderRadius.circular(config.radiusMd),
                  border: Border.all(color: config.borderLight),
                ),
                child: Column(
                  children: List.generate(3, (i) {
                    return Padding(
                      padding: EdgeInsets.only(bottom: config.sm),
                      child: Row(
                        children: [
                          SkeletonLoader(width: 100, height: 14),
                          const Spacer(),
                          SkeletonLoader(width: 80, height: 14),
                        ],
                      ),
                    );
                  }),
                ),
              ),
              if (index < sections - 1) SizedBox(height: config.lg),
            ],
          );
        }),
      ],
    );
  }
}

/// Skeleton for message thread.
class MessageThreadSkeleton extends StatelessWidget {
  const MessageThreadSkeleton({super.key, this.messageCount = 3});

  final int messageCount;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Column(
      children: List.generate(messageCount, (index) {
        final isOwn = index % 2 == 0;
        return Container(
          margin: EdgeInsets.only(bottom: config.sm),
          padding: EdgeInsets.all(config.sm),
          alignment: isOwn ? Alignment.centerRight : Alignment.centerLeft,
          child: Container(
            constraints: BoxConstraints(maxWidth: 300),
            padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
            decoration: BoxDecoration(
              color: isOwn ? config.primaryBg : config.bgSurface,
              borderRadius: BorderRadius.circular(config.radiusMd),
              border: isOwn ? null : Border.all(color: config.borderLight),
            ),
            child: SkeletonLoader(width: 150.0 + (index * 30.0), height: 16),
          ),
        );
      }),
    );
  }
}
