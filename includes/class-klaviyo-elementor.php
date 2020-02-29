<?php

class KlaviyoElementor{


	/**
	 * KlaviyoElementor constructor.
	 */
	public function __construct()
	{
		$this->hooks();

	}

	/**
	 * Hooks
	 */
	public function hooks()
	{
	    add_action( 'elementor_pro/init', [ $this, 'init_modules' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ], 9999 );
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
			plugins_url('dist/main.js', KLAVIYO_ELEMENTOR_FILE),
			[
				'backbone-marionette',
				'elementor-common-modules',
				'elementor-editor-modules',
			],
			"1.0",
			true
		);
	}
}

new KlaviyoElementor();