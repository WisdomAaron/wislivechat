console.log('🚀 Starting WisChat Backend...');

try {
  const express = require('express');
  console.log('✅ Express loaded');

  const cors = require('cors');
  console.log('✅ CORS loaded');

  const helmet = require('helmet');
  console.log('✅ Helmet loaded');

  const compression = require('compression');
  console.log('✅ Compression loaded');

  require('dotenv').config();
  console.log('✅ Dotenv loaded');

  const app = express();
  const PORT = process.env.PORT || 3000;

  console.log('📦 Environment:', process.env.NODE_ENV || 'production');
  console.log('🔌 Port:', PORT);

  // Add process error handlers
  process.on('uncaughtException', (error) => {
    console.error('❌ Uncaught Exception:', error);
    process.exit(1);
  });

  process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
    process.exit(1);
  });

  // Middleware
  console.log('🔧 Setting up middleware...');

  app.use(helmet());
  console.log('✅ Helmet middleware added');

  app.use(compression());
  console.log('✅ Compression middleware added');

  app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
  }));
  console.log('✅ CORS middleware added');

  app.use(express.json());
  console.log('✅ JSON middleware added');

  app.use(express.urlencoded({ extended: true }));
  console.log('✅ URL encoded middleware added');

// Health check endpoint
app.get('/api/v1/health', (req, res) => {
  res.json({
    status: 'OK',
    message: 'WisChat Backend is running!',
    timestamp: new Date().toISOString(),
    version: '1.0.0',
    environment: process.env.NODE_ENV || 'production'
  });
});

// Root endpoint
app.get('/', (req, res) => {
  res.json({
    message: 'WisChat Backend API',
    status: 'running',
    version: '1.0.0',
    endpoints: [
      'GET /api/v1/health',
      'GET /api/v1/firebase/config',
      'POST /api/v1/firebase/chat/message',
      'GET /api/v1/firebase/chat/sessions'
    ]
  });
});

// Firebase configuration endpoint
app.get('/api/v1/firebase/config', (req, res) => {
  res.json({
    apiKey: "AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ",
    authDomain: "wis-livechat.firebaseapp.com",
    databaseURL: "https://wis-livechat-default-rtdb.firebaseio.com",
    projectId: "wis-livechat",
    storageBucket: "wis-livechat.firebasestorage.app",
    messagingSenderId: "206365667705",
    appId: "1:206365667705:web:53b78c552588f354e87fa8",
    measurementId: "G-XC2YSBKQPP"
  });
});

// Chat message endpoint
app.post('/api/v1/firebase/chat/message', (req, res) => {
  const { sessionId, senderId, senderType, message, senderName } = req.body;
  
  console.log('Chat message received:', {
    sessionId,
    senderId,
    senderType,
    message,
    senderName,
    timestamp: new Date().toISOString()
  });

  // Return success response
  res.json({
    success: true,
    message: 'Message received and processed',
    data: {
      id: `msg_${Date.now()}`,
      sessionId: sessionId || `session_${Date.now()}`,
      senderId: senderId || 'anonymous',
      senderType: senderType || 'visitor',
      message: message || '',
      senderName: senderName || 'Anonymous',
      timestamp: new Date().toISOString(),
      status: 'sent'
    }
  });
});

// Chat sessions endpoint
app.get('/api/v1/firebase/chat/sessions', (req, res) => {
  res.json({
    success: true,
    sessions: [],
    message: 'Chat sessions retrieved',
    timestamp: new Date().toISOString()
  });
});

// FCM token registration endpoint
app.post('/api/v1/firebase/notifications/register', (req, res) => {
  const { fcmToken } = req.body;
  
  console.log('FCM token registered:', fcmToken);
  
  res.json({
    success: true,
    message: 'FCM token registered successfully',
    timestamp: new Date().toISOString()
  });
});

// Test notification endpoint
app.post('/api/v1/firebase/notifications/test', (req, res) => {
  const { title, body } = req.body;
  
  console.log('Test notification:', { title, body });
  
  res.json({
    success: true,
    message: 'Test notification sent',
    data: {
      title: title || 'Test Notification',
      body: body || 'This is a test notification from WisChat',
      timestamp: new Date().toISOString()
    }
  });
});

// Handle 404
app.use('*', (req, res) => {
  res.status(404).json({
    error: 'Endpoint not found',
    message: 'Please check the API documentation',
    availableEndpoints: [
      'GET /',
      'GET /api/v1/health',
      'GET /api/v1/firebase/config',
      'POST /api/v1/firebase/chat/message',
      'GET /api/v1/firebase/chat/sessions',
      'POST /api/v1/firebase/notifications/register',
      'POST /api/v1/firebase/notifications/test'
    ]
  });
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error('Error:', err);
  res.status(500).json({
    error: 'Internal server error',
    message: 'Something went wrong'
  });
});

  // Start server with error handling
  console.log('🚀 Starting server...');

  const server = app.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 WisChat Backend running on port ${PORT}`);
    console.log(`🌍 Environment: ${process.env.NODE_ENV || 'production'}`);
    console.log(`🔗 Health check: http://localhost:${PORT}/api/v1/health`);
    console.log(`✅ Server started successfully!`);
  }).on('error', (err) => {
    console.error('❌ Server failed to start:', err);
    process.exit(1);
  });

  // Graceful shutdown
  process.on('SIGTERM', () => {
    console.log('🛑 SIGTERM received, shutting down gracefully');
    server.close(() => {
      console.log('✅ Process terminated');
    });
  });

  module.exports = app;

} catch (error) {
  console.error('❌ FATAL ERROR during startup:', error);
  console.error('❌ Stack trace:', error.stack);
  process.exit(1);
}
