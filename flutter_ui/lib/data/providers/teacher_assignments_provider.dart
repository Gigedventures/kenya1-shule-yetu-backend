/// Teacher assignments provider.
library teacher_assignments_provider;

import 'package:flutter/material.dart';
import '../repositories/teacher_repository.dart';
import '../../models/teacher_models.dart';

enum TeacherAssignmentsState { idle, loading, loaded, error }

class TeacherAssignmentsProvider extends ChangeNotifier {
  TeacherAssignmentsProvider({required TeacherRepository repository}) : _repository = repository;

  final TeacherRepository _repository;

  TeacherAssignmentsState _state = TeacherAssignmentsState.idle;
  List<TeacherAssignment> _assignments = [];
  TeacherAssignmentSummary? _summary;
  PaginationInfo? _pagination;
  String? _errorMessage;

  // Filters
  int? _classFilter;
  int? _subjectFilter;
  String? _statusFilter;
  String? _examTypeFilter;

  TeacherAssignmentsState get state => _state;
  List<TeacherAssignment> get assignments => _assignments;
  TeacherAssignmentSummary? get summary => _summary;
  PaginationInfo? get pagination => _pagination;
  String? get errorMessage => _errorMessage;
  bool get isLoading => _state == TeacherAssignmentsState.loading;
  bool get hasError => _state == TeacherAssignmentsState.error;
  bool get hasData => _assignments.isNotEmpty;
  bool get hasNextPage => _pagination?.hasNextPage ?? false;

  int? get classFilter => _classFilter;
  int? get subjectFilter => _subjectFilter;
  String? get statusFilter => _statusFilter;
  String? get examTypeFilter => _examTypeFilter;

  Future<void> load({bool refresh = false}) async {
    if (refresh) {
      _assignments = [];
      _pagination = null;
    }

    _setState(TeacherAssignmentsState.loading);

    try {
      final page = (_pagination?.currentPage ?? 0) + 1;
      final result = await _repository.getAssignments(
        page: page,
        perPage: 20,
        classId: _classFilter,
        subjectId: _subjectFilter,
        status: _statusFilter,
        examType: _examTypeFilter,
      );

      if (refresh || page == 1) {
        _assignments = result.data;
      } else {
        _assignments.addAll(result.data);
      }

      _pagination = result.pagination;
      _summary = TeacherAssignmentSummary.fromMap(result.summary ?? {});
      _setState(TeacherAssignmentsState.loaded);
    } catch (e) {
      _errorMessage = e.toString();
      _setState(TeacherAssignmentsState.error);
    }
  }

  Future<void> loadMore() async {
    if (!hasNextPage || isLoading) return;
    await load();
  }

  Future<void> refresh() async {
    await load(refresh: true);
  }

  void setClassFilter(int? classId) {
    _classFilter = classId;
    refresh();
  }

  void setSubjectFilter(int? subjectId) {
    _subjectFilter = subjectId;
    refresh();
  }

  void setStatusFilter(String? status) {
    _statusFilter = status;
    refresh();
  }

  void setExamTypeFilter(String? examType) {
    _examTypeFilter = examType;
    refresh();
  }

  void clearFilters() {
    _classFilter = null;
    _subjectFilter = null;
    _statusFilter = null;
    _examTypeFilter = null;
    refresh();
  }

  List<TeacherAssignment> getAssignmentsNeedingGrading() {
    return _assignments.where((a) => a.needsGrading).toList();
  }

  int get pendingGradingCount => _assignments.where((a) => a.needsGrading).length;

  void _setState(TeacherAssignmentsState newState) {
    _state = newState;
    notifyListeners();
  }
}