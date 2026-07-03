import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/cbc_models.dart';

/// Service for CBC (Competency-Based Curriculum) data.
/// Connects to /v1/shule-yetu/* backend endpoints.
class CbcService {
  final String baseUrl;
  final Future<String?> Function() tokenProvider;
  final http.Client? client;

  const CbcService({
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

  /// POST /v1/shule-yetu/setup-cbc-full
  /// Sets up the CBC, JSS, and Senior Secondary classes/streams/subjects for a school.
  Future<CbcSetup> setupCbcFull() async {
    final token = await tokenProvider();
    final response = await _http.post(
      Uri.parse('$baseUrl/v1/shule-yetu/setup-cbc-full'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    return CbcSetup.fromJson(jsonDecode(response.body));
  }

  /// GET /v1/shule-yetu/cbc/classes
  /// Returns all CBC classes and streams for the school.
  Future<List<CbcClass>> getCbcClasses() async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/cbc/classes'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    final data = jsonDecode(response.body) as List;
    return data.map((e) => CbcClass.fromJson(e as Map<String, dynamic>)).toList();
  }

  /// GET /v1/shule-yetu/cbc/subjects
  /// Returns all CBC subjects for the school.
  Future<List<CbcSubject>> getCbcSubjects() async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/cbc/subjects'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    final data = jsonDecode(response.body) as List;
    return data.map((e) => CbcSubject.fromJson(e as Map<String, dynamic>)).toList();
  }

  void _checkResponse(http.Response response) {
    if (response.statusCode >= 400) {
      throw CbcServiceException(
        'API error ${response.statusCode}: ${response.body}',
        statusCode: response.statusCode,
      );
    }
  }
}

class CbcServiceException implements Exception {
  const CbcServiceException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;
  @override
  String toString() => 'CbcServiceException: $message';
}