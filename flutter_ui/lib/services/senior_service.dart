import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/senior_models.dart';

/// Service for senior secondary (Grade 10-12 / Senior) dashboard data.
/// Aggregates data from multiple backend APIs into a single dashboard.
class SeniorService {
  final String baseUrl;
  final Future<String?> Function() tokenProvider;
  final http.Client? client;

  const SeniorService({
    required this.baseUrl,
    required this.tokenProvider,
    this.client,
  });

  http.Client get _http => client ?? http.Client();

  Map<String, String> _authHeaders(String? token) => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  /// GET /v1/shule-yetu/senior/dashboard
  /// Returns the full senior dashboard data for a student.
  Future<SeniorDashboardData> getSeniorDashboard(String studentId) async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/senior/dashboard/$studentId'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    return SeniorDashboardData.fromJson(jsonDecode(response.body));
  }

  void _checkResponse(http.Response response) {
    if (response.statusCode >= 400) {
      throw SeniorServiceException(
        'API error ${response.statusCode}: ${response.body}',
        statusCode: response.statusCode,
      );
    }
  }
}

class SeniorServiceException implements Exception {
  const SeniorServiceException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;
  @override
  String toString() => 'SeniorServiceException: $message';
}