/// Student data repository - API → Repository → Provider → UI
library student_repository;

import 'dart:async';

import '../../services/k1_api_service.dart';
import '../../models/student_models.dart';
import '../mock_student_api_data.dart';

/// Student repository interface for testability.
abstract class StudentRepository {
  Future<PaginatedResult<Assignment>> getAssignments({
    int page = 1,
    int perPage = 20,
    String? status,
    int? subjectId,
    String? examType,
    DateTime? dateFrom,
    DateTime? dateTo,
  });

  Future<PaginatedResult<AttendanceRecord>> getAttendance({
    int page = 1,
    int perPage = 30,
    String? status,
    int? subjectId,
    DateTime? dateFrom,
    DateTime? dateTo,
  });

  Future<List<ClassSchedule>> getClasses();

  Future<AssignmentSummary> getAssignmentSummary();

  Future<AttendanceSummary> getAttendanceSummary();
}

/// Paginated result wrapper.
class PaginatedResult<T> {
  const PaginatedResult({
    required this.data,
    required this.pagination,
    this.summary,
  });

  final List<T> data;
  final PaginationInfo pagination;
  final Map<String, dynamic>? summary;

  bool get hasNextPage => pagination.currentPage < pagination.lastPage;
  bool get hasPreviousPage => pagination.currentPage > 1;
}

class PaginationInfo {
  const PaginationInfo({
    required this.currentPage,
    required this.perPage,
    required this.total,
    required this.lastPage,
    this.from,
    this.to,
  });

  final int currentPage;
  final int perPage;
  final int total;
  final int lastPage;
  final int? from;
  final int? to;

  bool get hasNextPage => currentPage < lastPage;
  bool get hasPreviousPage => currentPage > 1;
}

/// Student repository implementation using K1ApiService.
class StudentRepositoryImpl implements StudentRepository {
  StudentRepositoryImpl({required K1ApiService apiService}) : _apiService = apiService;

  final K1ApiService _apiService;

  @override
  Future<PaginatedResult<Assignment>> getAssignments({
    int page = 1,
    int perPage = 20,
    String? status,
    int? subjectId,
    String? examType,
    DateTime? dateFrom,
    DateTime? dateTo,
  }) async {
    try {
      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
      };

      if (status != null) queryParams['status'] = status;
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();
      if (examType != null) queryParams['exam_type'] = examType;
      if (dateFrom != null) queryParams['date_from'] = dateFrom.toIso8601String().split('T').first;
      if (dateTo != null) queryParams['date_to'] = dateTo.toIso8601String().split('T').first;

      final queryString = queryParams.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&');
      final response = await _apiService.get('/api/v1/student/assignments?$queryString');

      return _parseAssignmentsResponse(response);
    } on K1ApiException catch (_) {
      return _mockAssignments();
    }
  }

  PaginatedResult<Assignment> _mockAssignments() {
    return PaginatedResult<Assignment>(
      data: MockStudentApiData.assignments,
      pagination: PaginationInfo(
        currentPage: 1,
        perPage: 20,
        total: MockStudentApiData.assignments.length,
        lastPage: 1,
      ),
      summary: {
        'total': MockStudentApiData.assignments.length,
        'pending': MockStudentApiData.assignments.where((a) => a.status == 'pending').length,
        'submitted': MockStudentApiData.assignments.where((a) => a.status == 'submitted').length,
        'graded': MockStudentApiData.assignments.where((a) => a.status == 'graded').length,
        'overdue': MockStudentApiData.assignments.where((a) => a.status == 'overdue').length,
      },
    );
  }

  @override
  Future<PaginatedResult<AttendanceRecord>> getAttendance({
    int page = 1,
    int perPage = 30,
    String? status,
    int? subjectId,
    DateTime? dateFrom,
    DateTime? dateTo,
  }) async {
    try {
      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
      };

      if (status != null) queryParams['status'] = status;
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();
      if (dateFrom != null) queryParams['date_from'] = dateFrom.toIso8601String().split('T').first;
      if (dateTo != null) queryParams['date_to'] = dateTo.toIso8601String().split('T').first;

      final queryString = queryParams.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&');
      final response = await _apiService.get('/api/v1/student/attendance?$queryString');

      return _parseAttendanceResponse(response);
    } on K1ApiException catch (_) {
      return _mockAttendance();
    }
  }

  PaginatedResult<AttendanceRecord> _mockAttendance() {
    return PaginatedResult<AttendanceRecord>(
      data: MockStudentApiData.attendanceRecords,
      pagination: PaginationInfo(
        currentPage: 1,
        perPage: 30,
        total: MockStudentApiData.attendanceRecords.length,
        lastPage: 1,
      ),
      summary: {
        'present': 42,
        'absent': 2,
        'late': 1,
        'excused': 1,
        'rate': 94.4,
      },
    );
  }

  @override
  Future<List<ClassSchedule>> getClasses() async {
    try {
      final response = await _apiService.get('/api/v1/student/classes');
      return _parseClassesResponse(response);
    } on K1ApiException catch (_) {
      return MockStudentApiData.classSchedules;
    }
  }

  @override
  Future<AssignmentSummary> getAssignmentSummary() async {
    try {
      // Get first page to extract summary
      final result = await getAssignments(perPage: 1);
      return AssignmentSummary.fromMap(result.summary ?? {});
    } on K1ApiException catch (_) {
      return MockStudentApiData.assignmentSummary;
    }
  }

  @override
  Future<AttendanceSummary> getAttendanceSummary() async {
    try {
      final result = await getAttendance(perPage: 1);
      return AttendanceSummary.fromMap(result.summary ?? {});
    } on K1ApiException catch (_) {
      return MockStudentApiData.attendanceSummary;
    }
  }

  PaginatedResult<Assignment> _parseAssignmentsResponse(dynamic response) {
    final data = response['data'] as List? ?? [];
    final meta = response['meta'] as Map<String, dynamic>? ?? {};

    final assignments = data.map((json) => Assignment.fromJson(json as Map<String, dynamic>)).toList();

    final pagination = PaginationInfo(
      currentPage: meta['pagination']?['current_page'] ?? 1,
      perPage: meta['pagination']?['per_page'] ?? 20,
      total: meta['pagination']?['total'] ?? 0,
      lastPage: meta['pagination']?['last_page'] ?? 1,
      from: meta['pagination']?['from'],
      to: meta['pagination']?['to'],
    );

    return PaginatedResult<Assignment>(
      data: assignments,
      pagination: pagination,
      summary: meta['summary'] as Map<String, dynamic>?,
    );
  }

  PaginatedResult<AttendanceRecord> _parseAttendanceResponse(dynamic response) {
    final data = response['data'] as List? ?? [];
    final meta = response['meta'] as Map<String, dynamic>? ?? {};

    final records = data.map((json) => AttendanceRecord.fromJson(json as Map<String, dynamic>)).toList();

    final pagination = PaginationInfo(
      currentPage: meta['pagination']?['current_page'] ?? 1,
      perPage: meta['pagination']?['per_page'] ?? 30,
      total: meta['pagination']?['total'] ?? 0,
      lastPage: meta['pagination']?['last_page'] ?? 1,
      from: meta['pagination']?['from'],
      to: meta['pagination']?['to'],
    );

    return PaginatedResult<AttendanceRecord>(
      data: records,
      pagination: pagination,
      summary: meta['summary'] as Map<String, dynamic>?,
    );
  }

  List<ClassSchedule> _parseClassesResponse(dynamic response) {
    final data = response['data'] as List? ?? [];
    return data.map((json) => ClassSchedule.fromJson(json as Map<String, dynamic>)).toList();
  }
}

/// In-memory cache for student data.
class StudentRepositoryCache {
  StudentRepositoryCache({this.maxAge = const Duration(minutes: 5)});

  final Duration maxAge;
  final Map<String, _CacheEntry> _cache = {};

  T? get<T>(String key) {
    final entry = _cache[key];
    if (entry == null) return null;
    if (DateTime.now().difference(entry.timestamp) > maxAge) {
      _cache.remove(key);
      return null;
    }
    return entry.data as T?;
  }

  void set<T>(String key, T data) {
    _cache[key] = _CacheEntry(data: data, timestamp: DateTime.now());
  }

  void invalidate(String key) => _cache.remove(key);
  void clear() => _cache.clear();

  String _assignmentsKey({
    int page = 1,
    int perPage = 20,
    String? status,
    int? subjectId,
  }) =>
      'assignments_p${page}_pp${perPage}_s${status ?? ''}_sub${subjectId ?? ''}';

  String _attendanceKey({
    int page = 1,
    int perPage = 30,
    String? status,
    int? subjectId,
  }) =>
      'attendance_p${page}_pp${perPage}_s${status ?? ''}_sub${subjectId ?? ''}';

  String get classesKey => 'classes';
}

/// Cache entry.
class _CacheEntry {
  _CacheEntry({required this.data, required this.timestamp});

  final dynamic data;
  final DateTime timestamp;
}