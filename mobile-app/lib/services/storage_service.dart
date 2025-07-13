import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../constants/app_constants.dart';

final storageServiceProvider = Provider<StorageService>((ref) {
  return StorageService();
});

class StorageService {
  static late Box _box;
  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
    ),
    iOptions: IOSOptions(
      accessibility: KeychainAccessibility.first_unlock_this_device,
    ),
  );

  /// Initialize storage
  static Future<void> init() async {
    await Hive.initFlutter();
    _box = await Hive.openBox(AppConstants.storageBoxName);
  }

  /// Store string value
  Future<void> setString(String key, String value) async {
    if (_isSecureKey(key)) {
      await _secureStorage.write(key: key, value: value);
    } else {
      await _box.put(key, value);
    }
  }

  /// Get string value
  Future<String?> getString(String key) async {
    if (_isSecureKey(key)) {
      return await _secureStorage.read(key: key);
    } else {
      return _box.get(key);
    }
  }

  /// Store integer value
  Future<void> setInt(String key, int value) async {
    await _box.put(key, value);
  }

  /// Get integer value
  int? getInt(String key) {
    return _box.get(key);
  }

  /// Store double value
  Future<void> setDouble(String key, double value) async {
    await _box.put(key, value);
  }

  /// Get double value
  double? getDouble(String key) {
    return _box.get(key);
  }

  /// Store boolean value
  Future<void> setBool(String key, bool value) async {
    await _box.put(key, value);
  }

  /// Get boolean value
  bool? getBool(String key) {
    return _box.get(key);
  }

  /// Store list value
  Future<void> setStringList(String key, List<String> value) async {
    await _box.put(key, value);
  }

  /// Get list value
  List<String>? getStringList(String key) {
    final value = _box.get(key);
    if (value is List) {
      return value.cast<String>();
    }
    return null;
  }

  /// Store map value
  Future<void> setMap(String key, Map<String, dynamic> value) async {
    await _box.put(key, value);
  }

  /// Get map value
  Map<String, dynamic>? getMap(String key) {
    final value = _box.get(key);
    if (value is Map) {
      return Map<String, dynamic>.from(value);
    }
    return null;
  }

  /// Remove value
  Future<void> remove(String key) async {
    if (_isSecureKey(key)) {
      await _secureStorage.delete(key: key);
    } else {
      await _box.delete(key);
    }
  }

  /// Check if key exists
  bool containsKey(String key) {
    return _box.containsKey(key);
  }

  /// Get all keys
  Iterable<String> getKeys() {
    return _box.keys.cast<String>();
  }

  /// Clear all data
  Future<void> clear() async {
    await _box.clear();
    await _secureStorage.deleteAll();
  }

  /// Clear only non-secure data
  Future<void> clearNonSecure() async {
    await _box.clear();
  }

  /// Clear only secure data
  Future<void> clearSecure() async {
    await _secureStorage.deleteAll();
  }

  /// Get box size
  int get length => _box.length;

  /// Check if storage is empty
  bool get isEmpty => _box.isEmpty;

  /// Check if storage is not empty
  bool get isNotEmpty => _box.isNotEmpty;

  /// Listen to changes in a specific key
  Stream<BoxEvent> watch({String? key}) {
    return _box.watch(key: key);
  }

  /// Get all values
  Iterable<dynamic> getValues() {
    return _box.values;
  }

  /// Compact storage (optimize storage space)
  Future<void> compact() async {
    await _box.compact();
  }

  /// Close storage
  Future<void> close() async {
    await _box.close();
  }

  /// Check if a key should be stored securely
  bool _isSecureKey(String key) {
    const secureKeys = [
      AppConstants.authTokenKey,
      AppConstants.refreshTokenKey,
      AppConstants.fcmTokenKey,
    ];
    return secureKeys.contains(key);
  }

  /// Store user preferences
  Future<void> setUserPreference(String key, dynamic value) async {
    final preferences = getUserPreferences();
    preferences[key] = value;
    await setMap('user_preferences', preferences);
  }

  /// Get user preference
  T? getUserPreference<T>(String key) {
    final preferences = getUserPreferences();
    return preferences[key] as T?;
  }

  /// Get all user preferences
  Map<String, dynamic> getUserPreferences() {
    return getMap('user_preferences') ?? {};
  }

  /// Clear user preferences
  Future<void> clearUserPreferences() async {
    await remove('user_preferences');
  }

  /// Store app settings
  Future<void> setAppSetting(String key, dynamic value) async {
    final settings = getAppSettings();
    settings[key] = value;
    await setMap(AppConstants.settingsKey, settings);
  }

  /// Get app setting
  T? getAppSetting<T>(String key) {
    final settings = getAppSettings();
    return settings[key] as T?;
  }

  /// Get all app settings
  Map<String, dynamic> getAppSettings() {
    return getMap(AppConstants.settingsKey) ?? {};
  }

  /// Clear app settings
  Future<void> clearAppSettings() async {
    await remove(AppConstants.settingsKey);
  }

  /// Store cache data with expiration
  Future<void> setCacheData(
    String key,
    dynamic data, {
    Duration? expiration,
  }) async {
    final cacheItem = {
      'data': data,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
      'expiration': expiration?.inMilliseconds,
    };
    await setMap('cache_$key', cacheItem);
  }

  /// Get cache data if not expired
  T? getCacheData<T>(String key) {
    final cacheItem = getMap('cache_$key');
    if (cacheItem == null) return null;

    final timestamp = cacheItem['timestamp'] as int?;
    final expiration = cacheItem['expiration'] as int?;

    if (timestamp != null && expiration != null) {
      final now = DateTime.now().millisecondsSinceEpoch;
      final expirationTime = timestamp + expiration;
      
      if (now > expirationTime) {
        // Cache expired, remove it
        remove('cache_$key');
        return null;
      }
    }

    return cacheItem['data'] as T?;
  }

  /// Clear expired cache data
  Future<void> clearExpiredCache() async {
    final keys = getKeys().where((key) => key.startsWith('cache_')).toList();
    final now = DateTime.now().millisecondsSinceEpoch;

    for (final key in keys) {
      final cacheItem = getMap(key);
      if (cacheItem != null) {
        final timestamp = cacheItem['timestamp'] as int?;
        final expiration = cacheItem['expiration'] as int?;

        if (timestamp != null && expiration != null) {
          final expirationTime = timestamp + expiration;
          if (now > expirationTime) {
            await remove(key);
          }
        }
      }
    }
  }

  /// Clear all cache data
  Future<void> clearAllCache() async {
    final keys = getKeys().where((key) => key.startsWith('cache_')).toList();
    for (final key in keys) {
      await remove(key);
    }
  }

  /// Get storage statistics
  Map<String, dynamic> getStorageStats() {
    final totalKeys = length;
    final cacheKeys = getKeys().where((key) => key.startsWith('cache_')).length;
    final secureKeys = getKeys().where((key) => _isSecureKey(key)).length;
    
    return {
      'totalKeys': totalKeys,
      'cacheKeys': cacheKeys,
      'secureKeys': secureKeys,
      'regularKeys': totalKeys - cacheKeys,
      'isEmpty': isEmpty,
    };
  }

  /// Export data (excluding secure data)
  Map<String, dynamic> exportData() {
    final data = <String, dynamic>{};
    for (final key in getKeys()) {
      if (!_isSecureKey(key)) {
        data[key] = _box.get(key);
      }
    }
    return data;
  }

  /// Import data
  Future<void> importData(Map<String, dynamic> data) async {
    for (final entry in data.entries) {
      if (!_isSecureKey(entry.key)) {
        await _box.put(entry.key, entry.value);
      }
    }
  }
}
