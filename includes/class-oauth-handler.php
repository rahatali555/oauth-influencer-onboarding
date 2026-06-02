<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OIO_OAuth_Handler {
    public function __construct() {
        add_action( 'init', [ $this, 'handle_oauth_callback' ] );
    }

    public function handle_oauth_callback() {
        if ( ! isset( $_GET['oio_callback'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized.', 'oauth-influencer-onboarding' ) );
        }

        $platform = sanitize_key( $_GET['oio_callback'] );
        $code     = isset( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';
        $state    = isset( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';

        $expected_state = get_transient( "oio_{$platform}_state" );
        if ( ! $expected_state || $state !== $expected_state ) {
            wp_die( __( 'Invalid or expired state parameter.', 'oauth-influencer-onboarding' ) );
        }
        delete_transient( "oio_{$platform}_state" );

        // 🔒 Initialize variable BEFORE conditionals (satisfies static analyzer)
        $auth = null;

        if ( 'instagram' === $platform ) {
            $auth = new OIO_Instagram_OAuth();
        } elseif ( 'tiktok' === $platform ) {
            $auth = new OIO_TikTok_OAuth();
        } else {
            wp_die( __( 'Invalid platform.', 'oauth-influencer-onboarding' ) );
        }

        // Safety fallback (prevents fatal error if platform is invalid)
        if ( ! $auth ) {
            wp_die( __( 'Failed to load OAuth handler.', 'oauth-influencer-onboarding' ) );
        }

        $response = $auth->exchange_token( $code );

        if ( is_wp_error( $response ) ) {
            $this->redirect_with_message( 'error', $response->get_error_message(), $platform );
            return;
        }

        update_option( "oio_{$platform}_access_token", $response['access_token'] ?? '' );
        update_option( "oio_{$platform}_user_id", $response['user_id'] ?? '' );

        $this->redirect_with_message( 'success', __( 'Platform connected successfully!', 'oauth-influencer-onboarding' ), $platform );
    }

    private function redirect_with_message( string $type, string $message, string $platform ): void {
        $url = admin_url( 'options-general.php?page=oauth-influencer-onboarding' );
        $url = add_query_arg( [
            'oio_message' => sanitize_key( $type ),
            'oio_msg'     => rawurlencode( $message ),
            'platform'    => sanitize_key( $platform ),
        ], $url );
        wp_redirect( $url );
        exit;
    }
}