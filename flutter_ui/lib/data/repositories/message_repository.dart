/// Message repository for unified inbox.
library message_repository;

import 'dart:async';

import '../../services/k1_api_service.dart';
import '../../models/message_models.dart';
import '../mock_student_api_data.dart';

export '../../models/message_models.dart' show InboxSummary, PaginationInfo;

/// Message repository interface.
abstract class MessageRepository {
  Future<InboxResult> getInbox({int page = 1, int perPage = 20});

  Future<PaginatedResult<Message>> getThreadMessages(String threadId, {int page = 1, int perPage = 50});

  Future<Message> sendMessage(String threadId, String body);

  Future<void> markMessageRead(String messageId);

  Future<List<Contact>> getContacts();

  Future<Thread> createThread(int recipientId, String initialMessage);
}

/// Inbox result with threads and announcements.
class InboxResult {
  const InboxResult({
    required this.threads,
    required this.announcements,
    required this.pagination,
    required this.summary,
  });

  final List<Thread> threads;
  final List<Announcement> announcements;
  final PaginationInfo pagination;
  final InboxSummary summary;
}

/// Paginated result wrapper.
class PaginatedResult<T> {
  const PaginatedResult({
    required this.data,
    required this.pagination,
  });

  final List<T> data;
  final PaginationInfo pagination;

  bool get hasNextPage => pagination.hasNextPage;
  bool get hasPreviousPage => pagination.hasPreviousPage;
}

/// Message repository implementation.
class MessageRepositoryImpl implements MessageRepository {
  MessageRepositoryImpl({required K1ApiService apiService}) : _apiService = apiService;

  final K1ApiService _apiService;

  @override
  Future<InboxResult> getInbox({int page = 1, int perPage = 20}) async {
    try {
      final response = await _apiService.get('/api/v1/messages/inbox?page=$page&per_page=$perPage');
      return _parseInboxResponse(response);
    } on K1ApiException catch (_) {
      return _mockInboxResult();
    }
  }

  InboxResult _mockInboxResult() {
    return InboxResult(
      threads: MockMessageApiData.threads,
      announcements: MockMessageApiData.announcements,
      pagination: PaginationInfo(
        page: 1,
        pageSize: 20,
        totalItems: MockMessageApiData.threads.length,
        totalPages: 1,
      ),
      summary: MockMessageApiData.inboxSummary,
    );
  }

  @override
  Future<PaginatedResult<Message>> getThreadMessages(String threadId, {int page = 1, int perPage = 50}) async {
    final response = await _apiService.get('/api/v1/shule-yetu/communication/threads/$threadId/messages?page=$page&per_page=$perPage');
    return _parseMessagesResponse(response);
  }

  @override
  Future<Message> sendMessage(String threadId, String body) async {
    final response = await _apiService.post('/api/v1/shule-yetu/communication/threads/$threadId/messages', {'body': body});
    return Message.fromJson(response['data'] as Map<String, dynamic>);
  }

  @override
  Future<void> markMessageRead(String messageId) async {
    await _apiService.post('/api/v1/shule-yetu/communication/messages/$messageId/read', {});
  }

  @override
  Future<List<Contact>> getContacts() async {
    final response = await _apiService.get('/api/v1/shule-yetu/communication/contacts');
    final data = response['data'] as List? ?? [];
    return data.map((json) => Contact.fromJson(json as Map<String, dynamic>)).toList();
  }

  @override
  Future<Thread> createThread(int recipientId, String initialMessage) async {
    final response = await _apiService.post('/api/v1/shule-yetu/communication/threads', {
      'recipient_user_id': recipientId,
      'initial_message': initialMessage,
    });
    return Thread.fromJson(response['data'] as Map<String, dynamic>);
  }

  InboxResult _parseInboxResponse(dynamic response) {
    final data = response['data'] as Map<String, dynamic>? ?? {};
    final meta = response['meta'] as Map<String, dynamic>? ?? {};

    final threads = (data['threads'] as List? ?? [])
        .map((json) => Thread.fromJson(json as Map<String, dynamic>))
        .toList();

    final announcements = (data['announcements'] as List? ?? [])
        .map((json) => Announcement.fromJson(json as Map<String, dynamic>))
        .toList();

    final pagination = PaginationInfo(
      page: meta['pagination']?['current_page'] ?? 1,
      pageSize: meta['pagination']?['per_page'] ?? 20,
      totalItems: meta['pagination']?['total'] ?? 0,
      totalPages: meta['pagination']?['last_page'] ?? 1,
    );

    final summary = InboxSummary(
      totalThreads: meta['summary']?['total_threads'] ?? 0,
      unreadCount: meta['summary']?['total_unread'] ?? 0,
      urgentCount: 0,
      announcementCount: meta['summary']?['unread_announcements'] ?? 0,
      draftCount: 0,
    );

    return InboxResult(
      threads: threads,
      announcements: announcements,
      pagination: pagination,
      summary: summary,
    );
  }

  PaginatedResult<Message> _parseMessagesResponse(dynamic response) {
    final data = response['data'] as List? ?? [];
    final meta = response['meta'] as Map<String, dynamic>? ?? {};

    final messages = data.map((json) => Message.fromJson(json as Map<String, dynamic>)).toList();

    final pagination = PaginationInfo(
      page: meta['pagination']?['current_page'] ?? 1,
      pageSize: meta['pagination']?['per_page'] ?? 50,
      totalItems: meta['pagination']?['total'] ?? 0,
      totalPages: meta['pagination']?['last_page'] ?? 1,
    );

    return PaginatedResult<Message>(data: messages, pagination: pagination);
  }
}
