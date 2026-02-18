<?php
defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Registers PaymentSetu with the WooCommerce Block Checkout.
 */
final class WC_PaymentSetu_Blocks extends AbstractPaymentMethodType {

	/** @var string Must match WC_PaymentSetu_Gateway::$id */
	protected $name = 'paymentsetu';

	// ── AbstractPaymentMethodType interface ───────────────────────────────────

	public function initialize(): void {
		// Read directly from the option — WC()->payment_gateways may not be
		// initialized yet when this fires (during woocommerce_blocks_loaded).
		$this->settings = get_option( 'woocommerce_paymentsetu_settings', [] );
	}

	public function is_active(): bool {
		return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Return the script handle(s) that register the JS payment method.
	 */
	public function get_payment_method_script_handles(): array {
		wp_register_script(
			'paymentsetu-blocks',
			plugins_url( 'assets/js/paymentsetu-blocks.js', PAYMENTSETU_PLUGIN_FILE ),
			[
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-i18n',
				'wp-html-entities',
			],
			PAYMENTSETU_VERSION,
			true
		);

		return [ 'paymentsetu-blocks' ];
	}

	/**
	 * Data exposed to JS via wc.wcSettings.getSetting('paymentsetu_data').
	 */
	public function get_payment_method_data(): array {
		return [
			'title'       => $this->get_setting( 'title', '' ),
			'description' => $this->get_setting( 'description', '' ),
			'supports'    => $this->get_supported_features(),
		];
	}
}
