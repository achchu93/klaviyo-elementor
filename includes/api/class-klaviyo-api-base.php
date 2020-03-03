<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Klaviyo_Api_Base {

	protected $base_url = 'https://a.klaviyo.com/api/';
	protected $version  = 'v1/';
	protected $route    = '';

	private $api_key  = '';

	/**
	 * Klaviyo_Api_Base constructor.
	 *
	 * @param string $version
	 * @param string $route
	 */
	public function __construct( $version, $route, $api_key )
	{
		$this->version   = $version;
		$this->route     = $route;
		$this->api_key   = $api_key;
	}

	public function request( $url = '', $method = 'GET', $data = [] )
	{
		$url = ! empty( $url ) ? $url : $this->get_base_url();

		$request = wp_remote_request(
			$url,
			[
				'method'  => $method,
				'headers' => [
					'content-type' => 'application/json',
					'api-key'      => $this->api_key
				],
				'body'    => json_encode($data)
			]
		);

		return $this->parse_request_data($request);
	}

	/**
	 * @param $request
	 *
	 * @return array
	 */
	public function parse_request_data($request)
	{

		if( is_wp_error($request) )
		{
			return $this->parse_error_request_data($request);
		}

		return $this->parse_success_request_data( json_decode( wp_remote_retrieve_body( $request ) ) );

	}

	/**
	 * @param WP_Error $error
	 *
	 * @return array
	 */
	public function parse_error_request_data($error)
	{
		return [
			'success' => false,
			'message' => $error->get_error_message(),
			'code'    => $error->get_error_code()
		];
	}

	/**
	 * @param array $success
	 *
	 * @return array
	 */
	public function parse_success_request_data($success)
	{
		return [
			'success' => true,
			'data'    => $success
		];
	}

	/**
	 * Get current route url
	 *
	 * @return string
	 */
	public function get_base_url()
	{
		return esc_url( $this->base_url . $this->version . $this->route );
	}
}