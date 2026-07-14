/// Student assignments provider with state management.
library student_assignments_provider;

import 'package:flutter/material.dart';
import '../repositories/student_repository.dart';
import '../../models/student_models.dart';

/// Loading state enum.
enum AssignmentsState { idle, loading, loaded, error }

/// Provider for student assignments.
class StudentAssignmentsProvider extends ChangeNotifier {
  StudentAssignmentsProvider({required StudentRepository repository}) : _repository = repository;

  final StudentRepository _repository;

  // State
  AssignmentsState _state = AssignmentsState.idle;
  List<Assignment> _assignments = [];
  AssignmentSummary? _summary;
  PaginationInfo? _pagination;
  String? _errorMessage;

  // Filters
  String? _statusFilter;
  int? _subjectFilter;
  String? _examTypeFilter;
  DateTime? _dateFromFilter;
  DateTime? _dateToFilter;

  // Getters
  AssignmentsState get state => _state;
  List<Assignment> get assignments => _assignments;
  AssignmentSummary? get summary => _summary;
  PaginationInfo? get pagination => _pagination;
  String? get errorMessage => _errorMessage;
  bool get isLoading => _state == AssignmentsState.loading;
  bool get hasError => _state == AssignmentsState.error;
  bool get hasData => _assignments.isNotEmpty;
  bool get hasNextPage => _pagination?.hasNextPage ?? false;

  // Filters
  String? get statusFilter => _statusFilter;
  int? get subjectFilter => _subjectFilter;
  String? get examTypeFilter => _examTypeFilter;
  DateTime? get dateFromFilter => _dateFromFilter;
  DateTime? get dateToFilter => _dateToFilter;

  /// Load assignments with current filters.
  Future<void> load({bool refresh = false}) async {
    if (refresh) {
      _assignments = [];
      _pagination = null;
    }

    _setState(AssignmentsState.loading);

    try {
      final page = (_pagination?.currentPage ?? 0) + 1;
      final result = await _repository.getAssignments(
        page: page,
        perPage: 20,
        status: _statusFilter,
        subjectId: _subjectFilter,
        examType: _examTypeFilter,
        dateFrom: _dateFromFilter,
        dateTo: _dateToFilter,
      );

      if (refresh || page == 1) {
        _assignments = result.data;
      } else {
        _assignments.addAll(result.data);
      }

      _pagination = result.pagination;
      _summary = AssignmentSummary.fromMap(result.summary ?? {});
      _setState(AssignmentsState.loaded);
    } catch (e) {
      _errorMessage = e.toString();
      _setState(AssignmentsState.error);
    }
  }

  /// Load next page.
  Future<void> loadMore() async {
    if (!hasNextPage || isLoading) return;
    await load();
  }

  /// Refresh assignments.
  Future<void> refresh() async {
    await load(refresh: true);
  }

  /// Set status filter.
  void setStatusFilter(String? status) {
    _statusFilter = status;
    refresh();
  }

  /// Set subject filter.
  void setSubjectFilter(int? subjectId) {
    _subjectFilter = subjectId;
    refresh();
  }

  /// Set exam type filter.
  void setExamTypeFilter(String? examType) {
    _examTypeFilter = examType;
    refresh();
  }

  /// Set date range filter.
  void setDateRangeFilter(DateTime? from, DateTime? to) {
    _dateFromFilter = from;
    _dateToFilter = to;
    refresh();
  }

  /// Clear all filters.
  void clearFilters() {
    _statusFilter = null;
    _subjectFilter = null;
    _examTypeFilter = null;
    _dateFromFilter = null;
    _dateToFilter = null;
    refresh();
  }

  /// Get assignments by status.
  List<Assignment> getAssignmentsByStatus(String status) {
    return _assignments.where((a) => a.status == status).toList();
  }

  /// Get pending count.
  int get pendingCount => _assignments.where((a) => a.isPending).length;

  /// Get overdue count.
  int get overdueCount => _assignments.where((a) => a.isOverdue).length;

  void _setState(AssignmentsState newState) {
    _state = newState;
    notifyListeners();
  }
}