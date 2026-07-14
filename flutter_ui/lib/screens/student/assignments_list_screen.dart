/// Student assignments list screen - Work layer.
library assignments_list_screen;

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../data/providers/student_assignments_provider.dart';
import '../../data/repositories/student_repository.dart';
import '../../services/k1_api_service.dart';
import '../../widgets/density/index.dart';
import '../../theme/density_tokens.dart';
import '../../models/student_models.dart';
import 'assignment_detail_screen.dart';

class StudentAssignmentsListScreen extends StatelessWidget {
  const StudentAssignmentsListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => StudentAssignmentsProvider(
        repository: StudentRepositoryImpl(apiService: context.read<K1ApiService>()),
      )..load(),
      child: const _AssignmentsListView(),
    );
  }
}

class _AssignmentsListView extends StatefulWidget {
  const _AssignmentsListView();

  @override
  State<_AssignmentsListView> createState() => _AssignmentsListViewState();
}

class _AssignmentsListViewState extends State<_AssignmentsListView> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentAssignmentsProvider>().load();
    });
  }

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Scaffold(
      backgroundColor: config.bgBase,
      appBar: AppBar(
        title: Text('Assignments', style: config.title),
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
            icon: Icon(Icons.refresh, size: config.iconMd),
            onPressed: () => context.read<StudentAssignmentsProvider>().refresh(),
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: Consumer<StudentAssignmentsProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading && provider.assignments.isEmpty) {
            return _buildSkeleton(context);
          }

          if (provider.hasError) {
            return EmptyStates.error(
              message: provider.errorMessage ?? 'Failed to load assignments',
              onRetry: () => provider.refresh(),
            );
          }

          return Column(
            children: [
              // Summary bar
              _buildSummaryBar(context, provider),
              // Filter chips
              if (provider.statusFilter != null ||
                  provider.subjectFilter != null ||
                  provider.examTypeFilter != null ||
                  provider.dateFromFilter != null)
                _buildActiveFilters(context, provider),
              // Data table
              Expanded(
                child: DenseDataTable<Assignment>(
                  columns: _buildColumns(context),
                  data: provider.assignments,
                  sortState: null, // Server-side sorting
                  onSort: null,
                  pagination: PaginationState(
                    page: provider.pagination?.currentPage ?? 1,
                    perPage: provider.pagination?.perPage ?? 20,
                    total: provider.pagination?.total ?? 0,
                  ),
                  onPaginationChanged: (p) => provider.load(),
                  loading: provider.isLoading && provider.assignments.isNotEmpty,
                  emptyMessage: 'No assignments found',
                  emptyActionLabel: 'Clear filters',
                  onEmptyAction: () => provider.clearFilters(),
                  rowHeight: config.tableRowHeight,
                  compact: false,
                  showSelection: false,
                  onRowTap: (assignment, index) => _showAssignmentDetail(context, assignment),
                ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {}, // TODO: Create assignment
        icon: Icon(Icons.add, size: config.iconSm),
        label: Text('New', style: config.interactive),
      ),
    );
  }

  Widget _buildSummaryBar(BuildContext context, StudentAssignmentsProvider provider) {
    final config = context.density;
    final summary = provider.summary;

    return Container(
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        color: config.bgSurface,
        border: Border(bottom: BorderSide(color: config.borderLight)),
      ),
      child: Row(
        children: [
          _SummaryChip(
            label: 'Total',
            value: summary?.total.toString() ?? '0',
            color: config.primary,
          ),
          SizedBox(width: config.xs),
          _SummaryChip(
            label: 'Pending',
            value: summary?.pending.toString() ?? '0',
            color: Colors.orange,
          ),
          SizedBox(width: config.xs),
          _SummaryChip(
            label: 'Submitted',
            value: summary?.submitted.toString() ?? '0',
            color: Colors.blue,
          ),
          SizedBox(width: config.xs),
          _SummaryChip(
            label: 'Graded',
            value: summary?.graded.toString() ?? '0',
            color: Colors.green,
          ),
          SizedBox(width: config.xs),
          _SummaryChip(
            label: 'Overdue',
            value: summary?.overdue.toString() ?? '0',
            color: Colors.red,
          ),
          const Spacer(),
          if (provider.pagination != null)
            Text(
              'Page ${provider.pagination!.currentPage} of ${provider.pagination!.lastPage}',
              style: config.metadata.copyWith(color: config.textMuted),
            ),
        ],
      ),
    );
  }

  Widget _buildActiveFilters(BuildContext context, StudentAssignmentsProvider provider) {
    final config = context.density;
    final filters = <Widget>[];

    if (provider.statusFilter != null) {
      filters.add(_FilterChip(
        label: 'Status: ${provider.statusFilter}',
        onRemove: () => provider.setStatusFilter(null),
      ));
    }
    if (provider.examTypeFilter != null) {
      filters.add(_FilterChip(
        label: 'Type: ${provider.examTypeFilter}',
        onRemove: () => provider.setExamTypeFilter(null),
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

  List<DenseDataColumn<Assignment>> _buildColumns(BuildContext context) {
    final config = context.density;

    return [
      DenseDataColumn<Assignment>(
        key: 'title',
        label: 'Assignment',
        width: 280,
        minWidth: 200,
        builder: (assignment, index) => Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(assignment.title, style: config.tableCell.copyWith(fontWeight: FontWeight.w600), maxLines: 1, overflow: TextOverflow.ellipsis),
            Text(assignment.subject, style: config.caption.copyWith(color: config.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis),
          ],
        ),
      ),
      DenseDataColumn<Assignment>(
        key: 'type',
        label: 'Type',
        width: 100,
        builder: (assignment, index) => _TypeBadge(type: assignment.type),
      ),
      DenseDataColumn<Assignment>(
        key: 'status',
        label: 'Status',
        width: 110,
        builder: (assignment, index) => _StatusBadge(label: assignment.statusLabel, color: _statusColor(assignment.status)),
      ),
      DenseDataColumn<Assignment>(
        key: 'due_date',
        label: 'Due',
        width: 140,
        builder: (assignment, index) => Text(
          assignment.formattedDueDate,
          style: config.tableCell.copyWith(
            color: assignment.isOverdue ? config.error : config.textPrimary,
            fontWeight: assignment.isOverdue ? FontWeight.w600 : FontWeight.w400,
          ),
        ),
      ),
      DenseDataColumn<Assignment>(
        key: 'score',
        label: 'Score',
        width: 100,
        builder: (assignment, index) {
          if (assignment.score == null) {
            return Text('—', style: config.tableCell.copyWith(color: config.textMuted));
          }
          return Text(
            '${assignment.score!.toStringAsFixed(1)}/${assignment.maxScore}',
            style: config.tableCell.copyWith(fontWeight: FontWeight.w600),
          );
        },
      ),
      DenseDataColumn<Assignment>(
        key: 'teacher',
        label: 'Teacher',
        width: 140,
        builder: (assignment, index) => Text(assignment.teacherName, style: config.tableCell, maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
    ];
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

  Widget _buildSkeleton(BuildContext context) {
    final config = context.density;
    return ListView.builder(
      padding: EdgeInsets.all(config.sm),
      itemCount: 5,
      itemBuilder: (_, __) => ListCardSkeleton(hasLeading: false, hasTrailing: true),
    );
  }

  void _showFilters(BuildContext context) {
    // TODO: Implement filter bottom sheet
  }

  void _showAssignmentDetail(BuildContext context, Assignment assignment) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => AssignmentDetailScreen(assignment: assignment)),
    );
  }
}

class _SummaryChip extends StatelessWidget {
  const _SummaryChip({required this.label, required this.value, required this.color});

  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(config.badgeRadius),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(value, style: config.value.copyWith(color: color, fontSize: 16)),
          Text(label, style: config.caption.copyWith(color: color)),
        ],
      ),
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

class _TypeBadge extends StatelessWidget {
  const _TypeBadge({required this.type});

  final String type;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final (label, color) = switch (type.toLowerCase()) {
      'homework' => ('Homework', Colors.blue),
      'exam' => ('Exam', Colors.purple),
      'project' => ('Project', Colors.teal),
      _ => (type, Colors.grey),
    };

    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(config.badgeRadius),
      ),
      child: Text(label, style: config.caption.copyWith(color: color, fontWeight: FontWeight.w600)),
    );
  }
}

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
      child: Text(label, style: config.caption.copyWith(color: color, fontWeight: FontWeight.w600)),
    );
  }
}