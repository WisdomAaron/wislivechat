#!/usr/bin/env node

/**
 * WisChat Distribution Packager
 * 
 * Creates distribution packages for all components
 */

const fs = require('fs');
const path = require('path');
const archiver = require('archiver');
const { execSync } = require('child_process');

const DIST_DIR = 'dist';
const VERSION = '1.0.0';

console.log('📦 Creating WisChat Distribution Packages...\n');

// Create dist directory
if (!fs.existsSync(DIST_DIR)) {
    fs.mkdirSync(DIST_DIR, { recursive: true });
}

// Package 1: WordPress Plugin
console.log('🔌 Packaging WordPress Plugin...');
packageWordPressPlugin();

// Package 2: Backend Source Code
console.log('🖥️  Packaging Backend Source...');
packageBackend();

// Package 3: Mobile App Source
console.log('📱 Packaging Mobile App Source...');
packageMobileApp();

// Package 4: Complete Package
console.log('📦 Creating Complete Package...');
createCompletePackage();

// Generate distribution info
generateDistributionInfo();

console.log('\n✅ All packages created successfully!');
console.log('\n📁 Distribution files:');
console.log(`   ${DIST_DIR}/wischat-wordpress-plugin-v${VERSION}.zip`);
console.log(`   ${DIST_DIR}/wischat-backend-v${VERSION}.zip`);
console.log(`   ${DIST_DIR}/wischat-mobile-app-v${VERSION}.zip`);
console.log(`   ${DIST_DIR}/wischat-complete-v${VERSION}.zip`);
console.log(`   ${DIST_DIR}/DISTRIBUTION_INFO.md`);
console.log('\n🔥 Firebase Integration Complete!');
console.log('📖 Setup Guide: FIREBASE_INTEGRATION_GUIDE.md');
console.log('🎯 Ready for real-time chat with push notifications!');

function packageWordPressPlugin() {
    const output = fs.createWriteStream(path.join(DIST_DIR, `wischat-wordpress-plugin-v${VERSION}.zip`));
    const archive = archiver('zip', { zlib: { level: 9 } });
    
    archive.pipe(output);
    archive.directory('wordpress-plugin', 'wischat');
    archive.finalize();
}

function packageBackend() {
    const output = fs.createWriteStream(path.join(DIST_DIR, `wischat-backend-v${VERSION}.zip`));
    const archive = archiver('zip', { zlib: { level: 9 } });
    
    archive.pipe(output);
    archive.directory('backend', 'wischat-backend');
    archive.file('CLOUD_DEPLOYMENT.md', { name: 'DEPLOYMENT.md' });
    archive.finalize();
}

function packageMobileApp() {
    const output = fs.createWriteStream(path.join(DIST_DIR, `wischat-mobile-app-v${VERSION}.zip`));
    const archive = archiver('zip', { zlib: { level: 9 } });
    
    archive.pipe(output);
    archive.directory('mobile-app', 'wischat-mobile-app');
    archive.finalize();
}

function createCompletePackage() {
    const output = fs.createWriteStream(path.join(DIST_DIR, `wischat-complete-v${VERSION}.zip`));
    const archive = archiver('zip', { zlib: { level: 9 } });
    
    archive.pipe(output);
    
    // Add all components
    archive.directory('backend', 'backend');
    archive.directory('wordpress-plugin', 'wordpress-plugin');
    archive.directory('mobile-app', 'mobile-app');
    
    // Add documentation
    archive.file('README.md', { name: 'README.md' });
    archive.file('CLOUD_DEPLOYMENT.md', { name: 'CLOUD_DEPLOYMENT.md' });
    archive.file('BUSINESS_SETUP.md', { name: 'BUSINESS_SETUP.md' });
    archive.file('DEPLOYMENT.md', { name: 'DEPLOYMENT.md' });
    
    // Add build scripts
    archive.file('build-plugin.js', { name: 'build-plugin.js' });
    archive.file('package-for-distribution.js', { name: 'package-for-distribution.js' });
    
    archive.finalize();
}

function generateDistributionInfo() {
    const info = `# WisChat Distribution Information

## Package Contents

### 1. WordPress Plugin (wischat-wordpress-plugin-v${VERSION}.zip)
- **Ready for installation** on any WordPress site
- **Firebase real-time integration** for instant messaging
- **Enhanced settings panel** with Firebase configuration
- **Complete widget customization** (colors, themes, messages)
- **Multi-language support** (English, French, Spanish, German)
- **Push notification triggers** for mobile admin

**Installation:**
1. Upload to WordPress via Plugins → Add New → Upload
2. Activate the plugin
3. Configure API settings in WisChat → Settings
4. Add Firebase configuration for real-time features

### 2. Backend API (wischat-backend-v${VERSION}.zip)
- **Complete Node.js server** with all features
- **Database schemas** and migrations
- **Docker configuration** for easy deployment
- **Production-ready** with security features

**Deployment Options:**
- DigitalOcean App Platform (recommended)
- Heroku
- AWS/Google Cloud
- Self-hosted with Docker

### 3. Mobile App (wischat-mobile-app-v${VERSION}.zip)
- **Flutter source code** for iOS and Android
- **Firebase real-time database** integration
- **Push notifications (FCM)** for visitor activity and messages
- **Material Design 3** UI with dark/light themes
- **Real-time chat management** with live message sync
- **Notification handling** for background and foreground states

**Build Commands:**
\`\`\`bash
flutter build appbundle --release  # Android
flutter build ios --release        # iOS
\`\`\`

**Firebase Setup Required:**
- Add google-services.json (Android)
- Add GoogleService-Info.plist (iOS)

### 4. Complete Package (wischat-complete-v${VERSION}.zip)
- **All components** in one package
- **Complete documentation**
- **Business setup guide**
- **Deployment scripts**

## Quick Start

1. **Deploy Backend:**
   - Follow CLOUD_DEPLOYMENT.md
   - Use DigitalOcean for easiest setup
   - Cost: ~$50-80/month

2. **Install WordPress Plugin:**
   - Upload wischat-wordpress-plugin.zip
   - Configure with your API endpoint
   - Generate API key from backend admin

3. **Build Mobile Apps:**
   - Set up Flutter development environment
   - Update API endpoint in constants
   - Build for app stores

## Business Model

### SaaS Pricing (Recommended)
- **Free:** 100 messages/month
- **Starter:** $19/month - 1,000 messages
- **Pro:** $49/month - 10,000 messages
- **Enterprise:** $199/month - Unlimited

### One-time License
- **Single Site:** $99
- **Multi-site:** $299
- **Developer:** $599

## Revenue Potential

**Conservative Projections:**
- Month 1-3: $0-500
- Month 4-6: $500-2,000
- Month 7-12: $2,000-10,000
- Year 2: $10,000-50,000+

## Support & Documentation

- **Setup Guide:** DEPLOYMENT.md
- **Business Guide:** BUSINESS_SETUP.md
- **Cloud Deployment:** CLOUD_DEPLOYMENT.md
- **API Documentation:** Built into backend

## Technical Requirements

**Backend:**
- Node.js 18+
- PostgreSQL 12+
- Redis 6+
- 1GB RAM minimum

**WordPress Plugin:**
- WordPress 5.0+
- PHP 7.4+
- Active internet connection

**Mobile App:**
- Flutter 3.0+
- Android SDK / Xcode
- Firebase account (for notifications)

## License

- **Backend & Mobile:** MIT License (modify freely)
- **WordPress Plugin:** Commercial license template included
- **Documentation:** Creative Commons

## Next Steps

1. **Deploy backend** to cloud provider
2. **Test WordPress plugin** on demo site
3. **Set up payment processing** (Stripe recommended)
4. **Create marketing website**
5. **Submit to app stores**
6. **Launch marketing campaigns**

## Support

For technical support during setup:
- Check documentation files
- Review troubleshooting sections
- Test each component individually

---

**WisChat v${VERSION}** - Complete Live Chat Solution
Built for entrepreneurs and agencies who want to offer professional chat solutions.

Ready to launch your SaaS business? 🚀
`;

    fs.writeFileSync(path.join(DIST_DIR, 'DISTRIBUTION_INFO.md'), info);
}
