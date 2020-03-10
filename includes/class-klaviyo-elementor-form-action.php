<?php

use \Elementor\Settings;
use \ElementorPro\Modules\Forms\Classes\Integration_Base;
use \Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Mailchimp_Handler;
use \ElementorPro\Modules\Forms\Controls\Fields_Map;

if ( ! defined( 'ABSPATH' )  ) {
	exit; // Exit if accessed directly
}

class Klaviyo_Elementor_Form_Action extends Integration_Base{

	const OPTION_NAME_API_KEY = 'klaviyo_global_api_key';

	/**
	 * Klaviyo_Elementor_Form_Action constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'elementor/admin/after_create_settings/' . Settings::PAGE_ID, [ $this, 'register_admin_fields' ], 14 );
		}
		add_action( 'wp_ajax_' . self::OPTION_NAME_API_KEY . '_validate', [ $this, 'ajax_validate_api_token' ] );
	}


	/**
	 * Global Key name
	 * @return mixed|void
	 */
	private function get_global_api_key() {
		return get_option( 'elementor_' . self::OPTION_NAME_API_KEY );
	}

	/**
	 * Action slug
	 * @return string
	 */
	public function get_name() {
		return 'klaviyo-elementor';
	}

	/**
	 * Action label
	 * @return string
	 */
	public function get_label() {
		return __( 'Klaviyo', KLAVIYO_DOMAIN );
	}

	/**
	 * Registering settings
	 * @param \Elementor\Controls_Stack $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'section_klaviyo-elementor',
			[
				'label' => __( 'Klaviyo', KLAVIYO_DOMAIN ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		self::global_api_control(
			$widget,
			$this->get_global_api_key(),
			'Klaviyo API Key',
			[
				'klaviyo_api_key_source' => 'default',
			],
			$this->get_name()
		);

		$widget->add_control(
			'klaviyo_api_key_source',
			[
				'label' => __( 'API Key', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'label_block' => false,
				'options' => [
					'default' => 'Default',
					'custom' => 'Custom',
				],
				'default' => 'default',
			]
		);

		$widget->add_control(
			'klaviyo_api_key',
			[
				'label' => __( 'Custom API Key', KLAVIYO_DOMAIN ),
				'type' => Controls_Manager::TEXT,
				'description' => __( 'Use this field to set a custom API Key for the current form', KLAVIYO_DOMAIN ),
				'condition' => [
					'klaviyo_api_key_source' => 'custom',
				]
			]
		);

		$widget->add_control(
			'klaviyo_list',
			[
				'label' => __( 'Audience', KLAVIYO_DOMAIN ),
				'type' => Controls_Manager::SELECT,
				'options' => [],
				'render_type' => 'none',
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'klaviyo_api_key',
							'operator' => '!==',
							'value' => '',
						]
					]
				],
			]
		);

		$widget->add_control(
			'klaviyo_fields_map',
			[
				'label' => __( 'Field Mapping', 'elementor-pro' ),
				'type' => Fields_Map::CONTROL_TYPE,
				'separator' => 'before',
				'fields' => [
					[
						'name' => 'remote_id',
						'type' => Controls_Manager::HIDDEN,
					],
					[
						'name' => 'local_id',
						'type' => Controls_Manager::SELECT,
					],
				],
				'condition' => [
					'klaviyo_list!' => '',
				],
			]
		);

		$widget->end_controls_section();

	}

	/**
	 * @var \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @var \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		$settings = $record->get( 'form_settings' );
		$list     = $settings['klaviyo_list'];
		$api_key  = $settings['klaviyo_api_key'];
		$fields   = $record->get( 'fields' );

		try{

			if( empty( $api_key ) ){
				throw new Exception( "Api key is required" );
			}

			if( empty( $list ) ){
				throw new Exception( "List is required" );
			}

			$list_api = new Klaviyo_List_API($api_key);
			$response = $list_api->add_to_list(
				$list,
				[
					'api_key'  => $api_key,
					'profiles' => [
						[
							'email' => is_array( $fields['email'] ) ? $fields['email']['value'] : $fields['email']->value
						]
					]
				]
			);

			if( !$response['success'] ){
				throw new Exception( $response['message'], $response['code'] );
			}

			$ajax_handler->add_success_message( "Success!" );

		}catch (\Exception $exception){
			$ajax_handler->add_admin_error_message( 'Klaviyo: ' . $exception->getMessage() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function on_export( $element ) {

	}

	public function handle_panel_request( array $data ) {

		$key = $data['api_key'];

		if ( empty( $key ) ) {
			throw new \Exception( '`api_key` is required', 400 );
		}

		$lists_api = new Klaviyo_List_API( $key );
		$lists     = $lists_api->get_lists();

		$data = [
			'' => 'Select list...'
		];

		if( $lists['success'] && count($lists['data']) ){
			foreach ($lists['data'] as $list){
				$data[ $list->list_id ] = $list->list_name;
			}
		}

		return $data;
	}

	/**
	 * Register global settings
	 * @param Settings $settings
	 */
	public function register_admin_fields($settings)
	{
		$settings->add_section( Settings::TAB_INTEGRATIONS, 'klaviyo-elementor', [
			'callback' => function() {
				echo '<hr><h2>' . esc_html__( 'Klaviyo', KLAVIYO_DOMAIN ) . '</h2>';
			},
			'fields' => [
				self::OPTION_NAME_API_KEY => [
					'label' => __( 'API Key', KLAVIYO_DOMAIN ),
					'field_args' => [
						'type' => 'text',
						'desc' => sprintf( __( 'To integrate with our forms you need an <a href="%s" target="_blank">API Key</a>.', KLAVIYO_DOMAIN ), 'https://help.klaviyo.com/hc/en-us/articles/115005062267-Manage-Your-Account-s-API-Keys' ),
					],
				],
				'validate_api_data' => [
					'field_args' => [
						'type' => 'raw_html',
						'html' => sprintf( '<button data-action="%s" data-nonce="%s" class="button elementor-button-spinner" id="elementor_klaviyo_global_api_key_button">%s</button>', self::OPTION_NAME_API_KEY . '_validate', wp_create_nonce( self::OPTION_NAME_API_KEY ), __( 'Validate API Key', KLAVIYO_DOMAIN ) ),
					],
				],
			],
		] );
	}

	/**
	 * Global api key validator
	 */
	public function ajax_validate_api_token() {
		check_ajax_referer( self::OPTION_NAME_API_KEY, '_nonce' );
		if ( empty( $_POST['api_key'] ) ) {
			wp_send_json_error();
		}
		try {
			$list_api  = new Klaviyo_List_API( $_POST['api_key'] );
			$list      = $list_api->get_lists();
			error_log( json_encode( $list ) );

			if( !$list['success'] )
				throw new Exception();

		} catch ( \Exception $exception ) {
			wp_send_json_error();
		}
		wp_send_json_success();
	}
}