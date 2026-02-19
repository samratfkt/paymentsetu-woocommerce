<?php
defined('ABSPATH') || exit;

/**
 * Registers a dedicated REST endpoint for PaymentSetu webhooks and processes
 * incoming payment notifications.
 *
 * Endpoint: POST /wp-json/paymentsetu/v1/webhook
 */
class PaymentSetu_Webhook
{

	/** Meta key used to store the PaymentSetu transaction UTR on an order. */
	const META_TXN_UTR = '_paymentsetu_txn_utr';

	/** Meta key used to store the raw webhook payload for debugging. */
	const META_PAYLOAD = '_paymentsetu_webhook_payload';

	public static function init(): void
	{
		add_action('rest_api_init', [__CLASS__, 'register_route']);
	}

	public static function register_route(): void
	{
		register_rest_route(
			'paymentsetu/v1',
			'/webhook',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [__CLASS__, 'handle'],
				'permission_callback' => '__return_true', // Auth is done via HMAC below.
			]
		);
	}

	/**
	 * Main webhook handler.
	 */
	public static function handle(WP_REST_Request $request): WP_REST_Response
	{
		$gateway_settings = get_option('woocommerce_paymentsetu_settings', []);
		$api_key          = $gateway_settings['api_key'] ?? '';

		if (empty($api_key)) {
			return new WP_REST_Response(['error' => 'Gateway not configured.'], 500);
		}

		// ── Signature verification ──────────────────────────────────────────────
		$signature = $request->get_header('x-paymentsetu-signature');
		$timestamp = $request->get_header('x-paymentsetu-timestamp');
		$raw_body  = $request->get_body();

		if (empty($signature) || empty($timestamp)) {
			return new WP_REST_Response(['error' => 'Missing security headers.'], 401);
		}

		$expected = hash_hmac('sha256', $timestamp . '.' . $raw_body, $api_key);

		if (! hash_equals($expected, $signature)) {
			wc_get_logger()->warning(
				'PaymentSetu webhook: invalid signature.',
				['source' => 'paymentsetu-gateway']
			);
			return new WP_REST_Response(['error' => 'Invalid signature.'], 401);
		}

		// ── Parse payload ────────────────────────────────────────────────────────
		$payload = json_decode($raw_body, true);

		if (! is_array($payload)) {
			return new WP_REST_Response(['error' => 'Invalid JSON payload.'], 400);
		}

		$wc_order_id = $payload['order_id'] ?? '';
		$status      = $payload['status']   ?? '';
		$amount      = isset($payload['amount']) ? (int) $payload['amount'] : 0;
		$txn_utr     = $payload['txn_utr']  ?? '';

		if (empty($wc_order_id) || empty($status)) {
			return new WP_REST_Response(['error' => 'Missing required fields.'], 400);
		}

		// ── Look up WooCommerce order ─────────────────────────────────────────────
		$order = wc_get_order($wc_order_id);

		if (! $order) {
			wc_get_logger()->warning(
				sprintf('PaymentSetu webhook: order #%s not found.', $wc_order_id),
				['source' => 'paymentsetu-gateway']
			);
			return new WP_REST_Response(['error' => 'Order not found.'], 404);
		}

		// ── Guard: only process orders handled by this gateway ───────────────────
		if ($order->get_payment_method() !== 'paymentsetu') {
			return new WP_REST_Response(['error' => 'Order not associated with PaymentSetu.'], 400);
		}

		// ── Guard: skip already-completed orders ─────────────────────────────────
		if ($order->is_paid()) {
			return new WP_REST_Response(['message' => 'Order already processed.'], 200);
		}

		// ── Store raw payload for debugging ──────────────────────────────────────
		$order->update_meta_data(self::META_PAYLOAD, $payload);

		// ── Process status ───────────────────────────────────────────────────────
		if ($status === 'success') {
			// Validate amount matches order total (amount sent in paisa).
			$order_total_paisa = (int) round($order->get_total() * 100);

			if ($amount !== $order_total_paisa) {
				$order->add_order_note(
					sprintf(
						__('PaymentSetu: amount mismatch. Expected ₹%s, received ₹%s. Order put on-hold for manual review.', 'paymentsetu-gateway'),
						number_format($order->get_total(), 2),
						number_format($amount / 100, 2)
					)
				);
				$order->update_status('on-hold');
				$order->save();

				return new WP_REST_Response(['message' => 'Amount mismatch; order held.'], 200);
			}

			if (! empty($txn_utr)) {
				$order->update_meta_data(self::META_TXN_UTR, sanitize_text_field($txn_utr));
			}

			$order->payment_complete($txn_utr);
			$order->add_order_note(
				sprintf(
					/* translators: %1$s: UTR, %2$s: UPI ID, %3$s: transaction time */
					__('PaymentSetu: payment confirmed. UTR: %1$s | UPI ID: %2$s | Time: %3$s', 'paymentsetu-gateway'),
					esc_html($txn_utr),
					esc_html($payload['customer_upi_id'] ?? 'N/A'),
					esc_html($payload['txn_time']        ?? 'N/A')
				)
			);
		} elseif ($status === 'failed' || $status === 'expired') {
			$order->update_status(
				'failed',
				sprintf(
					__('PaymentSetu: payment %s.', 'paymentsetu-gateway'),
					$status
				)
			);
		} else {
			$order->add_order_note(
				sprintf(
					__('PaymentSetu: unhandled webhook status "%s".', 'paymentsetu-gateway'),
					sanitize_text_field($status)
				)
			);
		}

		$order->save();

		return new WP_REST_Response(['message' => 'Webhook processed.'], 200);
	}
}
