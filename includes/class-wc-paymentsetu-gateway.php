<?php
defined( 'ABSPATH' ) || exit;

/**
 * PaymentSetu WooCommerce Payment Gateway.
 */
class WC_PaymentSetu_Gateway extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'paymentsetu';
		$this->method_title       = __( 'PaymentSetu', 'paymentsetu-gateway' );
		$this->method_description = __( 'Accept UPI payments securely via PaymentSetu. Customers are redirected to a hosted UPI QR page to complete payment.', 'paymentsetu-gateway' );
		$this->has_fields         = false;
		$this->supports           = [ 'products' ];

		// Load settings.
		$this->init_form_fields();
		$this->init_settings();

		// Map settings to properties.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );

		// Save settings hook.
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			[ $this, 'process_admin_options' ]
		);
	}

	// ── Admin Settings ────────────────────────────────────────────────────────

	public function init_form_fields(): void {
		$webhook_url = rest_url( 'paymentsetu/v1/webhook' );

		$this->form_fields = [
			'enabled'     => [
				'title'   => __( 'Enable / Disable', 'paymentsetu-gateway' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PaymentSetu Gateway', 'paymentsetu-gateway' ),
				'default' => 'no',
			],
			'title'       => [
				'title'       => __( 'Payment Method Title', 'paymentsetu-gateway' ),
				'type'        => 'text',
				'description' => __( 'Shown to the customer at checkout.', 'paymentsetu-gateway' ),
				'default'     => __( 'UPI / QR Code', 'paymentsetu-gateway' ),
				'desc_tip'    => true,
			],
			'description' => [
				'title'       => __( 'Description', 'paymentsetu-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Shown below the payment method title at checkout.', 'paymentsetu-gateway' ),
				'default'     => __( 'Pay securely using UPI (GPay, PhonePe, Paytm, etc.) via a QR code.', 'paymentsetu-gateway' ),
				'desc_tip'    => true,
			],
			'api_credentials' => [
				'title' => __( 'API Credentials', 'paymentsetu-gateway' ),
				'type'  => 'title',
			],
			'api_key'     => [
				'title'       => __( 'API Key', 'paymentsetu-gateway' ),
				'type'        => 'password',
				'description' => __( 'Your PaymentSetu API key. Available on the API Credentials page in your dashboard.', 'paymentsetu-gateway' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'webhook_info' => [
				'title'       => __( 'Webhook URL', 'paymentsetu-gateway' ),
				'type'        => 'title',
				'description' => sprintf(
					/* translators: %s: webhook URL */
					__( 'Set this URL in your PaymentSetu dashboard under Webhook Settings: <br><code>%s</code>', 'paymentsetu-gateway' ),
					esc_url( $webhook_url )
				),
			],
			'order_prefix' => [
				'title'       => __( 'Order ID Prefix', 'paymentsetu-gateway' ),
				'type'        => 'text',
				'description' => __( 'Optional prefix added to WooCommerce order IDs when sending to PaymentSetu (e.g. "SHOP-"). Helps avoid conflicts if you use multiple stores with the same API key.', 'paymentsetu-gateway' ),
				'default'     => '',
				'desc_tip'    => true,
			],
		];
	}

	/**
	 * Show API credit balance in admin settings.
	 */
	public function admin_options(): void {
		echo '<h2>' . esc_html( $this->method_title ) . '</h2>';
		echo '<p>' . esc_html( $this->method_description ) . '</p>';

		$api_key = $this->get_option( 'api_key' );

		if ( ! empty( $api_key ) ) {
			$api      = new PaymentSetu_API( $api_key );
			$response = $api->check_credits();

			if ( ! empty( $response['status'] ) && isset( $response['credits'] ) ) {
				$credits = $response['credits'];
				echo '<div class="notice notice-info inline" style="padding:10px 14px; margin-bottom:15px;">';
				printf(
					'<strong>%s</strong> &nbsp;|&nbsp; %s',
					sprintf(
						/* translators: %d: remaining credits */
						esc_html__( 'Remaining credits: %d', 'paymentsetu-gateway' ),
						(int) ( $credits['remaining_credits'] ?? 0 )
					),
					sprintf(
						/* translators: %s: subscription status */
						esc_html__( 'Subscription: %s', 'paymentsetu-gateway' ),
						esc_html( ucfirst( $credits['subscription_status'] ?? 'unknown' ) )
					)
				);
				echo '</div>';
			}
		}

		echo '<table class="form-table">';
		$this->generate_settings_html();
		echo '</table>';
	}

	// ── Checkout ──────────────────────────────────────────────────────────────

	/**
	 * Process the payment and return result.
	 */
	public function process_payment( $order_id ): array {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			wc_add_notice( __( 'Order not found. Please try again.', 'paymentsetu-gateway' ), 'error' );
			return [ 'result' => 'failure' ];
		}

		$api_key = $this->get_option( 'api_key' );
		if ( empty( $api_key ) ) {
			wc_add_notice( __( 'PaymentSetu is not configured. Please contact the store owner.', 'paymentsetu-gateway' ), 'error' );
			return [ 'result' => 'failure' ];
		}

		$prefix     = $this->get_option( 'order_prefix', '' );
		$ps_id      = $prefix . $order_id;
		$amount     = (int) round( $order->get_total() * 100 ); // Convert to paisa.
		$return_url = $this->get_return_url( $order );

		$extra = [
			'customer_name'   => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
			'customer_email'  => $order->get_billing_email(),
			'customer_mobile' => $order->get_billing_phone(),
			'remarks'         => sprintf(
				/* translators: %s: order number */
				__( 'WooCommerce order #%s', 'paymentsetu-gateway' ),
				$order->get_order_number()
			),
		];

		$api      = new PaymentSetu_API( $api_key );
		$response = $api->create_order( $ps_id, $amount, $return_url, $extra );

		if ( empty( $response['status'] ) ) {
			$error_code = $response['error_code'] ?? '';
			$msg        = $response['msg']        ?? __( 'Could not create payment link. Please try again.', 'paymentsetu-gateway' );

			if ( $error_code === 'ALREADY_PAID' ) {
				// This order was already paid via PaymentSetu — complete it.
				$order->payment_complete();
				$order->add_order_note( __( 'PaymentSetu: order marked complete (already paid via gateway).', 'paymentsetu-gateway' ) );
				WC()->cart->empty_cart();
				return [
					'result'   => 'success',
					'redirect' => $return_url,
				];
			}

			if ( $error_code === 'CREDIT_EXHAUSTED' ) {
				wc_add_notice( __( 'This payment method is temporarily unavailable. Please choose a different payment method or contact support.', 'paymentsetu-gateway' ), 'error' );
			} else {
				wc_add_notice( esc_html( $msg ), 'error' );
			}

			wc_get_logger()->error(
				sprintf( 'PaymentSetu create_order failed for order #%s: %s', $order_id, wp_json_encode( $response ) ),
				[ 'source' => 'paymentsetu-gateway' ]
			);

			return [ 'result' => 'failure' ];
		}

		$payment_url = $response['payment_url'] ?? '';

		if ( empty( $payment_url ) ) {
			wc_add_notice( __( 'PaymentSetu did not return a payment URL. Please try again.', 'paymentsetu-gateway' ), 'error' );
			return [ 'result' => 'failure' ];
		}

		// Mark order as pending payment and store payment URL.
		$order->update_status( 'pending', __( 'Awaiting PaymentSetu UPI payment.', 'paymentsetu-gateway' ) );
		$order->update_meta_data( '_paymentsetu_payment_url', esc_url_raw( $payment_url ) );
		$order->save();

		return [
			'result'   => 'success',
			'redirect' => $payment_url,
		];
	}
}
