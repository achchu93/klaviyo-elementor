<?php

if ( ! defined( 'ABSPATH' )  ) {
	exit; // Exit if accessed directly
}

/**
 * @Class Klaviyotor_Form_Action
 */
class Klaviyotor_Form_Action extends \ElementorPro\Modules\Forms\Classes\Integration_Base{

	const OPTION_NAME_API_KEY = 'pro_klaviyo_global_api_key';

	/**
	 * section_klaviyotor constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'elementor/admin/after_create_settings/' . \Elementor\Settings::PAGE_ID, [ $this, 'register_admin_fields' ], 14 );
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
		return 'klaviyotor';
	}

	/**
	 * Action label
	 * @return string
	 */
	public function get_label() {
		return __( 'Klaviyotor', KLAVIYO_DOMAIN );
	}

	/**
	 * Registering settings
	 * @param \Elementor\Controls_Stack $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'section_klaviyotor',
			[
				'label' => __( $this->get_label(), KLAVIYO_DOMAIN ),
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
				'type' => \Elementor\Controls_Manager::SELECT,
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
				'type' => \Elementor\Controls_Manager::TEXT,
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
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [],
				'render_type' => 'none',
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'klaviyo_api_key',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'klaviyo_api_key_source',
							'operator' => '=',
							'value' => 'default',
						]
					]
				],
			]
		);

		$widget->add_control(
			'klaviyo_fields_map',
			[
				'label' => __( 'Field Mapping', 'elementor-pro' ),
				'type' => \ElementorPro\Modules\Forms\Controls\Fields_Map::CONTROL_TYPE,
				'separator' => 'before',
				'fields' => [
					[
						'name' => 'remote_id',
						'type' => \Elementor\Controls_Manager::HIDDEN,
					],
					[
						'name' => 'local_id',
						'type' => \Elementor\Controls_Manager::SELECT,
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
		$fields   = $record->get( 'fields' );

		if ( 'default' === $settings['klaviyo_api_key_source'] ) {
			$api_key = $this->get_global_api_key();
		} else {
			$api_key = $settings['klaviyo_api_key'];
		}

		try{

			if( empty( $api_key ) ){
				throw new Exception( "Api key is required" );
			}

			if( empty( $list ) ){
				throw new Exception( "List is required" );
			}

			if( count( array_diff( [ "email", "phone_number" ], array_keys( $fields ) ) ) > 1 ){
				throw new Exception( "Email or Phone Number Field is required" );
			}

			$list_api = new Klaviyo_List_API($api_key);
			$response = $list_api->add_to_list(
				$list,
				[
					'api_key'  => $api_key,
					'profiles' => [ $this->get_mapped_fields($fields) ]
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
	 * Export Elements
	 */
	public function on_export( $element ) {
		return $element;
	}

	public function handle_panel_request( array $data ) {

		if ( ! empty( $data['use_global_api_key'] ) && 'default' === $data['use_global_api_key'] ) {
			$key = $this->get_global_api_key();
		} elseif ( ! empty( $data['api_key'] ) ) {
			$key = $data['api_key'];
		}

		if ( empty( $key ) ) {
			throw new \Exception( '`api_key` is required', 400 );
		}

		$lists_api = new Klaviyo_List_API( $key );
		$lists     = $lists_api->get_lists();

		$data = [
			'lists' => [
				'' => 'Select list...'
			]
		];

		if( $lists['success'] && is_array($lists['data']) && count($lists['data']) ){
			foreach ($lists['data'] as $list){
				$data['lists'][ $list->list_id ] = $list->list_name;
			}
		}

		return $data;
	}

	/**
	 * Register global settings
	 * @param \Elementor\Settings $settings
	 */
	public function register_admin_fields($settings)
	{
		$settings->add_section( \Elementor\Settings::TAB_INTEGRATIONS, 'klaviyotor', [
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
						'html' => sprintf( '<button data-action="%s" data-nonce="%s" class="button elementor-button-spinner" id="elementor_pro_klaviyo_global_api_key_button">%s</button>', self::OPTION_NAME_API_KEY . '_validate', wp_create_nonce( self::OPTION_NAME_API_KEY ), __( 'Validate API Key', KLAVIYO_DOMAIN ) ),
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

			if( !$list['success'] || !is_array($list['data']) ){
				$message =  is_object($list['data']) && property_exists($list['data'], "message") ? $list['data']->message : __( "An error occurred" );
				throw new Exception( $message );
			}

		} catch ( \Exception $exception ) {
			wp_send_json_error( $exception->getMessage() );
		}
		wp_send_json_success();
	}

	/**
	 * @param $fields
	 *
	 * @return array
	 */
	private function get_mapped_fields($fields)
	{
		$map_fields = [];
		foreach ( $fields as $name => $field ){
			$map_fields[ $name ] = $field["value"];
		}

		return $map_fields;
	}
}