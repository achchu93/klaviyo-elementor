<?php

if ( ! defined( 'ABSPATH' )  ) {
	exit; // Exit if accessed directly
}

/**
 * Main Klaviyotor Class
 *
 * @class Klaviyotor
 */
class Klaviyotor{


	/**
	 * Klaviyotor constructor.
	 */
	public function __construct()
	{
		$this->includes();
		$this->hooks();

	}

	/**
	 * Includes api files
	 */
	public function includes()
	{
		include_once "api/class-klaviyo-api-base.php";
		include_once "api/class-klaviyo-list-api.php";
	}

	/**
	 * Hooks
	 */
	public function hooks()
	{
	    add_action( 'elementor_pro/init', [ $this, 'init_modules' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ], 99 );
		add_action( 'http_api_curl', [ $this, 'parse_data_to_json' ], 99, 3 );

		if( is_admin() ){
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 99 );
		}
	}

	/**
	 * Initializing modules
	 */
	public function init_modules()
	{
		include_once "form-actions/class-klaviyotor-form-action.php";

		$action = new Klaviyotor_Form_Action();
		\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $action->get_name(), $action );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_editor_scripts()
	{
		wp_register_script(
			'klaviyotor-editor',
			plugins_url( 'dist/editor.min.js', KLAVIYO_ELEMENTOR_FILE ),
			[
				'backbone-marionette',
				'elementor-common-modules',
				'elementor-editor-modules',
			],
			"1.0",
			true
		);

		wp_enqueue_script( 'klaviyotor-editor');
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts()
	{
		wp_register_script(
			'klaviyotor-admin',
			plugins_url('dist/admin.min.js', KLAVIYO_ELEMENTOR_FILE),
			[
				'elementor-common'
			],
			"1.0",
			true
		);

		wp_enqueue_script( 'klaviyotor-admin' );
	}

	/**
	 * Parsing data to JSON as Klaviyo process only JSON data
	 *
	 * @param $handle
	 * @param $data
	 * @param $url
	 */
	public function parse_data_to_json($handle, $data, $url)
	{
		$url = parse_url( $url );
		if( $url && !empty($url["host"]) && $url["host"] === "a.klaviyo.com" ){
			curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data["body"]));
		}
	}
}

new Klaviyotor();