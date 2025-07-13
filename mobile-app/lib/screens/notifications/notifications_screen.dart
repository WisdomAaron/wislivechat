import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../constants/app_constants.dart';

class NotificationsScreen extends ConsumerWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          IconButton(
            icon: const Icon(Icons.mark_email_read),
            onPressed: () {
              _markAllAsRead(context);
            },
          ),
        ],
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(AppConstants.defaultPadding),
        itemCount: 15, // Placeholder count
        itemBuilder: (context, index) {
          return _buildNotificationItem(context, index);
        },
      ),
    );
  }

  Widget _buildNotificationItem(BuildContext context, int index) {
    final isUnread = index < 3; // First 3 are unread
    
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: isUnread 
              ? Theme.of(context).colorScheme.primary
              : Theme.of(context).colorScheme.surfaceVariant,
          child: Icon(
            _getNotificationIcon(index),
            color: isUnread 
                ? Theme.of(context).colorScheme.onPrimary
                : Theme.of(context).colorScheme.onSurfaceVariant,
          ),
        ),
        title: Text(
          _getNotificationTitle(index),
          style: TextStyle(
            fontWeight: isUnread ? FontWeight.bold : FontWeight.normal,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(_getNotificationMessage(index)),
            const SizedBox(height: 4),
            Text(
              _getNotificationTime(index),
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
          ],
        ),
        trailing: isUnread 
            ? Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.primary,
                  shape: BoxShape.circle,
                ),
              )
            : null,
        onTap: () {
          _handleNotificationTap(context, index);
        },
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        tileColor: isUnread 
            ? Theme.of(context).colorScheme.primary.withOpacity(0.05)
            : Theme.of(context).colorScheme.surface,
      ),
    );
  }

  IconData _getNotificationIcon(int index) {
    switch (index % 4) {
      case 0:
        return Icons.chat_bubble;
      case 1:
        return Icons.person_add;
      case 2:
        return Icons.assignment;
      case 3:
        return Icons.info;
      default:
        return Icons.notifications;
    }
  }

  String _getNotificationTitle(int index) {
    switch (index % 4) {
      case 0:
        return 'New Message';
      case 1:
        return 'New Chat Started';
      case 2:
        return 'Chat Assigned';
      case 3:
        return 'System Update';
      default:
        return 'Notification';
    }
  }

  String _getNotificationMessage(int index) {
    switch (index % 4) {
      case 0:
        return 'You have a new message from Visitor ${index + 1}';
      case 1:
        return 'Visitor ${index + 1} started a new conversation';
      case 2:
        return 'Chat #${index + 1} has been assigned to you';
      case 3:
        return 'System maintenance scheduled for tonight';
      default:
        return 'You have a new notification';
    }
  }

  String _getNotificationTime(int index) {
    if (index < 3) {
      return '${index + 1} minutes ago';
    } else if (index < 8) {
      return '${index - 2} hours ago';
    } else {
      return '${index - 7} days ago';
    }
  }

  void _handleNotificationTap(BuildContext context, int index) {
    // TODO: Handle notification tap based on type
    switch (index % 4) {
      case 0:
      case 1:
      case 2:
        // Navigate to chat
        Navigator.pushNamed(
          context,
          '/chat/detail',
          arguments: {'chatId': 'chat_${index + 1}'},
        );
        break;
      case 3:
        // Show system info
        _showSystemInfo(context);
        break;
    }
  }

  void _markAllAsRead(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Mark All as Read'),
        content: const Text('Mark all notifications as read?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('All notifications marked as read'),
                ),
              );
              // TODO: Mark all notifications as read
            },
            child: const Text('Mark All'),
          ),
        ],
      ),
    );
  }

  void _showSystemInfo(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('System Update'),
        content: const Text(
          'System maintenance is scheduled for tonight from 2:00 AM to 4:00 AM. '
          'The service may be temporarily unavailable during this time.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }
}
