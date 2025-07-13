# 🚀 Super Simple Glitch Deployment (5 Minutes)

## 🎯 **Why Glitch?**
- ✅ **No GitHub needed**
- ✅ **No command line**
- ✅ **Copy-paste deployment**
- ✅ **Instant URL**
- ✅ **100% free**

## 📋 **Step-by-Step (5 Minutes)**

### **Step 1: Create Glitch Project**
1. Go to: [glitch.com](https://glitch.com)
2. Click **"New Project"**
3. Choose **"Node.js"**
4. You get instant code editor

### **Step 2: Replace package.json**
1. **Click on `package.json`** in Glitch
2. **Delete everything**
3. **Copy-paste this:**

```json
{
  "name": "wischat-backend",
  "version": "1.0.0",
  "description": "WisChat Backend API",
  "main": "server.js",
  "scripts": {
    "start": "node server.js"
  },
  "dependencies": {
    "express": "^4.18.2",
    "cors": "^2.8.5",
    "dotenv": "^16.3.1",
    "firebase-admin": "^11.11.1"
  },
  "engines": {
    "node": "18.x"
  }
}
```

### **Step 3: Replace server.js**
1. **Click on `server.js`** in Glitch
2. **Delete everything**
3. **Copy-paste this:**

```javascript
const express = require('express');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Health check
app.get('/api/v1/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    message: 'WisChat Backend is running!',
    timestamp: new Date().toISOString()
  });
});

// Firebase config endpoint
app.get('/api/v1/firebase/config', (req, res) => {
  res.json({
    apiKey: "AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ",
    authDomain: "wis-livechat.firebaseapp.com",
    databaseURL: "https://wis-livechat-default-rtdb.firebaseio.com",
    projectId: "wis-livechat",
    storageBucket: "wis-livechat.firebasestorage.app",
    messagingSenderId: "206365667705",
    appId: "1:206365667705:web:53b78c552588f354e87fa8"
  });
});

// Chat endpoints
app.post('/api/v1/firebase/chat/message', (req, res) => {
  console.log('Message received:', req.body);
  res.json({ 
    success: true, 
    message: 'Message received',
    data: req.body
  });
});

app.get('/api/v1/firebase/chat/sessions', (req, res) => {
  res.json({ 
    success: true, 
    sessions: []
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 WisChat Backend running on port ${PORT}`);
  console.log(`🔗 URL: https://your-project-name.glitch.me`);
});
```

### **Step 4: Create .env file**
1. **Click "New File"** in Glitch
2. **Name it**: `.env`
3. **Add this:**

```env
NODE_ENV=production
API_KEY=wischat_api_key_2025_secure_token_12345
FIREBASE_PROJECT_ID=wis-livechat
```

### **Step 5: Get Your URL**
1. **Glitch automatically deploys**
2. **Your URL**: `https://your-project-name.glitch.me`
3. **Test it**: Go to `https://your-project-name.glitch.me/api/v1/health`

## 🎉 **That's It! You're Done!**

Your backend is now live at:
`https://your-project-name.glitch.me`

## 🔧 **Use in WordPress Plugin**

**WordPress Admin** → **WisChat** → **Settings**:
- **API Endpoint**: `https://your-project-name.glitch.me`
- **API Key**: `wischat_api_key_2025_secure_token_12345`

## 🎯 **Benefits of Glitch**
- ✅ **Instant deployment** - No waiting
- ✅ **Auto-restart** - Never goes down
- ✅ **Free SSL** - Secure HTTPS
- ✅ **Global CDN** - Fast worldwide
- ✅ **No maintenance** - Just works

## 🚀 **Ready to Try?**

This is literally the easiest way to deploy your backend. Takes 5 minutes, no technical knowledge needed!
