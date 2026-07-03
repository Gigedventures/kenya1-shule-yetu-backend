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
  void checkResponse(int statusCode, String body) {
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
      checkResponse(response.statusCode, response.body);
      return jsonDecode(response.body);
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
        body: jsonEncode(body),
      );
      checkResponse(response.statusCode, response.body);
      return jsonDecode(response.body);
    } finally {
      client.close();
    }
  }
}

class K1ApiException implements Exception {
  const K1ApiException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;
  @override
  String toString() => 'K1ApiException: $message';
}