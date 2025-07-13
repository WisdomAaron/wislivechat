class Message {
  final String id;
  final String sessionId;
  final String senderId;
  final String senderType; // 'visitor' or 'admin'
  final String senderName;
  final String content;
  final DateTime timestamp;
  final bool isRead;
  final String? messageType; // 'text', 'image', 'file', etc.
  final Map<String, dynamic>? metadata;

  Message({
    required this.id,
    required this.sessionId,
    required this.senderId,
    required this.senderType,
    required this.senderName,
    required this.content,
    required this.timestamp,
    this.isRead = false,
    this.messageType = 'text',
    this.metadata,
  });

  factory Message.fromJson(Map<String, dynamic> json) {
    return Message(
      id: json['id'],
      sessionId: json['session_id'],
      senderId: json['sender_id'],
      senderType: json['sender_type'],
      senderName: json['sender_name'] ?? 'Unknown',
      content: json['content'],
      timestamp: DateTime.parse(json['timestamp']),
      isRead: json['is_read'] ?? false,
      messageType: json['message_type'] ?? 'text',
      metadata: json['metadata'],
    );
  }

  factory Message.fromFirebase(Map<dynamic, dynamic> data) {
    return Message(
      id: data['id'] ?? '',
      sessionId: data['sessionId'] ?? '',
      senderId: data['senderId'] ?? '',
      senderType: data['senderType'] ?? 'visitor',
      senderName: data['senderName'] ?? 'Unknown',
      content: data['message'] ?? '',
      timestamp: data['timestamp'] != null 
          ? DateTime.fromMillisecondsSinceEpoch(data['timestamp'])
          : DateTime.now(),
      isRead: data['read'] ?? false,
      messageType: data['messageType'] ?? 'text',
      metadata: data['metadata'] != null 
          ? Map<String, dynamic>.from(data['metadata'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'session_id': sessionId,
      'sender_id': senderId,
      'sender_type': senderType,
      'sender_name': senderName,
      'content': content,
      'timestamp': timestamp.toIso8601String(),
      'is_read': isRead,
      'message_type': messageType,
      'metadata': metadata,
    };
  }

  Map<String, dynamic> toFirebase() {
    return {
      'id': id,
      'sessionId': sessionId,
      'senderId': senderId,
      'senderType': senderType,
      'senderName': senderName,
      'message': content,
      'timestamp': timestamp.millisecondsSinceEpoch,
      'read': isRead,
      'messageType': messageType,
      'metadata': metadata,
    };
  }

  bool get isFromVisitor => senderType == 'visitor';
  bool get isFromAdmin => senderType == 'admin';

  String get timeFormatted {
    final now = DateTime.now();
    final difference = now.difference(timestamp);

    if (difference.inMinutes < 1) {
      return 'Just now';
    } else if (difference.inMinutes < 60) {
      return '${difference.inMinutes}m ago';
    } else if (difference.inHours < 24) {
      return '${difference.inHours}h ago';
    } else if (difference.inDays == 1) {
      return 'Yesterday';
    } else if (difference.inDays < 7) {
      return '${difference.inDays} days ago';
    } else {
      return '${timestamp.day}/${timestamp.month}/${timestamp.year}';
    }
  }

  String get timeShort {
    return '${timestamp.hour.toString().padLeft(2, '0')}:${timestamp.minute.toString().padLeft(2, '0')}';
  }

  bool get isToday {
    final now = DateTime.now();
    return timestamp.year == now.year &&
           timestamp.month == now.month &&
           timestamp.day == now.day;
  }

  bool get isYesterday {
    final yesterday = DateTime.now().subtract(const Duration(days: 1));
    return timestamp.year == yesterday.year &&
           timestamp.month == yesterday.month &&
           timestamp.day == yesterday.day;
  }

  String get displayTime {
    if (isToday) {
      return timeShort;
    } else if (isYesterday) {
      return 'Yesterday';
    } else {
      return '${timestamp.day}/${timestamp.month}';
    }
  }

  Message copyWith({
    String? id,
    String? sessionId,
    String? senderId,
    String? senderType,
    String? senderName,
    String? content,
    DateTime? timestamp,
    bool? isRead,
    String? messageType,
    Map<String, dynamic>? metadata,
  }) {
    return Message(
      id: id ?? this.id,
      sessionId: sessionId ?? this.sessionId,
      senderId: senderId ?? this.senderId,
      senderType: senderType ?? this.senderType,
      senderName: senderName ?? this.senderName,
      content: content ?? this.content,
      timestamp: timestamp ?? this.timestamp,
      isRead: isRead ?? this.isRead,
      messageType: messageType ?? this.messageType,
      metadata: metadata ?? this.metadata,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    
    return other is Message &&
        other.id == id &&
        other.content == content &&
        other.timestamp == timestamp &&
        other.isRead == isRead;
  }

  @override
  int get hashCode {
    return id.hashCode ^
        content.hashCode ^
        timestamp.hashCode ^
        isRead.hashCode;
  }

  @override
  String toString() {
    return 'Message(id: $id, senderType: $senderType, content: $content, timestamp: $timestamp)';
  }
}
