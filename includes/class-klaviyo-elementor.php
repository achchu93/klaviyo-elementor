<?php

if ( ! defined( 'ABSPATH' )  ) {
	exit; // Exit if accessed directly
}


class KlaviyoElementor{


	/**
	 * KlaviyoElementor constructor.
	 */
	public function __construct()
	{
		$this->includes();
		$this->hooks();

	}

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
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ], 9999 );

		if( is_admin() ){
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 9999 );
		}
	}

	/**
	 * Initializing modules
	 */
	public function init_modules()
	{
		include_once "class-klaviyo-elementor-form-action.php";

		$action = new Klaviyo_Elementor_Form_Action();
		\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $action->get_name(), $action );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_editor_scripts()
	{
		wp_enqueue_script(
			'klaviyo-elementor',
			plugins_url('dist/editor.min.js', KLAVIYO_ELEMENTOR_FILE),
			[
				'backbone-marionette',
				'elementor-common-modules',
				'elementor-editor-modules',
			],
			"1.0",
			true
		);
	}

	public function enqueue_admin_scripts()
	{
		wp_enqueue_script(
			'klaviyo-elementor-admin',
			plugins_url('dist/admin.min.js', KLAVIYO_ELEMENTOR_FILE),
			[
				'elementor-common'
			],
			"1.0",
			true
		);
	}
}

new KlaviyoElementor();