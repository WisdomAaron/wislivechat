import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';
import '../constants/app_constants.dart';

// Auth state
class AuthState {
  final User? user;
  final bool isLoading;
  final bool isAuthenticated;
  final String? error;

  const AuthState({
    this.user,
    this.isLoading = false,
    this.isAuthenticated = false,
    this.error,
  });

  AuthState copyWith({
    User? user,
    bool? isLoading,
    bool? isAuthenticated,
    String? error,
  }) {
    return AuthState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      error: error,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is AuthState &&
          runtimeType == other.runtimeType &&
          user == other.user &&
          isLoading == other.isLoading &&
          isAuthenticated == other.isAuthenticated &&
          error == other.error;

  @override
  int get hashCode =>
      user.hashCode ^
      isLoading.hashCode ^
      isAuthenticated.hashCode ^
      error.hashCode;

  @override
  String toString() {
    return 'AuthState{user: $user, isLoading: $isLoading, isAuthenticated: $isAuthenticated, error: $error}';
  }
}

// Auth provider
class AuthNotifier extends StateNotifier<AuthState> {
  final AuthService _authService;

  AuthNotifier(this._authService) : super(const AuthState());

  /// Login with email and password
  Future<bool> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      // Validate input
      if (!_authService.isValidEmail(email)) {
        state = state.copyWith(
          isLoading: false,
          error: 'Please enter a valid email address',
        );
        return false;
      }

      if (!_authService.isValidPassword(password)) {
        state = state.copyWith(
          isLoading: false,
          error: 'Password must be at least ${AppConstants.minPasswordLength} characters',
        );
        return false;
      }

      // Attempt login
      final authResponse = await _authService.login(email, password);

      state = state.copyWith(
        user: authResponse.user,
        isLoading: false,
        isAuthenticated: true,
        error: null,
      );

      return true;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        isAuthenticated: false,
        error: e.toString().replaceFirst('AuthException: ', ''),
      );
      return false;
    }
  }

  /// Logout user
  Future<void> logout() async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      await _authService.logout();
      
      state = const AuthState(
        user: null,
        isLoading: false,
        isAuthenticated: false,
        error: null,
      );
    } catch (e) {
      // Even if logout fails, clear local state
      state = const AuthState(
        user: null,
        isLoading: false,
        isAuthenticated: false,
        error: null,
      );
    }
  }

  /// Load user data from storage
  Future<void> loadUserData() async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final user = await _authService.getStoredUser();
      if (user != null) {
        // Verify authentication by fetching current user
        final currentUser = await _authService.getCurrentUser();
        
        state = state.copyWith(
          user: currentUser,
          isLoading: false,
          isAuthenticated: true,
          error: null,
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          isAuthenticated: false,
          error: null,
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        isAuthenticated: false,
        error: null, // Don't show error for failed auto-login
      );
    }
  }

  /// Refresh user data
  Future<void> refreshUserData() async {
    if (!state.isAuthenticated) return;

    try {
      final user = await _authService.getCurrentUser();
      state = state.copyWith(user: user, error: null);
    } catch (e) {
      // If refresh fails, user might need to re-authenticate
      state = state.copyWith(
        error: 'Session expired. Please login again.',
        isAuthenticated: false,
      );
    }
  }

  /// Update user profile
  Future<bool> updateProfile({
    String? firstName,
    String? lastName,
    String? avatarUrl,
  }) async {
    if (!state.isAuthenticated || state.user == null) return false;

    state = state.copyWith(isLoading: true, error: null);

    try {
      final updatedUser = await _authService.updateProfile(
        firstName: firstName,
        lastName: lastName,
        avatarUrl: avatarUrl,
      );

      state = state.copyWith(
        user: updatedUser,
        isLoading: false,
        error: null,
      );

      return true;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString().replaceFirst('AuthException: ', ''),
      );
      return false;
    }
  }

  /// Check authentication status
  Future<bool> checkAuthStatus() async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final isAuthenticated = await _authService.isAuthenticated();
      
      if (isAuthenticated) {
        final user = await _authService.getCurrentUser();
        state = state.copyWith(
          user: user,
          isLoading: false,
          isAuthenticated: true,
          error: null,
        );
      } else {
        state = state.copyWith(
          user: null,
          isLoading: false,
          isAuthenticated: false,
          error: null,
        );
      }

      return isAuthenticated;
    } catch (e) {
      state = state.copyWith(
        user: null,
        isLoading: false,
        isAuthenticated: false,
        error: null,
      );
      return false;
    }
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }

  /// Set user online status
  void setUserOnlineStatus(bool isOnline) {
    if (state.user != null) {
      final updatedUser = state.user!.copyWith(isOnline: isOnline);
      state = state.copyWith(user: updatedUser);
    }
  }

  /// Get user role permissions
  bool hasPermission(String permission) {
    if (state.user == null) return false;

    switch (permission) {
      case 'view_analytics':
        return state.user!.isAdmin || state.user!.isManager;
      case 'manage_users':
        return state.user!.isAdmin;
      case 'manage_settings':
        return state.user!.isAdmin || state.user!.isManager;
      case 'handle_chats':
        return true; // All authenticated users can handle chats
      case 'close_chats':
        return state.user!.isAdmin || state.user!.isManager || state.user!.isAgent;
      case 'transfer_chats':
        return state.user!.isAdmin || state.user!.isManager;
      default:
        return false;
    }
  }

  /// Check if user can perform admin actions
  bool get canPerformAdminActions => state.user?.isAdmin ?? false;

  /// Check if user can manage other agents
  bool get canManageAgents => state.user?.isAdmin ?? false || state.user?.isManager ?? false;

  /// Get user display name
  String get userDisplayName => state.user?.displayName ?? 'Unknown User';

  /// Get user avatar URL
  String? get userAvatarUrl => state.user?.avatarUrl;

  /// Get user role
  String get userRole => state.user?.role ?? 'unknown';
}

// Provider
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.read(authServiceProvider));
});

// Convenience providers
final currentUserProvider = Provider<User?>((ref) {
  return ref.watch(authProvider).user;
});

final isAuthenticatedProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isAuthenticated;
});

final isLoadingProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isLoading;
});

final authErrorProvider = Provider<String?>((ref) {
  return ref.watch(authProvider).error;
});

final userRoleProvider = Provider<String>((ref) {
  return ref.watch(authProvider).user?.role ?? 'unknown';
});

final userPermissionsProvider = Provider<bool Function(String)>((ref) {
  final authNotifier = ref.read(authProvider.notifier);
  return (permission) => authNotifier.hasPermission(permission);
});
