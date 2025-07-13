import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../constants/app_constants.dart';
import 'storage_service.dart';

final apiServiceProvider = Provider<ApiService>((ref) {
  return ApiService(
    storageService: ref.read(storageServiceProvider),
  );
});

class ApiService {
  late final Dio _dio;
  final StorageService _storageService;

  ApiService({
    required StorageService storageService,
  }) : _storageService = storageService {
    _initializeDio();
  }

  void _initializeDio() {
    _dio = Dio();
    
    // Configure base options
    _dio.options = BaseOptions(
      connectTimeout: AppConstants.apiTimeout,
      receiveTimeout: AppConstants.apiTimeout,
      sendTimeout: AppConstants.apiTimeout,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'User-Agent': 'WisChat-Mobile/${AppConstants.appVersion}',
      },
    );

    // Add interceptors
    _dio.interceptors.add(_createAuthInterceptor());
    _dio.interceptors.add(_createLoggingInterceptor());
    _dio.interceptors.add(_createErrorInterceptor());
  }

  /// Set API base URL
  Future<void> setBaseUrl(String baseUrl) async {
    final formattedUrl = baseUrl.endsWith('/') ? baseUrl.substring(0, baseUrl.length - 1) : baseUrl;
    _dio.options.baseUrl = '$formattedUrl/api/${AppConstants.apiVersion}';
    
    // Store the endpoint for future use
    await _storageService.setString(AppConstants.apiEndpointKey, baseUrl);
  }

  /// Get current base URL
  Future<String?> getBaseUrl() async {
    return await _storageService.getString(AppConstants.apiEndpointKey);
  }

  /// Initialize with stored base URL
  Future<void> initializeWithStoredUrl() async {
    final storedUrl = await getBaseUrl();
    if (storedUrl != null) {
      await setBaseUrl(storedUrl);
    }
  }

  /// GET request
  Future<Response> get(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.get(
        path,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw _handleError(e);
    }
  }

  /// POST request
  Future<Response> post(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.post(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw _handleError(e);
    }
  }

  /// PUT request
  Future<Response> put(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.put(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw _handleError(e);
    }
  }

  /// DELETE request
  Future<Response> delete(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.delete(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw _handleError(e);
    }
  }

  /// Upload file
  Future<Response> uploadFile(
    String path,
    String filePath, {
    String fieldName = 'file',
    Map<String, dynamic>? data,
    ProgressCallback? onSendProgress,
  }) async {
    try {
      final formData = FormData();
      
      // Add file
      formData.files.add(MapEntry(
        fieldName,
        await MultipartFile.fromFile(filePath),
      ));
      
      // Add additional data
      if (data != null) {
        data.forEach((key, value) {
          formData.fields.add(MapEntry(key, value.toString()));
        });
      }

      return await _dio.post(
        path,
        data: formData,
        options: Options(
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        ),
        onSendProgress: onSendProgress,
      );
    } catch (e) {
      throw _handleError(e);
    }
  }

  /// Create authentication interceptor
  Interceptor _createAuthInterceptor() {
    return InterceptorsWrapper(
      onRequest: (options, handler) async {
        // Add auth token to requests
        final token = await _storageService.getString(AppConstants.authTokenKey);
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) async {
        // Handle token refresh on 401 errors
        if (error.response?.statusCode == 401) {
          try {
            await _refreshToken();
            
            // Retry the original request
            final options = error.requestOptions;
            final token = await _storageService.getString(AppConstants.authTokenKey);
            if (token != null) {
              options.headers['Authorization'] = 'Bearer $token';
            }
            
            final response = await _dio.fetch(options);
            handler.resolve(response);
            return;
          } catch (e) {
            // Refresh failed, clear auth data
            await _clearAuthData();
          }
        }
        handler.next(error);
      },
    );
  }

  /// Create logging interceptor
  Interceptor _createLoggingInterceptor() {
    return InterceptorsWrapper(
      onRequest: (options, handler) {
        if (AppConstants.enableLogging) {
          print('🚀 API Request: ${options.method} ${options.uri}');
          if (options.data != null) {
            print('📤 Request Data: ${options.data}');
          }
        }
        handler.next(options);
      },
      onResponse: (response, handler) {
        if (AppConstants.enableLogging) {
          print('✅ API Response: ${response.statusCode} ${response.requestOptions.uri}');
        }
        handler.next(response);
      },
      onError: (error, handler) {
        if (AppConstants.enableLogging) {
          print('❌ API Error: ${error.response?.statusCode} ${error.requestOptions.uri}');
          print('Error Message: ${error.message}');
        }
        handler.next(error);
      },
    );
  }

  /// Create error interceptor
  Interceptor _createErrorInterceptor() {
    return InterceptorsWrapper(
      onError: (error, handler) {
        // Transform DioException to more user-friendly errors
        final transformedError = _transformError(error);
        handler.next(transformedError);
      },
    );
  }

  /// Refresh authentication token
  Future<void> _refreshToken() async {
    final refreshToken = await _storageService.getString(AppConstants.refreshTokenKey);
    if (refreshToken == null) {
      throw DioException(
        requestOptions: RequestOptions(path: ''),
        error: 'No refresh token available',
      );
    }

    final response = await _dio.post(
      '/auth/refresh',
      data: {'refreshToken': refreshToken},
      options: Options(
        headers: {
          'Authorization': null, // Don't include auth header for refresh
        },
      ),
    );

    final tokens = response.data['tokens'];
    await _storageService.setString(AppConstants.authTokenKey, tokens['accessToken']);
    await _storageService.setString(AppConstants.refreshTokenKey, tokens['refreshToken']);
  }

  /// Clear authentication data
  Future<void> _clearAuthData() async {
    await Future.wait([
      _storageService.remove(AppConstants.authTokenKey),
      _storageService.remove(AppConstants.refreshTokenKey),
      _storageService.remove(AppConstants.userDataKey),
    ]);
  }

  /// Transform DioException to more user-friendly errors
  DioException _transformError(DioException error) {
    String message;
    
    switch (error.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        message = AppConstants.errorServerConnection;
        break;
      case DioExceptionType.connectionError:
        message = AppConstants.errorNetworkConnection;
        break;
      case DioExceptionType.badResponse:
        message = error.response?.data?['message'] ?? 
                 'Server error (${error.response?.statusCode})';
        break;
      case DioExceptionType.cancel:
        message = 'Request was cancelled';
        break;
      case DioExceptionType.unknown:
      default:
        message = error.message ?? AppConstants.errorUnknown;
        break;
    }

    return DioException(
      requestOptions: error.requestOptions,
      response: error.response,
      type: error.type,
      error: message,
      message: message,
    );
  }

  /// Handle and throw appropriate errors
  Exception _handleError(dynamic error) {
    if (error is DioException) {
      return ApiException(error.message ?? AppConstants.errorUnknown);
    } else {
      return ApiException(error.toString());
    }
  }

  /// Test API connection
  Future<bool> testConnection() async {
    try {
      final response = await get('/health');
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  /// Get API health status
  Future<Map<String, dynamic>?> getHealthStatus() async {
    try {
      final response = await get('/health');
      return response.data;
    } catch (e) {
      return null;
    }
  }

  // Firebase endpoints
  Future<Map<String, dynamic>> registerFCMToken(String token) async {
    final response = await post('/firebase/notifications/register', data: {
      'fcmToken': token,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> getChatSessions() async {
    final response = await get('/firebase/chat/sessions');
    return response.data;
  }

  Future<Map<String, dynamic>> getSessionMessages(String sessionId) async {
    final response = await get('/firebase/chat/session/$sessionId/messages');
    return response.data;
  }

  Future<Map<String, dynamic>> sendMessage({
    required String sessionId,
    required String senderId,
    required String senderType,
    required String message,
    String? senderName,
  }) async {
    final response = await post('/firebase/chat/message', data: {
      'sessionId': sessionId,
      'senderId': senderId,
      'senderType': senderType,
      'message': message,
      'senderName': senderName,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> markMessagesAsRead(
    String sessionId,
    List<String> messageIds,
  ) async {
    final response = await post('/firebase/chat/session/$sessionId/read', data: {
      'messageIds': messageIds,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> updateSessionStatus(
    String sessionId,
    String status,
  ) async {
    final response = await put('/firebase/chat/session/$sessionId/status', data: {
      'status': status,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> getChatStatistics() async {
    final response = await get('/firebase/chat/stats');
    return response.data;
  }

  Future<Map<String, dynamic>> sendTestNotification({
    String? title,
    String? body,
  }) async {
    final response = await post('/firebase/notifications/test', data: {
      'title': title,
      'body': body,
    });
    return response.data;
  }
}

/// Custom exception for API errors
class ApiException implements Exception {
  final String message;
  
  ApiException(this.message);
  
  @override
  String toString() => 'ApiException: $message';
}
