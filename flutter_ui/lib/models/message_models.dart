/// Message and communication models.
library message_models;

import 'package:flutter/material.dart';

/// Message thread for inbox.
class Thread {
  const Thread({
    required this.id,
    required this.participant,
    required this.lastMessage,
    required this.lastMessageTime,
    required this.unreadCount,
    this.subject,
    this.isSender = false,
  });

  final String id;
  final Participant participant;
  final String lastMessage;
  final DateTime lastMessageTime;
  final int unreadCount;
  final String? subject;
  final bool isSender;

  factory Thread.fromJson(Map<String, dynamic> json) {
    return Thread(
      id: json['id'] as String? ?? '',
      participant: Participant.fromJson(json['participant'] as Map<String, dynamic>? ?? {}),
      lastMessage: json['last_message'] as String? ?? '',
      lastMessageTime: DateTime.tryParse(json['last_message_time'] as String? ?? '') ?? DateTime.now(),
      unreadCount: json['unread_count'] as int? ?? 0,
      subject: json['subject'] as String?,
      isSender: json['is_sender'] as bool? ?? false,
    );
  }

  String get formattedTime {
    final now = DateTime.now();
    final diff = now.difference(lastMessageTime);
    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inHours < 1) return '${diff.inMinutes}m';
    if (diff.inDays < 1) return '${diff.inHours}h';
    if (diff.inDays < 7) return '${diff.inDays}d';
    return '${lastMessageTime.day}/${lastMessageTime.month}/${lastMessageTime.year}';
  }
}

/// Thread participant.
class Participant {
  const Participant({
    required this.id,
    required this.name,
    required this.role,
    this.avatarUrl,
    this.isOnline = false,
  });

  final int id;
  final String name;
  final String role;
  final String? avatarUrl;
  final bool isOnline;

  factory Participant.fromJson(Map<String, dynamic> json) {
    return Participant(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? 'Unknown',
      role: json['role'] as String? ?? 'User',
      avatarUrl: json['avatar_url'] as String?,
      isOnline: json['is_online'] as bool? ?? false,
    );
  }
}

/// Individual message in thread.
class Message {
  const Message({
    required this.id,
    required this.threadId,
    required this.senderId,
    required this.body,
    required this.sentAt,
    this.readAt,
    this.sender,
  });

  final String id;
  final String threadId;
  final int senderId;
  final String body;
  final DateTime sentAt;
  final DateTime? readAt;
  final Participant? sender;

  factory Message.fromJson(Map<String, dynamic> json) {
    return Message(
      id: json['id'] as String? ?? '',
      threadId: json['thread_id'] as String? ?? '',
      senderId: json['sender_user_id'] as int? ?? 0,
      body: json['body'] as String? ?? '',
      sentAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
      readAt: json['read_at'] != null ? DateTime.tryParse(json['read_at'] as String) : null,
      sender: json['sender'] != null ? Participant.fromJson(json['sender'] as Map<String, dynamic>) : null,
    );
  }

  bool get isRead => readAt != null;
  String get formattedTime {
    final now = DateTime.now();
    final diff = now.difference(sentAt);
    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inHours < 1) return '${diff.inMinutes}m';
    if (diff.inDays < 1) return '${diff.inHours}h';
    return '${sentAt.day}/${sentAt.month}/${sentAt.year}';
  }
}

/// Contact for starting new threads.
class Contact {
  const Contact({
    required this.id,
    required this.name,
    required this.role,
    this.avatarUrl,
    this.isOnline = false,
  });

  final int id;
  final String name;
  final String role;
  final String? avatarUrl;
  final bool isOnline;

  factory Contact.fromJson(Map<String, dynamic> json) {
    return Contact(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      role: json['role'] as String? ?? 'Staff',
      avatarUrl: json['avatar_url'] as String?,
      isOnline: json['is_online'] as bool? ?? false,
    );
  }
}

/// Announcement model.
class Announcement {
  const Announcement({
    required this.id,
    required this.title,
    required this.body,
    required this.time,
    this.priority = 'normal',
    this.isRead = false,
  });

  final String id;
  final String title;
  final String body;
  final DateTime time;
  final String priority; // normal, high, urgent
  final bool isRead;

  factory Announcement.fromJson(Map<String, dynamic> json) {
    return Announcement(
      id: json['id'] as String? ?? '',
      title: json['title'] as String? ?? '',
      body: json['body'] as String? ?? '',
      time: DateTime.tryParse(json['time'] as String? ?? '') ?? DateTime.now(),
      priority: json['priority'] as String? ?? 'normal',
      isRead: json['is_read'] as bool? ?? false,
    );
  }

  Color get priorityColor {
    switch (priority) {
      case 'urgent':
        return const Color(0xFFEF4444);
      case 'high':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF3B82F6);
    }
  }
}

/// Inbox summary.
class InboxSummary {
  const InboxSummary({
    required this.totalThreads,
    required this.unreadCount,
    required this.urgentCount,
    required this.announcementCount,
    required this.draftCount,
  });

  final int totalThreads;
  final int unreadCount;
  final int urgentCount;
  final int announcementCount;
  final int draftCount;

  factory InboxSummary.fromJson(Map<String, dynamic> json) {
    return InboxSummary(
      totalThreads: json['total_threads'] as int? ?? 0,
      unreadCount: json['unread_count'] as int? ?? 0,
      urgentCount: json['urgent_count'] as int? ?? 0,
      announcementCount: json['announcement_count'] as int? ?? 0,
      draftCount: json['draft_count'] as int? ?? 0,
    );
  }
}

/// Pagination info for paginated endpoints.
class PaginationInfo {
  const PaginationInfo({
    required this.page,
    required this.pageSize,
    required this.totalItems,
    required this.totalPages,
  });

  final int page;
  final int pageSize;
  final int totalItems;
  final int totalPages;

  factory PaginationInfo.fromJson(Map<String, dynamic> json) {
    return PaginationInfo(
      page: json['page'] as int? ?? 1,
      pageSize: json['page_size'] as int? ?? 20,
      totalItems: json['total_items'] as int? ?? 0,
      totalPages: json['total_pages'] as int? ?? 1,
    );
  }

  bool get hasNextPage => page < totalPages;
  bool get hasPreviousPage => page > 1;
}