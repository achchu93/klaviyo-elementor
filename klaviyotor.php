<?php
/**
 * Plugin Name:       Klaviyotor
 * Plugin URI:        http://klaviyotor.brixyt.com/
 * Description:       Elementor Pro extension for klaviyo form action
 * Version:           1.1.1
 * Requires PHP:      5.5
 * Author:            Ahamed Arshad
 * Author URI:        mailto:achchu.zats@gmail.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       klaviyotor
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Define constants
define( 'KLAVIYO_ELEMENTOR_FILE', __FILE__);

// Checks PHP version
if ( version_compare( PHP_VERSION, '5.5.0', '<' ) ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="error notice is-dismissible"><p>%s</p></div>',
			__( "Klaviyotor Requires atleast PHP version of 5.5.", "klaviyotor" )
		);
	} );
	return;
}

if ( !function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Checks Elementor Pro plugin has been installed
if ( !is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="error notice is-dismissible"><p>%s</p></div>',
			__( "Klaviyotor Requires Elementor Pro plugin to be activated.", "klaviyotor" )
		);
	} );
	return;
}

include_once "includes/class-klaviyotor.php";
