import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:firebase_database/firebase_database.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'dart:convert';
import '../models/chat_session.dart';
import '../models/message.dart';
import '../services/api_service.dart';

class FirebaseService {
  static final FirebaseService _instance = FirebaseService._internal();
  factory FirebaseService() => _instance;
  FirebaseService._internal();

  FirebaseMessaging? _messaging;
  FirebaseDatabase? _database;
  FlutterLocalNotificationsPlugin? _localNotifications;
  
  // Stream controllers for real-time data
  Stream<List<ChatSession>>? _chatSessionsStream;
  Map<String, Stream<List<Message>>> _messageStreams = {};

  Future<void> initialize() async {
    await Firebase.initializeApp();
    
    _messaging = FirebaseMessaging.instance;
    _database = FirebaseDatabase.instance;
    _localNotifications = FlutterLocalNotificationsPlugin();

    // Request permission
    await _requestPermission();
    
    // Initialize local notifications
    await _initializeLocalNotifications();
    
    // Setup message handlers
    _setupMessageHandlers();
    
    // Register FCM token
    await _registerFCMToken();
  }

  Future<void> _requestPermission() async {
    NotificationSettings settings = await _messaging!.requestPermission(
      alert: true,
      announcement: false,
      badge: true,
      carPlay: false,
      criticalAlert: false,
      provisional: false,
      sound: true,
    );

    print('User granted permission: ${settings.authorizationStatus}');
  }

  Future<void> _initializeLocalNotifications() async {
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    
    const DarwinInitializationSettings initializationSettingsIOS =
        DarwinInitializationSettings(
      requestSoundPermission: true,
      requestBadgePermission: true,
      requestAlertPermission: true,
    );

    const InitializationSettings initializationSettings =
        InitializationSettings(
      android: initializationSettingsAndroid,
      iOS: initializationSettingsIOS,
    );

    await _localNotifications!.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );
  }

  void _setupMessageHandlers() {
    // Handle messages when app is in foreground
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('Got a message whilst in the foreground!');
      print('Message data: ${message.data}');

      if (message.notification != null) {
        _showLocalNotification(message);
      }
    });

    // Handle messages when app is opened from background
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('A new onMessageOpenedApp event was published!');
      _handleNotificationTap(message.data);
    });

    // Handle messages when app is opened from terminated state
    FirebaseMessaging.getInitialMessage().then((RemoteMessage? message) {
      if (message != null) {
        _handleNotificationTap(message.data);
      }
    });
  }

  Future<void> _registerFCMToken() async {
    try {
      String? token = await _messaging!.getToken();
      if (token != null) {
        print('FCM Token: $token');
        
        // Register token with backend
        await ApiService().registerFCMToken(token);
      }
    } catch (e) {
      print('Error registering FCM token: $e');
    }
  }

  Future<void> _showLocalNotification(RemoteMessage message) async {
    const AndroidNotificationDetails androidPlatformChannelSpecifics =
        AndroidNotificationDetails(
      'wischat_channel',
      'WisChat Notifications',
      channelDescription: 'Notifications for WisChat messages',
      importance: Importance.max,
      priority: Priority.high,
      showWhen: false,
    );

    const DarwinNotificationDetails iOSPlatformChannelSpecifics =
        DarwinNotificationDetails();

    const NotificationDetails platformChannelSpecifics = NotificationDetails(
      android: androidPlatformChannelSpecifics,
      iOS: iOSPlatformChannelSpecifics,
    );

    await _localNotifications!.show(
      message.hashCode,
      message.notification?.title,
      message.notification?.body,
      platformChannelSpecifics,
      payload: jsonEncode(message.data),
    );
  }

  void _onNotificationTapped(NotificationResponse response) {
    if (response.payload != null) {
      Map<String, dynamic> data = jsonDecode(response.payload!);
      _handleNotificationTap(data);
    }
  }

  void _handleNotificationTap(Map<String, dynamic> data) {
    String? type = data['type'];
    String? sessionId = data['sessionId'];

    switch (type) {
      case 'new_message':
      case 'visitor_landed':
        if (sessionId != null) {
          // Navigate to chat session
          // This would be handled by the app's navigation system
          print('Navigate to chat session: $sessionId');
        }
        break;
    }
  }

  // Get real-time chat sessions
  Stream<List<ChatSession>> getChatSessionsStream() {
    if (_chatSessionsStream == null) {
      _chatSessionsStream = _database!
          .ref('chats')
          .orderByChild('lastActivity')
          .limitToLast(50)
          .onValue
          .map((event) {
        List<ChatSession> sessions = [];
        
        if (event.snapshot.value != null) {
          Map<dynamic, dynamic> data = event.snapshot.value as Map<dynamic, dynamic>;
          
          data.forEach((key, value) {
            try {
              sessions.add(ChatSession.fromFirebase(value));
            } catch (e) {
              print('Error parsing chat session: $e');
            }
          });
        }
        
        // Sort by last activity (newest first)
        sessions.sort((a, b) => b.lastActivity.compareTo(a.lastActivity));
        
        return sessions;
      });
    }
    
    return _chatSessionsStream!;
  }

  // Get real-time messages for a session
  Stream<List<Message>> getMessagesStream(String sessionId) {
    if (!_messageStreams.containsKey(sessionId)) {
      _messageStreams[sessionId] = _database!
          .ref('chats/$sessionId/messages')
          .orderByChild('timestamp')
          .onValue
          .map((event) {
        List<Message> messages = [];
        
        if (event.snapshot.value != null) {
          Map<dynamic, dynamic> data = event.snapshot.value as Map<dynamic, dynamic>;
          
          data.forEach((key, value) {
            try {
              messages.add(Message.fromFirebase(value));
            } catch (e) {
              print('Error parsing message: $e');
            }
          });
        }
        
        // Sort by timestamp
        messages.sort((a, b) => a.timestamp.compareTo(b.timestamp));
        
        return messages;
      });
    }
    
    return _messageStreams[sessionId]!;
  }

  // Send message to Firebase
  Future<bool> sendMessage({
    required String sessionId,
    required String senderId,
    required String message,
    String senderName = 'Admin',
  }) async {
    try {
      DatabaseReference messageRef = _database!
          .ref('chats/$sessionId/messages')
          .push();

      await messageRef.set({
        'id': messageRef.key,
        'senderId': senderId,
        'senderType': 'admin',
        'senderName': senderName,
        'message': message,
        'timestamp': ServerValue.timestamp,
        'read': false,
      });

      // Update session last activity
      await _database!.ref('chats/$sessionId').update({
        'lastActivity': ServerValue.timestamp,
        'lastMessage': message,
        'lastMessageTime': ServerValue.timestamp,
      });

      return true;
    } catch (e) {
      print('Error sending message: $e');
      return false;
    }
  }

  // Mark messages as read
  Future<void> markMessagesAsRead(String sessionId, List<String> messageIds) async {
    try {
      Map<String, dynamic> updates = {};
      
      for (String messageId in messageIds) {
        updates['chats/$sessionId/messages/$messageId/read'] = true;
      }
      
      // Reset unread count
      updates['chats/$sessionId/unreadCount'] = 0;
      
      await _database!.ref().update(updates);
    } catch (e) {
      print('Error marking messages as read: $e');
    }
  }

  // Update session status
  Future<void> updateSessionStatus(String sessionId, String status) async {
    try {
      await _database!.ref('chats/$sessionId').update({
        'status': status,
        'lastActivity': ServerValue.timestamp,
      });
    } catch (e) {
      print('Error updating session status: $e');
    }
  }

  // Get chat statistics
  Future<Map<String, int>> getChatStatistics() async {
    try {
      DatabaseEvent event = await _database!.ref('chats').once();
      
      if (event.snapshot.value == null) {
        return {
          'totalSessions': 0,
          'activeSessions': 0,
          'unreadMessages': 0,
        };
      }
      
      Map<dynamic, dynamic> data = event.snapshot.value as Map<dynamic, dynamic>;
      
      int totalSessions = data.length;
      int activeSessions = 0;
      int unreadMessages = 0;
      
      data.forEach((key, value) {
        if (value['status'] == 'active') {
          activeSessions++;
        }
        
        if (value['unreadCount'] != null) {
          unreadMessages += (value['unreadCount'] as int);
        }
      });
      
      return {
        'totalSessions': totalSessions,
        'activeSessions': activeSessions,
        'unreadMessages': unreadMessages,
      };
    } catch (e) {
      print('Error getting chat statistics: $e');
      return {
        'totalSessions': 0,
        'activeSessions': 0,
        'unreadMessages': 0,
      };
    }
  }

  void dispose() {
    _messageStreams.clear();
  }
}
