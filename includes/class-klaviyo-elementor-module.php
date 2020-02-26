<?php

use \ElementorPro\Modules\Forms\Classes\Integration_Base;
use \Elementor\Controls_Manager;

class Klaviyo_Elementor_Form_Action extends Integration_Base{

	public function get_name() {
		return 'klaviyo-elementor';
	}

	public function get_label() {
		return __( 'Klaviyo', KLAVIYO_DOMAIN );
	}

	/**
	 * @param \Elementor\Controls_Stack $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'section_klaviyo',
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

		$widget->end_controls_section();

	}

	/**
	 * @inheritDoc
	 */
	public function run( $record, $ajax_handler ) {

	}

	/**
	 * @inheritDoc
	 */
	public function on_export( $element ) {

	}

	public function handle_panel_request( array $data ) {

		$key = $data['klaviyo_api_key'];

		if ( empty( $key ) ) {
			throw new \Exception( '`api_key` is required', 400 );
		}

		$response = wp_remote_get(
			'https://a.klaviyo.com/api/v2/lists',
			[
				"api_key" => $key
			]
		);

		if(is_wp_error($response)){
			throw new \Exception( $response->get_error_message(), $response->get_error_code() );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}