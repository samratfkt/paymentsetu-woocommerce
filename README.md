# PaymentSetu Gateway for WooCommerce

[![PaymentSetu](https://img.shields.io/badge/PaymentSetu-Payment%20Gateway-4CAF50?style=for-the-badge)](https://paymentsetu.com)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-Compatible-blue?style=for-the-badge)](https://woocommerce.com)
[![Version](https://img.shields.io/badge/version-1.0-orange?style=for-the-badge)](https://github.com)

Accept UPI payments in your WooCommerce store with PaymentSetu - India's leading
payment verification platform.

> ### 💰 **Zero Transaction Fees! Pay Only a Fixed Monthly Charge & Keep 100% of Your Revenue**
>
> Unlike traditional payment platforms that charge per transaction, PaymentSetu
> offers a simple flat monthly subscription. **No hidden fees. No percentage
> cuts. Just unlimited verifications for one fixed price!**

---

## 🚀 Features

- ✅ **UPI Payments** - Accept payments via UPI QR codes (GPay, PhonePe, Paytm,
  etc.)
- ✅ **Easy Integration** - Simple setup in minutes
- ✅ **Automatic Webhook** - Real-time payment notifications
- ✅ **INR Currency Support** - Built for Indian businesses
- ✅ **HPOS & Blocks** - Compatible with WooCommerce HPOS and Cart & Checkout
  Blocks
- ✅ **Secure** - Industry-standard security practices

---

## 📋 Prerequisites

Before you begin, make sure you have:

1. WordPress (version 5.8 or higher)
2. WooCommerce (version 6.0 or higher) installed and active
3. PHP 7.4 or higher

---

## 🛠️ Installation Guide

Follow these simple steps to get started — from downloading the plugin to
accepting your first payment.

### Step 1: Create a PaymentSetu Account

1. Go to [https://paymentsetu.com](https://paymentsetu.com)
2. Click on **Sign Up** and create your account
3. Complete the registration and log in to your dashboard

### Step 2: Download the Plugin

📦
**[Download paymentsetu-gateway.zip (v1.0)](https://github.com/samratfkt/paymentsetu-woocommerce/releases/download/v1.0/paymentsetu-gateway.zip)**

### Step 3: Install the Plugin in WordPress

#### ⚡ Quick Install (Recommended)

1. Log in to your WordPress admin panel
2. Go to **Plugins** → **Add New**
3. Click **Upload Plugin** and choose the downloaded
   **`paymentsetu-gateway.zip`**
4. Click **Install Now**, then **Activate Plugin**
5. You're done with this step!

#### 🔧 Manual Install

If you prefer to upload files manually (e.g. via FTP or cPanel):

1. Extract the zip on your computer
2. Upload the **`paymentsetu-gateway`** folder to your WordPress
   **`wp-content/plugins/`** directory
3. In WordPress, go to **Plugins**, find **PaymentSetu Gateway for WooCommerce**
   and click **Activate**

**Folder structure after upload:**

```
wp-content/
└── plugins/
    └── paymentsetu-gateway/
        ├── paymentsetu-gateway.php
        ├── includes/
        └── assets/
```

### Step 4: Enable the Gateway in WooCommerce

1. Go to **WooCommerce** → **Settings** → **Payments**
2. Find **PaymentSetu** (or **UPI / QR Code**) in the list
3. Toggle it **On** to enable the gateway
4. Click **Manage** to open the PaymentSetu settings page

### Step 5: Copy the Webhook URL from WooCommerce

On the PaymentSetu settings page in WooCommerce, you will see a **Webhook URL**
displayed.

1. The webhook URL will look like this:
   ```
   https://yourdomain.com/wp-json/paymentsetu/v1/webhook
   ```
2. **Copy this URL** — you will need it in the next step

> 💡 **Tip:** You can also open this page from **Plugins** → find PaymentSetu
> Gateway → click **Settings**.

### Step 6: Get Your API Key from PaymentSetu Dashboard

Now you need to register the webhook URL and generate your API key.

1. Go to
   [https://paymentsetu.com/developer/api-credentials](https://paymentsetu.com/developer/api-credentials)
2. Log in to your PaymentSetu account
3. You will see a form to **Create New API Key**

#### Fill in the form:

- **NAME:** Enter a name for this API key (e.g., "WooCommerce Production" or "My
  WooCommerce Store")
- **WEBHOOK URL (OPTIONAL):** Paste the webhook URL you copied from WooCommerce
  in Step 5

4. Click the **Create API Key** button
5. Your API key will be generated automatically
6. **Copy the API key** — you will need it in the next step

> ⚠️ **Important:** Keep your API key secure and never share it publicly.

### Step 7: Add API Key to WooCommerce

Now return to your WordPress admin panel:

1. Go to **WooCommerce** → **Settings** → **Payments** → **PaymentSetu** (click
   **Manage**)
2. In the **API Key** field, paste the API key you copied from PaymentSetu
3. (Optional) Edit **Payment Method Title** (e.g. "UPI / QR Code") and
   **Description** shown at checkout
4. (Optional) Set **Order ID Prefix** if you use multiple stores with the same
   API key (e.g. "SHOP-")
5. Click **Save changes**

---

## ✅ You're Done!

The PaymentSetu gateway is now active on your WooCommerce store. Customers will
see the UPI / QR Code option at checkout and will be redirected to PaymentSetu's
secure payment page to complete their order.

---

## 💳 How It Works

1. Customer adds products to cart and proceeds to checkout
2. Customer selects **UPI / QR Code** (PaymentSetu) as payment method
3. Customer is redirected to PaymentSetu's payment page
4. Customer scans the UPI QR code and completes payment
5. PaymentSetu verifies the payment and sends a webhook notification to
   WooCommerce
6. Order is automatically marked as paid in WooCommerce

---

## 🔧 Configuration Options

| Option                   | Description                                                                                |
| ------------------------ | ------------------------------------------------------------------------------------------ |
| **Enable / Disable**     | Turn the PaymentSetu gateway on or off                                                     |
| **Payment Method Title** | Label shown at checkout (e.g. "UPI / QR Code")                                             |
| **Description**          | Short text shown below the payment method at checkout                                      |
| **API Key**              | Your PaymentSetu API key from the dashboard                                                |
| **Webhook URL**          | Auto-generated URL for receiving payment notifications (set this in PaymentSetu dashboard) |
| **Order ID Prefix**      | Optional prefix for order IDs sent to PaymentSetu (e.g. "SHOP-")                           |

---

## 🆘 Troubleshooting

### Payment not updating in WooCommerce?

1. Check that the webhook URL is correctly configured in your PaymentSetu
   dashboard
2. Verify that the API key in WooCommerce matches the one in PaymentSetu
3. Ensure your site uses HTTPS so the webhook can be reached

### Getting "PaymentSetu is not configured" at checkout?

- Enter and save your API key in **WooCommerce** → **Settings** → **Payments** →
  **PaymentSetu** → **Manage**
- Click **Save changes** after pasting the API key

### Gateway not listed under Payment Gateways?

- Make sure WooCommerce is active
- Activate the **PaymentSetu Gateway for WooCommerce** plugin under **Plugins**
- Enable the gateway under **WooCommerce** → **Settings** → **Payments**

### Credits or subscription status not showing in settings?

- Verify your API key is correct and has access to the credits API
- Check that your PaymentSetu account is in good standing

---

## 📞 Support

- **PaymentSetu Support:** Visit
  [https://paymentsetu.com/support](https://paymentsetu.com/support)
- **Documentation:**
  [https://paymentsetu.com/developer/api-credentials](https://paymentsetu.com/developer/api-credentials)

---

## 📄 License

This plugin is provided as-is for use with PaymentSetu payment verification
services.

---

## 🔗 Links

- [PaymentSetu Website](https://paymentsetu.com)
- [Create PaymentSetu Account](https://paymentsetu.com)
- [API Credentials Dashboard](https://paymentsetu.com/developer/api-credentials)
- [WooCommerce Documentation](https://woocommerce.com/documentation/)

---

<div align="center">

**Made with ❤️ for WooCommerce users**

[Get Started](https://paymentsetu.com) |
[Documentation](https://paymentsetu.com/developer/api-credentials) |
[Support](https://paymentsetu.com/support)

</div>
