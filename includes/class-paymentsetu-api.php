<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handles communication with the PaymentSetu REST API.
 */
class PaymentSetu_API {

	const BASE_URL = 'https://paymentsetu.com/api';

	/** @var string */
	private $api_key;

	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Build common request args.
	 */
	private function request_args( string $method, array $body = [] ): array {
		$args = [
			'method'  => $method,
			'timeout' => 30,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];

		if ( ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		return $args;
	}

	/**
	 * Create a payment order.
	 *
	 * @param string $order_id     Your unique order identifier.
	 * @param int    $amount       Amount in paisa.
	 * @param string $redirect_url URL to redirect after payment.
	 * @param array  $extra        Optional: customer_name, customer_email, customer_mobile, remarks.
	 *
	 * @return array{status: bool, payment_url?: string, msg?: string, error_code?: string}
	 */
	public function create_order( string $order_id, int $amount, string $redirect_url, array $extra = [] ): array {
		$body = array_merge(
			[
				'order_id'     => $order_id,
				'amount'       => $amount,
				'redirect_url' => $redirect_url,
			],
			array_filter( $extra )
		);

		$response = wp_remote_post(
			self::BASE_URL . '/create_order',
			$this->request_args( 'POST', $body )
		);

		return $this->parse_response( $response );
	}

	/**
	 * Check remaining API credits.
	 *
	 * @return array
	 */
	public function check_credits(): array {
		$response = wp_remote_get(
			self::BASE_URL . '/check_credits',
			$this->request_args( 'GET' )
		);

		return $this->parse_response( $response );
	}

	/**
	 * Parse a WP HTTP API response.
	 */
	private function parse_response( $response ): array {
		if ( is_wp_error( $response ) ) {
			return [
				'status' => false,
				'msg'    => $response->get_error_message(),
			];
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return [
				'status' => false,
				'msg'    => sprintf( 'Unexpected API response (HTTP %d).', $code ),
			];
		}

		return $body;
	}
}
