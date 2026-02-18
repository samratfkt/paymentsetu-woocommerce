# PaymentSetu Gateway for WooCommerce

Accept **UPI payments** (GPay, PhonePe, Paytm, etc.) in your WooCommerce store via the [PaymentSetu](https://paymentsetu.com) payment gateway. Customers are redirected to a hosted UPI QR page to complete payment securely.

---

## Download

**Plugin ZIP (v1.0):**  
[https://github.com/samratfkt/paymentsetu-woocommerce/releases/download/v1.0/paymentsetu-gateway.zip](https://github.com/samratfkt/paymentsetu-woocommerce/releases/download/v1.0/paymentsetu-gateway.zip)

---

## Requirements

| Requirement        | Version  |
|--------------------|----------|
| WordPress          | 5.8+     |
| WooCommerce        | 6.0+     |
| PHP                | 7.4+     |

The plugin is compatible with **WooCommerce HPOS** (High-Performance Order Storage) and **Cart & Checkout Blocks**.

---

## Step-by-step installation

### Step 1: Download the plugin

1. Open the download link above and save `paymentsetu-gateway.zip` to your computer.

### Step 2: Install in WordPress

1. Log in to your WordPress admin.
2. Go to **Plugins → Add New**.
3. Click **Upload Plugin** and choose `paymentsetu-gateway.zip`.
4. Click **Install Now**, then **Activate Plugin**.

**Alternative (via FTP/cPanel):**

1. Unzip `paymentsetu-gateway.zip` on your computer.
2. Upload the `paymentsetu-gateway` folder to `wp-content/plugins/`.
3. In WordPress, go to **Plugins**, find **PaymentSetu Gateway for WooCommerce** and click **Activate**.

### Step 3: Enable and configure the gateway

1. Go to **WooCommerce → Settings → Payments**.
2. Find **PaymentSetu** (or **UPI / QR Code**) and turn it **On**.
3. Click **Manage** (or the gateway name) to open its settings.

### Step 4: Add your API credentials

1. In the PaymentSetu settings page, under **API Credentials**:
   - **API Key:** Paste your PaymentSetu API key from your [PaymentSetu dashboard](https://paymentsetu.com) (API Credentials section).
2. Optionally set:
   - **Payment Method Title** – e.g. “UPI / QR Code” (shown at checkout).
   - **Description** – Short text shown under the payment method.
   - **Order ID Prefix** – Optional prefix for order IDs sent to PaymentSetu (e.g. `SHOP-`).
3. Click **Save changes**.

### Step 5: Set up the webhook (important)

For orders to be marked **Paid** automatically after a successful UPI payment, you must configure the webhook in PaymentSetu:

1. In the PaymentSetu settings page in WooCommerce, find the **Webhook URL** section.
2. Copy the URL shown (it looks like:  
   `https://yoursite.com/wp-json/paymentsetu/v1/webhook`).
3. Log in to your [PaymentSetu dashboard](https://paymentsetu.com).
4. Go to **Webhook Settings** (or equivalent).
5. Paste the webhook URL and save.

Without this step, payments may succeed on PaymentSetu but order status in WooCommerce might not update automatically.

---

## Verification

- On the PaymentSetu gateway settings page, if your **API Key** is valid, you’ll see **Remaining credits** and **Subscription** status.
- Place a test order and choose the PaymentSetu/UPI option; you should be redirected to the UPI QR page. After paying (or using a test flow), the order should move to **Processing** or **Completed** when the webhook is set up correctly.

---

## Troubleshooting

| Issue | What to check |
|-------|----------------|
| Gateway not listed | WooCommerce is active; plugin is activated; gateway is enabled under **WooCommerce → Settings → Payments**. |
| “PaymentSetu is not configured” | API Key is entered and saved in the gateway settings. |
| Order not updating after payment | Webhook URL is set in the PaymentSetu dashboard and matches the URL shown in WooCommerce (use HTTPS). |
| Credits / subscription not shown | API Key is correct and has access to the credits API. |

---

## Support

- **PaymentSetu:** [https://paymentsetu.com](https://paymentsetu.com)  
- **Plugin:** PaymentSetu Gateway for WooCommerce v1.0
