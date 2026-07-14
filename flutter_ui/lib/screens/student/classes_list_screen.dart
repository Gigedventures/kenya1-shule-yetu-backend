/// Student classes list screen - Work layer.
library classes_list_screen;

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../data/providers/student_classes_provider.dart';
import '../../data/repositories/student_repository.dart'
    show StudentRepositoryImpl;
import '../../services/k1_api_service.dart';
import '../../widgets/density/index.dart';
import '../../theme/density_tokens.dart';
import '../../models/student_models.dart';

class StudentClassesListScreen extends StatelessWidget {
  const StudentClassesListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => StudentClassesProvider(
        repository: StudentRepositoryImpl(apiService: context.read<K1ApiService>()),
      )..load(),
      child: const _ClassesListView(),
    );
  }
}

class _ClassesListView extends StatefulWidget {
  const _ClassesListView();

  @override
  State<_ClassesListView> createState() => _ClassesListViewState();
}

class _ClassesListViewState extends State<_ClassesListView> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentClassesProvider>().load();
    });
  }

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Scaffold(
      backgroundColor: config.bgBase,
      appBar: AppBar(
        title: Text('My Classes', style: config.title),
        backgroundColor: config.bgSurface,
        elevation: 0,
        scrolledUnderElevation: 1,
        surfaceTintColor: Colors.transparent,
        actions: [
          IconButton(
            icon: Icon(Icons.refresh, size: config.iconMd),
            onPressed: () => context.read<StudentClassesProvider>().refresh(),
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: Consumer<StudentClassesProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading) {
            return _buildSkeleton(context);
          }

          if (provider.hasError) {
            return EmptyStates.error(
              message: provider.errorMessage ?? 'Failed to load classes',
              onRetry: () => provider.refresh(),
            );
          }

          if (!provider.hasData) {
            return EmptyStates.noClasses(
              onViewSchedule: () {}, // TODO: Navigate to schedule
              onRefresh: () => provider.refresh(),
            );
          }

          final currentClass = provider.getCurrentClass();
          final todaySchedule = provider.getTodaySchedule();

          return CustomScrollView(
            slivers: [
              // Current/Next class header
              if (currentClass != null)
                SliverToBoxAdapter(
                  child: _buildCurrentClassCard(context, currentClass),
                ),
              // Today's schedule
              if (todaySchedule.isNotEmpty) ...[
                SliverToBoxAdapter(
                  child: _buildTodaySchedule(
                    context,
                    todaySchedule,
                    currentClass != null ? _parseColor(currentClass.color) : config.primary,
                  ),
                ),
              ],
              // All classes list
              SliverPadding(
                padding: EdgeInsets.all(config.sm),
                sliver: SliverList.separated(
                  itemCount: provider.classes.length,
                  separatorBuilder: (_, __) => SizedBox(height: config.denseGap),
                  itemBuilder: (context, index) {
                    final cls = provider.classes[index];
                    return _ClassCard(
                      classSchedule: cls,
                      isCurrent: cls.id == currentClass?.id,
                      onTap: () => _showClassDetail(context, cls),
                    );
                  },
                ),
              ),
              SliverPadding(padding: EdgeInsets.only(bottom: config.xxl)),
            ],
          );
        },
      ),
    );
  }

  Widget _buildCurrentClassCard(BuildContext context, ClassSchedule cls) {
    final config = context.density;
    final currentOrNext = cls.getCurrentOrNextClass();
    final color = _parseColor(cls.color);

    return Container(
      margin: EdgeInsets.all(config.sm),
      padding: EdgeInsets.all(config.lg),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [color, color.withValues(alpha: 0.8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(config.radiusLg),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(config.badgeRadius),
                ),
                child: Text(
                  currentOrNext != null ? 'Now' : 'Next',
                  style: config.caption.copyWith(color: Colors.white, fontWeight: FontWeight.w700),
                ),
              ),
              const Spacer(),
              Text(
                cls.name,
                style: config.title.copyWith(fontSize: 20, color: Colors.white, fontWeight: FontWeight.w800),
              ),
            ],
          ),
          SizedBox(height: config.md),
          if (currentOrNext != null) ...[
            Row(
              children: [
                Icon(Icons.access_time, size: config.iconSm, color: Colors.white.withValues(alpha: 0.9)),
                SizedBox(width: config.xs),
                Text(
                  '${currentOrNext.start} - ${currentOrNext.end}',
                  style: config.value.copyWith(color: Colors.white, fontSize: 18),
                ),
                SizedBox(width: config.lg),
                Icon(Icons.room, size: config.iconSm, color: Colors.white.withValues(alpha: 0.9)),
                SizedBox(width: config.xs),
                Text(currentOrNext.room, style: config.body.copyWith(color: Colors.white)),
              ],
            ),
          ],
          SizedBox(height: config.sm),
          Row(
            children: [
              Icon(Icons.person, size: config.iconSm, color: Colors.white.withValues(alpha: 0.9)),
              SizedBox(width: config.xs),
              Text(cls.teacher, style: config.body.copyWith(color: Colors.white)),
              const Spacer(),
              FilledButton.tonal(
                onPressed: () {}, // TODO: Join class / view details
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white.withValues(alpha: 0.2),
                  foregroundColor: Colors.white,
                  padding: EdgeInsets.symmetric(horizontal: config.md, vertical: config.xs),
                ),
                child: Text('View', style: config.interactive),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTodaySchedule(BuildContext context, List<ScheduleEntry> schedule, Color accentColor) {
    final config = context.density;

    return Container(
      margin: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: EdgeInsets.symmetric(horizontal: config.sm),
            child: Text("Today's Schedule", style: config.title),
          ),
          SizedBox(height: config.xs),
          ...schedule.map((entry) => _ScheduleItemCard(entry: entry, accentColor: accentColor)).toList(),
        ],
      ),
    );
  }

  Widget _buildSkeleton(BuildContext context) {
    final config = context.density;
    return ListView.builder(
      padding: EdgeInsets.all(config.sm),
      itemCount: 5,
      itemBuilder: (_, __) => ListCardSkeleton(hasLeading: true, hasTrailing: true),
    );
  }

  void _showClassDetail(BuildContext context, ClassSchedule cls) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => ClassDetailScreen(classSchedule: cls)),
    );
  }
}

Color _parseColor(String hex) {
  try {
    return Color(int.parse(hex.replaceFirst('#', '0xFF')));
  } catch (_) {
    return Colors.blue;
  }
}

class _ScheduleItemCard extends StatelessWidget {
  const _ScheduleItemCard({required this.entry, required this.accentColor});

  final ScheduleEntry entry;
  final Color accentColor;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final now = DateTime.now();
    final startTime = entry.startTime;
    final endTime = entry.endTime;
    final isCurrent = startTime.isBefore(now) && endTime.isAfter(now);
    final isUpcoming = startTime.isAfter(now);

    return Container(
      margin: EdgeInsets.only(bottom: config.denseGap),
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        color: isCurrent ? accentColor.withValues(alpha: 0.1) : config.bgSurface,
        borderRadius: BorderRadius.circular(config.radiusMd),
        border: Border.all(
          color: isCurrent ? accentColor : config.borderLight,
          width: isCurrent ? 2 : 1,
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 4,
            height: 40,
            decoration: BoxDecoration(
              color: isCurrent ? accentColor : config.borderLight,
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
                Text(entry.subjectName, style: config.body.copyWith(fontWeight: FontWeight.w600)),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${entry.start} - ${entry.end}',
                style: config.value.copyWith(color: isCurrent ? accentColor : config.textPrimary),
              ),
              Text(entry.room, style: config.caption.copyWith(color: config.textMuted)),
            ],
          ),
          if (isCurrent) ...[
            SizedBox(width: config.sm),
            Container(
              padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
              decoration: BoxDecoration(
                color: accentColor,
                borderRadius: BorderRadius.circular(config.badgeRadius),
              ),
              child: Text('LIVE', style: config.caption.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
            ),
          ],
        ],
      ),
    );
  }
}

class _ClassCard extends StatelessWidget {
  const _ClassCard({
    required this.classSchedule,
    required this.isCurrent,
    required this.onTap,
  });

  final ClassSchedule classSchedule;
  final bool isCurrent;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final color = _parseColor(classSchedule.color);

    return CompactCard(
      onTap: onTap,
      padding: EdgeInsets.all(config.sm),
      borderColor: isCurrent ? color : null,
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(config.radiusMd),
            ),
            child: Icon(Icons.class_, size: config.iconMd, color: color),
          ),
          SizedBox(width: config.sm),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        classSchedule.name,
                        style: config.body.copyWith(fontWeight: FontWeight.w600),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (isCurrent)
                      Container(
                        padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 1),
                        decoration: BoxDecoration(
                          color: color,
                          borderRadius: BorderRadius.circular(config.badgeRadius),
                        ),
                        child: Text('Current', style: config.caption.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
                      ),
                  ],
                ),
                SizedBox(height: 2),
                Text(classSchedule.teacher, style: config.metadata.copyWith(color: config.textMuted)),
                SizedBox(height: 2),
                Row(
                  children: [
                    Icon(Icons.room, size: config.iconXs, color: config.textMuted),
                    SizedBox(width: 4),
                    Text(classSchedule.room, style: config.caption.copyWith(color: config.textMuted)),
                    SizedBox(width: config.sm),
                    Icon(Icons.calendar_today, size: config.iconXs, color: config.textMuted),
                    SizedBox(width: 4),
                    Text('${classSchedule.schedule.length} periods/week', style: config.caption.copyWith(color: config.textMuted)),
                  ],
                ),
              ],
            ),
          ),
          Icon(Icons.chevron_right, size: config.iconSm, color: config.textMuted),
        ],
      ),
    );
  }
}

/// Class detail screen.
class ClassDetailScreen extends StatelessWidget {
  const ClassDetailScreen({super.key, required this.classSchedule});

  final ClassSchedule classSchedule;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final color = _parseColor(classSchedule.color);

    return Scaffold(
      backgroundColor: config.bgBase,
      appBar: AppBar(
        title: Text(classSchedule.name, style: config.title),
        backgroundColor: config.bgSurface,
        elevation: 0,
        scrolledUnderElevation: 1,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(config.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Container(
              width: double.infinity,
              padding: EdgeInsets.all(config.lg),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [color, color.withValues(alpha: 0.8)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(config.radiusLg),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(classSchedule.name, style: config.title.copyWith(fontSize: 24, color: Colors.white, fontWeight: FontWeight.w800)),
                  SizedBox(height: config.sm),
                  Row(
                    children: [
                      Icon(Icons.person, size: config.iconSm, color: Colors.white.withValues(alpha: 0.9)),
                      SizedBox(width: config.xs),
                      Text(classSchedule.teacher, style: config.body.copyWith(color: Colors.white)),
                      SizedBox(width: config.lg),
                      Icon(Icons.room, size: config.iconSm, color: Colors.white.withValues(alpha: 0.9)),
                      SizedBox(width: config.xs),
                      Text(classSchedule.room, style: config.body.copyWith(color: Colors.white)),
                    ],
                  ),
                ],
              ),
            ),

            SizedBox(height: config.lg),

            // Schedule
            CompactCard(
              padding: EdgeInsets.all(config.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Weekly Schedule', style: config.title),
                  SizedBox(height: config.md),
                  ...classSchedule.schedule.map((entry) => _ScheduleRow(entry: entry, color: color)).toList(),
                ],
              ),
            ),

            SizedBox(height: config.xxl),
          ],
        ),
      ),
    );
  }
}

class _ScheduleRow extends StatelessWidget {
  const _ScheduleRow({required this.entry, required this.color});

  final ScheduleEntry entry;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return Padding(
      padding: EdgeInsets.only(bottom: config.sm),
      child: Row(
        children: [
          Container(
            width: 60,
            padding: EdgeInsets.symmetric(vertical: config.xs),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(config.radiusSm),
            ),
            child: Center(
              child: Text(entry.dayName.substring(0, 3), style: config.metadata.copyWith(color: color, fontWeight: FontWeight.w700)),
            ),
          ),
          SizedBox(width: config.sm),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Period ${entry.period}', style: config.caption.copyWith(color: config.textMuted)),
                Text(entry.subjectName, style: config.body.copyWith(fontWeight: FontWeight.w600)),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text('${entry.start} - ${entry.end}', style: config.body),
              Text(entry.room, style: config.caption.copyWith(color: config.textMuted)),
            ],
          ),
        ],
      ),
    );
  }
}