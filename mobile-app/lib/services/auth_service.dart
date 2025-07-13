import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import '../models/user_model.dart';
import '../constants/app_constants.dart';
import 'storage_service.dart';
import 'api_service.dart';

final authServiceProvider = Provider<AuthService>((ref) {
  return AuthService(
    apiService: ref.read(apiServiceProvider),
    storageService: ref.read(storageServiceProvider),
  );
});

class AuthService {
  final ApiService _apiService;
  final StorageService _storageService;

  AuthService({
    required ApiService apiService,
    required StorageService storageService,
  })  : _apiService = apiService,
        _storageService = storageService;

  /// Login with email and password
  Future<AuthResponse> login(String email, String password) async {
    try {
      final loginRequest = LoginRequest(
        email: email,
        password: password,
      );

      final response = await _apiService.post(
        '/auth/login',
        data: loginRequest.toJson(),
      );

      final authResponse = AuthResponse.fromJson(response.data);

      // Store tokens and user data
      await _storeAuthData(authResponse);

      return authResponse;
    } on DioException catch (e) {
      throw _handleAuthError(e);
    } catch (e) {
      throw AuthException('Login failed: ${e.toString()}');
    }
  }

  /// Logout user
  Future<void> logout() async {
    try {
      // Call logout endpoint if token exists
      final token = await getAccessToken();
      if (token != null) {
        try {
          await _apiService.post('/auth/logout');
        } catch (e) {
          // Ignore logout API errors, still clear local data
        }
      }

      // Clear stored auth data
      await _clearAuthData();
    } catch (e) {
      // Always clear local data even if API call fails
      await _clearAuthData();
      throw AuthException('Logout failed: ${e.toString()}');
    }
  }

  /// Refresh access token
  Future<AuthTokens> refreshToken() async {
    try {
      final refreshToken = await getRefreshToken();
      if (refreshToken == null) {
        throw AuthException('No refresh token available');
      }

      final refreshRequest = RefreshTokenRequest(refreshToken: refreshToken);

      final response = await _apiService.post(
        '/auth/refresh',
        data: refreshRequest.toJson(),
      );

      final tokens = AuthTokens.fromJson(response.data['tokens']);

      // Store new tokens
      await _storageService.setString(AppConstants.authTokenKey, tokens.accessToken);
      await _storageService.setString(AppConstants.refreshTokenKey, tokens.refreshToken);

      return tokens;
    } on DioException catch (e) {
      throw _handleAuthError(e);
    } catch (e) {
      throw AuthException('Token refresh failed: ${e.toString()}');
    }
  }

  /// Get current user profile
  Future<User> getCurrentUser() async {
    try {
      final response = await _apiService.get('/auth/me');
      return User.fromJson(response.data['user']);
    } on DioException catch (e) {
      throw _handleAuthError(e);
    } catch (e) {
      throw AuthException('Failed to get user profile: ${e.toString()}');
    }
  }

  /// Update user profile
  Future<User> updateProfile({
    String? firstName,
    String? lastName,
    String? avatarUrl,
  }) async {
    try {
      final data = <String, dynamic>{};
      if (firstName != null) data['firstName'] = firstName;
      if (lastName != null) data['lastName'] = lastName;
      if (avatarUrl != null) data['avatarUrl'] = avatarUrl;

      final response = await _apiService.put(
        '/auth/profile',
        data: data,
      );

      final updatedUser = User.fromJson(response.data['user']);

      // Update stored user data
      await _storageService.setString(
        AppConstants.userDataKey,
        jsonEncode(updatedUser.toJson()),
      );

      return updatedUser;
    } on DioException catch (e) {
      throw _handleAuthError(e);
    } catch (e) {
      throw AuthException('Profile update failed: ${e.toString()}');
    }
  }

  /// Check if user is authenticated
  Future<bool> isAuthenticated() async {
    try {
      final token = await getAccessToken();
      if (token == null) return false;

      // Verify token by making a test API call
      await getCurrentUser();
      return true;
    } catch (e) {
      // Token is invalid or expired
      await _clearAuthData();
      return false;
    }
  }

  /// Get stored access token
  Future<String?> getAccessToken() async {
    return await _storageService.getString(AppConstants.authTokenKey);
  }

  /// Get stored refresh token
  Future<String?> getRefreshToken() async {
    return await _storageService.getString(AppConstants.refreshTokenKey);
  }

  /// Get stored user data
  Future<User?> getStoredUser() async {
    try {
      final userJson = await _storageService.getString(AppConstants.userDataKey);
      if (userJson == null) return null;

      final userData = jsonDecode(userJson) as Map<String, dynamic>;
      return User.fromJson(userData);
    } catch (e) {
      return null;
    }
  }

  /// Store authentication data
  Future<void> _storeAuthData(AuthResponse authResponse) async {
    await Future.wait([
      _storageService.setString(
        AppConstants.authTokenKey,
        authResponse.tokens.accessToken,
      ),
      _storageService.setString(
        AppConstants.refreshTokenKey,
        authResponse.tokens.refreshToken,
      ),
      _storageService.setString(
        AppConstants.userDataKey,
        jsonEncode(authResponse.user.toJson()),
      ),
    ]);
  }

  /// Clear stored authentication data
  Future<void> _clearAuthData() async {
    await Future.wait([
      _storageService.remove(AppConstants.authTokenKey),
      _storageService.remove(AppConstants.refreshTokenKey),
      _storageService.remove(AppConstants.userDataKey),
    ]);
  }

  /// Handle authentication errors
  AuthException _handleAuthError(DioException error) {
    if (error.response?.statusCode == 401) {
      return AuthException(AppConstants.errorInvalidCredentials);
    } else if (error.response?.statusCode == 403) {
      return AuthException('Access forbidden');
    } else if (error.type == DioExceptionType.connectionTimeout ||
               error.type == DioExceptionType.receiveTimeout) {
      return AuthException(AppConstants.errorServerConnection);
    } else if (error.type == DioExceptionType.connectionError) {
      return AuthException(AppConstants.errorNetworkConnection);
    } else {
      final message = error.response?.data?['message'] ?? AppConstants.errorUnknown;
      return AuthException(message);
    }
  }

  /// Validate email format
  bool isValidEmail(String email) {
    return RegExp(AppConstants.emailRegex).hasMatch(email);
  }

  /// Validate password strength
  bool isValidPassword(String password) {
    return password.length >= AppConstants.minPasswordLength &&
           password.length <= AppConstants.maxPasswordLength;
  }

  /// Get password strength score (0-4)
  int getPasswordStrength(String password) {
    int score = 0;
    
    if (password.length >= 8) score++;
    if (password.contains(RegExp(r'[A-Z]'))) score++;
    if (password.contains(RegExp(r'[a-z]'))) score++;
    if (password.contains(RegExp(r'[0-9]'))) score++;
    if (password.contains(RegExp(r'[!@#$%^&*(),.?":{}|<>]'))) score++;
    
    return score > 4 ? 4 : score;
  }

  /// Get password strength text
  String getPasswordStrengthText(int strength) {
    switch (strength) {
      case 0:
      case 1:
        return 'Weak';
      case 2:
        return 'Fair';
      case 3:
        return 'Good';
      case 4:
        return 'Strong';
      default:
        return 'Weak';
    }
  }
}

/// Custom exception for authentication errors
class AuthException implements Exception {
  final String message;
  
  AuthException(this.message);
  
  @override
  String toString() => 'AuthException: $message';
}
