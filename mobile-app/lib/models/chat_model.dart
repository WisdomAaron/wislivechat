import 'package:json_annotation/json_annotation.dart';
import 'user_model.dart';

part 'chat_model.g.dart';

@JsonSerializable()
class ChatSession {
  final String id;
  final String status;
  final String priority;
  final String? subject;
  @JsonKey(name: 'started_at')
  final DateTime startedAt;
  @JsonKey(name: 'ended_at')
  final DateTime? endedAt;
  @JsonKey(name: 'last_activity')
  final DateTime lastActivity;
  final int? rating;
  final String? feedback;
  @JsonKey(name: 'message_count')
  final int messageCount;
  @JsonKey(name: 'unread_count')
  final int unreadCount;
  final Visitor visitor;
  final User? agent;

  ChatSession({
    required this.id,
    required this.status,
    required this.priority,
    this.subject,
    required this.startedAt,
    this.endedAt,
    required this.lastActivity,
    this.rating,
    this.feedback,
    required this.messageCount,
    required this.unreadCount,
    required this.visitor,
    this.agent,
  });

  factory ChatSession.fromJson(Map<String, dynamic> json) =>
      _$ChatSessionFromJson(json);
  Map<String, dynamic> toJson() => _$ChatSessionToJson(this);

  bool get isActive => status == 'active';
  bool get isClosed => status == 'closed';
  bool get isWaiting => status == 'waiting';
  bool get isTransferred => status == 'transferred';
  bool get hasUnreadMessages => unreadCount > 0;
  bool get isAssigned => agent != null;

  Duration get duration {
    final end = endedAt ?? DateTime.now();
    return end.difference(startedAt);
  }

  String get durationText {
    final dur = duration;
    if (dur.inHours > 0) {
      return '${dur.inHours}h ${dur.inMinutes % 60}m';
    } else if (dur.inMinutes > 0) {
      return '${dur.inMinutes}m';
    } else {
      return '${dur.inSeconds}s';
    }
  }

  ChatSession copyWith({
    String? id,
    String? status,
    String? priority,
    String? subject,
    DateTime? startedAt,
    DateTime? endedAt,
    DateTime? lastActivity,
    int? rating,
    String? feedback,
    int? messageCount,
    int? unreadCount,
    Visitor? visitor,
    User? agent,
  }) {
    return ChatSession(
      id: id ?? this.id,
      status: status ?? this.status,
      priority: priority ?? this.priority,
      subject: subject ?? this.subject,
      startedAt: startedAt ?? this.startedAt,
      endedAt: endedAt ?? this.endedAt,
      lastActivity: lastActivity ?? this.lastActivity,
      rating: rating ?? this.rating,
      feedback: feedback ?? this.feedback,
      messageCount: messageCount ?? this.messageCount,
      unreadCount: unreadCount ?? this.unreadCount,
      visitor: visitor ?? this.visitor,
      agent: agent ?? this.agent,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ChatSession && runtimeType == other.runtimeType && id == other.id;

  @override
  int get hashCode => id.hashCode;
}

@JsonSerializable()
class Visitor {
  final String id;
  final String? name;
  final String? email;
  final String? phone;
  @JsonKey(name: 'ip_address')
  final String? ipAddress;
  final String? browser;
  final String? os;
  final String? device;
  final String? country;
  final String? city;
  @JsonKey(name: 'website_url')
  final String? websiteUrl;
  @JsonKey(name: 'referrer_url')
  final String? referrerUrl;
  @JsonKey(name: 'is_blocked')
  final bool isBlocked;
  @JsonKey(name: 'first_visit')
  final DateTime firstVisit;
  @JsonKey(name: 'last_visit')
  final DateTime lastVisit;
  @JsonKey(name: 'visit_count')
  final int visitCount;

  Visitor({
    required this.id,
    this.name,
    this.email,
    this.phone,
    this.ipAddress,
    this.browser,
    this.os,
    this.device,
    this.country,
    this.city,
    this.websiteUrl,
    this.referrerUrl,
    required this.isBlocked,
    required this.firstVisit,
    required this.lastVisit,
    required this.visitCount,
  });

  factory Visitor.fromJson(Map<String, dynamic> json) =>
      _$VisitorFromJson(json);
  Map<String, dynamic> toJson() => _$VisitorToJson(this);

  String get displayName => name ?? email ?? 'Anonymous';
  
  String get location {
    if (city != null && country != null) {
      return '$city, $country';
    } else if (country != null) {
      return country!;
    } else {
      return 'Unknown';
    }
  }

  bool get isReturning => visitCount > 1;
}

@JsonSerializable()
class Message {
  final String id;
  @JsonKey(name: 'chat_session_id')
  final String chatSessionId;
  @JsonKey(name: 'sender_type')
  final String senderType;
  @JsonKey(name: 'sender_id')
  final String? senderId;
  @JsonKey(name: 'sender_name')
  final String? senderName;
  @JsonKey(name: 'message_type')
  final String messageType;
  final String content;
  @JsonKey(name: 'file_url')
  final String? fileUrl;
  @JsonKey(name: 'file_name')
  final String? fileName;
  @JsonKey(name: 'file_size')
  final int? fileSize;
  @JsonKey(name: 'file_type')
  final String? fileType;
  @JsonKey(name: 'is_read')
  final bool isRead;
  @JsonKey(name: 'read_at')
  final DateTime? readAt;
  @JsonKey(name: 'delivered_at')
  final DateTime? deliveredAt;
  @JsonKey(name: 'created_at')
  final DateTime createdAt;

  Message({
    required this.id,
    required this.chatSessionId,
    required this.senderType,
    this.senderId,
    this.senderName,
    required this.messageType,
    required this.content,
    this.fileUrl,
    this.fileName,
    this.fileSize,
    this.fileType,
    required this.isRead,
    this.readAt,
    this.deliveredAt,
    required this.createdAt,
  });

  factory Message.fromJson(Map<String, dynamic> json) =>
      _$MessageFromJson(json);
  Map<String, dynamic> toJson() => _$MessageToJson(this);

  bool get isFromVisitor => senderType == 'visitor';
  bool get isFromAgent => senderType == 'agent';
  bool get isFromSystem => senderType == 'system';
  bool get isTextMessage => messageType == 'text';
  bool get isFileMessage => messageType == 'file';
  bool get isImageMessage => messageType == 'image';
  bool get hasFile => fileUrl != null;

  String get displaySenderName {
    if (senderName != null && senderName!.isNotEmpty) {
      return senderName!;
    }
    switch (senderType) {
      case 'visitor':
        return 'Visitor';
      case 'agent':
        return 'Agent';
      case 'system':
        return 'System';
      default:
        return 'Unknown';
    }
  }

  Message copyWith({
    String? id,
    String? chatSessionId,
    String? senderType,
    String? senderId,
    String? senderName,
    String? messageType,
    String? content,
    String? fileUrl,
    String? fileName,
    int? fileSize,
    String? fileType,
    bool? isRead,
    DateTime? readAt,
    DateTime? deliveredAt,
    DateTime? createdAt,
  }) {
    return Message(
      id: id ?? this.id,
      chatSessionId: chatSessionId ?? this.chatSessionId,
      senderType: senderType ?? this.senderType,
      senderId: senderId ?? this.senderId,
      senderName: senderName ?? this.senderName,
      messageType: messageType ?? this.messageType,
      content: content ?? this.content,
      fileUrl: fileUrl ?? this.fileUrl,
      fileName: fileName ?? this.fileName,
      fileSize: fileSize ?? this.fileSize,
      fileType: fileType ?? this.fileType,
      isRead: isRead ?? this.isRead,
      readAt: readAt ?? this.readAt,
      deliveredAt: deliveredAt ?? this.deliveredAt,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is Message && runtimeType == other.runtimeType && id == other.id;

  @override
  int get hashCode => id.hashCode;
}

@JsonSerializable()
class ChatSessionsResponse {
  final List<ChatSession> sessions;
  final Pagination pagination;

  ChatSessionsResponse({
    required this.sessions,
    required this.pagination,
  });

  factory ChatSessionsResponse.fromJson(Map<String, dynamic> json) =>
      _$ChatSessionsResponseFromJson(json);
  Map<String, dynamic> toJson() => _$ChatSessionsResponseToJson(this);
}

@JsonSerializable()
class MessagesResponse {
  final List<Message> messages;

  MessagesResponse({
    required this.messages,
  });

  factory MessagesResponse.fromJson(Map<String, dynamic> json) =>
      _$MessagesResponseFromJson(json);
  Map<String, dynamic> toJson() => _$MessagesResponseToJson(this);
}

@JsonSerializable()
class Pagination {
  final int total;
  final int limit;
  final int offset;
  @JsonKey(name: 'has_more')
  final bool hasMore;

  Pagination({
    required this.total,
    required this.limit,
    required this.offset,
    required this.hasMore,
  });

  factory Pagination.fromJson(Map<String, dynamic> json) =>
      _$PaginationFromJson(json);
  Map<String, dynamic> toJson() => _$PaginationToJson(this);

  int get currentPage => (offset / limit).floor() + 1;
  int get totalPages => (total / limit).ceil();
}

@JsonSerializable()
class SendMessageRequest {
  final String content;
  @JsonKey(name: 'messageType')
  final String messageType;
  @JsonKey(name: 'fileUrl')
  final String? fileUrl;
  @JsonKey(name: 'fileName')
  final String? fileName;
  @JsonKey(name: 'fileSize')
  final int? fileSize;

  SendMessageRequest({
    required this.content,
    required this.messageType,
    this.fileUrl,
    this.fileName,
    this.fileSize,
  });

  factory SendMessageRequest.fromJson(Map<String, dynamic> json) =>
      _$SendMessageRequestFromJson(json);
  Map<String, dynamic> toJson() => _$SendMessageRequestToJson(this);
}
