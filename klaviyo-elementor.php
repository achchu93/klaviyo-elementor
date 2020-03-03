<?php
/**
 * Plugin Name:       Klaviyo for Elementor
 * Plugin URI:        #
 * Description:       Elementor Pro extension for klaviyo form action
 * Version:           0.1
 * Requires PHP:      5.6
 * Author:            Ahamed Arshad
 * Author URI:        mailto:achchu.zats@gmail.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       klaviyo-elementor
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$dep = "elementor-pro/elementor-pro.php";
if ( ! in_array( $dep, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

define( 'KLAVIYO_ELEMENTOR_FILE', __FILE__);
define( 'KLAVIYO_DOMAIN', 'klaviyo-elementor' );

include_once "includes/class-klaviyo-elementor.php";