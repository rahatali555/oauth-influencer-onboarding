<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OIO_TikTok_OAuth {
    private string $client_key;
    private string $client_secret;
    private string $redirect_uri;

    public function __construct() {
        $this->client_key    = get_option( 'oio_tiktok_client_key', '' );
        $this->client_secret = get_option( 'oio_tiktok_client_secret', '' );
        $this->redirect_uri  = get_option( 'oio_tiktok_redirect_uri', '' );
    }

    /**
     * Generate secure state parameter for CSRF protection
     */
    public function generate_state(): string {
        $state = wp_generate_password( 32, false );
        set_transient( 'oio_tiktok_state', $state, 15 * MINUTE_IN_SECONDS );
        return $state;
    }

    /**
     * Build TikTok authorization URL
     */
    public function get_auth_url() {
        if ( empty( $this->client_key ) || empty( $this->redirect_uri ) ) {
            return new WP_Error( 'missing_config', __( 'TikTok API credentials not configured.', 'oauth-influencer-onboarding' ) );
        }

        $state = $this->generate_state();
        $params = http_build_query([
            'client_key'    => $this->client_key,
            'redirect_uri'  => $this->redirect_uri,
            'scope'         => 'user.info.basic,video.list',
            'state'         => $state,
            'response_type' => 'code'
        ]);

        return 'https://www.tiktok.com/auth/authorize/?' . $params;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchange_token( string $code ) {
        if ( empty( $code ) ) {
            return new WP_Error( 'missing_code', __( 'Authorization code is missing.', 'oauth-influencer-onboarding' ) );
        }

        $response = wp_remote_post( 'https://open.tiktokapis.com/v2/oauth/token/', [
            'body' => [
                'client_key'    => $this->client_key,
                'client_secret' => $this->client_secret,
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->redirect_uri,
            ],
            'timeout' => 15,
        ]);

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['error'] ) ) {
            return new WP_Error( 'tiktok_api_error', $body['error']['description'] ?? __( 'Unknown TikTok API error.', 'oauth-influencer-onboarding' ) );
        }

        return $body;
    }
}