# 🔥 Firebase Integration Guide - WisChat Real-time System

Complete guide to set up Firebase for real-time communication between WordPress plugin and mobile app.

## 🎯 Overview

The updated WisChat system now uses Firebase Realtime Database for:
- **Real-time messaging** between website visitors and mobile admin
- **Push notifications** to mobile app when visitors land or send messages
- **Synchronized chat sessions** across all platforms
- **Live status updates** and typing indicators

## 🔧 Step 1: Firebase Project Setup

### 1. Create Firebase Project

1. **Go to Firebase Console**: [console.firebase.google.com](https://console.firebase.google.com)
2. **Click "Create a project"**
3. **Enter project name**: `wischat-your-company`
4. **Enable Google Analytics** (recommended)
5. **Create project**

### 2. Enable Required Services

**Realtime Database:**
1. Go to **Build** → **Realtime Database**
2. Click **"Create Database"**
3. Choose **"Start in test mode"** (we'll secure it later)
4. Select your preferred location
5. Click **"Done"**

**Cloud Messaging (FCM):**
1. Go to **Build** → **Cloud Messaging**
2. FCM is automatically enabled
3. Note your **Server Key** and **Sender ID**

### 3. Configure Security Rules

Go to **Realtime Database** → **Rules** and replace with:

```json
{
  "rules": {
    "chats": {
      "$sessionId": {
        ".read": true,
        ".write": true,
        "messages": {
          "$messageId": {
            ".validate": "newData.hasChildren(['id', 'senderId', 'senderType', 'message', 'timestamp'])"
          }
        }
      }
    },
    "admin_tokens": {
      ".read": "auth != null",
      ".write": "auth != null"
    }
  }
}
```

## 🔧 Step 2: Get Firebase Configuration

### 1. Web Configuration (for WordPress)

1. Go to **Project Settings** (gear icon)
2. Scroll to **"Your apps"**
3. Click **"Web"** icon (`</>`)
4. Enter app nickname: `wischat-web`
5. **Don't check** "Also set up Firebase Hosting"
6. Click **"Register app"**
7. **Copy the config object** - you'll need this!

Example config:
```javascript
{
  "apiKey": "AIzaSyC...",
  "authDomain": "wischat-project.firebaseapp.com",
  "databaseURL": "https://wischat-project-default-rtdb.firebaseio.com",
  "projectId": "wischat-project",
  "storageBucket": "wischat-project.appspot.com",
  "messagingSenderId": "123456789",
  "appId": "1:123456789:web:abc123"
}
```

### 2. Android Configuration (for Mobile App)

1. In **Project Settings**, click **"Android"** icon
2. Enter package name: `com.wischat.admin`
3. Enter app nickname: `wischat-mobile-android`
4. **Download `google-services.json`**
5. Place file in `mobile-app/android/app/`

### 3. iOS Configuration (for Mobile App)

1. In **Project Settings**, click **"iOS"** icon
2. Enter bundle ID: `com.wischat.admin`
3. Enter app nickname: `wischat-mobile-ios`
4. **Download `GoogleService-Info.plist`**
5. Place file in `mobile-app/ios/Runner/`

## 🔧 Step 3: Backend Configuration

### 1. Update Environment Variables

Add to your `backend/.env`:

```env
# Firebase Configuration
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_PRIVATE_KEY_ID=your-private-key-id
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYour-Private-Key\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your-client-id
FIREBASE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_API_KEY=your-web-api-key
FIREBASE_MESSAGING_SENDER_ID=your-sender-id
FIREBASE_APP_ID=your-app-id
```

### 2. Get Service Account Key

1. Go to **Project Settings** → **Service accounts**
2. Click **"Generate new private key"**
3. Download the JSON file
4. Extract values for your `.env` file:
   - `project_id` → `FIREBASE_PROJECT_ID`
   - `private_key_id` → `FIREBASE_PRIVATE_KEY_ID`
   - `private_key` → `FIREBASE_PRIVATE_KEY`
   - `client_email` → `FIREBASE_CLIENT_EMAIL`
   - `client_id` → `FIREBASE_CLIENT_ID`

### 3. Install Firebase Admin SDK

```bash
cd backend
npm install firebase-admin
```

### 4. Test Backend Firebase Connection

```bash
# Start your backend
npm run dev

# Test Firebase endpoint
curl -X GET http://localhost:3000/api/v1/firebase/config \
  -H "Authorization: Bearer YOUR_API_KEY"
```

## 🔧 Step 4: WordPress Plugin Configuration

### 1. Access Enhanced Settings

1. **Go to WordPress Admin** → **WisChat** → **Settings**
2. **Click "Firebase Configuration" tab**

### 2. Configure Firebase

**Option A: Paste Complete Config**
```json
{
  "apiKey": "AIzaSyC...",
  "authDomain": "wischat-project.firebaseapp.com",
  "databaseURL": "https://wischat-project-default-rtdb.firebaseio.com",
  "projectId": "wischat-project",
  "storageBucket": "wischat-project.appspot.com",
  "messagingSenderId": "123456789",
  "appId": "1:123456789:web:abc123"
}
```

**Option B: Individual Fields**
- **API Key**: `AIzaSyC...`
- **Project ID**: `wischat-project`
- **Messaging Sender ID**: `123456789`
- **App ID**: `1:123456789:web:abc123`

### 3. Test Firebase Connection

1. **Click "Test Firebase Connection"**
2. **Should show**: ✅ "Firebase configuration looks valid!"

### 4. Customize Widget

**Widget Customization Tab:**
- **Position**: Bottom Right, Bottom Left, etc.
- **Theme**: Light, Dark, Auto
- **Colors**: Primary, Secondary, Text, Background
- **Preview**: Real-time widget preview

**Messages Tab:**
- **Welcome Message**: "Hello! How can we help you today?"
- **Offline Message**: "We're currently offline. Please leave a message."
- **Placeholder**: "Type your message..."
- **Language**: English, Français, Español, Deutsch

**Notifications Tab:**
- ✅ **Visitor Notifications**: When someone lands on website
- ✅ **Message Notifications**: When someone sends a message

## 🔧 Step 5: Mobile App Configuration

### 1. Update Firebase Dependencies

The mobile app already includes Firebase dependencies. If needed:

```yaml
# pubspec.yaml
dependencies:
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.10
  firebase_database: ^10.4.0
  flutter_local_notifications: ^16.3.2
```

### 2. Initialize Firebase in App

The app automatically initializes Firebase on startup. No additional configuration needed.

### 3. Test Mobile App

1. **Build and run** the mobile app
2. **Login** with admin credentials
3. **Check notifications** are working
4. **Test real-time chat** functionality

## 🧪 Step 6: Testing the Complete System

### 1. End-to-End Test

**Test Flow:**
1. **Visit WordPress site** with chat widget
2. **Mobile app should receive** "visitor landed" notification
3. **Send message** from website
4. **Mobile app should receive** "new message" notification
5. **Reply from mobile app**
6. **Website should show** reply instantly

### 2. Test Notifications

**WordPress Admin:**
1. Go to **WisChat** → **Settings** → **Notifications**
2. Click **"Send Test Notification"**
3. **Mobile app should receive** test notification

**Mobile App:**
1. Open **Settings** → **Notifications**
2. Tap **"Send Test Notification"**
3. **Should receive** notification on device

### 3. Test Real-time Sync

1. **Open mobile app** chat list
2. **Send message from website**
3. **Chat should appear** in mobile app instantly
4. **Unread count** should update
5. **Reply from mobile app**
6. **Website should show** reply immediately

## 🔒 Step 7: Security Configuration

### 1. Production Security Rules

Replace test rules with production rules:

```json
{
  "rules": {
    "chats": {
      "$sessionId": {
        ".read": true,
        "messages": {
          ".write": "!data.exists() || data.child('senderType').val() == 'visitor'",
          "$messageId": {
            ".validate": "newData.hasChildren(['id', 'senderId', 'senderType', 'message', 'timestamp']) && (newData.child('senderType').val() == 'visitor' || newData.child('senderType').val() == 'admin')"
          }
        },
        ".write": "!data.exists()"
      }
    },
    "admin_tokens": {
      ".read": "auth != null && auth.token.admin == true",
      ".write": "auth != null && auth.token.admin == true"
    }
  }
}
```

### 2. API Key Restrictions

1. **Go to Google Cloud Console**
2. **Navigate to APIs & Services** → **Credentials**
3. **Find your API key**
4. **Add restrictions**:
   - **HTTP referrers**: Your website domains
   - **APIs**: Firebase Realtime Database API, Firebase Cloud Messaging API

## 📊 Step 8: Monitoring & Analytics

### 1. Firebase Analytics

1. **Go to Analytics** in Firebase Console
2. **View real-time users**
3. **Monitor chat engagement**
4. **Track notification delivery**

### 2. Performance Monitoring

1. **Enable Performance Monitoring** in Firebase
2. **Monitor app performance**
3. **Track database usage**
4. **Monitor notification success rates**

## 🚨 Troubleshooting

### Common Issues:

**1. Firebase Connection Failed**
```
Error: Firebase configuration is incomplete
```
**Solution**: Verify all Firebase config values are correct

**2. Notifications Not Working**
```
Error: No admin tokens found
```
**Solution**: Ensure mobile app is logged in and FCM token is registered

**3. Real-time Updates Not Working**
```
Error: Permission denied
```
**Solution**: Check Firebase security rules allow read/write access

**4. Mobile App Crashes on Startup**
```
Error: Firebase not initialized
```
**Solution**: Ensure `google-services.json` and `GoogleService-Info.plist` are in correct locations

### Debug Commands:

```bash
# Test Firebase config endpoint
curl -X GET http://localhost:3000/api/v1/firebase/config \
  -H "Authorization: Bearer YOUR_API_KEY"

# Test chat session creation
curl -X POST http://localhost:3000/api/v1/firebase/chat/session \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{"visitorId":"test123","websiteUrl":"https://example.com"}'

# Test message sending
curl -X POST http://localhost:3000/api/v1/firebase/chat/message \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{"sessionId":"test123","senderId":"visitor","senderType":"visitor","message":"Hello!"}'
```

## ✅ Success Checklist

- [ ] Firebase project created and configured
- [ ] Realtime Database enabled with security rules
- [ ] Cloud Messaging (FCM) enabled
- [ ] Web app registered and config obtained
- [ ] Android app registered and `google-services.json` added
- [ ] iOS app registered and `GoogleService-Info.plist` added
- [ ] Backend environment variables configured
- [ ] WordPress plugin Firebase settings configured
- [ ] Mobile app Firebase initialized
- [ ] End-to-end test completed successfully
- [ ] Notifications working on mobile app
- [ ] Real-time messaging working between web and mobile
- [ ] Security rules configured for production

## 🎉 You're Ready!

Your WisChat system now has:
- ✅ **Real-time messaging** between website and mobile app
- ✅ **Push notifications** for visitor activity
- ✅ **Synchronized chat sessions** across platforms
- ✅ **Secure Firebase integration**
- ✅ **Production-ready configuration**

The system will automatically handle:
- Creating chat sessions when visitors land
- Sending notifications to mobile admin
- Syncing messages in real-time
- Managing unread counts
- Updating session status

**Your customers can now have seamless real-time conversations!** 🚀
