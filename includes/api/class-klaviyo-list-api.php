<?php

if ( ! defined( 'ABSPATH' )  ) {
	exit; // Exit if accessed directly
}

/**
 * Klaviyo List API
 *
 * @class Klaviyo_List_API
 */
class Klaviyo_List_API extends Klaviyo_Api_Base {

	/**
	 * API Version
	 *
	 * @var string
	 */
	protected $version  = 'v2/';

	/**
	 * API Route
	 *
	 * @var string
	 */
	protected $route    = 'list';

	/**
	 * Klaviyo_List_API constructor.
	 *
	 * @param String $api_key
	 */
	public function __construct( $api_key ) {
		parent::__construct( $this->version, $this->route, $api_key );
	}

	/**
	 * Get all lists
	 *
	 * @return array
	 */
	public function get_lists()
	{
		return $this->request( $this->get_base_url() . 's' );
	}

	/**
	 * Add member to a list
	 *
	 * @param string $list
	 * @param array $data
	 *
	 * @return array
	 */
	public function add_to_list($list, $data)
	{
		$url = $this->get_base_url() . "/$list/members";
		return $this->request( $url, 'POST', $data );
	}

	/**
	 * Subscribe member to a list
	 *
	 * @param string $list
	 * @param array $data
	 *
	 * @return array
	 */
	public function subscribe_to_list($list, $data)
	{
		$url = $this->get_base_url() . "/$list/subscribe";
		return $this->request( $url, 'POST', $data );
	}

}