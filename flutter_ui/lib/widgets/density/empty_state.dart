/// Empty state widgets for dense SaaS UI.
library empty_state;

import 'package:flutter/material.dart';
import '../../theme/density_tokens.dart';

/// Empty state with illustration, message, and action.
class EmptyState extends StatelessWidget {
  const EmptyState({
    super.key,
    required this.icon,
    required this.title,
    this.message,
    this.actionLabel,
    this.onAction,
    this.secondaryActionLabel,
    this.onSecondaryAction,
    this.iconSize = 64,
    this.iconColor,
    this.spacing,
  });

  final IconData icon;
  final String title;
  final String? message;
  final String? actionLabel;
  final VoidCallback? onAction;
  final String? secondaryActionLabel;
  final VoidCallback? onSecondaryAction;
  final double iconSize;
  final Color? iconColor;
  final double? spacing;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final s = spacing ?? config.lg;

    return Center(
      child: Padding(
        padding: EdgeInsets.all(config.xl),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 400),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: iconSize,
                height: iconSize,
                decoration: BoxDecoration(
                  color: (iconColor ?? config.primary).withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, size: iconSize * 0.5, color: iconColor ?? config.primary),
              ),
              SizedBox(height: s),
              Text(
                title,
                style: config.title.copyWith(fontSize: 16, color: config.textPrimary),
                textAlign: TextAlign.center,
              ),
              if (message != null) ...[
                SizedBox(height: config.xs),
                Text(
                  message!,
                  style: config.body.copyWith(color: config.textMuted),
                  textAlign: TextAlign.center,
                ),
              ],
              if (actionLabel != null || secondaryActionLabel != null) ...[
                SizedBox(height: s),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (secondaryActionLabel != null && onSecondaryAction != null) ...[
                      OutlinedButton(
                        onPressed: onSecondaryAction,
                        style: OutlinedButton.styleFrom(
                          padding: EdgeInsets.symmetric(horizontal: config.lg, vertical: config.sm),
                        ),
                        child: Text(secondaryActionLabel!, style: config.interactive),
                      ),
                      SizedBox(width: config.sm),
                    ],
                    if (actionLabel != null && onAction != null)
                      FilledButton(
                        onPressed: onAction,
                        style: FilledButton.styleFrom(
                          padding: EdgeInsets.symmetric(horizontal: config.lg, vertical: config.sm),
                        ),
                        child: Text(actionLabel!, style: config.interactive.copyWith(color: config.textInverse)),
                      ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

/// Predefined empty states for common scenarios.
class EmptyStates {
  /// No assignments found.
  static Widget noAssignments({
    required VoidCallback onCreate,
    VoidCallback? onRefresh,
  }) {
    return EmptyState(
      icon: Icons.assignment_outlined,
      title: 'No assignments yet',
      message: 'When assignments are created, they\'ll appear here.',
      actionLabel: 'Create assignment',
      onAction: onCreate,
      secondaryActionLabel: 'Refresh',
      onSecondaryAction: onRefresh,
    );
  }

  /// No attendance records.
  static Widget noAttendance({
    required VoidCallback onMark,
    VoidCallback? onRefresh,
  }) {
    return EmptyState(
      icon: Icons.fact_check_outlined,
      title: 'No attendance records',
      message: 'Mark attendance to start tracking.',
      actionLabel: 'Mark attendance',
      onAction: onMark,
      secondaryActionLabel: 'Refresh',
      onSecondaryAction: onRefresh,
    );
  }

  /// No classes scheduled.
  static Widget noClasses({
    required VoidCallback onViewSchedule,
    VoidCallback? onRefresh,
  }) {
    return EmptyState(
      icon: Icons.schedule_outlined,
      title: 'No classes scheduled',
      message: 'Your class schedule will appear here once enrolled.',
      actionLabel: 'View schedule',
      onAction: onViewSchedule,
      secondaryActionLabel: 'Refresh',
      onSecondaryAction: onRefresh,
    );
  }

  /// No messages.
  static Widget noMessages({
    required VoidCallback onCompose,
    VoidCallback? onRefresh,
  }) {
    return EmptyState(
      icon: Icons.mark_chat_unread_outlined,
      title: 'No messages',
      message: 'Start a conversation with teachers or classmates.',
      actionLabel: 'New message',
      onAction: onCompose,
      secondaryActionLabel: 'Refresh',
      onSecondaryAction: onRefresh,
    );
  }

  /// No search results.
  static Widget noResults({
    required String query,
    VoidCallback? onClearSearch,
  }) {
    return EmptyState(
      icon: Icons.search_off_outlined,
      title: 'No results for "$query"',
      message: 'Try adjusting your search or filters.',
      actionLabel: 'Clear search',
      onAction: onClearSearch,
    );
  }

  /// Network error.
  static Widget error({
    required String message,
    required VoidCallback onRetry,
  }) {
    return EmptyState(
      icon: Icons.cloud_off_outlined,
      iconColor: Colors.red,
      title: 'Unable to load',
      message: message,
      actionLabel: 'Retry',
      onAction: onRetry,
    );
  }

  /// Offline state.
  static Widget offline({VoidCallback? onRetry}) {
    return EmptyState(
      icon: Icons.wifi_off_outlined,
      iconColor: Colors.orange,
      title: 'You\'re offline',
      message: 'Check your connection and try again.',
      actionLabel: 'Retry',
      onAction: onRetry,
    );
  }

  /// Permission denied.
  static Widget permissionDenied({
    required String feature,
    VoidCallback? onSettings,
  }) {
    return EmptyState(
      icon: Icons.lock_outline,
      title: 'Access denied',
      message: 'You don\'t have permission to view $feature.',
      actionLabel: 'Open settings',
      onAction: onSettings,
    );
  }
}

/// Inline empty state for lists (smaller footprint).
class InlineEmptyState extends StatelessWidget {
  const InlineEmptyState({
    super.key,
    required this.icon,
    required this.title,
    this.message,
    this.actionLabel,
    this.onAction,
  });

  final IconData icon;
  final String title;
  final String? message;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Padding(
      padding: EdgeInsets.all(config.lg),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 32, color: config.textMuted),
          SizedBox(height: config.sm),
          Text(title, style: config.body.copyWith(color: config.textSecondary), textAlign: TextAlign.center),
          if (message != null) ...[
            SizedBox(height: config.xs),
            Text(message!, style: config.metadata.copyWith(color: config.textMuted), textAlign: TextAlign.center),
          ],
          if (actionLabel != null && onAction != null) ...[
            SizedBox(height: config.sm),
            TextButton(onPressed: onAction, child: Text(actionLabel!, style: config.interactive)),
          ],
        ],
      ),
    );
  }
}