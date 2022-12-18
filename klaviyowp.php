<?php
/**
 * Plugin Name:       Klaviyo WP (Klaviyotor)
 * Plugin URI:        http://klaviyotor.brixyt.com/
 * Description:       Formerly known as Klaviyotor. Intend to integrate kalviyo with WP Visual Builders.
 * Version:           1.1.1
 * Requires PHP:      5.5
 * Author:            Ahamed Arshad
 * Author URI:        mailto:achchu.zats@gmail.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       klaviyo-wp
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Define constants
define( 'KLAVIYO_WP_FILE', __FILE__ );

// Checks PHP version
if ( version_compare( PHP_VERSION, '7.4.0', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="error notice is-dismissible"><p>%s</p></div>',
				esc_html__( 'Klaviyo WP Requires atleast PHP version of 7.4', 'klaviyo-wp' )
			);
		}
	);
	return;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Checks Elementor Pro plugin has been installed
if ( ! is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="error notice is-dismissible"><p>%s</p></div>',
				esc_html__( 'Klaviyo WP Requires Elementor Pro plugin to be activated.', 'klaviyo-wp' )
			);
		}
	);
	return;
}

require_once 'includes/class-klaviyowp.php';