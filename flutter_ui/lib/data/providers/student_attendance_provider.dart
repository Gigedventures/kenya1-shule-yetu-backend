/// Student attendance provider.
library student_attendance_provider;

import 'package:flutter/material.dart';
import '../repositories/student_repository.dart';
import '../../models/student_models.dart';

enum AttendanceState { idle, loading, loaded, error }

class StudentAttendanceProvider extends ChangeNotifier {
  StudentAttendanceProvider({required StudentRepository repository}) : _repository = repository;

  final StudentRepository _repository;

  AttendanceState _state = AttendanceState.idle;
  List<AttendanceRecord> _records = [];
  AttendanceSummary? _summary;
  PaginationInfo? _pagination;
  String? _errorMessage;

  // Filters
  String? _statusFilter;
  int? _subjectFilter;
  DateTime? _dateFromFilter;
  DateTime? _dateToFilter;

  AttendanceState get state => _state;
  List<AttendanceRecord> get records => _records;
  AttendanceSummary? get summary => _summary;
  PaginationInfo? get pagination => _pagination;
  String? get errorMessage => _errorMessage;
  bool get isLoading => _state == AttendanceState.loading;
  bool get hasError => _state == AttendanceState.error;
  bool get hasData => _records.isNotEmpty;
  bool get hasNextPage => _pagination?.hasNextPage ?? false;

  String? get statusFilter => _statusFilter;
  int? get subjectFilter => _subjectFilter;
  DateTime? get dateFromFilter => _dateFromFilter;
  DateTime? get dateToFilter => _dateToFilter;

  Future<void> load({bool refresh = false}) async {
    if (refresh) {
      _records = [];
      _pagination = null;
    }

    _setState(AttendanceState.loading);

    try {
      final page = (_pagination?.currentPage ?? 0) + 1;
      final result = await _repository.getAttendance(
        page: page,
        perPage: 30,
        status: _statusFilter,
        subjectId: _subjectFilter,
        dateFrom: _dateFromFilter,
        dateTo: _dateToFilter,
      );

      if (refresh || page == 1) {
        _records = result.data;
      } else {
        _records.addAll(result.data);
      }

      _pagination = result.pagination;
      _summary = AttendanceSummary.fromMap(result.summary ?? {});
      _setState(AttendanceState.loaded);
    } catch (e) {
      _errorMessage = e.toString();
      _setState(AttendanceState.error);
    }
  }

  Future<void> loadMore() async {
    if (!hasNextPage || isLoading) return;
    await load();
  }

  Future<void> refresh() async {
    await load(refresh: true);
  }

  void setStatusFilter(String? status) {
    _statusFilter = status;
    refresh();
  }

  void setSubjectFilter(int? subjectId) {
    _subjectFilter = subjectId;
    refresh();
  }

  void setDateRangeFilter(DateTime? from, DateTime? to) {
    _dateFromFilter = from;
    _dateToFilter = to;
    refresh();
  }

  void clearFilters() {
    _statusFilter = null;
    _subjectFilter = null;
    _dateFromFilter = null;
    _dateToFilter = null;
    refresh();
  }

  List<AttendanceRecord> getRecordsByStatus(String status) {
    return _records.where((r) => r.status == status).toList();
  }

  Map<String, int> getStatusCounts() {
    final counts = <String, int>{};
    for (final record in _records) {
      counts[record.status] = (counts[record.status] ?? 0) + 1;
    }
    return counts;
  }

  void _setState(AttendanceState newState) {
    _state = newState;
    notifyListeners();
  }
}