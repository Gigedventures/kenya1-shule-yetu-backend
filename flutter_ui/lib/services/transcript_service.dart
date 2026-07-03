import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/transcript_models.dart';

class TranscriptService {
  TranscriptService({
    required this.baseUrl,
    required this.tokenProvider,
    this.client,
  });

  final String baseUrl;
  final Future<String?> Function() tokenProvider;
  final http.Client? client;

  http.Client get _http => client ?? http.Client();

  /// GET /v1/shule-yetu/transcripts/students/{student}/transcript
  /// Returns the student's full academic transcript
  Future<AcademicTranscript> getStudentTranscript(String studentId) async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/transcripts/students/$studentId/transcript'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    return AcademicTranscript.fromJson(jsonDecode(response.body));
  }

  Map<String, String> _authHeaders(String? token) => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  void _checkResponse(http.Response response) {
    if (response.statusCode >= 400) {
      throw TranscriptServiceException(
        'API error ${response.statusCode}: ${response.body}',
        statusCode: response.statusCode,
      );
    }
  }
}

class TranscriptServiceException implements Exception {
  const TranscriptServiceException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;
  @override
  String toString() => 'TranscriptServiceException: $message';
}