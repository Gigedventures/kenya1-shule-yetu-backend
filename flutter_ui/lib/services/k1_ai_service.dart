import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/ai_models.dart';

/// K1 AI Engine client service — connects to /v1/shule-yetu/ai/* API endpoints.
class K1AiService {
  K1AiService({
    required this.baseUrl,
    required this.tokenProvider,
  });

  final String baseUrl;
  final Future<String?> Function() tokenProvider;

  String? _cachedToken;

  Future<void> _ensureToken() async {
    _cachedToken ??= await tokenProvider();
    _cachedToken ??= await tokenProvider();
  }

  Map<String, String> get _headers {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (_cachedToken != null) 'Authorization': 'Bearer $_cachedToken',
    };
  }

  http.Client _client() => http.Client();

  /// POST /v1/shule-yetu/ai/students/{student}/predict
  /// Returns predicted student performance with risk level and strength/weakness analysis.
  Future<StudentPerformancePrediction> predictStudentPerformance(
    String studentId,
  ) async {
    await _ensureToken();
    final client = _client();
    try {
      final response = await client.post(
        Uri.parse('$baseUrl/v1/shule-yetu/ai/students/$studentId/predict'),
        headers: _headers,
      );
      _checkResponse(response);
      return StudentPerformancePrediction.fromJson(
        jsonDecode(response.body) as Map<String, dynamic>,
      );
    } finally {
      client.close();
    }
  }

  /// POST /v1/shule-yetu/ai/students/{student}/risk
  /// Returns at-risk detection with risk score, level, reasons, and actions.
  Future<AtRiskResult> detectAtRisk(String studentId) async {
    await _ensureToken();
    final client = _client();
    try {
      final response = await client.post(
        Uri.parse('$baseUrl/v1/shule-yetu/ai/students/$studentId/risk'),
        headers: _headers,
      );
      _checkResponse(response);
      return AtRiskResult.fromJson(
        jsonDecode(response.body) as Map<String, dynamic>,
      );
    } finally {
      client.close();
    }
  }

  /// GET /v1/shule-yetu/ai/students/{student}/competency-gaps
  /// Returns CBC competency gap analysis with interventions.
  Future<CompetencyGapResult> analyzeCompetencyGaps(String studentId) async {
    await _ensureToken();
    final client = _client();
    try {
      final response = await client.get(
        Uri.parse('$baseUrl/v1/shule-yetu/ai/students/$studentId/competency-gaps'),
        headers: _headers,
      );
      _checkResponse(response);
      return CompetencyGapResult.fromJson(
        jsonDecode(response.body) as Map<String, dynamic>,
      );
    } finally {
      client.close();
    }
  }

  /// POST /v1/shule-yetu/ai/students/{student}/learning-plan
  /// Returns a 14-day personalized learning plan with daily schedules.
  Future<LearningPlanResult> generateLearningPlan(String studentId) async {
    await _ensureToken();
    final client = _client();
    try {
      final response = await client.post(
        Uri.parse('$baseUrl/v1/shule-yetu/ai/students/$studentId/learning-plan'),
        headers: _headers,
      );
      _checkResponse(response);
      return LearningPlanResult.fromJson(
        jsonDecode(response.body) as Map<String, dynamic>,
      );
    } finally {
      client.close();
    }
  }

  void _checkResponse(http.Response response) {
    if (response.statusCode >= 400) {
      throw K1AiException(
        'AI API error ${response.statusCode}: ${response.body}',
        statusCode: response.statusCode,
      );
    }
  }
}

class K1AiException implements Exception {
  const K1AiException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;
  @override
  String toString() => 'K1AiException: $message';
}