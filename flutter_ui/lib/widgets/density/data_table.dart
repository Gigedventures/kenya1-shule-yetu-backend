/// Dense data table component for work layer lists.
library data_table;

import 'package:flutter/material.dart';
import '../../theme/density_tokens.dart';
import 'skeleton_loader.dart';

/// Column definition for dense data table.
class DenseDataColumn<T> {
  const DenseDataColumn({
    required this.key,
    required this.label,
    required this.builder,
    this.width,
    this.minWidth,
    this.maxWidth,
    this.align = TextAlign.start,
    this.headerAlign,
    this.sortable = false,
    this.visible = true,
    this.pinned = false,
    this.cellPadding,
  });

  final String key;
  final String label;
  final Widget Function(T item, int index) builder;
  final double? width;
  final double? minWidth;
  final double? maxWidth;
  final TextAlign align;
  final TextAlign? headerAlign;
  final bool sortable;
  final bool visible;
  final bool pinned;
  final EdgeInsetsGeometry? cellPadding;
}

/// Sort state for columns.
enum SortDirection { none, ascending, descending }

/// Sort configuration.
class SortState<T> {
  SortState({this.columnKey, this.direction = SortDirection.none});

  final String? columnKey;
  final SortDirection direction;

  SortState<T> copyWith({String? columnKey, SortDirection? direction}) {
    return SortState<T>(
      columnKey: columnKey ?? this.columnKey,
      direction: direction ?? this.direction,
    );
  }

  SortState<T> toggle(String key) {
    if (columnKey == key) {
      return copyWith(
        direction: direction == SortDirection.ascending
            ? SortDirection.descending
            : SortDirection.ascending,
      );
    }
    return copyWith(columnKey: key, direction: SortDirection.ascending);
  }
}

/// Filter configuration for columns.
class ColumnFilter {
  ColumnFilter({this.value, this.options});

  final String? value;
  final List<FilterOption>? options;

  ColumnFilter copyWith({String? value, List<FilterOption>? options}) {
    return ColumnFilter(value: value ?? this.value, options: options ?? this.options);
  }
}

class FilterOption {
  const FilterOption({required this.value, required this.label});
  final String value;
  final String label;
}

/// Pagination state.
class PaginationState {
  const PaginationState({
    this.page = 1,
    this.perPage = 20,
    this.total = 0,
  });

  final int page;
  final int perPage;
  final int total;

  int get totalPages => (total / perPage).ceil();
  bool get hasNext => page < totalPages;
  bool get hasPrevious => page > 1;

  PaginationState copyWith({int? page, int? perPage, int? total}) {
    return PaginationState(
      page: page ?? this.page,
      perPage: perPage ?? this.perPage,
      total: total ?? this.total,
    );
  }
}

/// Selection state for rows.
class SelectionState<T> {
  SelectionState({this.selectedIds = const <String>{}});

  final Set<String> selectedIds;

  bool isSelected(String id) => selectedIds.contains(id);
  bool get hasSelection => selectedIds.isNotEmpty;
  int get count => selectedIds.length;

  SelectionState<T> toggle(String id) {
    final newSet = Set<String>.from(selectedIds);
    if (newSet.contains(id)) {
      newSet.remove(id);
    } else {
      newSet.add(id);
    }
    return SelectionState<T>(selectedIds: newSet);
  }

  SelectionState<T> selectAll(Iterable<String> ids) {
    return SelectionState<T>(selectedIds: Set<String>.from(ids));
  }

  SelectionState<T> clear() => SelectionState<T>();
}

/// Main dense data table widget.
class DenseDataTable<T> extends StatefulWidget {
  const DenseDataTable({
    super.key,
    required this.columns,
    required this.data,
    this.sortState,
    this.onSort,
    this.filters,
    this.onFilterChanged,
    this.pagination,
    this.onPaginationChanged,
    this.selection,
    this.onSelectionChanged,
    this.onRowTap,
    this.rowIdExtractor,
    this.loading = false,
    this.emptyMessage = 'No data available',
    this.emptyActionLabel,
    this.onEmptyAction,
    this.showHeader = true,
    this.showPagination = true,
    this.showSelection = false,
    this.stickyHeader = true,
    this.rowHeight,
    this.compact = false,
    this.sortableColumns = const [],
    this.filterableColumns = const [],
  });

  final List<DenseDataColumn<T>> columns;
  final List<T> data;
  final SortState<T>? sortState;
  final void Function(SortState<T>)? onSort;
  final Map<String, ColumnFilter>? filters;
  final void Function(Map<String, ColumnFilter>)? onFilterChanged;
  final PaginationState? pagination;
  final void Function(PaginationState)? onPaginationChanged;
  final SelectionState<T>? selection;
  final void Function(SelectionState<T>)? onSelectionChanged;
  final void Function(T item, int index)? onRowTap;
  final String Function(T item)? rowIdExtractor;
  final bool loading;
  final String emptyMessage;
  final String? emptyActionLabel;
  final VoidCallback? onEmptyAction;
  final bool showHeader;
  final bool showPagination;
  final bool showSelection;
  final bool stickyHeader;
  final double? rowHeight;
  final bool compact;
  final List<String> sortableColumns;
  final List<String> filterableColumns;

  @override
  State<DenseDataTable<T>> createState() => _DenseDataTableState<T>();
}

class _DenseDataTableState<T> extends State<DenseDataTable<T>> {
  late SortState<T> _sortState;
  late Map<String, ColumnFilter> _filters;
  late PaginationState _pagination;
  late SelectionState<T> _selection;
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _sortState = widget.sortState ?? SortState<T>();
    _filters = widget.filters ?? <String, ColumnFilter>{};
    _pagination = widget.pagination ?? const PaginationState();
    _selection = widget.selection ?? SelectionState<T>();
  }

  @override
  void didUpdateWidget(covariant DenseDataTable<T> oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.sortState != oldWidget.sortState && widget.sortState != null) {
      _sortState = widget.sortState!;
    }
    if (widget.filters != oldWidget.filters) {
      _filters = widget.filters ?? <String, ColumnFilter>{};
    }
    if (widget.pagination != oldWidget.pagination && widget.pagination != null) {
      _pagination = widget.pagination!;
    }
    if (widget.selection != oldWidget.selection && widget.selection != null) {
      _selection = widget.selection!;
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  List<DenseDataColumn<T>> get _visibleColumns =>
      widget.columns.where((c) => c.visible).toList();

  String _getRowId(T item, int index) {
    if (widget.rowIdExtractor != null) {
      return widget.rowIdExtractor!(item);
    }
    return 'row-$index';
  }

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final visibleColumns = _visibleColumns;
    final rowHeight = widget.rowHeight ?? (widget.compact ? config.tableRowHeightCompact : config.tableRowHeight);

    if (widget.loading) {
      return _buildSkeleton(config, visibleColumns.length, rowHeight);
    }

    if (widget.data.isEmpty) {
      return _buildEmptyState(config);
    }

    return Column(
      children: [
        if (widget.showHeader) _buildHeader(config, visibleColumns),
        Expanded(
          child: widget.stickyHeader
              ? _buildScrollableTable(config, visibleColumns, rowHeight)
              : _buildSimpleTable(config, visibleColumns, rowHeight),
        ),
        if (widget.showPagination && _pagination.totalPages > 1)
          _buildPagination(config),
      ],
    );
  }

  Widget _buildHeader(DensityConfig config, List<DenseDataColumn<T>> columns) {
    return Container(
      height: config.tableHeaderHeight,
      decoration: BoxDecoration(
        color: config.bgBase,
        border: Border(bottom: BorderSide(color: config.borderLight)),
      ),
      child: Row(
        children: [
          if (widget.showSelection)
            SizedBox(
              width: 40,
              child: Checkbox(
                value: _selection.hasSelection && _selection.count == widget.data.length,
                tristate: _selection.hasSelection && _selection.count < widget.data.length,
                onChanged: (_) => _toggleSelectAll(),
                materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
            ),
          ...columns.map((col) => _buildHeaderCell(config, col)).toList(),
        ],
      ),
    );
  }

  Widget _buildHeaderCell(DensityConfig config, DenseDataColumn<T> col) {
    final isSorted = _sortState.columnKey == col.key;
    final sortDirection = _sortState.direction;
    final canSort = widget.sortableColumns.contains(col.key) && col.sortable;

    return SizedBox(
      width: col.width,
      child: InkWell(
        onTap: canSort ? () => _handleSort(col.key) : null,
        child: Container(
          padding: col.cellPadding ?? EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
          alignment: Alignment.centerLeft,
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Flexible(
                child: Text(
                  col.label,
                  style: config.tableHeader.copyWith(color: config.textSecondary),
                  overflow: TextOverflow.ellipsis,
                  maxLines: 1,
                ),
              ),
              if (canSort) ...[
                SizedBox(width: config.xs),
                Icon(
                  isSorted
                      ? (sortDirection == SortDirection.ascending ? Icons.arrow_upward : Icons.arrow_downward)
                      : Icons.unfold_more,
                  size: config.iconXs,
                  color: isSorted ? config.primary : config.textMuted,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildScrollableTable(DensityConfig config, List<DenseDataColumn<T>> columns, double rowHeight) {
    return Scrollbar(
      controller: _scrollController,
      child: SingleChildScrollView(
        controller: _scrollController,
        scrollDirection: Axis.vertical,
        child: Column(
          children: widget.data.asMap().entries.map((entry) {
            final index = entry.key;
            final item = entry.value;
            return _buildRow(config, columns, item, index, rowHeight);
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildSimpleTable(DensityConfig config, List<DenseDataColumn<T>> columns, double rowHeight) {
    return ListView.separated(
      controller: _scrollController,
      itemCount: widget.data.length,
      separatorBuilder: (_, __) => Divider(height: 1, color: config.borderLight, indent: 0, endIndent: 0),
      itemBuilder: (context, index) {
        return _buildRow(config, columns, widget.data[index], index, rowHeight);
      },
    );
  }

  Widget _buildRow(DensityConfig config, List<DenseDataColumn<T>> columns, T item, int index, double rowHeight) {
    final rowId = _getRowId(item, index);
    final isSelected = _selection.isSelected(rowId);
    final isEven = index.isEven;

    return InkWell(
      onTap: () => widget.onRowTap?.call(item, index),
      onLongPress: widget.showSelection ? () => _toggleSelection(rowId) : null,
      child: AnimatedContainer(
        duration: config.motionFast,
        height: rowHeight,
        color: isSelected
            ? config.primaryBg
            : isEven
                ? config.bgBase.withValues(alpha: 0.3)
                : Colors.transparent,
        child: Row(
          children: [
            if (widget.showSelection)
              SizedBox(
                width: 40,
                child: Checkbox(
                  value: isSelected,
                  onChanged: (_) => _toggleSelection(rowId),
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
              ),
            ...columns.map((col) => _buildCell(config, col, item, index)).toList(),
          ],
        ),
      ),
    );
  }

  Widget _buildCell(DensityConfig config, DenseDataColumn<T> col, T item, int index) {
    return SizedBox(
      width: col.width,
      child: Container(
        padding: col.cellPadding ?? EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
        alignment: Alignment.centerLeft,
        child: col.builder(item, index),
      ),
    );
  }

  Widget _buildSkeleton(DensityConfig config, int columnCount, double rowHeight) {
    return Column(
      children: List.generate(5, (index) {
        final isEven = index.isEven;
        return Container(
          height: rowHeight,
          color: isEven ? config.bgBase.withValues(alpha: 0.3) : Colors.transparent,
          child: Row(
            children: [
              if (widget.showSelection) const SizedBox(width: 40),
              ...List.generate(columnCount, (colIndex) {
                return Expanded(
                  child: Padding(
                    padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
                    child: SkeletonLoader(width: double.infinity, height: 14),
                  ),
                );
              }),
            ],
          ),
        );
      }),
    );
  }

  Widget _buildEmptyState(DensityConfig config) {
    return Center(
      child: Padding(
        padding: EdgeInsets.all(config.xl),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.inbox_outlined, size: 48, color: config.textMuted),
            SizedBox(height: config.sm),
            Text(widget.emptyMessage, style: config.body.copyWith(color: config.textSecondary), textAlign: TextAlign.center),
            if (widget.emptyActionLabel != null && widget.onEmptyAction != null) ...[
              SizedBox(height: config.lg),
              OutlinedButton(
                onPressed: widget.onEmptyAction,
                style: OutlinedButton.styleFrom(
                  padding: EdgeInsets.symmetric(horizontal: config.lg, vertical: config.sm),
                ),
                child: Text(widget.emptyActionLabel!, style: config.interactive),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildPagination(DensityConfig config) {
    return Container(
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        color: config.bgSurface,
        border: Border(top: BorderSide(color: config.borderLight)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            'Showing ${(_pagination.page - 1) * _pagination.perPage + 1}–${(_pagination.page * _pagination.perPage).clamp(0, _pagination.total)} of ${_pagination.total}',
            style: config.metadata.copyWith(color: config.textMuted),
          ),
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              IconButton(
                icon: Icon(Icons.chevron_left, size: config.iconSm),
                onPressed: _pagination.hasPrevious
                    ? () => _handlePageChange(_pagination.page - 1)
                    : null,
                tooltip: 'Previous',
                constraints: BoxConstraints(minWidth: 32, minHeight: 32),
                padding: EdgeInsets.zero,
              ),
              Text('Page ${_pagination.page} of ${_pagination.totalPages}', style: config.metadata),
              IconButton(
                icon: Icon(Icons.chevron_right, size: config.iconSm),
                onPressed: _pagination.hasNext
                    ? () => _handlePageChange(_pagination.page + 1)
                    : null,
                tooltip: 'Next',
                constraints: BoxConstraints(minWidth: 32, minHeight: 32),
                padding: EdgeInsets.zero,
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _handleSort(String columnKey) {
    final newState = _sortState.toggle(columnKey);
    setState(() => _sortState = newState);
    widget.onSort?.call(newState);
  }

  void _handlePageChange(int page) {
    final newState = _pagination.copyWith(page: page);
    setState(() => _pagination = newState);
    widget.onPaginationChanged?.call(newState);
  }

  void _toggleSelection(String id) {
    final newState = _selection.toggle(id);
    setState(() => _selection = newState);
    widget.onSelectionChanged?.call(newState);
  }

  void _toggleSelectAll() {
    final List<String> allIds = widget.data.asMap().entries.map((e) => _getRowId(e.value, e.key)).toList();
    final newState = _selection.count == allIds.length ? _selection.clear() : _selection.selectAll(allIds);
    setState(() => _selection = newState);
    widget.onSelectionChanged?.call(newState);
  }
}
