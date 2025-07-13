import 'package:flutter/material.dart';
import '../constants/app_constants.dart';
import '../screens/splash_screen.dart';
import '../screens/auth/login_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/chat/chat_list_screen.dart';
import '../screens/chat/chat_detail_screen.dart';
import '../screens/profile/profile_screen.dart';
import '../screens/settings/settings_screen.dart';
import '../screens/analytics/analytics_screen.dart';
import '../screens/notifications/notifications_screen.dart';
import '../screens/about/about_screen.dart';

class AppRouter {
  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case AppRoutes.splash:
        return _buildRoute(const SplashScreen(), settings);
        
      case AppRoutes.login:
        return _buildRoute(const LoginScreen(), settings);
        
      case AppRoutes.home:
        return _buildRoute(const HomeScreen(), settings);
        
      case AppRoutes.chat:
        return _buildRoute(const ChatListScreen(), settings);
        
      case AppRoutes.chatDetail:
        final args = settings.arguments as Map<String, dynamic>?;
        final chatId = args?['chatId'] as String?;
        
        if (chatId == null) {
          return _buildErrorRoute('Chat ID is required');
        }
        
        return _buildRoute(
          ChatDetailScreen(chatId: chatId),
          settings,
        );
        
      case AppRoutes.profile:
        return _buildRoute(const ProfileScreen(), settings);
        
      case AppRoutes.settings:
        return _buildRoute(const SettingsScreen(), settings);
        
      case AppRoutes.analytics:
        return _buildRoute(const AnalyticsScreen(), settings);
        
      case AppRoutes.notifications:
        return _buildRoute(const NotificationsScreen(), settings);
        
      case AppRoutes.about:
        return _buildRoute(const AboutScreen(), settings);
        
      default:
        return _buildErrorRoute('Route not found: ${settings.name}');
    }
  }

  static PageRoute<dynamic> _buildRoute(Widget page, RouteSettings settings) {
    return PageRouteBuilder<dynamic>(
      settings: settings,
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        return _buildTransition(animation, child, settings.name);
      },
      transitionDuration: AppConstants.mediumAnimation,
      reverseTransitionDuration: AppConstants.shortAnimation,
    );
  }

  static Widget _buildTransition(
    Animation<double> animation,
    Widget child,
    String? routeName,
  ) {
    // Different transitions for different routes
    switch (routeName) {
      case AppRoutes.splash:
        return FadeTransition(opacity: animation, child: child);
        
      case AppRoutes.login:
        return SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(0.0, 1.0),
            end: Offset.zero,
          ).animate(CurvedAnimation(
            parent: animation,
            curve: Curves.easeInOut,
          )),
          child: child,
        );
        
      case AppRoutes.chatDetail:
        return SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(1.0, 0.0),
            end: Offset.zero,
          ).animate(CurvedAnimation(
            parent: animation,
            curve: Curves.easeInOut,
          )),
          child: child,
        );
        
      case AppRoutes.settings:
      case AppRoutes.profile:
      case AppRoutes.analytics:
      case AppRoutes.notifications:
      case AppRoutes.about:
        return SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(1.0, 0.0),
            end: Offset.zero,
          ).animate(CurvedAnimation(
            parent: animation,
            curve: Curves.easeInOut,
          )),
          child: child,
        );
        
      default:
        return FadeTransition(
          opacity: animation,
          child: SlideTransition(
            position: Tween<Offset>(
              begin: const Offset(0.0, 0.1),
              end: Offset.zero,
            ).animate(CurvedAnimation(
              parent: animation,
              curve: Curves.easeInOut,
            )),
            child: child,
          ),
        );
    }
  }

  static Route<dynamic> _buildErrorRoute(String message) {
    return MaterialPageRoute<dynamic>(
      builder: (context) => Scaffold(
        appBar: AppBar(
          title: const Text('Error'),
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(AppConstants.defaultPadding),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(
                  Icons.error_outline,
                  size: 64,
                  color: Colors.red,
                ),
                const SizedBox(height: 16),
                Text(
                  'Navigation Error',
                  style: Theme.of(context).textTheme.headlineSmall,
                ),
                const SizedBox(height: 8),
                Text(
                  message,
                  style: Theme.of(context).textTheme.bodyMedium,
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).pushNamedAndRemoveUntil(
                      AppRoutes.home,
                      (route) => false,
                    );
                  },
                  child: const Text('Go Home'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// Navigation helper class
class AppNavigator {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  static NavigatorState? get navigator => navigatorKey.currentState;
  static BuildContext? get context => navigatorKey.currentContext;

  // Navigation methods
  static Future<T?> push<T extends Object?>(String routeName, {Object? arguments}) {
    return navigator!.pushNamed<T>(routeName, arguments: arguments);
  }

  static Future<T?> pushReplacement<T extends Object?, TO extends Object?>(
    String routeName, {
    Object? arguments,
    TO? result,
  }) {
    return navigator!.pushReplacementNamed<T, TO>(
      routeName,
      arguments: arguments,
      result: result,
    );
  }

  static Future<T?> pushAndRemoveUntil<T extends Object?>(
    String routeName,
    bool Function(Route<dynamic>) predicate, {
    Object? arguments,
  }) {
    return navigator!.pushNamedAndRemoveUntil<T>(
      routeName,
      predicate,
      arguments: arguments,
    );
  }

  static void pop<T extends Object?>([T? result]) {
    return navigator!.pop<T>(result);
  }

  static void popUntil(bool Function(Route<dynamic>) predicate) {
    return navigator!.popUntil(predicate);
  }

  static bool canPop() {
    return navigator!.canPop();
  }

  // Specific navigation methods
  static Future<void> goToLogin() {
    return pushAndRemoveUntil(
      AppRoutes.login,
      (route) => false,
    );
  }

  static Future<void> goToHome() {
    return pushAndRemoveUntil(
      AppRoutes.home,
      (route) => false,
    );
  }

  static Future<void> goToChat(String chatId) {
    return push(
      AppRoutes.chatDetail,
      arguments: {'chatId': chatId},
    );
  }

  static Future<void> goToProfile() {
    return push(AppRoutes.profile);
  }

  static Future<void> goToSettings() {
    return push(AppRoutes.settings);
  }

  static Future<void> goToAnalytics() {
    return push(AppRoutes.analytics);
  }

  static Future<void> goToNotifications() {
    return push(AppRoutes.notifications);
  }

  static Future<void> goToAbout() {
    return push(AppRoutes.about);
  }

  // Dialog helpers
  static Future<T?> showCustomDialog<T>({
    required Widget child,
    bool barrierDismissible = true,
    Color? barrierColor,
    String? barrierLabel,
  }) {
    return showDialog<T>(
      context: context!,
      barrierDismissible: barrierDismissible,
      barrierColor: barrierColor,
      barrierLabel: barrierLabel,
      builder: (context) => child,
    );
  }

  static Future<void> showErrorDialog(String message, {String? title}) {
    return showCustomDialog(
      child: AlertDialog(
        title: Text(title ?? 'Error'),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => pop(),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  static Future<bool?> showConfirmDialog({
    required String message,
    String? title,
    String confirmText = 'Confirm',
    String cancelText = 'Cancel',
  }) {
    return showCustomDialog<bool>(
      child: AlertDialog(
        title: title != null ? Text(title) : null,
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => pop(false),
            child: Text(cancelText),
          ),
          ElevatedButton(
            onPressed: () => pop(true),
            child: Text(confirmText),
          ),
        ],
      ),
    );
  }

  // Bottom sheet helpers
  static Future<T?> showCustomBottomSheet<T>({
    required Widget child,
    bool isScrollControlled = false,
    bool isDismissible = true,
    bool enableDrag = true,
  }) {
    return showModalBottomSheet<T>(
      context: context!,
      isScrollControlled: isScrollControlled,
      isDismissible: isDismissible,
      enableDrag: enableDrag,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(AppConstants.largeBorderRadius),
        ),
      ),
      builder: (context) => child,
    );
  }

  // Snackbar helpers
  static void showSnackBar(String message, {
    SnackBarAction? action,
    Duration duration = const Duration(seconds: 4),
    Color? backgroundColor,
  }) {
    final scaffoldMessenger = ScaffoldMessenger.of(context!);
    scaffoldMessenger.hideCurrentSnackBar();
    scaffoldMessenger.showSnackBar(
      SnackBar(
        content: Text(message),
        action: action,
        duration: duration,
        backgroundColor: backgroundColor,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppConstants.smallBorderRadius),
        ),
      ),
    );
  }

  static void showSuccessSnackBar(String message) {
    showSnackBar(
      message,
      backgroundColor: const Color(AppColors.successColorValue),
    );
  }

  static void showErrorSnackBar(String message) {
    showSnackBar(
      message,
      backgroundColor: const Color(AppColors.errorColorValue),
    );
  }

  static void showWarningSnackBar(String message) {
    showSnackBar(
      message,
      backgroundColor: const Color(AppColors.warningColorValue),
    );
  }
}
