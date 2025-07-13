# 🔥 Your WisLiveChat Firebase Setup Guide

## ✅ **Your Firebase Project Configuration**

**Project ID**: `wis-livechat`  
**Project Number**: `206365667705`  

### **🌐 Web App Configuration (WordPress Plugin)**
```json
{
  "apiKey": "AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ",
  "authDomain": "wis-livechat.firebaseapp.com",
  "databaseURL": "https://wis-livechat-default-rtdb.firebaseio.com",
  "projectId": "wis-livechat",
  "storageBucket": "wis-livechat.firebasestorage.app",
  "messagingSenderId": "206365667705",
  "appId": "1:206365667705:web:53b78c552588f354e87fa8",
  "measurementId": "G-XC2YSBKQPP"
}
```

### **📱 Android App Configuration**
- **Package Name**: `com.wistech.wislivechat`
- **API Key**: `AIzaSyCSu6v-E2LXxCKlHj9tr7_PLZc0whCvjLY`
- **App ID**: `1:206365667705:android:715fc7b43f93b225e87fa8`

### **🍎 iOS App Configuration**
- **Bundle ID**: `com.wistech.wislivechat`
- **API Key**: `AIzaSyB43WWq1kNb71-RORkAopQIHrUe_LPuB2g`
- **App ID**: `1:206365667705:ios:693b7dee012f27b7e87fa8`

## 🚀 **Step-by-Step Setup Instructions**

### **Step 1: Backend Configuration (5 minutes)**

1. **Navigate to your backend folder**
2. **Copy `.env.example` to `.env`**
3. **Update your `.env` file with these values**:

```env
# Firebase Configuration (wis-livechat project)
FIREBASE_PROJECT_ID=wis-livechat
FIREBASE_MESSAGING_SENDER_ID=206365667705
FIREBASE_AUTH_DOMAIN=wis-livechat.firebaseapp.com
FIREBASE_DATABASE_URL=https://wis-livechat-default-rtdb.firebaseio.com
FIREBASE_STORAGE_BUCKET=wis-livechat.firebasestorage.app

# Platform-specific Firebase configuration
FIREBASE_WEB_API_KEY=AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ
FIREBASE_ANDROID_API_KEY=AIzaSyCSu6v-E2LXxCKlHj9tr7_PLZc0whCvjLY
FIREBASE_IOS_API_KEY=AIzaSyB43WWq1kNb71-RORkAopQIHrUe_LPuB2g
FIREBASE_WEB_APP_ID=1:206365667705:web:53b78c552588f354e87fa8
FIREBASE_ANDROID_APP_ID=1:206365667705:android:715fc7b43f93b225e87fa8
FIREBASE_IOS_APP_ID=1:206365667705:ios:693b7dee012f27b7e87fa8

# You still need to get these from Firebase Service Account:
FIREBASE_PRIVATE_KEY_ID=your_private_key_id_from_service_account
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYour-Private-Key-Here\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-xxxxx@wis-livechat.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your_client_id_from_service_account
```

4. **Install Firebase Admin SDK**:
```bash
cd backend
npm install firebase-admin
```

5. **Start your backend**:
```bash
npm run dev
```

### **Step 2: Get Firebase Service Account Key**

1. **Go to Firebase Console** → **Project Settings** → **Service accounts**
2. **Click "Generate new private key"**
3. **Download the JSON file**
4. **Extract these values for your `.env`**:
   - `project_id` → Already set as `wis-livechat`
   - `private_key_id` → `FIREBASE_PRIVATE_KEY_ID`
   - `private_key` → `FIREBASE_PRIVATE_KEY`
   - `client_email` → `FIREBASE_CLIENT_EMAIL`
   - `client_id` → `FIREBASE_CLIENT_ID`

### **Step 3: WordPress Plugin Configuration (3 minutes)**

1. **Install the WordPress plugin** (upload the .zip file)
2. **Go to WordPress Admin** → **WisChat** → **Settings**
3. **In the Firebase Configuration tab**, paste this JSON:

```json
{
  "apiKey": "AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ",
  "authDomain": "wis-livechat.firebaseapp.com",
  "databaseURL": "https://wis-livechat-default-rtdb.firebaseio.com",
  "projectId": "wis-livechat",
  "storageBucket": "wis-livechat.firebasestorage.app",
  "messagingSenderId": "206365667705",
  "appId": "1:206365667705:web:53b78c552588f354e87fa8"
}
```

4. **Click "Test Firebase Connection"**
5. **Customize your widget** in the other tabs
6. **Save settings**

### **Step 4: Mobile App Configuration (2 minutes)**

The mobile app is already configured with your Firebase settings! The files are in place:

- ✅ `mobile-app/android/app/google-services.json`
- ✅ `mobile-app/ios/Runner/GoogleService-Info.plist`
- ✅ App constants updated with your project details

**To build the mobile app**:

```bash
cd mobile-app
flutter pub get
flutter build appbundle --release  # Android
flutter build ios --release        # iOS
```

### **Step 5: Firebase Database Setup (2 minutes)**

1. **Go to Firebase Console** → **Realtime Database**
2. **Click "Create Database"**
3. **Choose "Start in test mode"**
4. **Select your preferred location**
5. **Update security rules** with this:

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

## 🧪 **Testing Your Setup**

### **Test 1: Backend Firebase Connection**
```bash
curl -X GET http://localhost:3000/api/v1/firebase/config \
  -H "Authorization: Bearer YOUR_API_KEY"
```

### **Test 2: WordPress Plugin**
1. **Visit your WordPress site**
2. **Chat widget should appear**
3. **Send a test message**
4. **Check Firebase Console** → **Realtime Database** for the message

### **Test 3: Mobile App Notifications**
1. **Build and install mobile app**
2. **Login with admin credentials**
3. **Visit WordPress site** → Should trigger notification
4. **Send message from website** → Should trigger notification

### **Test 4: Real-time Messaging**
1. **Open mobile app chat list**
2. **Send message from website**
3. **Message should appear instantly in mobile app**
4. **Reply from mobile app**
5. **Reply should appear instantly on website**

## 🔧 **Troubleshooting**

### **Common Issues:**

**1. "Firebase configuration is incomplete"**
- Check that all Firebase config values are correct in WordPress settings
- Verify the JSON format is valid

**2. "No admin tokens found for notification"**
- Make sure mobile app is logged in
- Check that FCM token registration is working

**3. "Permission denied" in Firebase**
- Verify Firebase security rules are set correctly
- Check that database is in test mode initially

**4. Mobile app won't build**
- Ensure `google-services.json` is in `android/app/` folder
- Ensure `GoogleService-Info.plist` is in `ios/Runner/` folder

## ✅ **Success Checklist**

- [ ] Backend `.env` configured with Firebase settings
- [ ] Firebase Service Account key added to backend
- [ ] WordPress plugin installed and Firebase configured
- [ ] Mobile app builds successfully for Android and iOS
- [ ] Firebase Realtime Database created with security rules
- [ ] Test message sent from website appears in Firebase
- [ ] Mobile app receives push notifications
- [ ] Real-time messaging works between website and mobile app

## 🎉 **You're Ready!**

Once all tests pass, your WisLiveChat system is fully operational with:

- ✅ **Real-time messaging** between website visitors and mobile admin
- ✅ **Push notifications** for visitor activity and messages
- ✅ **Synchronized chat sessions** across all platforms
- ✅ **Professional mobile app** for chat management
- ✅ **Customizable WordPress widget**

**Your customers can now have seamless real-time conversations!** 🚀

## 📞 **Need Help?**

If you encounter any issues:
1. Check the troubleshooting section above
2. Verify all configuration values match exactly
3. Test each component individually
4. Check Firebase Console for any error messages

**Your Firebase project `wis-livechat` is ready for production!**
