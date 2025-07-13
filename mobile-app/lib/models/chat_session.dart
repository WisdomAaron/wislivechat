class ChatSession {
  final String id;
  final String visitorId;
  final String? agentId;
  final String status;
  final String? subject;
  final DateTime startedAt;
  final DateTime? endedAt;
  final DateTime lastActivity;
  final int? rating;
  final String? feedback;
  final Map<String, dynamic> metadata;
  final String? websiteUrl;
  final Map<String, dynamic> visitorInfo;
  final int unreadCount;
  final String? lastMessage;
  final DateTime? lastMessageTime;

  ChatSession({
    required this.id,
    required this.visitorId,
    this.agentId,
    required this.status,
    this.subject,
    required this.startedAt,
    this.endedAt,
    required this.lastActivity,
    this.rating,
    this.feedback,
    this.metadata = const {},
    this.websiteUrl,
    this.visitorInfo = const {},
    this.unreadCount = 0,
    this.lastMessage,
    this.lastMessageTime,
  });

  factory ChatSession.fromJson(Map<String, dynamic> json) {
    return ChatSession(
      id: json['id'],
      visitorId: json['visitor_id'],
      agentId: json['agent_id'],
      status: json['status'],
      subject: json['subject'],
      startedAt: DateTime.parse(json['started_at']),
      endedAt: json['ended_at'] != null ? DateTime.parse(json['ended_at']) : null,
      lastActivity: DateTime.parse(json['last_activity']),
      rating: json['rating'],
      feedback: json['feedback'],
      metadata: json['metadata'] ?? {},
      websiteUrl: json['website_url'],
      visitorInfo: json['visitor_info'] ?? {},
      unreadCount: json['unread_count'] ?? 0,
      lastMessage: json['last_message'],
      lastMessageTime: json['last_message_time'] != null 
          ? DateTime.parse(json['last_message_time']) 
          : null,
    );
  }

  factory ChatSession.fromFirebase(Map<dynamic, dynamic> data) {
    return ChatSession(
      id: data['sessionId'] ?? '',
      visitorId: data['visitorId'] ?? '',
      agentId: data['agentId'],
      status: data['status'] ?? 'active',
      subject: data['subject'],
      startedAt: data['createdAt'] != null 
          ? DateTime.fromMillisecondsSinceEpoch(data['createdAt'])
          : DateTime.now(),
      endedAt: data['endedAt'] != null 
          ? DateTime.fromMillisecondsSinceEpoch(data['endedAt'])
          : null,
      lastActivity: data['lastActivity'] != null 
          ? DateTime.fromMillisecondsSinceEpoch(data['lastActivity'])
          : DateTime.now(),
      rating: data['rating'],
      feedback: data['feedback'],
      metadata: Map<String, dynamic>.from(data['metadata'] ?? {}),
      websiteUrl: data['websiteUrl'],
      visitorInfo: Map<String, dynamic>.from(data['visitorInfo'] ?? {}),
      unreadCount: data['unreadCount'] ?? 0,
      lastMessage: data['lastMessage'],
      lastMessageTime: data['lastMessageTime'] != null 
          ? DateTime.fromMillisecondsSinceEpoch(data['lastMessageTime'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'visitor_id': visitorId,
      'agent_id': agentId,
      'status': status,
      'subject': subject,
      'started_at': startedAt.toIso8601String(),
      'ended_at': endedAt?.toIso8601String(),
      'last_activity': lastActivity.toIso8601String(),
      'rating': rating,
      'feedback': feedback,
      'metadata': metadata,
      'website_url': websiteUrl,
      'visitor_info': visitorInfo,
      'unread_count': unreadCount,
      'last_message': lastMessage,
      'last_message_time': lastMessageTime?.toIso8601String(),
    };
  }

  String get displayName {
    if (visitorInfo['name'] != null && visitorInfo['name'].isNotEmpty) {
      return visitorInfo['name'];
    }
    if (visitorInfo['email'] != null && visitorInfo['email'].isNotEmpty) {
      return visitorInfo['email'];
    }
    return 'Anonymous Visitor';
  }

  String get websiteDomain {
    if (websiteUrl != null) {
      try {
        Uri uri = Uri.parse(websiteUrl!);
        return uri.host;
      } catch (e) {
        return websiteUrl!;
      }
    }
    return 'Unknown Website';
  }

  bool get hasUnreadMessages => unreadCount > 0;

  String get statusDisplayName {
    switch (status.toLowerCase()) {
      case 'active':
        return 'Active';
      case 'waiting':
        return 'Waiting';
      case 'closed':
        return 'Closed';
      case 'transferred':
        return 'Transferred';
      default:
        return status;
    }
  }

  String get lastActivityFormatted {
    final now = DateTime.now();
    final difference = now.difference(lastActivity);

    if (difference.inMinutes < 1) {
      return 'Just now';
    } else if (difference.inMinutes < 60) {
      return '${difference.inMinutes}m ago';
    } else if (difference.inHours < 24) {
      return '${difference.inHours}h ago';
    } else if (difference.inDays < 7) {
      return '${difference.inDays}d ago';
    } else {
      return '${lastActivity.day}/${lastActivity.month}/${lastActivity.year}';
    }
  }

  String get shortLastMessage {
    if (lastMessage == null || lastMessage!.isEmpty) {
      return 'No messages yet';
    }
    
    if (lastMessage!.length > 50) {
      return '${lastMessage!.substring(0, 50)}...';
    }
    
    return lastMessage!;
  }

  ChatSession copyWith({
    String? id,
    String? visitorId,
    String? agentId,
    String? status,
    String? subject,
    DateTime? startedAt,
    DateTime? endedAt,
    DateTime? lastActivity,
    int? rating,
    String? feedback,
    Map<String, dynamic>? metadata,
    String? websiteUrl,
    Map<String, dynamic>? visitorInfo,
    int? unreadCount,
    String? lastMessage,
    DateTime? lastMessageTime,
  }) {
    return ChatSession(
      id: id ?? this.id,
      visitorId: visitorId ?? this.visitorId,
      agentId: agentId ?? this.agentId,
      status: status ?? this.status,
      subject: subject ?? this.subject,
      startedAt: startedAt ?? this.startedAt,
      endedAt: endedAt ?? this.endedAt,
      lastActivity: lastActivity ?? this.lastActivity,
      rating: rating ?? this.rating,
      feedback: feedback ?? this.feedback,
      metadata: metadata ?? this.metadata,
      websiteUrl: websiteUrl ?? this.websiteUrl,
      visitorInfo: visitorInfo ?? this.visitorInfo,
      unreadCount: unreadCount ?? this.unreadCount,
      lastMessage: lastMessage ?? this.lastMessage,
      lastMessageTime: lastMessageTime ?? this.lastMessageTime,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    
    return other is ChatSession &&
        other.id == id &&
        other.visitorId == visitorId &&
        other.status == status &&
        other.lastActivity == lastActivity &&
        other.unreadCount == unreadCount;
  }

  @override
  int get hashCode {
    return id.hashCode ^
        visitorId.hashCode ^
        status.hashCode ^
        lastActivity.hashCode ^
        unreadCount.hashCode;
  }

  @override
  String toString() {
    return 'ChatSession(id: $id, visitorId: $visitorId, status: $status, unreadCount: $unreadCount)';
  }
}
