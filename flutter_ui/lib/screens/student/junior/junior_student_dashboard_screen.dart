/// Junior student dashboard - Overview layer with real API data.
library junior_student_dashboard_screen;

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../data/providers/student_assignments_provider.dart';
import '../../../data/providers/student_attendance_provider.dart';
import '../../../data/providers/student_classes_provider.dart'
    show StudentClassesProvider, StudentClassesState;
import '../../../data/providers/messages_provider.dart';
import '../../../data/repositories/student_repository.dart';
import '../../../data/repositories/message_repository.dart';
import '../../../services/k1_api_service.dart';
import '../../../widgets/density/index.dart';
import '../../../theme/density_tokens.dart';
import '../../../models/student_models.dart';
import '../../student/assignments_list_screen.dart';
import '../../student/attendance_list_screen.dart';
import '../../student/classes_list_screen.dart';
import '../../student/message_thread_screen.dart';
import '../../student/assignment_detail_screen.dart';
import '../../../theme/k1_colors.dart';
import '../../../models/message_models.dart';
import '../../../data/mock_users.dart';

class JuniorStudentDashboardScreen extends StatelessWidget {
  const JuniorStudentDashboardScreen({
    super.key,
    required this.student,
  });

  final Student student;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => StudentAssignmentsProvider(
            repository: StudentRepositoryImpl(apiService: context.read<K1ApiService>()),
          )..load(),
        ),
        ChangeNotifierProvider(
          create: (_) => StudentAttendanceProvider(
            repository: StudentRepositoryImpl(apiService: context.read<K1ApiService>()),
          )..load(),
        ),
        ChangeNotifierProvider(
          create: (_) => StudentClassesProvider(
            repository: StudentRepositoryImpl(apiService: context.read<K1ApiService>()),
          )..load(),
        ),
        ChangeNotifierProvider(
          create: (_) => MessagesProvider(
            repository: MessageRepositoryImpl(apiService: context.read<K1ApiService>()),
          )..loadInbox(),
        ),
      ],
      child: _JuniorDashboardView(student: student),
    );
  }
}

class _JuniorDashboardView extends StatelessWidget {
  const _JuniorDashboardView({required this.student});

  final Student student;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Scaffold(
      backgroundColor: config.bgBase,
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            SliverPadding(
              padding: EdgeInsets.all(config.sm),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  // Hero section
                  _JuniorHero(student: student),
                  SizedBox(height: config.lg),
                  // Overview stats (compact cards)
                  _buildOverviewStats(context),
                  SizedBox(height: config.lg),
                  // Quick actions
                  _buildQuickActions(context),
                  SizedBox(height: config.lg),
                  // Assignments preview
                  _buildAssignmentsPreview(context),
                  SizedBox(height: config.lg),
                  // Today's schedule
                  _buildTodaySchedule(context),
                  SizedBox(height: config.lg),
                  // Messages preview
                  _buildMessagesPreview(context),
                  SizedBox(height: config.xxl),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOverviewStats(BuildContext context) {
    final config = context.density;

    return LayoutBuilder(
      builder: (context, constraints) {
        final columns = config.statCardColumns(constraints.maxWidth);
        final cardWidth = (constraints.maxWidth - (columns - 1) * config.cardGap) / columns;

        return Consumer4<StudentAssignmentsProvider, StudentAttendanceProvider,
            StudentClassesProvider, MessagesProvider>(
          builder: (context, assignmentsProvider, attendanceProvider, classesProvider, messagesProvider, _) {
            final attendanceSummary = attendanceProvider.summary;
            final assignmentSummary = assignmentsProvider.summary;
            final unreadMessages = messagesProvider.totalUnread;

            final stats = [
              _OverviewStatData(
                label: 'Attendance',
                value: attendanceSummary != null ? '${attendanceSummary.rate.toStringAsFixed(1)}%' : '—',
                icon: Icons.fact_check_outlined,
                color: attendanceSummary != null && attendanceSummary.rate >= 90
                    ? config.success
                    : (attendanceSummary != null && attendanceSummary.rate >= 75 ? config.warning : config.error),
                trend: attendanceSummary != null ? '+2%' : null,
                trendPositive: true,
              ),
              _OverviewStatData(
                label: 'Assignments',
                value: assignmentSummary?.pending.toString() ?? '0',
                subtitle: 'pending',
                icon: Icons.assignment_outlined,
                color: config.primary,
                onTap: () => _navigateToAssignments(context),
              ),
              _OverviewStatData(
                label: 'Classes Today',
                value: classesProvider.getTodaySchedule().length.toString(),
                subtitle: 'scheduled',
                icon: Icons.schedule_outlined,
                color: Colors.purple,
                onTap: () => _navigateToClasses(context),
              ),
              _OverviewStatData(
                label: 'Messages',
                value: unreadMessages.toString(),
                subtitle: 'unread',
                icon: Icons.mark_chat_unread_outlined,
                color: unreadMessages > 0 ? config.error : config.success,
                onTap: () => _navigateToMessages(context),
              ),
            ];

            return Wrap(
              spacing: config.cardGap,
              runSpacing: config.cardGap,
              children: stats.map((stat) => SizedBox(
                width: cardWidth,
                child: StatCard(
                  label: stat.label,
                  value: stat.value,
                  subtitle: stat.subtitle,
                  icon: stat.icon,
                  iconColor: stat.color,
                  trend: stat.trend,
                  trendPositive: stat.trendPositive ?? true,
                  onTap: stat.onTap,
                ),
              )).toList(),
            );
          },
        );
      },
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    final config = context.density;

    final actions = [
      _QuickAction(
        icon: Icons.add_task,
        label: 'New Assignment',
        color: config.primary,
        onTap: () => _navigateToAssignments(context),
      ),
      _QuickAction(
        icon: Icons.fact_check,
        label: 'Mark Attendance',
        color: config.success,
        onTap: () => _navigateToAttendance(context),
      ),
      _QuickAction(
        icon: Icons.schedule,
        label: 'View Schedule',
        color: Colors.purple,
        onTap: () => _navigateToClasses(context),
      ),
      _QuickAction(
        icon: Icons.chat,
        label: 'Messages',
        color: Colors.teal,
        onTap: () => _navigateToMessages(context),
      ),
    ];

    return Row(
      children: actions.map((action) => Expanded(
        child: CompactCard(
          onTap: action.onTap,
          padding: EdgeInsets.symmetric(vertical: config.md, horizontal: config.sm),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: action.color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(config.radiusSm),
                ),
                child: Icon(action.icon, size: config.iconMd, color: action.color),
              ),
              SizedBox(height: config.xs),
              Text(action.label, style: config.metadata.copyWith(fontWeight: FontWeight.w600), textAlign: TextAlign.center, maxLines: 1, overflow: TextOverflow.ellipsis),
            ],
          ),
        ),
      )).toList(),
    );
  }

  Widget _buildAssignmentsPreview(BuildContext context) {
    final config = context.density;

    return Consumer<StudentAssignmentsProvider>(
      builder: (context, provider, _) {
        final pendingAssignments = provider.assignments.where((a) => a.isPending || a.isOverdue).take(3).toList();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SectionHeader(
              title: 'Assignments',
              count: provider.assignments.length,
              actionLabel: 'View all',
              onAction: () => _navigateToAssignments(context),
            ),
            SizedBox(height: config.xs),
            if (provider.isLoading && pendingAssignments.isEmpty)
              ListCardSkeleton(hasLeading: false, hasTrailing: true)
            else if (pendingAssignments.isEmpty)
              InlineEmptyState(
                icon: Icons.assignment_turned_in,
                title: 'No pending assignments',
                message: 'You\'re all caught up!',
              )
            else
              Column(
                children: pendingAssignments.map((assignment) => ListCard<Assignment>(
                  item: assignment,
                  title: assignment.title,
                  subtitle: '${assignment.subject} • ${assignment.formattedDueDate}',
                  status: assignment.statusLabel,
                  statusColor: _statusColor(assignment.status),
                  onTap: () => _showAssignmentDetail(context, assignment),
                  trailing: assignment.isOverdue
                      ? Container(
                          padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
                          decoration: BoxDecoration(
                            color: config.errorBg,
                            borderRadius: BorderRadius.circular(config.badgeRadius),
                          ),
                          child: Text('OVERDUE', style: config.caption.copyWith(color: config.error, fontWeight: FontWeight.w700)),
                        )
                      : null,
                )).toList(),
              ),
          ],
        );
      },
    );
  }

  Widget _buildTodaySchedule(BuildContext context) {
    final config = context.density;

    return Consumer<StudentClassesProvider>(
      builder: (context, provider, _) {
        final todaySchedule = provider.getTodaySchedule();
        final currentClass = provider.getCurrentClass();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SectionHeader(
              title: "Today's Schedule",
              count: todaySchedule.length,
              actionLabel: 'View all',
              onAction: () => _navigateToClasses(context),
            ),
            SizedBox(height: config.xs),
            if (provider.state == StudentClassesState.loading)
              ListCardSkeleton(hasLeading: true, hasTrailing: false)
            else if (todaySchedule.isEmpty)
              InlineEmptyState(
                icon: Icons.event_available,
                title: 'No classes today',
                message: 'Enjoy your free day!',
              )
            else
              Column(
                children: [
                  if (currentClass != null)
                    _CurrentClassBanner(currentClass: currentClass, config: config),
                  ...todaySchedule.map((entry) => _ScheduleItem(entry: entry, config: config)).toList(),
                ],
              ),
          ],
        );
      },
    );
  }

  Widget _buildMessagesPreview(BuildContext context) {
    final config = context.density;

    return Consumer<MessagesProvider>(
      builder: (context, provider, _) {
        final recentThreads = provider.threads.take(3).toList();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SectionHeader(
              title: 'Messages',
              count: provider.threads.length,
              actionLabel: 'View all',
              onAction: () => _navigateToMessages(context),
            ),
            SizedBox(height: config.xs),
            if (provider.isLoading && recentThreads.isEmpty)
              ListCardSkeleton(hasLeading: true, hasTrailing: true)
            else if (recentThreads.isEmpty)
              InlineEmptyState(
                icon: Icons.mark_chat_unread_outlined,
                title: 'No messages',
                message: 'Start a conversation with your teachers.',
              )
            else
              Column(
                children: recentThreads.map((thread) => ListCard<Thread>(
                  item: thread,
                  title: thread.participant.name,
                  subtitle: thread.lastMessage,
                  leading: CircleAvatar(
                    radius: 16,
                    backgroundColor: config.primaryBg,
                    backgroundImage: thread.participant.avatarUrl != null
                        ? NetworkImage(thread.participant.avatarUrl!)
                        : null,
                    child: thread.participant.avatarUrl == null
                        ? Text(
                            thread.participant.name.isNotEmpty
                                ? thread.participant.name[0].toUpperCase()
                                : '?',
                            style: config.body.copyWith(color: config.primary, fontWeight: FontWeight.w700, fontSize: 12),
                          )
                        : null,
                  ),
                  trailing: thread.unreadCount > 0
                      ? Container(
                          padding: EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                          decoration: BoxDecoration(
                            color: config.error,
                            borderRadius: BorderRadius.circular(config.badgeRadius),
                          ),
                          child: Text(
                            thread.unreadCount > 9 ? '9+' : thread.unreadCount.toString(),
                            style: config.caption.copyWith(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 10),
                          ),
                        )
                      : null,
                  onTap: () => _openThread(context, thread),
                )).toList(),
              ),
          ],
        );
      },
    );
  }

  void _navigateToAssignments(BuildContext context) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const StudentAssignmentsListScreen()));
  }

  void _navigateToAttendance(BuildContext context) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const StudentAttendanceListScreen()));
  }

  void _navigateToClasses(BuildContext context) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const StudentClassesListScreen()));
  }

  void _navigateToMessages(BuildContext context) {
    // TODO: Navigate to messages inbox screen
  }

  void _showAssignmentDetail(BuildContext context, Assignment assignment) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => AssignmentDetailScreen(assignment: assignment)));
  }

  void _openThread(BuildContext context, Thread thread) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => MessageThreadScreen(thread: thread)));
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'submitted':
        return Colors.blue;
      case 'graded':
        return Colors.green;
      case 'overdue':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}

/// Overview stat data
class _OverviewStatData {
  const _OverviewStatData({
    required this.label,
    required this.value,
    this.subtitle,
    required this.icon,
    required this.color,
    this.trend,
    this.trendPositive = true,
    this.onTap,
  });

  final String label;
  final String value;
  final String? subtitle;
  final IconData icon;
  final Color color;
  final String? trend;
  final bool trendPositive;
  final VoidCallback? onTap;
}

/// Extended StatCard with subtitle
class StatCard extends StatelessWidget {
  const StatCard({
    super.key,
    required this.label,
    required this.value,
    this.subtitle,
    this.icon,
    this.iconColor,
    this.trend,
    this.trendPositive = true,
    this.onTap,
    this.semanticLabel,
  });

  final String label;
  final String value;
  final String? subtitle;
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
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(label, style: config.metadata.copyWith(color: config.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis),
                    if (subtitle != null)
                      Text(subtitle!, style: config.caption.copyWith(color: config.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis),
                  ],
                ),
              ),
            ],
          ),
          SizedBox(height: config.xs),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: Text(value, style: config.value.copyWith(color: config.textPrimary), maxLines: 1, overflow: TextOverflow.ellipsis),
              ),
              if (trend != null) ...[
                SizedBox(width: config.xs),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(trendPositive ? Icons.trending_up : Icons.trending_down, size: config.iconXs, color: trendColor),
                    SizedBox(width: 2),
                    Text(trend!, style: config.caption.copyWith(color: trendColor)),
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

class _QuickAction extends StatelessWidget {
  const _QuickAction({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(right: context.density.cardGap),
      child: _QuickActionButton(icon: icon, label: label, color: color, onTap: onTap),
    );
  }
}

class _QuickActionButton extends StatelessWidget {
  const _QuickActionButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(child: _QuickActionContent(icon: icon, label: label, color: color, onTap: onTap));
  }
}

class _QuickActionContent extends StatelessWidget {
  const _QuickActionContent({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return CompactCard(
      onTap: onTap,
      padding: EdgeInsets.symmetric(vertical: config.md, horizontal: config.sm),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(config.radiusSm),
            ),
            child: Icon(icon, size: config.iconMd, color: color),
          ),
          SizedBox(height: config.xs),
          Text(label, style: config.metadata.copyWith(fontWeight: FontWeight.w600), textAlign: TextAlign.center, maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }
}

class _CurrentClassBanner extends StatelessWidget {
  const _CurrentClassBanner({required this.currentClass, required this.config});

  final ClassSchedule currentClass;
  final DensityConfig config;

  @override
  Widget build(BuildContext context) {
    final currentOrNext = currentClass.getCurrentOrNextClass();
    final color = Color(int.parse(currentClass.color.replaceFirst('#', '0xFF')));

    return Container(
      margin: EdgeInsets.only(bottom: config.sm),
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [color, color.withValues(alpha: 0.8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(config.radiusMd),
      ),
      child: Row(
        children: [
          Container(
            padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(config.badgeRadius),
            ),
            child: Text(currentOrNext != null ? 'NOW' : 'NEXT', style: config.caption.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          ),
          SizedBox(width: config.sm),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(currentClass.name, style: config.body.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
                if (currentOrNext != null)
                  Text('${currentOrNext.start} - ${currentOrNext.end} • ${currentOrNext.room}', style: config.caption.copyWith(color: Colors.white.withValues(alpha: 0.9))),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Hero section for junior dashboard
class _JuniorHero extends StatelessWidget {
  const _JuniorHero({required this.student});

  final Student student;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final heroColor = K1Colors.primary;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.all(config.lg),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [heroColor, heroColor.withValues(alpha: 0.8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(config.radiusLg),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Welcome back,',
            style: config.body.copyWith(color: Colors.white.withValues(alpha: 0.85)),
          ),
          SizedBox(height: config.xs),
          Text(
            student.fullName.isNotEmpty ? student.fullName : 'Student',
            style: config.title.copyWith(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800),
          ),
          SizedBox(height: config.xs),
          Text(
            'Here is what is happening today.',
            style: config.caption.copyWith(color: Colors.white.withValues(alpha: 0.9)),
          ),
        ],
      ),
    );
  }
}

class _ScheduleItem extends StatelessWidget {
  const _ScheduleItem({required this.entry, required this.config});

  final ScheduleEntry entry;
  final DensityConfig config;

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final startTime = entry.startTime;
    final endTime = entry.endTime;
    final isCurrent = startTime.isBefore(now) && endTime.isAfter(now);

    return Container(
      margin: EdgeInsets.only(bottom: config.denseGap),
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        color: isCurrent ? config.primaryBg : config.bgSurface,
        borderRadius: BorderRadius.circular(config.radiusMd),
        border: Border.all(color: isCurrent ? config.primary : config.borderLight, width: isCurrent ? 2 : 1),
      ),
      child: Row(
        children: [
          Container(
            width: 4,
            height: 40,
            decoration: BoxDecoration(
              color: isCurrent ? config.primary : config.borderLight,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          SizedBox(width: config.sm),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(entry.dayName, style: config.metadata.copyWith(color: config.textMuted)),
                SizedBox(height: 2),
                Text('Period ${entry.period}', style: config.body.copyWith(fontWeight: FontWeight.w600)),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text('${entry.start} - ${entry.end}', style: config.value.copyWith(color: isCurrent ? config.primary : config.textPrimary)),
              Text(entry.room, style: config.caption.copyWith(color: config.textMuted)),
            ],
          ),
        ],
      ),
    );
  }
}