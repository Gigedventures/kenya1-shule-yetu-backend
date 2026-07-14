/// Messages provider for unified inbox.
library messages_provider;

import 'package:flutter/material.dart';
import '../repositories/message_repository.dart';
import '../../models/message_models.dart';

enum MessagesState { idle, loading, loaded, error, sending }

class MessagesProvider extends ChangeNotifier {
  MessagesProvider({required MessageRepository repository}) : _repository = repository;

  final MessageRepository _repository;

  MessagesState _state = MessagesState.idle;
  List<Thread> _threads = [];
  List<Announcement> _announcements = [];
  InboxSummary? _summary;
  PaginationInfo? _pagination;
  String? _errorMessage;

  // Current thread
  String? _currentThreadId;
  List<Message> _currentMessages = [];
  PaginationInfo? _currentThreadPagination;

  MessagesState get state => _state;
  List<Thread> get threads => _threads;
  List<Announcement> get announcements => _announcements;
  InboxSummary? get summary => _summary;
  PaginationInfo? get pagination => _pagination;
  String? get errorMessage => _errorMessage;
  bool get isLoading => _state == MessagesState.loading;
  bool get isSending => _state == MessagesState.sending;
  bool get hasError => _state == MessagesState.error;
  bool get hasData => _threads.isNotEmpty;
  bool get hasNextPage => _pagination?.hasNextPage ?? false;

  // Current thread
  String? get currentThreadId => _currentThreadId;
  List<Message> get currentMessages => _currentMessages;
  bool get hasCurrentThread => _currentThreadId != null;
  bool get currentThreadHasMore => _currentThreadPagination?.hasNextPage ?? false;

  int get totalUnread => _summary?.unreadCount ?? 0;
  int get unreadAnnouncements => _summary?.announcementCount ?? 0;

  Future<void> loadInbox({bool refresh = false}) async {
    if (refresh) {
      _threads = [];
      _pagination = null;
    }

    _setState(MessagesState.loading);

    try {
      final page = (_pagination?.page ?? 0) + 1;
      final result = await _repository.getInbox(page: page, perPage: 20);

      if (refresh || page == 1) {
        _threads = result.threads;
        _announcements = result.announcements;
      } else {
        _threads.addAll(result.threads);
      }

      _pagination = result.pagination;
      _summary = result.summary;
      _setState(MessagesState.loaded);
    } catch (e) {
      _errorMessage = e.toString();
      _setState(MessagesState.error);
    }
  }

  Future<void> loadThread(String threadId, {bool refresh = false}) async {
    if (refresh) {
      _currentMessages = [];
      _currentThreadPagination = null;
    }

    if (_currentThreadId != threadId) {
      _currentThreadId = threadId;
      _currentMessages = [];
      _currentThreadPagination = null;
    }

    try {
      final page = (_currentThreadPagination?.page ?? 0) + 1;
      final result = await _repository.getThreadMessages(threadId, page: page, perPage: 50);

      if (refresh || page == 1) {
        _currentMessages = result.data;
      } else {
        _currentMessages = [...result.data, ..._currentMessages]; // Prepend older messages
      }

      _currentThreadPagination = result.pagination;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
    }
  }

  Future<void> loadMoreInThread() async {
    if (!currentThreadHasMore || _state == MessagesState.loading) return;
    await loadThread(_currentThreadId!);
  }

  Future<Message> sendMessage(String threadId, String body) async {
    _setState(MessagesState.sending);

    try {
      final message = await _repository.sendMessage(threadId, body);

      // Add to current thread if it's the active one
      if (_currentThreadId == threadId) {
        _currentMessages.add(message);
      }

      // Update thread in list
      final threadIndex = _threads.indexWhere((t) => t.id == threadId);
      if (threadIndex >= 0) {
        final thread = _threads[threadIndex];
        _threads[threadIndex] = Thread(
          id: thread.id,
          participant: thread.participant,
          lastMessage: body,
          lastMessageTime: message.sentAt,
          unreadCount: 0,
          subject: thread.subject,
          isSender: true,
        );
      }

      _setState(MessagesState.loaded);
      return message;
    } catch (e) {
      _errorMessage = e.toString();
      _setState(MessagesState.error);
      rethrow;
    }
  }

  Future<void> markThreadRead(String threadId) async {
    // Mark all messages in thread as read locally
    if (_currentThreadId == threadId) {
      for (final _ in _currentMessages) {
        // Local update - in real app would call API
      }
    }

    // Update thread unread count
    final threadIndex = _threads.indexWhere((t) => t.id == threadId);
    int previousUnread = 0;
    if (threadIndex >= 0) {
      final thread = _threads[threadIndex];
      previousUnread = thread.unreadCount;
      _threads[threadIndex] = Thread(
        id: thread.id,
        participant: thread.participant,
        lastMessage: thread.lastMessage,
        lastMessageTime: thread.lastMessageTime,
        unreadCount: 0,
        subject: thread.subject,
        isSender: thread.isSender,
      );
    }

    // Update summary
    if (_summary != null) {
      _summary = InboxSummary(
        totalThreads: _summary!.totalThreads,
        unreadCount: (_summary!.unreadCount - previousUnread).clamp(0, _summary!.unreadCount),
        urgentCount: _summary!.urgentCount,
        announcementCount: _summary!.announcementCount,
        draftCount: _summary!.draftCount,
      );
    }

    notifyListeners();
  }

  Future<void> refresh() async {
    await loadInbox(refresh: true);
  }

  void clearCurrentThread() {
    _currentThreadId = null;
    _currentMessages = [];
    _currentThreadPagination = null;
    notifyListeners();
  }

  void _setState(MessagesState newState) {
    _state = newState;
    notifyListeners();
  }
}