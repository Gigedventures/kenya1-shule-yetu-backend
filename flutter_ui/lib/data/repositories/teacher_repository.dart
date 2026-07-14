/// Teacher data repository.
library teacher_repository;

import 'dart:async';

import '../../services/k1_api_service.dart';
import '../../models/teacher_models.dart';

/// Teacher repository interface.
abstract class TeacherRepository {
  Future<PaginatedResult<TeacherAssignment>> getAssignments({
    int page = 1,
    int perPage = 20,
    int? classId,
    int? subjectId,
    String? status,
    String? examType,
  });

  Future<PaginatedResult<AttendanceRecord>> getAttendance({
    int page = 1,
    int perPage = 50,
    DateTime? date,
    DateTime? dateFrom,
    DateTime? dateTo,
    int? classId,
    int? studentId,
    String? status,
    int? subjectId,
  });

  Future<MarkAttendanceResult> markAttendance(List<AttendanceMarkRequest> records);

  Future<List<AttendanceStats>> getAttendanceStats();

  Future<TeacherAssignmentSummary> getAssignmentSummary();
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

/// Mark attendance result.
class MarkAttendanceResult {
  const MarkAttendanceResult({
    required this.created,
    required this.updated,
    required this.total,
  });

  final int created;
  final int updated;
  final int total;
}

/// Attendance mark request.
class AttendanceMarkRequest {
  const AttendanceMarkRequest({
    required this.studentId,
    required this.classId,
    this.streamId,
    this.subjectId,
    required this.attendanceDate,
    required this.status,
    this.checkInTime,
    this.checkOutTime,
    this.notes,
  });

  final int studentId;
  final int classId;
  final int? streamId;
  final int? subjectId;
  final DateTime attendanceDate;
  final String status; // present, absent, late, excused
  final String? checkInTime; // HH:mm
  final String? checkOutTime; // HH:mm
  final String? notes;

  Map<String, dynamic> toJson() => {
        'student_id': studentId,
        'class_id': classId,
        if (streamId != null) 'stream_id': streamId,
        if (subjectId != null) 'subject_id': subjectId,
        'attendance_date': attendanceDate.toIso8601String().split('T').first,
        'status': status,
        if (checkInTime != null) 'check_in_time': checkInTime,
        if (checkOutTime != null) 'check_out_time': checkOutTime,
        if (notes != null) 'notes': notes,
      };
}

/// Teacher repository implementation.
class TeacherRepositoryImpl implements TeacherRepository {
  TeacherRepositoryImpl({required K1ApiService apiService}) : _apiService = apiService;

  final K1ApiService _apiService;

  @override
  Future<PaginatedResult<TeacherAssignment>> getAssignments({
    int page = 1,
    int perPage = 20,
    int? classId,
    int? subjectId,
    String? status,
    String? examType,
  }) async {
    final queryParams = <String, String>{
      'page': page.toString(),
      'per_page': perPage.toString(),
    };

    if (classId != null) queryParams['class_id'] = classId.toString();
    if (subjectId != null) queryParams['subject_id'] = subjectId.toString();
    if (status != null) queryParams['status'] = status;
    if (examType != null) queryParams['exam_type'] = examType;

    final queryString = queryParams.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&');
    final response = await _apiService.get('/api/v1/teacher/assignments?$queryString');

    return _parseAssignmentsResponse(response);
  }

  @override
  Future<PaginatedResult<AttendanceRecord>> getAttendance({
    int page = 1,
    int perPage = 50,
    DateTime? date,
    DateTime? dateFrom,
    DateTime? dateTo,
    int? classId,
    int? studentId,
    String? status,
    int? subjectId,
  }) async {
    final queryParams = <String, String>{
      'page': page.toString(),
      'per_page': perPage.toString(),
    };

    if (date != null) queryParams['date'] = date.toIso8601String().split('T').first;
    if (dateFrom != null) queryParams['date_from'] = dateFrom.toIso8601String().split('T').first;
    if (dateTo != null) queryParams['date_to'] = dateTo.toIso8601String().split('T').first;
    if (classId != null) queryParams['class_id'] = classId.toString();
    if (studentId != null) queryParams['student_id'] = studentId.toString();
    if (status != null) queryParams['status'] = status;
    if (subjectId != null) queryParams['subject_id'] = subjectId.toString();

    final queryString = queryParams.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&');
    final response = await _apiService.get('/api/v1/teacher/attendance?$queryString');

    return _parseAttendanceResponse(response);
  }

  @override
  Future<MarkAttendanceResult> markAttendance(List<AttendanceMarkRequest> records) async {
    final response = await _apiService.post('/api/v1/teacher/attendance', {
      'records': records.map((r) => r.toJson()).toList(),
    });

    return MarkAttendanceResult(
      created: response['data']['created'] ?? 0,
      updated: response['data']['updated'] ?? 0,
      total: response['data']['total'] ?? 0,
    );
  }

  @override
  Future<List<AttendanceStats>> getAttendanceStats() async {
    final response = await _apiService.get('/api/v1/teacher/attendance/stats');
    final data = response['data'] as List? ?? [];
    return data.map((json) => AttendanceStats.fromJson(json as Map<String, dynamic>)).toList();
  }

  @override
  Future<TeacherAssignmentSummary> getAssignmentSummary() async {
    final result = await getAssignments(perPage: 1);
    return TeacherAssignmentSummary.fromMap(result.summary ?? {});
  }

  PaginatedResult<TeacherAssignment> _parseAssignmentsResponse(dynamic response) {
    final data = response['data'] as List? ?? [];
    final meta = response['meta'] as Map<String, dynamic>? ?? {};

    final assignments = data.map((json) => TeacherAssignment.fromJson(json as Map<String, dynamic>)).toList();

    final pagination = PaginationInfo(
      currentPage: meta['pagination']?['current_page'] ?? 1,
      perPage: meta['pagination']?['per_page'] ?? 20,
      total: meta['pagination']?['total'] ?? 0,
      lastPage: meta['pagination']?['last_page'] ?? 1,
      from: meta['pagination']?['from'],
      to: meta['pagination']?['to'],
    );

    return PaginatedResult<TeacherAssignment>(
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
      perPage: meta['pagination']?['per_page'] ?? 50,
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
}