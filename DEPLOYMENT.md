# WisChat Deployment Guide

This guide covers deploying the complete WisChat system including the backend API, WordPress plugin, and mobile app.

## 🚀 Quick Start

### Prerequisites
- Node.js 18+ and npm
- PostgreSQL 12+
- Redis 6+
- PHP 7.4+ (for WordPress plugin)
- Flutter 3.0+ (for mobile app)
- Docker (optional)

## 🖥️ Backend Deployment

### Environment Setup

1. **Clone and setup**
   ```bash
   git clone <repository-url>
   cd wischat/backend
   npm install
   ```

2. **Environment configuration**
   ```bash
   cp .env.example .env
   ```

   Configure your `.env` file:
   ```env
   # Server
   PORT=3000
   NODE_ENV=production
   
   # Database
   DB_HOST=localhost
   DB_PORT=5432
   DB_NAME=wischat
   DB_USER=your_username
   DB_PASSWORD=your_password
   
   # Redis
   REDIS_HOST=localhost
   REDIS_PORT=6379
   
   # JWT
   JWT_SECRET=your_super_secret_key
   JWT_EXPIRES_IN=24h
   REFRESH_TOKEN_EXPIRES_IN=7d
   
   # File Upload
   MAX_FILE_SIZE=10485760
   UPLOAD_PATH=./uploads
   
   # CORS
   CORS_ORIGIN=https://yourwebsite.com
   ```

3. **Database setup**
   ```bash
   npm run db:migrate
   npm run db:seed
   ```

### Production Deployment Options

#### Option 1: PM2 (Recommended)
```bash
npm install -g pm2
pm2 start ecosystem.config.js
pm2 startup
pm2 save
```

#### Option 2: Docker
```bash
docker build -t wischat-backend .
docker run -d -p 3000:3000 --name wischat-api wischat-backend
```

#### Option 3: Docker Compose
```yaml
version: '3.8'
services:
  api:
    build: ./backend
    ports:
      - "3000:3000"
    environment:
      - NODE_ENV=production
    depends_on:
      - postgres
      - redis
  
  postgres:
    image: postgres:13
    environment:
      POSTGRES_DB: wischat
      POSTGRES_USER: wischat
      POSTGRES_PASSWORD: password
    volumes:
      - postgres_data:/var/lib/postgresql/data
  
  redis:
    image: redis:6-alpine
    
volumes:
  postgres_data:
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name api.yourwebsite.com;
    
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
```

## 🔌 WordPress Plugin Deployment

### Manual Installation

1. **Upload plugin files**
   ```bash
   # Zip the plugin directory
   cd wordpress-plugin
   zip -r wischat.zip .
   ```

2. **Install via WordPress Admin**
   - Go to Plugins → Add New → Upload Plugin
   - Upload the zip file
   - Activate the plugin

3. **Configure settings**
   - Navigate to WisChat → Settings
   - Enter your API endpoint: `https://api.yourwebsite.com`
   - Generate and enter API key from backend admin
   - Customize widget appearance

### FTP Installation
```bash
# Upload via FTP to your WordPress installation
scp -r wordpress-plugin/ user@yourserver:/path/to/wordpress/wp-content/plugins/wischat/
```

### Plugin Configuration

1. **API Settings**
   - API Endpoint: Your backend server URL
   - API Key: Generated from backend admin panel

2. **Widget Customization**
   - Position: bottom-right, bottom-left, top-right, top-left
   - Colors: Primary, secondary, text colors
   - Theme: light, dark, auto

3. **Behavior Settings**
   - Working hours configuration
   - Pre-chat form fields
   - GDPR compliance options
   - File upload settings

## 📱 Mobile App Deployment

### Android Deployment

1. **Build APK**
   ```bash
   cd mobile-app
   flutter build apk --release
   ```

2. **Build App Bundle (for Play Store)**
   ```bash
   flutter build appbundle --release
   ```

3. **Sign the app**
   - Create keystore file
   - Configure `android/key.properties`
   - Update `android/app/build.gradle`

### iOS Deployment

1. **Build for iOS**
   ```bash
   flutter build ios --release
   ```

2. **Archive in Xcode**
   - Open `ios/Runner.xcworkspace` in Xcode
   - Select "Any iOS Device" as target
   - Product → Archive
   - Upload to App Store Connect

### Firebase Configuration

1. **Android Setup**
   - Add `google-services.json` to `android/app/`
   - Configure Firebase project

2. **iOS Setup**
   - Add `GoogleService-Info.plist` to `ios/Runner/`
   - Configure Firebase project

### App Configuration

Update API endpoint in the app:
```dart
// lib/constants/app_constants.dart
static const String defaultApiBaseUrl = 'https://api.yourwebsite.com';
```

## 🔒 Security Considerations

### Backend Security
- Use HTTPS in production
- Configure CORS properly
- Set strong JWT secrets
- Enable rate limiting
- Use environment variables for secrets
- Regular security updates

### Database Security
- Use strong passwords
- Enable SSL connections
- Regular backups
- Restrict network access

### WordPress Security
- Keep WordPress updated
- Use strong admin passwords
- Enable two-factor authentication
- Regular plugin updates

## 📊 Monitoring & Maintenance

### Health Checks
```bash
# Check API health
curl https://api.yourwebsite.com/health

# Check database connection
curl https://api.yourwebsite.com/api/v1/health/db
```

### Logging
- Configure Winston for structured logging
- Set up log rotation
- Monitor error rates
- Set up alerts for critical errors

### Backup Strategy
- Database: Daily automated backups
- File uploads: Regular sync to cloud storage
- Configuration: Version control all config files

### Performance Monitoring
- Monitor API response times
- Track WebSocket connection counts
- Monitor database performance
- Set up alerts for high resource usage

## 🚨 Troubleshooting

### Common Issues

1. **WebSocket connection fails**
   - Check CORS configuration
   - Verify proxy settings
   - Check firewall rules

2. **Database connection errors**
   - Verify credentials
   - Check network connectivity
   - Ensure database is running

3. **File upload issues**
   - Check file permissions
   - Verify upload directory exists
   - Check file size limits

4. **WordPress plugin not working**
   - Verify API endpoint is accessible
   - Check API key configuration
   - Review browser console for errors

### Debug Commands
```bash
# Check backend logs
pm2 logs wischat-api

# Test database connection
npm run db:test

# Check Redis connection
redis-cli ping

# WordPress debug mode
# Add to wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📈 Scaling Considerations

### Horizontal Scaling
- Use load balancer for multiple API instances
- Configure Redis for session sharing
- Use CDN for static assets
- Database read replicas

### Performance Optimization
- Enable Redis caching
- Optimize database queries
- Use connection pooling
- Implement rate limiting

### High Availability
- Multiple server instances
- Database clustering
- Redis clustering
- Health check endpoints

## 🔄 Updates & Maintenance

### Backend Updates
```bash
git pull origin main
npm install
npm run db:migrate
pm2 restart wischat-api
```

### WordPress Plugin Updates
- Upload new plugin files
- Deactivate and reactivate plugin
- Clear any caches

### Mobile App Updates
- Build new version
- Test thoroughly
- Deploy to app stores
- Notify users of updates

---

For additional support, please refer to the main [README.md](README.md) or contact support.
