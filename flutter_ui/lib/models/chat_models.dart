class ChatThread {
  const ChatThread({
    required this.id,
    required this.title,
    required this.participantName,
    required this.participantRole,
    required this.lastMessage,
    required this.lastMessageTime,
    required this.unreadCount,
    required this.isOnline,
    this.participantAvatarUrl,
  });

  final String id;
  final String title;
  final String participantName;
  final String participantRole;
  final String lastMessage;
  final DateTime lastMessageTime;
  final int unreadCount;
  final bool isOnline;
  final String? participantAvatarUrl;

  factory ChatThread.fromJson(Map<String, dynamic> json) {
    return ChatThread(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      participantName: json['participant_name']?.toString() ?? '',
      participantRole: json['participant_role']?.toString() ?? '',
      lastMessage: json['last_message']?.toString() ?? '',
      lastMessageTime: DateTime.tryParse(json['last_message_time']?.toString() ?? '') ?? DateTime.now(),
      unreadCount: (json['unread_count'] as num?)?.toInt() ?? 0,
      isOnline: (json['is_online'] as bool?) ?? false,
      participantAvatarUrl: json['participant_avatar_url']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'participant_name': participantName,
        'participant_role': participantRole,
        'last_message': lastMessage,
        'last_message_time': lastMessageTime.toIso8601String(),
        'unread_count': unreadCount,
        'is_online': isOnline,
        'participant_avatar_url': participantAvatarUrl,
      };
}

class ChatMessage {
  const ChatMessage({
    required this.id,
    required this.threadId,
    required this.senderId,
    required this.senderName,
    required this.senderRole,
    required this.body,
    required this.sentAt,
    required this.isRead,
    this.isFromCurrentUser = false,
    this.senderAvatarUrl,
  });

  final String id;
  final String threadId;
  final String senderId;
  final String senderName;
  final String senderRole;
  final String body;
  final DateTime sentAt;
  final bool isRead;
  final bool isFromCurrentUser;
  final String? senderAvatarUrl;

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id']?.toString() ?? '',
      threadId: json['thread_id']?.toString() ?? '',
      senderId: json['sender_id']?.toString() ?? '',
      senderName: json['sender_name']?.toString() ?? '',
      senderRole: json['sender_role']?.toString() ?? '',
      body: json['body']?.toString() ?? '',
      sentAt: DateTime.tryParse(json['sent_at']?.toString() ?? '') ?? DateTime.now(),
      isRead: (json['is_read'] as bool?) ?? false,
      isFromCurrentUser: (json['is_from_current_user'] as bool?) ?? false,
      senderAvatarUrl: json['sender_avatar_url']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'thread_id': threadId,
        'sender_id': senderId,
        'sender_name': senderName,
        'sender_role': senderRole,
        'body': body,
        'sent_at': sentAt.toIso8601String(),
        'is_read': isRead,
        'is_from_current_user': isFromCurrentUser,
        'sender_avatar_url': senderAvatarUrl,
      };
}

class Announcement {
  const Announcement({
    required this.id,
    required this.title,
    required this.body,
    required this.audience,
    required this.publishedAt,
    this.authorName,
  });

  final String id;
  final String title;
  final String body;
  final String audience;
  final DateTime publishedAt;
  final String? authorName;

  factory Announcement.fromJson(Map<String, dynamic> json) {
    return Announcement(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      body: json['body']?.toString() ?? '',
      audience: json['audience']?.toString() ?? '',
      publishedAt: DateTime.tryParse(json['published_at']?.toString() ?? '') ?? DateTime.now(),
      authorName: json['author_name']?.toString(),
    );
  }
}