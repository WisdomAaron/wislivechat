const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(helmet());
app.use(compression());
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization']
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

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

// Admin Dashboard Route
app.get('/admin', (req, res) => {
  res.send(`
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WisChat Admin Dashboard - API Key Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; color: #333; }
        .header { background: #2c3e50; color: white; padding: 1rem 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { font-size: 1.5rem; font-weight: 600; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .card-header { padding: 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h2 { font-size: 1.25rem; font-weight: 600; }
        .card-body { padding: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 4px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .code-block { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 1rem; font-family: 'Courier New', monospace; font-size: 0.85rem; overflow-x: auto; margin: 1rem 0; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔑 WisChat Admin Dashboard - API Key Management</h1>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>🔐 Generate New API Key</h2>
            </div>
            <div class="card-body">
                <form id="generateKeyForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="clientName">Client Name *</label>
                            <input type="text" id="clientName" name="clientName" required>
                        </div>
                        <div class="form-group">
                            <label for="clientEmail">Client Email *</label>
                            <input type="email" id="clientEmail" name="clientEmail" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="clientWebsite">Client Website</label>
                            <input type="url" id="clientWebsite" name="clientWebsite" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label for="subscriptionType">Subscription Type</label>
                            <select id="subscriptionType" name="subscriptionType">
                                <option value="free">Free (1,000 messages/month)</option>
                                <option value="basic">Basic (10,000 messages/month) - 5,000 XAF</option>
                                <option value="premium">Premium (50,000 messages/month) - 15,000 XAF</option>
                                <option value="enterprise">Enterprise (Unlimited) - 50,000 XAF</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="allowedDomains">Allowed Domains (one per line, optional)</label>
                        <textarea id="allowedDomains" name="allowedDomains" rows="3" placeholder="example.com"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Additional notes about this client..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">🔑 Generate API Key</button>
                </form>
            </div>
        </div>

        <div id="apiKeyResult" class="card" style="display: none;">
            <div class="card-header">
                <h2>✅ API Key Generated Successfully!</h2>
            </div>
            <div class="card-body">
                <div id="apiKeyDetails"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>📋 How to Use API Keys</h2>
            </div>
            <div class="card-body">
                <h3>WordPress Plugin Configuration:</h3>
                <div class="code-block">// In wp-config.php or plugin settings
define('WISCHAT_API_KEY', 'your_generated_key_id');
define('WISCHAT_API_SECRET', 'your_generated_secret_key');
define('WISCHAT_API_ENDPOINT', 'https://wislivechat.onrender.com');</div>

                <h3>Subscription Tiers:</h3>
                <ul>
                    <li><strong>Free:</strong> 1,000 messages/month - 0 XAF</li>
                    <li><strong>Basic:</strong> 10,000 messages/month - 5,000 XAF</li>
                    <li><strong>Premium:</strong> 50,000 messages/month - 15,000 XAF</li>
                    <li><strong>Enterprise:</strong> Unlimited - 50,000 XAF</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('generateKeyForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = {
                clientName: formData.get('clientName'),
                clientEmail: formData.get('clientEmail'),
                clientWebsite: formData.get('clientWebsite'),
                subscriptionType: formData.get('subscriptionType'),
                allowedDomains: formData.get('allowedDomains').split('\\n').filter(d => d.trim()),
                notes: formData.get('notes')
            };

            const keyId = 'wc_' + Math.random().toString(36).substr(2, 32);
            const secretKey = Math.random().toString(36).substr(2, 32) + Math.random().toString(36).substr(2, 32);
            const clientId = 'client_' + Math.random().toString(36).substr(2, 8);

            const result = {
                keyId: keyId,
                secretKey: secretKey,
                clientId: clientId,
                clientName: data.clientName,
                subscriptionType: data.subscriptionType,
                monthlyMessageLimit: getMessageLimit(data.subscriptionType),
                allowedDomains: data.allowedDomains
            };

            showApiKeyResult(result);
            e.target.reset();
        });

        function getMessageLimit(type) {
            switch(type) {
                case 'free': return 1000;
                case 'basic': return 10000;
                case 'premium': return 50000;
                case 'enterprise': return -1;
                default: return 1000;
            }
        }

        function showApiKeyResult(apiKey) {
            const resultDiv = document.getElementById('apiKeyResult');
            const detailsDiv = document.getElementById('apiKeyDetails');

            detailsDiv.innerHTML = \`
                <div class="alert alert-success">
                    <strong>API Key Generated Successfully!</strong><br>
                    Please save these credentials securely.
                </div>

                <div class="form-group">
                    <label>Client Information</label>
                    <div class="code-block">
                        <strong>Name:</strong> \${apiKey.clientName}<br>
                        <strong>Client ID:</strong> \${apiKey.clientId}<br>
                        <strong>Subscription:</strong> \${apiKey.subscriptionType}<br>
                        <strong>Monthly Limit:</strong> \${apiKey.monthlyMessageLimit === -1 ? 'Unlimited' : apiKey.monthlyMessageLimit.toLocaleString()} messages
                    </div>
                </div>

                <div class="form-group">
                    <label>API Credentials (Save These!)</label>
                    <div class="code-block">
                        <strong>Key ID:</strong> \${apiKey.keyId}<br>
                        <strong>Secret Key:</strong> \${apiKey.secretKey}
                    </div>
                </div>

                <div class="form-group">
                    <label>WordPress Configuration</label>
                    <div class="code-block">define('WISCHAT_API_KEY', '\${apiKey.keyId}');
define('WISCHAT_API_SECRET', '\${apiKey.secretKey}');
define('WISCHAT_API_ENDPOINT', 'https://wislivechat.onrender.com');</div>
                </div>
            \`;

            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
  `);
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error('Error:', err);
  res.status(500).json({
    error: 'Internal server error',
    message: 'Something went wrong'
  });
});

app.listen(PORT, () => {
  console.log(`🚀 WisChat Backend running on port ${PORT}`);
  console.log(`🌍 Environment: ${process.env.NODE_ENV || 'production'}`);
  console.log(`🔗 Health check: http://localhost:${PORT}/api/v1/health`);
});

module.exports = app;
