/// Teacher attendance provider.
library teacher_attendance_provider;

import 'package:flutter/material.dart';
import '../repositories/teacher_repository.dart';
import '../../models/teacher_models.dart';

enum TeacherAttendanceState { idle, loading, loaded, error, marking }

class TeacherAttendanceProvider extends ChangeNotifier {
  TeacherAttendanceProvider({required TeacherRepository repository}) : _repository = repository;

  final TeacherRepository _repository;

  TeacherAttendanceState _state = TeacherAttendanceState.idle;
  List<AttendanceRecord> _records = [];
  List<AttendanceStats> _stats = [];
  PaginationInfo? _pagination;
  String? _errorMessage;

  // Filters
  DateTime? _dateFilter;
  DateTime? _dateFromFilter;
  DateTime? _dateToFilter;
  int? _classFilter;
  int? _studentFilter;
  String? _statusFilter;
  int? _subjectFilter;

  TeacherAttendanceState get state => _state;
  List<AttendanceRecord> get records => _records;
  List<AttendanceStats> get stats => _stats;
  PaginationInfo? get pagination => _pagination;
  String? get errorMessage => _errorMessage;
  bool get isLoading => _state == TeacherAttendanceState.loading;
  bool get isMarking => _state == TeacherAttendanceState.marking;
  bool get hasError => _state == TeacherAttendanceState.error;
  bool get hasData => _records.isNotEmpty;
  bool get hasNextPage => _pagination?.hasNextPage ?? false;

  DateTime? get dateFilter => _dateFilter;
  DateTime? get dateFromFilter => _dateFromFilter;
  DateTime? get dateToFilter => _dateToFilter;
  int? get classFilter => _classFilter;
  int? get studentFilter => _studentFilter;
  String? get statusFilter => _statusFilter;
  int? get subjectFilter => _subjectFilter;

  Future<void> load({bool refresh = false}) async {
    if (refresh) {
      _records = [];
      _pagination = null;
    }

    _setState(TeacherAttendanceState.loading);

    try {
      final page = (_pagination?.currentPage ?? 0) + 1;
      final result = await _repository.getAttendance(
        page: page,
        perPage: 50,
        date: _dateFilter,
        dateFrom: _dateFromFilter,
        dateTo: _dateToFilter,
        classId: _classFilter,
        studentId: _studentFilter,
        status: _statusFilter,
        subjectId: _subjectFilter,
      );

      if (refresh || page == 1) {
        _records = result.data;
      } else {
        _records.addAll(result.data);
      }

      _pagination = result.pagination;
      _setState(TeacherAttendanceState.loaded);
    } catch (e) {
      _errorMessage = e.toString();
      _setState(TeacherAttendanceState.error);
    }
  }

  Future<void> loadStats() async {
    try {
      _stats = await _repository.getAttendanceStats();
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
    }
  }

  Future<void> loadMore() async {
    if (!hasNextPage || isLoading) return;
    await load();
  }

  Future<void> refresh() async {
    await load(refresh: true);
    await loadStats();
  }

  Future<MarkAttendanceResult> markAttendance(List<AttendanceMarkRequest> records) async {
    _setState(TeacherAttendanceState.marking);

    try {
      final result = await _repository.markAttendance(records);
      await refresh(); // Reload to get updated data
      return result;
    } catch (e) {
      _errorMessage = e.toString();
      _setState(TeacherAttendanceState.error);
      rethrow;
    }
  }

  void setDateFilter(DateTime? date) {
    _dateFilter = date;
    _dateFromFilter = null;
    _dateToFilter = null;
    refresh();
  }

  void setDateRangeFilter(DateTime? from, DateTime? to) {
    _dateFromFilter = from;
    _dateToFilter = to;
    _dateFilter = null;
    refresh();
  }

  void setClassFilter(int? classId) {
    _classFilter = classId;
    refresh();
  }

  void setStudentFilter(int? studentId) {
    _studentFilter = studentId;
    refresh();
  }

  void setStatusFilter(String? status) {
    _statusFilter = status;
    refresh();
  }

  void setSubjectFilter(int? subjectId) {
    _subjectFilter = subjectId;
    refresh();
  }

  void clearFilters() {
    _dateFilter = null;
    _dateFromFilter = null;
    _dateToFilter = null;
    _classFilter = null;
    _studentFilter = null;
    _statusFilter = null;
    _subjectFilter = null;
    refresh();
  }

  Map<String, int> getStatusCounts() {
    final counts = <String, int>{};
    for (final record in _records) {
      counts[record.status] = (counts[record.status] ?? 0) + 1;
    }
    return counts;
  }

  void _setState(TeacherAttendanceState newState) {
    _state = newState;
    notifyListeners();
  }
}