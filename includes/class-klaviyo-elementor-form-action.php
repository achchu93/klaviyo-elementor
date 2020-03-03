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
}