<?php
/**
 * Base class for the Klaviyo API
 *
 * @package KlaviyoWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Klaviyo API Base Class
 *
 * @class Klaviyo_Api_Base
 */
class Klaviyo_Api_Base {

	/**
	 * Base URL
	 *
	 * @var string
	 */
	protected $base_url = 'https://a.klaviyo.com/api/';

	/**
	 * API Version
	 *
	 * @var string
	 */
	protected $version = 'v1/';

	/**
	 * API Route
	 *
	 * @var string
	 */
	protected $route = '';

	/**
	 * API key
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * Klaviyo_Api_Base constructor.
	 *
	 * @param string $version API version
	 * @param string $route API ndpoint
	 * @param string $api_key API Key
	 */
	public function __construct( $version, $route, $api_key ) {
		$this->version = $version;
		$this->route   = $route;
		$this->api_key = $api_key;
	}

	/**
	 * API Request
	 *
	 * @param string $url Request url
	 * @param string $method Request method
	 * @param array  $data Request data
	 *
	 * @return array
	 */
	public function request( $url = '', $method = 'GET', $data = [] ) {
		$url = ! empty( $url ) ? $url : $this->get_base_url();

		$request = wp_remote_request(
			$url,
			[
				'method'  => $method,
				'headers' => [
					'content-type' => 'application/json',
					'api-key'      => $this->api_key,
				],
				'body'    => $data,
			]
		);

		return $this->parse_request_data( $request );
	}

	/**
	 * Parse response data
	 *
	 * @param array $request Request data
	 *
	 * @return array
	 */
	public function parse_request_data( $request ) {

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
			return $this->parse_error_request_data( $request );
		}

		return $this->parse_success_request_data( json_decode( wp_remote_retrieve_body( $request ) ) );

	}

	/**
	 * Parse error response data
	 *
	 * @param WP_Error|array $error Request error data
	 *
	 * @return array
	 */
	public function parse_error_request_data( $error ) {
		$message = wp_remote_retrieve_response_message( $error );

		if ( is_array( $error ) && ! empty( $error['body'] ) ) {
			$object  = json_decode( $error['body'] );
			$message = is_object( $object ) && property_exists( $object, 'detail' ) ? $object->detail : $message;
		}

		return [
			'success' => false,
			'message' => $message,
			'code'    => wp_remote_retrieve_response_code( $error ),
		];
	}

	/**
	 * Parse success response data
	 *
	 * @param array $success Request success data
	 *
	 * @return array
	 */
	public function parse_success_request_data( $success ) {
		return [
			'success' => true,
			'data'    => $success,
		];
	}

	/**
	 * Get base route url
	 *
	 * @return string
	 */
	public function get_base_url() {
		return esc_url( $this->base_url . $this->version . $this->route );
	}
}
