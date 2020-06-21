<?php
/**
 * Integration class for Klaviyo wordpress plugin
 * 
 * @since 1.1.0
 */

 defined( 'ABSPATH' ) || exit;

 if( !is_plugin_active( 'klaviyo/klaviyo.php' ) ) return;

 /**
  * Klaviyo_Integration
  */
 class Klaviyo_Integration {

	/**
	 * Custom settings fields
	 */
	private $settings;

	/**
	 * Klaviyo_Integration constructor
	 */
	public function __construct(){
		$this->settings = [
			'klaviyo_newsletter_position' 		 => [
				'default' => 'woocommerce_after_checkout_billing_form'
			],
			'klaviyo_newsletter_checked_default' => [
				'default' => false
			]
		];

		add_action( 'woocommerce_klaviyo_init', [ $this, 'init_woocommerce_klaviyo_integration' ], 99 );
	}

	/**
	 * get custom settings value
	 */
	public function get_option($name){
		$klaviyo_settings = get_option( 'klaviyo_settings' );

		return !empty( $klaviyo_settings[$name] ) ? $klaviyo_settings[$name] : $this->settings[$name]['default'];
	}

	/**
	 * Init Klaviyo integrations
	 */
	public function init_woocommerce_klaviyo_integration(){

		$this->init_checkout_newsletter();

		add_action( 'admin_menu', [ $this, 'klaviyo_settings_page' ] );
	}

	/**
	 * Klaviyo settings page override
	 */
	public function klaviyo_settings_page(){
		global $klaviyowp_admin;

		remove_action( 'toplevel_page_klaviyo_settings', [ $klaviyowp_admin, 'settings' ] );

		add_action( 'toplevel_page_klaviyo_settings',  [ $this, 'settings_extended' ] );
	}

	/**
	 * Klaviyo settings page content override
	 */
	public function settings_extended(){
		global $klaviyowp_admin;

		$klaviyo_settings = $this->process_settings_extended();
		$key              = get_option( 'elementor_pro_klaviyo_global_api_key' );
		$api              = new Klaviyo_List_Api( $key );
		$response         = $api->get_lists();

		ob_start();
		include_once( "template-settings.php" );
		$content = ob_get_clean();
        
        $wrapped_content = $klaviyowp_admin->postbox( 'klaviyo-settings', 'Connect to Klaviyo', $content );
        
        $klaviyowp_admin->admin_wrap( 'Klaviyo Settings', $wrapped_content );
	}

	/**
	 * Saving custom settings
	 */
	public function process_settings_extended(){
		global $klaviyowp_admin;

		$settings = $klaviyowp_admin->process_settings();

		$settings['klaviyo_newsletter_position']        = !empty( $_POST['klaviyo_newsletter_position'] ) ? $_POST['klaviyo_newsletter_position'] : $this->get_option( 'klaviyo_newsletter_position' );
		$settings['klaviyo_newsletter_checked_default'] = !empty( $_POST['klaviyo_newsletter_checked_default'] ) ? $_POST['klaviyo_newsletter_checked_default'] : $this->get_option( 'klaviyo_newsletter_checked_default' );

		update_option( 'klaviyo_settings', $settings );

		return get_option( 'klaviyo_settings' );
	}

	/**
	 * Get newsletter position hooks
	 */
	public function get_newsletter_positions(){
		return [
			'woocommerce_checkout_before_customer_details'     => 'Before customer details',
			'woocommerce_checkout_after_customer_details' 	   => 'After customer details',
			'woocommerce_checkout_before_order_review_heading' => 'Before review heading',
			'woocommerce_checkout_before_order_review' 		   => 'Before order review',
			'woocommerce_checkout_after_order_review' 		   => 'After order review',
			'woocommerce_before_checkout_billing_form' 		   => 'Before billing form',
			'woocommerce_after_checkout_billing_form' 		   => 'After billing form',
			'woocommerce_before_checkout_shipping_form' 	   => 'Before shipping form',
			'woocommerce_after_checkout_shipping_form' 		   => 'After shipping form',
			'woocommerce_before_order_notes' 				   => 'Before order notes',
			'woocommerce_after_order_notes' 				   => 'After order notes',
			'woocommerce_review_order_before_payment' 		   => 'Before payment section',
			'woocommerce_review_order_after_payment' 		   => 'After payment section',
			'woocommerce_review_order_before_submit' 		   => 'Before order button',
			'woocommerce_review_order_after_submit' 		   => 'After proceed button'
		];
	}

	/**
	 * Klaviyo newsletter checkout field override 
	 */
	public function init_checkout_newsletter(){
		$position         = $this->get_option( 'klaviyo_newsletter_position' );
		
		remove_action( 'woocommerce_after_checkout_billing_form', 'kl_checkbox_custom_checkout_field' );
		add_action( $position, [ $this, 'checkout_klaviyo_newsletter_field' ] );
	}

	/**
	 * Klaviyo newsletter checkout field content override 
	 */
	public function checkout_klaviyo_newsletter_field(){
		$klaviyo_settings = get_option( 'klaviyo_settings' );
    	woocommerce_form_field( 
			'kl_newsletter_checkbox', 
			[
				'type'     => 'checkbox',
				'class'    => ['kl_newsletter_checkbox_field'],
				'label'    => $klaviyo_settings['klaviyo_newsletter_text'],
				'value'    => true,
				'default'  => $this->get_option( 'klaviyo_newsletter_checked_default' ) == 'true' ? 1 : 0,
				'required' => false,
			], 
			WC_Checkout::instance()->get_value( 'kl_newsletter_checkbox' )
		);
	}

 }

 new Klaviyo_Integration();