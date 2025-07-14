const express = require('express');
const app = express();
const PORT = process.env.PORT || 10000;

console.log('🚀 Starting WisChat Backend...');
console.log('🔌 Port:', PORT);

// Basic middleware
app.use(express.json());

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

// Health check endpoint
app.get('/api/v1/health', (req, res) => {
  res.json({
    status: 'OK',
    message: 'WisChat Backend is running!',
    timestamp: new Date().toISOString(),
    version: '1.0.0'
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

  console.log('💬 Chat message received:', {
    sessionId,
    senderId,
    senderType,
    message,
    senderName,
    timestamp: new Date().toISOString()
  });

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

app.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 WisChat Backend running on port ${PORT}`);
  console.log(`✅ Server started successfully!`);
});
