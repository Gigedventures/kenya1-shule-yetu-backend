import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/chat_models.dart';

class ChatService {
  ChatService({
    required this.baseUrl,
    required this.tokenProvider,
    this.client,
  });

  final String baseUrl;
  final Future<String?> Function() tokenProvider;
  final http.Client? client;

  http.Client get _http => client ?? http.Client();

  /// GET /v1/shule-yetu/communication/threads
  /// Returns list of chat threads for the current user (parent/guardian)
  Future<List<ChatThread>> getChatThreads() async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/communication/threads'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    final data = jsonDecode(response.body) as List;
    return data.map((e) => ChatThread.fromJson(e)).toList();
  }

  /// GET /v1/shule-yetu/communication/threads/{threadId}/messages
  /// Returns messages for a specific thread
  Future<List<ChatMessage>> getThreadMessages(String threadId) async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/communication/threads/$threadId/messages'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    final data = jsonDecode(response.body) as List;
    return data.map((e) => ChatMessage.fromJson(e)).toList();
  }

  /// POST /v1/shule-yetu/communication/threads/{threadId}/messages
  /// Sends a message in a thread
  Future<ChatMessage> sendMessage({
    required String threadId,
    required String body,
  }) async {
    final token = await tokenProvider();
    final response = await _http.post(
      Uri.parse('$baseUrl/v1/shule-yetu/communication/threads/$threadId/messages'),
      headers: _authHeaders(token),
      body: jsonEncode({'body': body}),
    );
    _checkResponse(response);
    return ChatMessage.fromJson(jsonDecode(response.body));
  }

  /// POST /v1/shule-yetu/communication/messages/{messageId}/read
  /// Marks a message as read
  Future<void> markMessageRead(String messageId) async {
    final token = await tokenProvider();
    final response = await _http.post(
      Uri.parse('$baseUrl/v1/shule-yetu/communication/messages/$messageId/read'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
  }

  /// GET /v1/shule-yetu/communication/announcements
  /// Returns announcements for the current user's audience (parent)
  Future<List<Announcement>> getAnnouncements() async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/communication/announcements'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    final data = jsonDecode(response.body) as List;
    return data.map((e) => Announcement.fromJson(e)).toList();
  }

  /// GET /v1/shule-yetu/communication/contacts
  /// Returns available contacts for starting new chats (teachers, staff)
  Future<List<ChatContact>> getContacts() async {
    final token = await tokenProvider();
    final response = await _http.get(
      Uri.parse('$baseUrl/v1/shule-yetu/communication/contacts'),
      headers: _authHeaders(token),
    );
    _checkResponse(response);
    final data = jsonDecode(response.body) as List;
    return data.map((e) => ChatContact.fromJson(e)).toList();
  }

  /// POST /v1/shule-yetu/communication/threads
  /// Creates a new chat thread with a contact
  Future<ChatThread> createThread({
    required String recipientUserId,
    required String initialMessage,
  }) async {
    final token = await tokenProvider();
    final response = await _http.post(
      Uri.parse('$baseUrl/v1/shule-yetu/communication/threads'),
      headers: _authHeaders(token),
      body: jsonEncode({
        'recipient_user_id': recipientUserId,
        'initial_message': initialMessage,
      }),
    );
    _checkResponse(response);
    return ChatThread.fromJson(jsonDecode(response.body));
  }

  Map<String, String> _authHeaders(String? token) => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  void _checkResponse(http.Response response) {
    if (response.statusCode >= 400) {
      throw ChatServiceException(
        'API error ${response.statusCode}: ${response.body}',
        statusCode: response.statusCode,
      );
    }
  }
}

class ChatContact {
  const ChatContact({
    required this.id,
    required this.name,
    required this.role,
    required this.isOnline,
    this.avatarUrl,
  });

  final String id;
  final String name;
  final String role;
  final bool isOnline;
  final String? avatarUrl;

  factory ChatContact.fromJson(Map<String, dynamic> json) {
    return ChatContact(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      role: json['role']?.toString() ?? '',
      isOnline: (json['is_online'] as bool?) ?? false,
      avatarUrl: json['avatar_url']?.toString(),
    );
  }
}

class ChatServiceException implements Exception {
  const ChatServiceException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;
  @override
  String toString() => 'ChatServiceException: $message';
}