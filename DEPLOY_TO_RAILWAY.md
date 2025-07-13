# 🚀 Deploy WisLiveChat Backend to Railway

## 🎯 **Why Railway?**
- ✅ **Free tier** - Perfect for testing
- ✅ **Instant deployment** - No complex setup
- ✅ **Global CDN** - Fast worldwide
- ✅ **Automatic HTTPS** - Secure by default
- ✅ **Custom domains** - Professional URLs

## 📋 **Deployment Steps (10 minutes)**

### **Step 1: Create Railway Account**
1. Go to: [railway.app](https://railway.app)
2. Click **"Start a New Project"**
3. Sign up with **GitHub** (recommended)

### **Step 2: Deploy from GitHub**
1. **Create GitHub repository** for your backend
2. **Upload backend folder** to GitHub
3. **Connect Railway to GitHub**
4. **Deploy automatically**

### **Step 3: Set Environment Variables**
Railway will need these environment variables:

```env
NODE_ENV=production
API_KEY=wischat_api_key_2025_secure_token_12345

# Firebase Configuration
FIREBASE_PROJECT_ID=wis-livechat
FIREBASE_PRIVATE_KEY_ID=f9c5784bc7e7bff3b96dba003dffca187bda0743
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDNs7syTicH+zOe\nPZiDNVw3pq0LwqbuAvBeoNZ/mfgFahQOw5wccSy2s85Rz3QkbV1FvkXCrw1SfeJP\n0TT575Il6waW4ensXyimtdwkJjdb4WS4crMln+YAFGN9j4szKJ/FnyuftSyqlp3T\nqdetPB8fi2x5ePhrhc+XK+q44qbnvttTlLeXkvVk3dLncowAl0+iQcF/MJM1rA2M\ncmB5ZUVFW+BzGgJyTq3+6h+RuA9g1TSvE0mDm84TSjX3FepeDPFuLXZ7rUJba3gw\nMeMJzPfKaSHB0Hp1QVxX8Z19BPeqJuNcYU2bqbJoeXzpPPRYuQ9bmoom95aOTrqC\nPAU4XWwNAgMBAAECggEAD3Z/z2/FilmNT0tlEBPuSzap0nT+9GxfIlXn2e66kLmh\nKVz1+sNdL5zDmS3Flslr1gbQGqrrDvTsksBwwYsLSvNoauYvnfenSCTqmAMRbUQ7\nvIvOwLjM4tulgPZN6foXTJVDA73sTiSFV7hwQJMA6e+ip1z7G6YS7VO9UMmk702s\njG7m8jqcCyiibF6aE0cPp0SQvlpw9uMneRh9Xyt8M/eQdLERdxXj9miKQc6934Mv\n0sdCt61rtvwtbI0Vhg3FUho9jxDsgrncvwuKa/yrrSwIaGSVjgqhMqwZzOkVWr1a\nNzdTnce2unwYOQ949pUILfGRv0utcgpnvS5qZAdyBwKBgQDtsTc1d4QKP49IAGPG\nennDHDIuqshTaXVNafisDPjCzgF/0n8E/wKHYUlpYzihdR4n0uLvNaDgKeRLVpfc\nHz3J5Mbb+JlmExjp97wHv76J+fWoSJQ4oIAnkJn5mpCExHtQUh/L24XyD55VBrhl\nBdVoQSciJFltG63nFw8oIEFatwKBgQDdi7zQ+T0FthhJvVr6vbxT/vGDCXPmCf4k\npVKGlUZf8E+f++eaRPQEgwxR+iuTh4aM7UfXM/cQSmuV1gPH4Q0TbuwJ1CRhiTth\nj/qFpsFOW7q2e6bxEputAePduEQpHh5CzeXMn/ub268u/u6QnrsU04h+Yz+giaFH\nbGoSxpY7WwKBgFeDFMGRtEKVriojQpjzxNrKCcvWwxS60h5jctPdnsUSVcj69hsf\nzI2NixrLFjGTzjt5UGYkB4wgwFXglt7LfdNUcMbSW6ASTezucgMkXIGuTvzbY/8J\nUMXAXoBgoO7Q7FnhMqW6uDEsAO5rH/7JzA2rVz4hxe7+7uMTQVhTr9w3AoGBAMIp\nIUWT6WMD/61AgmeVuWNz332T3zsQLuc27Gh+krqqBV6UipmtLJ53q2h884vMDqOb\noIZ+SFzCnB8o/Q9DsQ4C/GJwxaGlf6E1ab2QM4HU6oHbRKeqQz0Qlv9N6o6Okr7B\nbFA1NXGWyqVJTxL3ycWqzcjWeoDbnbjWzu+9kxRZAoGBAKpQYRsl1wu1iFoo4HJH\nHxWJZ+Hlh8NkR/KmqQ/KwgLtcR+ytGktW7ozApOIvySJnw9rAE5LszlnIF6sintM\n1heaXHu6aryZQkcBSF9pSzYiKHgsF9p9F7FLzxCvrfKZjr77iZg22QrsYdZKJME/\nS5Ye3E0Lm+oAuJ1niyVOCLDO\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-fbsvc@wis-livechat.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=115770184882480085382
FIREBASE_WEB_API_KEY=AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ
FIREBASE_DATABASE_URL=https://wis-livechat-default-rtdb.firebaseio.com
FIREBASE_MESSAGING_SENDER_ID=206365667705
```

### **Step 4: Get Your Production URL**
After deployment, Railway gives you a URL like:
`https://wischat-backend-production.railway.app`

### **Step 5: Update WordPress Plugin**
Use your new production URL in WordPress:
- **API Endpoint**: `https://your-railway-url.railway.app`
- **API Key**: `wischat_api_key_2025_secure_token_12345`

## 🎉 **Benefits of Railway Deployment**
- ✅ **Global availability** - Works worldwide
- ✅ **Automatic scaling** - Handles traffic spikes
- ✅ **SSL certificates** - Secure HTTPS
- ✅ **Easy updates** - Deploy new versions instantly
- ✅ **Professional URLs** - No localhost needed

## 💰 **Pricing**
- **Free tier**: Perfect for testing and small usage
- **Pro tier**: $5/month when you need more resources
- **No hidden costs** - Pay only for what you use

## 🔧 **Alternative: Heroku**
If you prefer Heroku:
1. Go to [heroku.com](https://heroku.com)
2. Create new app
3. Connect GitHub repository
4. Set environment variables
5. Deploy

## 📞 **Need Help?**
If you encounter any issues:
1. Check Railway logs for errors
2. Verify environment variables are set
3. Test endpoints with curl
4. Contact Railway support (excellent!)

**Your WisLiveChat backend will be globally accessible!** 🌍
