class AppConstants {
  // App Information
  static const String appName = 'WisLiveChat Admin';
  static const String appVersion = '1.0.0';
  static const String appDescription = 'Mobile app for managing WisLiveChat conversations';
  
  // API Configuration
  static const String defaultApiBaseUrl = 'http://localhost:3000';
  static const String apiVersion = 'v1';
  static const Duration apiTimeout = Duration(seconds: 30);
  static const Duration socketTimeout = Duration(seconds: 10);

  // Firebase Configuration
  static const String firebaseProjectId = 'wis-livechat';
  static const String firebaseMessagingSenderId = '206365667705';
  static const String firebaseDatabaseUrl = 'https://wis-livechat-default-rtdb.firebaseio.com';
  static const String firebaseStorageBucket = 'wis-livechat.firebasestorage.app';

  // Platform-specific Firebase App IDs
  static const String firebaseAndroidAppId = '1:206365667705:android:715fc7b43f93b225e87fa8';
  static const String firebaseIosAppId = '1:206365667705:ios:693b7dee012f27b7e87fa8';

  // Platform-specific API Keys
  static const String firebaseAndroidApiKey = 'AIzaSyCSu6v-E2LXxCKlHj9tr7_PLZc0whCvjLY';
  static const String firebaseIosApiKey = 'AIzaSyB43WWq1kNb71-RORkAopQIHrUe_LPuB2g';
  
  // Storage Keys
  static const String storageBoxName = 'wischat_admin';
  static const String authTokenKey = 'auth_token';
  static const String refreshTokenKey = 'refresh_token';
  static const String userDataKey = 'user_data';
  static const String settingsKey = 'app_settings';
  static const String apiEndpointKey = 'api_endpoint';
  static const String fcmTokenKey = 'fcm_token';
  static const String lastSyncKey = 'last_sync';
  
  // Chat Constants
  static const int maxMessageLength = 1000;
  static const int maxFileSize = 10 * 1024 * 1024; // 10MB
  static const List<String> allowedFileTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'text/plain',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  ];
  
  // Notification Constants
  static const String notificationChannelId = 'wischat_notifications';
  static const String notificationChannelName = 'WisChat Notifications';
  static const String notificationChannelDescription = 'Notifications for new chat messages and updates';
  
  // UI Constants
  static const double defaultPadding = 16.0;
  static const double smallPadding = 8.0;
  static const double largePadding = 24.0;
  static const double defaultBorderRadius = 12.0;
  static const double smallBorderRadius = 8.0;
  static const double largeBorderRadius = 16.0;
  
  // Animation Durations
  static const Duration shortAnimation = Duration(milliseconds: 200);
  static const Duration mediumAnimation = Duration(milliseconds: 300);
  static const Duration longAnimation = Duration(milliseconds: 500);
  
  // Refresh Intervals
  static const Duration chatRefreshInterval = Duration(seconds: 30);
  static const Duration statusRefreshInterval = Duration(minutes: 1);
  static const Duration analyticsRefreshInterval = Duration(minutes: 5);
  
  // Pagination
  static const int defaultPageSize = 20;
  static const int maxPageSize = 100;
  
  // Chat Status
  static const String chatStatusActive = 'active';
  static const String chatStatusClosed = 'closed';
  static const String chatStatusWaiting = 'waiting';
  static const String chatStatusTransferred = 'transferred';
  
  // Message Types
  static const String messageTypeText = 'text';
  static const String messageTypeFile = 'file';
  static const String messageTypeImage = 'image';
  static const String messageTypeSystem = 'system';
  
  // User Roles
  static const String roleAdmin = 'admin';
  static const String roleAgent = 'agent';
  static const String roleManager = 'manager';
  
  // Socket Events
  static const String socketEventConnect = 'connect';
  static const String socketEventDisconnect = 'disconnect';
  static const String socketEventMessage = 'message';
  static const String socketEventTypingStart = 'typing_start';
  static const String socketEventTypingStop = 'typing_stop';
  static const String socketEventAgentJoined = 'agent_joined';
  static const String socketEventAgentLeft = 'agent_left';
  static const String socketEventChatClosed = 'chat_closed';
  static const String socketEventStatusUpdate = 'status_update';
  
  // Error Messages
  static const String errorNetworkConnection = 'No internet connection';
  static const String errorServerConnection = 'Unable to connect to server';
  static const String errorInvalidCredentials = 'Invalid email or password';
  static const String errorSessionExpired = 'Session expired. Please login again';
  static const String errorUnknown = 'An unexpected error occurred';
  static const String errorFileUpload = 'Failed to upload file';
  static const String errorFileTooLarge = 'File size exceeds limit';
  static const String errorFileTypeNotSupported = 'File type not supported';
  
  // Success Messages
  static const String successLogin = 'Login successful';
  static const String successLogout = 'Logout successful';
  static const String successMessageSent = 'Message sent';
  static const String successFileUploaded = 'File uploaded successfully';
  static const String successChatClosed = 'Chat closed successfully';
  
  // Validation
  static const int minPasswordLength = 8;
  static const int maxPasswordLength = 128;
  static const String emailRegex = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$';
  
  // URLs
  static const String privacyPolicyUrl = 'https://wischat.com/privacy';
  static const String termsOfServiceUrl = 'https://wischat.com/terms';
  static const String supportUrl = 'https://support.wischat.com';
  static const String documentationUrl = 'https://docs.wischat.com';
  
  // Feature Flags
  static const bool enableAnalytics = true;
  static const bool enableCrashReporting = true;
  static const bool enablePushNotifications = true;
  static const bool enableFileUpload = true;
  static const bool enableVoiceMessages = false; // Future feature
  static const bool enableVideoCall = false; // Future feature
  
  // Development
  static const bool isDebugMode = true; // Should be false in production
  static const bool enableLogging = true;
  static const bool enableMockData = false;
}

class AppRoutes {
  static const String splash = '/';
  static const String login = '/login';
  static const String home = '/home';
  static const String chat = '/chat';
  static const String chatDetail = '/chat/detail';
  static const String profile = '/profile';
  static const String settings = '/settings';
  static const String analytics = '/analytics';
  static const String notifications = '/notifications';
  static const String about = '/about';
}

class AppAssets {
  // Images
  static const String logo = 'assets/images/logo.png';
  static const String logoWhite = 'assets/images/logo_white.png';
  static const String placeholder = 'assets/images/placeholder.png';
  static const String avatarPlaceholder = 'assets/images/avatar_placeholder.png';
  static const String emptyState = 'assets/images/empty_state.png';
  static const String errorState = 'assets/images/error_state.png';
  
  // Icons
  static const String appIcon = 'assets/icons/app_icon.png';
  static const String chatIcon = 'assets/icons/chat.svg';
  static const String notificationIcon = 'assets/icons/notification.svg';
  static const String settingsIcon = 'assets/icons/settings.svg';
  static const String analyticsIcon = 'assets/icons/analytics.svg';
  
  // Animations
  static const String loadingAnimation = 'assets/animations/loading.json';
  static const String successAnimation = 'assets/animations/success.json';
  static const String errorAnimation = 'assets/animations/error.json';
  static const String emptyAnimation = 'assets/animations/empty.json';
  
  // Sounds
  static const String notificationSound = 'assets/sounds/notification.mp3';
  static const String messageSound = 'assets/sounds/message.mp3';
  static const String successSound = 'assets/sounds/success.mp3';
  static const String errorSound = 'assets/sounds/error.mp3';
}

class AppColors {
  // Primary Colors
  static const int primaryColorValue = 0xFF007CBA;
  static const int secondaryColorValue = 0xFF6C757D;
  static const int accentColorValue = 0xFF28A745;
  
  // Status Colors
  static const int successColorValue = 0xFF28A745;
  static const int warningColorValue = 0xFFFFC107;
  static const int errorColorValue = 0xFFDC3545;
  static const int infoColorValue = 0xFF17A2B8;
  
  // Chat Status Colors
  static const int activeStatusColor = 0xFF28A745;
  static const int waitingStatusColor = 0xFFFFC107;
  static const int closedStatusColor = 0xFF6C757D;
  static const int transferredStatusColor = 0xFF17A2B8;
}

class AppSizes {
  // Screen Breakpoints
  static const double mobileBreakpoint = 768;
  static const double tabletBreakpoint = 1024;
  static const double desktopBreakpoint = 1440;
  
  // Component Sizes
  static const double appBarHeight = 56.0;
  static const double bottomNavHeight = 60.0;
  static const double fabSize = 56.0;
  static const double avatarSizeSmall = 32.0;
  static const double avatarSizeMedium = 48.0;
  static const double avatarSizeLarge = 64.0;
  static const double iconSizeSmall = 16.0;
  static const double iconSizeMedium = 24.0;
  static const double iconSizeLarge = 32.0;
  
  // Input Sizes
  static const double inputHeight = 48.0;
  static const double buttonHeight = 48.0;
  static const double cardElevation = 2.0;
  static const double modalElevation = 8.0;
}
