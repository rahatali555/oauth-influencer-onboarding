<?php
/**
 * Plugin Name: OAuth Influencer Onboarding
 * Description: Secure WordPress OAuth plugin for Instagram & TikTok account verification.
 * Version: 0.1.0
 * Author: Your Name
 * Text Domain: oauth-influencer-onboarding
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OIO_VERSION', '0.1.0' );
define( 'OIO_DIR', plugin_dir_path( __FILE__ ) );
define( 'OIO_URL', plugin_dir_url( __FILE__ ) );

// Load core classes
require_once OIO_DIR . 'includes/class-instagram-oauth.php';
require_once OIO_DIR . 'includes/class-tiktok-oauth.php';
// We'll add compliance & settings next

/**
 * Initialize the plugin
 */
function oio_init() {
    // Initialize settings
    $settings = new OIO_Settings();
    add_action( 'admin_init', [ $settings, 'register_settings' ] );
    add_action( 'admin_menu', [ $settings, 'add_settings_page' ] );

    // Initialize compliance logger
    new OIO_Compliance();
}
add_action( 'plugins_loaded', 'oio_init' );