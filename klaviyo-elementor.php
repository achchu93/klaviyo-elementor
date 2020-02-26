<?php
/**
 * Plugin Name:       Klaviyo for Elementor
 * Plugin URI:        #
 * Description:       An extension for the Dynamics 365 CRM
 * Version:           0.1
 * Requires PHP:      5.6
 * Author:            Ahamed Arshad
 * Author URI:        mailto:achchu.zats@gmail.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       klaviyo-elementor
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'ELEMENTOR_PRO__FILE__' ) ) {
	exit; // Exit if accessed directly or Elementor Pro not activated
}

define( 'KLAVIYO_ELEMENTOR_FILE', __FILE__);
define( 'KLAVIYO_DOMAIN', 'klaviyo-elementor' );

include_once "includes/class-klaviyo-elementor.php";