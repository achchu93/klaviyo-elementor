<?php

use \ElementorPro\Modules\Forms\Classes\Integration_Base;
use \Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Controls\Fields_Map;

if ( ! defined( 'ABSPATH' )  ) {
	exit; // Exit if accessed directly
}

class Klaviyo_Elementor_Form_Action extends Integration_Base{

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

		$widget->add_control(
			'klaviyo_api_key',
			[
				'label' => __( 'Custom API Key', KLAVIYO_DOMAIN ),
				'type' => Controls_Manager::TEXT,
				'description' => __( 'Use this field to set a custom API Key for the current form', KLAVIYO_DOMAIN ),
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

			$response = wp_remote_post(
				"https://a.klaviyo.com/api/v2/list/$list/members",
				[
					'headers' => [
						'content-type' => 'application/json'
					],
					'body' => json_encode([
						'api_key'  => $api_key,
						'profiles' => [
							[
								'email' => is_array( $fields['email'] ) ? $fields['email']['value'] : $fields['email']->value
							]
						]
					])
				]
			);

			if( is_wp_error($response) ){
				throw new Exception( $response->get_error_message(), $response->get_error_code() );
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

		$response = wp_remote_get( 'https://a.klaviyo.com/api/v2/lists?api_key=' . $key );

		if(is_wp_error($response)){
			throw new \Exception( $response->get_error_message(), $response->get_error_code() );
		}

		$results = json_decode( wp_remote_retrieve_body( $response ) );

		$data = [
			'' => 'Select list...'
		];

		if( is_array($results) && count($results) ){
			foreach ($results as $result){
				$data[ $result->list_id ] = $result->list_name;
			}
		}

		return $data;
	}
}