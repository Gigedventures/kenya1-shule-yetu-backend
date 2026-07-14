/// Student attendance list screen - Work layer.
library attendance_list_screen;

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../data/providers/student_attendance_provider.dart';
import '../../data/repositories/student_repository.dart';
import '../../services/k1_api_service.dart';
import '../../widgets/density/index.dart'
    show DenseDataTable, DenseDataColumn, EmptyStates, ListCardSkeleton, PaginationState, StatCard;
import '../../theme/density_tokens.dart';
import '../../models/student_models.dart';
import 'attendance_detail_screen.dart';

class StudentAttendanceListScreen extends StatelessWidget {
  const StudentAttendanceListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => StudentAttendanceProvider(
        repository: StudentRepositoryImpl(apiService: context.read<K1ApiService>()),
      )..load(),
      child: const _AttendanceListView(),
    );
  }
}

class _AttendanceListView extends StatefulWidget {
  const _AttendanceListView();

  @override
  State<_AttendanceListView> createState() => _AttendanceListViewState();
}

class _AttendanceListViewState extends State<_AttendanceListView> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentAttendanceProvider>().load();
    });
  }

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Scaffold(
      backgroundColor: config.bgBase,
      appBar: AppBar(
        title: Text('Attendance', style: config.title),
        backgroundColor: config.bgSurface,
        elevation: 0,
        scrolledUnderElevation: 1,
        surfaceTintColor: Colors.transparent,
        actions: [
          IconButton(
            icon: Icon(Icons.filter_list, size: config.iconMd),
            onPressed: () => _showFilters(context),
            tooltip: 'Filters',
          ),
          IconButton(
            icon: Icon(Icons.calendar_today, size: config.iconMd),
            onPressed: () => _showDatePicker(context),
            tooltip: 'Select date',
          ),
          IconButton(
            icon: Icon(Icons.refresh, size: config.iconMd),
            onPressed: () => context.read<StudentAttendanceProvider>().refresh(),
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: Consumer<StudentAttendanceProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.records.isEmpty) {
            return _buildSkeleton(context);
          }

          if (provider.hasError) {
            return EmptyStates.error(
              message: provider.errorMessage ?? 'Failed to load attendance',
              onRetry: () => provider.refresh(),
            );
          }

          return Column(
            children: [
              // Summary cards
              _buildSummaryCards(context, provider),
              // Filter chips
              if (provider.statusFilter != null ||
                  provider.subjectFilter != null ||
                  provider.dateFromFilter != null)
                _buildActiveFilters(context, provider),
              // Data table
              Expanded(
                child: DenseDataTable<AttendanceRecord>(
                  columns: _buildColumns(context),
                  data: provider.records,
                  pagination: provider.pagination != null
                      ? PaginationState(
                          page: provider.pagination!.currentPage,
                          perPage: provider.pagination!.perPage,
                          total: provider.pagination!.total,
                        )
                      : null,
                  onPaginationChanged: (p) => provider.load(),
                  loading: provider.isLoading && provider.records.isNotEmpty,
                  emptyMessage: 'No attendance records found',
                  emptyActionLabel: 'Clear filters',
                  onEmptyAction: () => provider.clearFilters(),
                  rowHeight: config.tableRowHeight,
                  showSelection: false,
                  onRowTap: (record, index) => _showAttendanceDetail(context, record),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSummaryCards(BuildContext context, StudentAttendanceProvider provider) {
    final config = context.density;
    final summary = provider.summary;

    if (summary == null) return const SizedBox.shrink();

    return Container(
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        color: config.bgSurface,
        border: Border(bottom: BorderSide(color: config.borderLight)),
      ),
      child: Row(
        children: [
          Expanded(
            child: StatCard(
              label: 'Rate',
              value: '${summary.rate.toStringAsFixed(1)}%',
              icon: Icons.trending_up,
              iconColor: summary.rate >= 90 ? config.success : (summary.rate >= 75 ? config.warning : config.error),
            ),
          ),
          SizedBox(width: config.sm),
          Expanded(
            child: StatCard(
              label: 'Present',
              value: summary.present.toString(),
              icon: Icons.check_circle,
              iconColor: config.success,
            ),
          ),
          SizedBox(width: config.sm),
          Expanded(
            child: StatCard(
              label: 'Absent',
              value: summary.absent.toString(),
              icon: Icons.cancel,
              iconColor: config.error,
            ),
          ),
          SizedBox(width: config.sm),
          Expanded(
            child: StatCard(
              label: 'Late',
              value: summary.late.toString(),
              icon: Icons.access_time,
              iconColor: config.warning,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActiveFilters(BuildContext context, StudentAttendanceProvider provider) {
    final config = context.density;
    final filters = <Widget>[];

    if (provider.statusFilter != null) {
      filters.add(_FilterChip(
        label: 'Status: ${provider.statusFilter}',
        onRemove: () => provider.setStatusFilter(null),
      ));
    }
    if (provider.dateFromFilter != null || provider.dateToFilter != null) {
      filters.add(_FilterChip(
        label: 'Date range',
        onRemove: () => provider.setDateRangeFilter(null, null),
      ));
    }

    if (filters.isEmpty) return const SizedBox.shrink();

    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
      decoration: BoxDecoration(
        color: config.primaryBg,
        border: Border(bottom: BorderSide(color: config.borderLight)),
      ),
      child: Wrap(
        spacing: config.xs,
        children: [
          ...filters,
          TextButton(
            onPressed: () => provider.clearFilters(),
            style: TextButton.styleFrom(
              padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
              minimumSize: Size.zero,
              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
            child: Text('Clear all', style: config.interactive.copyWith(color: config.primary)),
          ),
        ],
      ),
    );
  }

  List<DenseDataColumn<AttendanceRecord>> _buildColumns(BuildContext context) {
    final config = context.density;

    return [
      DenseDataColumn<AttendanceRecord>(
        key: 'date',
        label: 'Date',
        width: 120,
        builder: (record, index) => Text(_formatDate(record.date), style: config.tableCell.copyWith(fontWeight: FontWeight.w600)),
      ),
      DenseDataColumn<AttendanceRecord>(
        key: 'class',
        label: 'Class',
        width: 140,
        builder: (record, index) => Text(record.className, style: config.tableCell, maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
      DenseDataColumn<AttendanceRecord>(
        key: 'subject',
        label: 'Subject',
        width: 140,
        builder: (record, index) => Text(record.subject, style: config.tableCell, maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
      DenseDataColumn<AttendanceRecord>(
        key: 'status',
        label: 'Status',
        width: 100,
        builder: (record, index) => _AttendanceStatusBadge(status: record.status, color: record.statusColor),
      ),
      DenseDataColumn<AttendanceRecord>(
        key: 'check_in',
        label: 'Check-in',
        width: 90,
        builder: (record, index) => Text(record.checkInTime ?? '—', style: config.tableCell.copyWith(color: record.checkInTime != null ? config.textPrimary : config.textMuted)),
      ),
      DenseDataColumn<AttendanceRecord>(
        key: 'marked_by',
        label: 'Marked by',
        width: 140,
        builder: (record, index) => Text(record.markedBy, style: config.tableCell, maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
    ];
  }

  Widget _buildSkeleton(BuildContext context) {
    final config = context.density;
    return ListView.builder(
      padding: EdgeInsets.all(config.sm),
      itemCount: 5,
      itemBuilder: (_, __) => ListCardSkeleton(hasLeading: false, hasTrailing: true),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }

  void _showFilters(BuildContext context) {
    // TODO: Implement filter bottom sheet
  }

  void _showDatePicker(BuildContext context) async {
    final config = context.density;
    final provider = context.read<StudentAttendanceProvider>();
    final date = await showDatePicker(
      context: context,
      initialDate: provider.dateFromFilter ?? DateTime.now(),
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (date != null) {
      provider.setDateRangeFilter(date, date);
    }
  }

  void _showAttendanceDetail(BuildContext context, AttendanceRecord record) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => AttendanceDetailScreen(record: record)),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({required this.label, required this.onRemove});

  final String label;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
      decoration: BoxDecoration(
        color: config.bgSurface,
        borderRadius: BorderRadius.circular(config.badgeRadius),
        border: Border.all(color: config.borderMedium),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(label, style: config.caption),
          SizedBox(width: config.xs),
          GestureDetector(
            onTap: onRemove,
            child: Icon(Icons.close, size: config.iconXs, color: config.textMuted),
          ),
        ],
      ),
    );
  }
}

class _AttendanceStatusBadge extends StatelessWidget {
  const _AttendanceStatusBadge({required this.status, required this.color});

  final String status;
  final String color;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final statusColor = _parseColor(color);

    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
      decoration: BoxDecoration(
        color: statusColor.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(config.badgeRadius),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 6, height: 6, decoration: BoxDecoration(color: statusColor, shape: BoxShape.circle)),
          SizedBox(width: 4),
          Text(_statusLabel(status), style: config.caption.copyWith(color: statusColor, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  Color _parseColor(String hex) {
    try {
      return Color(int.parse(hex.replaceFirst('#', '0xFF')));
    } catch (_) {
      return Colors.grey;
    }
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'present':
        return 'Present';
      case 'absent':
        return 'Absent';
      case 'late':
        return 'Late';
      case 'excused':
        return 'Excused';
      default:
        return status;
    }
  }
}