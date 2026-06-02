<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OIO_Settings {
    public function register_settings() {
        register_setting( 'oio_settings_group', 'oio_instagram_client_id', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
        register_setting( 'oio_settings_group', 'oio_instagram_client_secret', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
        register_setting( 'oio_settings_group', 'oio_instagram_redirect_uri', [ 'type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '' ] );
        register_setting( 'oio_settings_group', 'oio_tiktok_client_key', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
        register_setting( 'oio_settings_group', 'oio_tiktok_client_secret', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
        register_setting( 'oio_settings_group', 'oio_tiktok_redirect_uri', [ 'type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '' ] );
    }

    public function add_settings_page() {
        add_options_page(
            __( 'OAuth Influencer Onboarding', 'oauth-influencer-onboarding' ),
            __( 'OAuth Onboarding', 'oauth-influencer-onboarding' ),
            'manage_options',
            'oauth-influencer-onboarding',
            [ $this, 'render_settings_page' ]
        );
    }

    public function render_settings_page() {
        // 1. Show success/error notices from OAuth redirect
        if ( isset( $_GET['oio_message'] ) && isset( $_GET['oio_msg'] ) ) {
            $type    = sanitize_key( $_GET['oio_message'] );
            $message = urldecode( sanitize_text_field( $_GET['oio_msg'] ) );
            $class   = ( 'error' === $type ) ? 'notice-error' : 'notice-success';
            echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        }

        // 2. Prepare OAuth objects & connection status
        $ig_oauth     = new OIO_Instagram_OAuth();
        $tt_oauth     = new OIO_TikTok_OAuth();
        $ig_connected = (bool) get_option( 'oio_instagram_access_token' );
        $tt_connected = (bool) get_option( 'oio_tiktok_access_token' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'oio_settings_group' ); ?>
                <table class="form-table">
                    <tr><th><?php _e( 'Instagram Client ID', 'oauth-influencer-onboarding' ); ?></th><td><input type="text" name="oio_instagram_client_id" value="<?php echo esc_attr( get_option( 'oio_instagram_client_id' ) ); ?>" class="regular-text" /></td></tr>
                    <tr><th><?php _e( 'Instagram Client Secret', 'oauth-influencer-onboarding' ); ?></th><td><input type="password" name="oio_instagram_client_secret" value="<?php echo esc_attr( get_option( 'oio_instagram_client_secret' ) ); ?>" class="regular-text" /></td></tr>
                    <tr><th><?php _e( 'Instagram Redirect URI', 'oauth-influencer-onboarding' ); ?></th><td><input type="url" name="oio_instagram_redirect_uri" value="<?php echo esc_attr( get_option( 'oio_instagram_redirect_uri' ) ); ?>" class="regular-text" /></td></tr>
                    <tr><th><?php _e( 'TikTok Client Key', 'oauth-influencer-onboarding' ); ?></th><td><input type="text" name="oio_tiktok_client_key" value="<?php echo esc_attr( get_option( 'oio_tiktok_client_key' ) ); ?>" class="regular-text" /></td></tr>
                    <tr><th><?php _e( 'TikTok Client Secret', 'oauth-influencer-onboarding' ); ?></th><td><input type="password" name="oio_tiktok_client_secret" value="<?php echo esc_attr( get_option( 'oio_tiktok_client_secret' ) ); ?>" class="regular-text" /></td></tr>
                    <tr><th><?php _e( 'TikTok Redirect URI', 'oauth-influencer-onboarding' ); ?></th><td><input type="url" name="oio_tiktok_redirect_uri" value="<?php echo esc_attr( get_option( 'oio_tiktok_redirect_uri' ) ); ?>" class="regular-text" /></td></tr>
                </table>

                <h2><?php _e( 'Connect Accounts', 'oauth-influencer-onboarding' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php _e( 'Instagram', 'oauth-influencer-onboarding' ); ?></th>
                        <td><?php echo $ig_connected ? '<span style="color:#00a32a;">✅ ' . __( 'Connected', 'oauth-influencer-onboarding' ) . '</span>' : '<a href="' . esc_url( $ig_oauth->get_auth_url() ) . '" class="button button-primary">' . __( 'Connect Instagram', 'oauth-influencer-onboarding' ) . '</a>'; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'TikTok', 'oauth-influencer-onboarding' ); ?></th>
                        <td><?php echo $tt_connected ? '<span style="color:#00a32a;">✅ ' . __( 'Connected', 'oauth-influencer-onboarding' ) . '</span>' : '<a href="' . esc_url( $tt_oauth->get_auth_url() ) . '" class="button button-primary">' . __( 'Connect TikTok', 'oauth-influencer-onboarding' ) . '</a>'; ?></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}