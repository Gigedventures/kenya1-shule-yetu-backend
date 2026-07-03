import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/fee_models.dart';

class FeeService {
  FeeService({
    required this.baseUrl,
    required this.tokenProvider,
    this.client,
  });

  final String baseUrl;
  final Future<String?> Function() tokenProvider;
  final http.Client? client;

  http.Client get _http => client ?? http.Client();

  /// GET /v1/shule-yetu/finance/students/{student}/statement
  /// Returns student's fee statement with bills, payments, and summary
  Future<StudentStatement> getStudentStatement(String studentId) async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/finance/students/$studentId/statement'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    return StudentStatement.fromJson(jsonDecode(response.body));
  }

  /// POST /v1/shule-yetu/finance/students/{student}/payments
  /// Records a payment for a student
  Future<Payment> recordPayment({
    required String studentId,
    required double amount,
    required String paymentMethod,
    String? reference,
    String? idempotencyKey,
  }) async {
    final token = await tokenProvider();
    final response = await _http.post(
      Uri.parse('$baseUrl/v1/shule-yetu/finance/students/$studentId/payments'),
      headers: _authHeaders(token),
      body: jsonEncode({
        'amount': amount,
        'payment_method': paymentMethod,
        if (reference != null) 'reference': reference,
        if (idempotencyKey != null) 'idempotency_key': idempotencyKey,
      }),
    );
    _checkResponse(response);
    return Payment.fromJson(jsonDecode(response.body));
  }

  /// GET /v1/shule-yetu/finance/structures
  /// Returns list of fee structures
  Future<List<FeeStructure>> getFeeStructures() async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/finance/structures'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    final data = jsonDecode(response.body) as List;
    return data.map((e) => FeeStructure.fromJson(e)).toList();
  }

  Map<String, String> _authHeaders(String? token) => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  void _checkResponse(http.Response response) {
    if (response.statusCode >= 400) {
      throw FeeServiceException(
        'API error ${response.statusCode}: ${response.body}',
        statusCode: response.statusCode,
      );
    }
  }
}

class FeeServiceException implements Exception {
  const FeeServiceException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;
  @override
  String toString() => 'FeeServiceException: $message';
}