// Load core classes
require_once OIO_DIR . 'includes/class-instagram-oauth.php';
require_once OIO_DIR . 'includes/class-tiktok-oauth.php';
require_once OIO_DIR . 'includes/class-compliance.php';
require_once OIO_DIR . 'includes/class-settings.php';

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