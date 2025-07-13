# 📱 MTN Mobile Money Integration Setup Guide

Complete guide to integrate MTN Mobile Money payments into your WisChat system for African markets.

## 🌍 Supported Countries & Currencies

MTN Mobile Money is available in these countries:

| Country | Currency | Country Code | Example Number |
|---------|----------|--------------|----------------|
| Uganda | UGX (Uganda Shillings) | 256 | 256701234567 |
| Ghana | GHS (Ghana Cedis) | 233 | 233241234567 |
| Cameroon | XAF (Central African Francs) | 237 | 237671234567 |
| Ivory Coast | XOF (West African Francs) | 225 | 225071234567 |
| Zambia | ZMW (Zambian Kwacha) | 260 | 260971234567 |
| South Africa | ZAR (South African Rand) | 27 | 27821234567 |

## 🚀 Step 1: MTN Developer Account Setup

### 1. Create MTN Developer Account

1. **Visit MTN Developer Portal**: [momodeveloper.mtn.com](https://momodeveloper.mtn.com)
2. **Sign up** for a developer account
3. **Verify your email** and complete profile
4. **Choose your target market** (country)

### 2. Subscribe to Collections API

1. **Go to Products** → **Collections**
2. **Subscribe** to the Collections API
3. **Note your Primary Key** (this is your Subscription Key)

### 3. Create API User and Key

**For Sandbox (Testing):**
```bash
# Create API User
curl -X POST \
  https://sandbox.momodeveloper.mtn.com/v1_0/apiuser \
  -H 'Content-Type: application/json' \
  -H 'Ocp-Apim-Subscription-Key: YOUR_SUBSCRIPTION_KEY' \
  -H 'X-Reference-Id: YOUR_UUID' \
  -d '{
    "providerCallbackHost": "your-domain.com"
  }'

# Create API Key
curl -X POST \
  https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/YOUR_UUID/apikey \
  -H 'Ocp-Apim-Subscription-Key: YOUR_SUBSCRIPTION_KEY'
```

**For Production:**
Contact MTN to get production credentials after testing.

## 🔧 Step 2: Configure WisChat Backend

### 1. Update Environment Variables

Add these to your `.env` file:

```env
# MTN Mobile Money Configuration
MTN_MOMO_BASE_URL=https://sandbox.momodeveloper.mtn.com
MTN_MOMO_SUBSCRIPTION_KEY=your_subscription_key_from_mtn_portal
MTN_MOMO_API_USER_ID=your_api_user_uuid
MTN_MOMO_API_KEY=your_api_key_from_mtn
MTN_MOMO_ENVIRONMENT=sandbox
MTN_MOMO_CALLBACK_URL=https://your-api-domain.com/api/v1/payments/mtn-callback
```

### 2. Install Required Dependencies

```bash
cd backend
npm install axios uuid
```

### 3. Run Database Migration

```bash
# Run the payments and subscriptions migration
psql -h localhost -U your_user -d wischat -f src/database/migrations/003_payments_subscriptions.sql
```

### 4. Test the Integration

```bash
# Start your backend server
npm run dev

# Test the API endpoints
curl -X GET http://localhost:3000/api/v1/payments/plans
```

## 💰 Step 3: Pricing Strategy for African Markets

### Recommended Pricing (Uganda - UGX)

```javascript
const ugandaPricing = {
  free: {
    price: 0,
    messages: 100,
    websites: 1
  },
  starter: {
    price: 50000, // ~$13.50 USD
    messages: 1000,
    websites: 3
  },
  professional: {
    price: 150000, // ~$40 USD
    messages: 10000,
    websites: 10
  },
  enterprise: {
    price: 500000, // ~$135 USD
    messages: -1, // Unlimited
    websites: -1  // Unlimited
  }
};
```

### Pricing for Other Countries

**Ghana (GHS):**
- Starter: 200 GHS (~$13.50)
- Professional: 600 GHS (~$40)
- Enterprise: 2000 GHS (~$135)

**South Africa (ZAR):**
- Starter: 250 ZAR (~$13.50)
- Professional: 750 ZAR (~$40)
- Enterprise: 2500 ZAR (~$135)

## 🧪 Step 4: Testing the Payment Flow

### 1. Test Account Validation

```bash
curl -X POST http://localhost:3000/api/v1/payments/validate-account \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "phoneNumber": "256701234567"
  }'
```

### 2. Test Subscription Payment

```bash
curl -X POST http://localhost:3000/api/v1/payments/subscribe \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "planId": "starter",
    "phoneNumber": "256701234567",
    "currency": "UGX"
  }'
```

### 3. Test Payment Status Check

```bash
curl -X GET http://localhost:3000/api/v1/payments/status/REFERENCE_ID \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 📱 Step 5: WordPress Plugin Integration

### 1. Enable Subscription Management

The WordPress plugin automatically includes the subscription management interface. Users can:

1. **View available plans** with local pricing
2. **Enter MTN Mobile Money number**
3. **Validate their account** before payment
4. **Complete payment** via SMS prompt
5. **Track payment status** in real-time

### 2. Customize for Local Market

Edit the subscription page to include local information:

```php
// In wordpress-plugin/admin/class-wischat-subscription-page.php
// Add local payment information, customer support numbers, etc.
```

## 🔄 Step 6: Production Deployment

### 1. Switch to Production Environment

```env
# Production MTN MoMo Configuration
MTN_MOMO_BASE_URL=https://momodeveloper.mtn.com
MTN_MOMO_ENVIRONMENT=production
MTN_MOMO_SUBSCRIPTION_KEY=your_production_subscription_key
MTN_MOMO_API_USER_ID=your_production_api_user_id
MTN_MOMO_API_KEY=your_production_api_key
MTN_MOMO_CALLBACK_URL=https://your-production-domain.com/api/v1/payments/mtn-callback
```

### 2. Set Up Webhooks

Configure your callback URL in MTN Developer Portal:
- **Callback URL**: `https://your-domain.com/api/v1/payments/mtn-callback`
- **Ensure HTTPS** is enabled
- **Test webhook delivery** with MTN

### 3. Compliance & Security

1. **Data Protection**: Ensure GDPR compliance
2. **PCI Compliance**: Follow payment security standards
3. **Local Regulations**: Check local financial regulations
4. **Customer Support**: Set up local support channels

## 📊 Step 7: Analytics & Monitoring

### 1. Track Key Metrics

Monitor these important metrics:

```javascript
// Payment success rate by country
// Average transaction value
// Customer acquisition cost
// Monthly recurring revenue
// Churn rate by plan
```

### 2. Set Up Alerts

```javascript
// Failed payment alerts
// High churn rate warnings
// Revenue milestone notifications
// Technical error alerts
```

## 🎯 Step 8: Marketing Strategy for African Markets

### 1. Local Partnerships

- **MTN Distributors**: Partner with local MTN agents
- **Tech Hubs**: Collaborate with innovation centers
- **Universities**: Offer student discounts
- **SME Associations**: Target small business groups

### 2. Localized Marketing

- **Local Languages**: Translate key materials
- **Local Currency**: Always show prices in local currency
- **Local Use Cases**: Highlight relevant business scenarios
- **Local Testimonials**: Feature African customer success stories

### 3. Payment Education

Many users may be new to online subscriptions:

- **How-to Videos**: Create payment tutorials
- **SMS Guides**: Send step-by-step SMS instructions
- **Customer Support**: Provide phone support in local languages
- **Demo Accounts**: Offer free trials to build confidence

## 🛠️ Step 9: Troubleshooting Common Issues

### 1. Payment Failures

**Common Causes:**
- Insufficient balance
- Incorrect PIN
- Network issues
- Account not active

**Solutions:**
- Clear error messages
- Retry mechanisms
- Alternative payment methods
- Customer support escalation

### 2. Integration Issues

**API Errors:**
```bash
# Check API credentials
curl -X GET https://sandbox.momodeveloper.mtn.com/collection/v1_0/account/balance \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Ocp-Apim-Subscription-Key: YOUR_KEY"

# Validate webhook endpoint
curl -X POST https://your-domain.com/api/v1/payments/mtn-callback \
  -H "Content-Type: application/json" \
  -d '{"test": "webhook"}'
```

### 3. Customer Support

**Set up support channels:**
- **WhatsApp Business**: Popular in Africa
- **SMS Support**: For basic queries
- **Phone Support**: Local numbers
- **Email Support**: For detailed issues

## 📈 Revenue Projections for African Markets

### Conservative Estimates (Uganda Market)

**Month 1-3:** $500-2,000
- 10-40 paying customers
- Average $50/month per customer

**Month 4-6:** $2,000-8,000
- 40-160 paying customers
- Word-of-mouth growth

**Month 7-12:** $8,000-25,000
- 160-500 paying customers
- Market penetration

**Year 2:** $25,000-100,000+
- Multi-country expansion
- Enterprise customers

### Growth Strategies

1. **Freemium Model**: Start with generous free tier
2. **Local Partnerships**: Work with MTN directly
3. **Referral Program**: Incentivize customer referrals
4. **Enterprise Sales**: Target larger organizations
5. **Multi-Country**: Expand to other MTN markets

## 🎉 Launch Checklist

- [ ] MTN Developer account created
- [ ] API credentials configured
- [ ] Database migration completed
- [ ] Payment flow tested in sandbox
- [ ] WordPress plugin configured
- [ ] Pricing localized for target market
- [ ] Customer support channels set up
- [ ] Marketing materials prepared
- [ ] Production environment configured
- [ ] Monitoring and analytics enabled

## 📞 Support Resources

**MTN Developer Support:**
- Portal: [momodeveloper.mtn.com](https://momodeveloper.mtn.com)
- Email: developer@mtn.com
- Documentation: [MTN MoMo API Docs](https://momodeveloper.mtn.com/docs)

**WisChat Support:**
- Email: support@wischat.com
- Documentation: This guide
- GitHub: Issues and discussions

---

**Ready to launch in African markets with MTN Mobile Money!** 🚀

This integration opens up access to over 280 million MTN Mobile Money users across Africa, providing a familiar and trusted payment method for your customers.
