# WisChat - Full-Featured Live Chat System

A comprehensive live chat solution consisting of a mobile application and WordPress plugin for seamless real-time communication between website visitors and support teams.

## 🚀 Features

### Core Functionality
- **Real-time messaging** with typing indicators and read receipts
- **Push notifications** for both mobile app and web users
- **Admin mobile dashboard** for managing chats on-the-go
- **WordPress integration** with customizable chat widget
- **Multi-language support** with auto-detection
- **GDPR compliant** with data protection features

### Advanced Features
- **Analytics dashboard** with performance metrics
- **Subscription management** with MTN Mobile Money
- **Canned responses** for quick replies
- **Working hours** and auto-offline scheduling
- **Spam protection** and user blocking
- **File sharing** capabilities
- **Light/dark mode** support

## 🏗️ Architecture

```
WisChat System
├── backend/              # Node.js API server with Socket.io
├── mobile-app/          # Flutter cross-platform app
├── wordpress-plugin/    # WordPress plugin
├── database/           # PostgreSQL schema and migrations
└── docs/              # Documentation and guides
```

### Technology Stack
- **Backend**: Node.js, Express.js, Socket.io, JWT
- **Database**: PostgreSQL with Redis for caching
- **Mobile**: Flutter (Android & iOS)
- **WordPress**: PHP following WP standards
- **Real-time**: WebSocket with Socket.io
- **Notifications**: Firebase Cloud Messaging
- **Payments**: MTN Mobile Money integration
- **Security**: HTTPS, JWT tokens, data encryption

## 📦 Project Structure

### Backend API (`/backend`)
- RESTful API endpoints for chat management
- Socket.io server for real-time communication
- Authentication and authorization
- Push notification service
- Database models and migrations

### Mobile App (`/mobile-app`)
- Flutter cross-platform application
- Admin dashboard for chat management
- Real-time message handling
- Push notification integration
- Offline support

### WordPress Plugin (`/wordpress-plugin`)
- Frontend chat widget
- Admin panel for configuration
- Integration with backend API
- Customization options
- Multi-language support

## 🚀 Quick Start

### Prerequisites
- Node.js 18+ and npm
- PostgreSQL 12+
- Flutter SDK 3.0+
- WordPress 5.0+
- Firebase project for notifications

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd wischat
   ```

2. **Set up the backend**
   ```bash
   cd backend
   npm install
   cp .env.example .env
   # Configure your environment variables
   npm run migrate
   npm start
   ```

3. **Install WordPress plugin**
   - Upload the plugin zip to WordPress admin
   - Activate the plugin
   - Configure API endpoint in settings

4. **Build mobile app**
   ```bash
   cd mobile-app
   flutter pub get
   flutter run
   ```

## 📚 Documentation

- [API Documentation](docs/api.md)
- [WordPress Plugin Guide](docs/wordpress-plugin.md)
- [Mobile App Setup](docs/mobile-app.md)
- [Deployment Guide](docs/deployment.md)

## 🔒 Security

- End-to-end encryption for messages
- JWT-based authentication
- GDPR compliance features
- Rate limiting and spam protection
- Secure file upload handling

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## 📞 Support

For support and questions, please contact [support@wischat.com](mailto:support@wischat.com)
