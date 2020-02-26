<?php

class KlaviyoElementor{


	/**
	 * KlaviyoElementor constructor.
	 */
	public function __construct()
	{
		$this->hooks();

	}

	public function hooks()
	{
	    add_action( 'elementor_pro/init', [ $this, 'init_modules' ] );
	}
	
	public function init_modules()
	{
		include_once "class-klaviyo-elementor-module.php";

		$action = new Klaviyo_Elementor_Form_Action();
		\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $action->get_name(), $action );
	}
}

new KlaviyoElementor();