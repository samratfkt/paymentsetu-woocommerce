<?php
/**
 * Plugin Name: PaymentSetu Gateway for WooCommerce
 * Plugin URI:  https://paymentsetu.com
 * Description: Accept UPI payments via PaymentSetu payment gateway in WooCommerce.
 * Version:     1.0.0
 * Author:      PaymentSetu
 * Author URI:  https://paymentsetu.com
 * Text Domain: paymentsetu-gateway
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.0
 */

defined( 'ABSPATH' ) || exit;

define( 'PAYMENTSETU_PLUGIN_FILE', __FILE__ );
define( 'PAYMENTSETU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PAYMENTSETU_VERSION', '1.0.0' );

/**
 * Declare HPOS + Cart/Checkout Blocks compatibility.
 */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'cart_checkout_blocks',
			__FILE__,
			true
		);
	}
} );

/**
 * Register with WooCommerce Block Checkout.
 * MUST be at top level — woocommerce_blocks_loaded fires at plugins_loaded priority 10,
 * before our gateway init at priority 11, so it cannot be nested inside plugins_loaded.
 */
add_action( 'woocommerce_blocks_loaded', 'paymentsetu_register_blocks_support' );

function paymentsetu_register_blocks_support(): void {
	if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		return;
	}

	require_once PAYMENTSETU_PLUGIN_DIR . 'includes/class-paymentsetu-blocks.php';

	add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function ( \Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $registry ) {
			$container = \Automattic\WooCommerce\Blocks\Package::container();
			$container->register(
				WC_PaymentSetu_Blocks::class,
				function () {
					return new WC_PaymentSetu_Blocks();
				}
			);
			$registry->register( $container->get( WC_PaymentSetu_Blocks::class ) );
		},
		5
	);
}

/**
 * Load the gateway class after WooCommerce is fully loaded.
 */
add_action( 'plugins_loaded', 'paymentsetu_init_gateway', 11 );
function paymentsetu_init_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once PAYMENTSETU_PLUGIN_DIR . 'includes/class-paymentsetu-api.php';
	require_once PAYMENTSETU_PLUGIN_DIR . 'includes/class-paymentsetu-webhook.php';
	require_once PAYMENTSETU_PLUGIN_DIR . 'includes/class-wc-paymentsetu-gateway.php';

	// Register the classic gateway.
	add_filter( 'woocommerce_payment_gateways', 'paymentsetu_add_gateway' );

	// Register the webhook endpoint.
	PaymentSetu_Webhook::init();
}

function paymentsetu_add_gateway( $gateways ) {
	$gateways[] = 'WC_PaymentSetu_Gateway';
	return $gateways;
}

/**
 * Add Settings link on the Plugins page.
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'paymentsetu_plugin_action_links' );
function paymentsetu_plugin_action_links( $links ) {
	$settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paymentsetu' );
	array_unshift( $links, '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'paymentsetu-gateway' ) . '</a>' );
	return $links;
}
