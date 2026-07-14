/// Kenya1 / Shule Yetu unified API service.
/// Centralizes base URL, auth, and error handling for all Shule Yetu API calls.
///
/// Services that compose this:
///   - cbc_service (CBC curriculum setup)
///   - fee_service (finance, payments, invoices)
///   - transcript_service (academic records)
///   - chat_service (messaging, announcements)
///   - senior_service (senior dashboard)
///
/// Replace individual service constructions with this one where possible.
library k1_api_service;

import 'dart:convert';
import 'package:http/http.dart' as http;

class K1ApiService {
  K1ApiService({
    required this.baseUrl,
    required this.tokenProvider,
  });

  final String baseUrl;
  final Future<String?> Function() tokenProvider;

  /// Default request headers including auth token.
  Map<String, String> get headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_cachedToken != null) 'Authorization': 'Bearer $_cachedToken',
      };

  String? _cachedToken;

  /// Ensure a fresh token is cached.
  Future<void> ensureToken() async {
    _cachedToken ??= await tokenProvider();
  }

  /// Build a full URI for any Shule Yetu API path.
  Uri _uri(String path) => Uri.parse('$baseUrl$path');

  /// Check response for error codes >= 400.
  void _checkResponse(int statusCode, String body) {
    if (statusCode >= 400) {
      throw K1ApiException(
        'API error $statusCode: $body',
        statusCode: statusCode,
      );
    }
  }

  /// Generic GET — returns Map or List from JSON body.
  Future<dynamic> get(String path) async {
    await ensureToken();
    final client = http.Client();
    try {
      final response = await client.get(_uri(path), headers: headers);
      _checkResponse(response.statusCode, response.body);
      return json.decode(response.body);
    } finally {
      client.close();
    }
  }

  /// Generic POST — sends JSON body.
  Future<dynamic> post(String path, Map<String, dynamic> body) async {
    await ensureToken();
    final client = http.Client();
    try {
      final response = await client.post(
        _uri(path),
        headers: headers,
        body: json.encode(body),
      );
      _checkResponse(response.statusCode, response.body);
      return json.decode(response.body);
    } finally {
      client.close();
    }
  }

  /// Generic PUT — sends JSON body.
  Future<dynamic> put(String path, Map<String, dynamic> body) async {
    await ensureToken();
    final client = http.Client();
    try {
      final response = await client.put(
        _uri(path),
        headers: headers,
        body: json.encode(body),
      );
      _checkResponse(response.statusCode, response.body);
      return json.decode(response.body);
    } finally {
      client.close();
    }
  }

  /// Generic DELETE.
  Future<dynamic> delete(String path) async {
    await ensureToken();
    final client = http.Client();
    try {
      final response = await client.delete(_uri(path), headers: headers);
      _checkResponse(response.statusCode, response.body);
      return json.decode(response.body);
    } finally {
      client.close();
    }
  }

  // ==================== STUDENT API ====================

  /// Get student assignments with pagination and filters.
  Future<Map<String, dynamic>> getStudentAssignments({
    int page = 1,
    int perPage = 20,
    String? status,
    int? subjectId,
    String? examType,
    DateTime? dateFrom,
    DateTime? dateTo,
  }) async {
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
    return await get('/api/v1/student/assignments?$queryString');
  }

  /// Get student attendance with pagination and filters.
  Future<Map<String, dynamic>> getStudentAttendance({
    int page = 1,
    int perPage = 30,
    String? status,
    int? subjectId,
    DateTime? dateFrom,
    DateTime? dateTo,
  }) async {
    final queryParams = <String, String>{
      'page': page.toString(),
      'per_page': perPage.toString(),
    };
    if (status != null) queryParams['status'] = status;
    if (subjectId != null) queryParams['subject_id'] = subjectId.toString();
    if (dateFrom != null) queryParams['date_from'] = dateFrom.toIso8601String().split('T').first;
    if (dateTo != null) queryParams['date_to'] = dateTo.toIso8601String().split('T').first;

    final queryString = queryParams.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&');
    return await get('/api/v1/student/attendance?$queryString');
  }

  /// Get student class schedule.
  Future<Map<String, dynamic>> getStudentClasses() async {
    return await get('/api/v1/student/classes');
  }

  // ==================== TEACHER API ====================

  /// Get teacher assignments with pagination and filters.
  Future<Map<String, dynamic>> getTeacherAssignments({
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
    return await get('/api/v1/teacher/assignments?$queryString');
  }

  /// Get teacher attendance with pagination and filters.
  Future<Map<String, dynamic>> getTeacherAttendance({
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
    return await get('/api/v1/teacher/attendance?$queryString');
  }

  /// Mark attendance for multiple students.
  Future<Map<String, dynamic>> markTeacherAttendance(List<Map<String, dynamic>> records) async {
    return await post('/api/v1/teacher/attendance', {'records': records});
  }

  /// Get teacher attendance stats.
  Future<Map<String, dynamic>> getTeacherAttendanceStats() async {
    return await get('/api/v1/teacher/attendance/stats');
  }

  // ==================== MESSAGES API ====================

  /// Get unified inbox (threads + announcements).
  Future<Map<String, dynamic>> getInbox({int page = 1, int perPage = 20}) async {
    return await get('/api/v1/messages/inbox?page=$page&per_page=$perPage');
  }

  /// Get messages for a thread.
  Future<Map<String, dynamic>> getThreadMessages(String threadId, {int page = 1, int perPage = 50}) async {
    return await get('/api/v1/shule-yetu/communication/threads/$threadId/messages?page=$page&per_page=$perPage');
  }

  /// Send message in thread.
  Future<Map<String, dynamic>> sendMessage(String threadId, String body) async {
    return await post('/api/v1/shule-yetu/communication/threads/$threadId/messages', {'body': body});
  }

  /// Mark message as read.
  Future<Map<String, dynamic>> markMessageRead(String messageId) async {
    return await post('/api/v1/shule-yetu/communication/messages/$messageId/read', {});
  }

  /// Get contacts for new thread.
  Future<Map<String, dynamic>> getContacts() async {
    return await get('/api/v1/shule-yetu/communication/contacts');
  }

  /// Create new thread.
  Future<Map<String, dynamic>> createThread(int recipientId, String initialMessage) async {
    return await post('/api/v1/shule-yetu/communication/threads', {
      'recipient_user_id': recipientId,
      'initial_message': initialMessage,
    });
  }
}

class K1ApiException implements Exception {
  const K1ApiException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;

  @override
  String toString() => 'K1ApiException: $message';
}