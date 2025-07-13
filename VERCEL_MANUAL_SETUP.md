# 🚀 Manual Vercel Setup (Copy-Paste Method)

## 📋 **If Drag & Drop Doesn't Work**

### **Step 1: Create New Project in Vercel**
1. Go to [vercel.com](https://vercel.com)
2. Click **"New Project"**
3. Choose **"Browse All Templates"**
4. Select **"Node.js"** or **"Express.js"**
5. Click **"Deploy"**

### **Step 2: Edit Files in Vercel Dashboard**
Once deployed, you can edit files directly in Vercel:

#### **Replace package.json with:**
```json
{
  "name": "wischat-backend",
  "version": "1.0.0",
  "description": "WisChat Backend API",
  "main": "api/index.js",
  "scripts": {
    "start": "node api/index.js",
    "dev": "node api/index.js"
  },
  "dependencies": {
    "express": "^4.18.2",
    "cors": "^2.8.5",
    "dotenv": "^16.3.1"
  },
  "engines": {
    "node": "18.x"
  }
}
```

#### **Create api/index.js:**
```javascript
const express = require('express');
const cors = require('cors');

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Health check
app.get('/api/health', (req, res) => {
  res.json({
    status: 'OK',
    message: 'WisChat Backend is running on Vercel!',
    timestamp: new Date().toISOString()
  });
});

// Firebase config
app.get('/api/firebase/config', (req, res) => {
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

// Chat message endpoint
app.post('/api/firebase/chat/message', (req, res) => {
  console.log('Message received:', req.body);
  res.json({
    success: true,
    message: 'Message received',
    data: req.body
  });
});

// Export for Vercel
module.exports = app;
```

#### **Create vercel.json:**
```json
{
  "version": 2,
  "builds": [
    {
      "src": "api/index.js",
      "use": "@vercel/node"
    }
  ],
  "routes": [
    {
      "src": "/(.*)",
      "dest": "api/index.js"
    }
  ]
}
```

### **Step 3: Deploy**
1. **Save all files**
2. **Vercel auto-deploys**
3. **Get your URL**: `https://your-project-name.vercel.app`

### **Step 4: Test**
- Health: `https://your-project-name.vercel.app/api/health`
- Config: `https://your-project-name.vercel.app/api/firebase/config`
