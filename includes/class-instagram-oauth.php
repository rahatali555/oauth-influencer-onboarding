<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OIO_Instagram_OAuth {
    private string $client_id;
    private string $client_secret;
    private string $redirect_uri;

    public function __construct() {
        $this->client_id     = get_option( 'oio_instagram_client_id', '' );
        $this->client_secret = get_option( 'oio_instagram_client_secret', '' );
        $this->redirect_uri  = get_option( 'oio_instagram_redirect_uri', '' );
    }

    /**
     * Generate secure state parameter for CSRF protection
     */
    public function generate_state(): string {
        $state = wp_generate_password( 32, false );
        set_transient( 'oio_instagram_state', $state, 15 * MINUTE_IN_SECONDS );
        return $state;
    }

    /**
     * Build Instagram authorization URL
     */
    public function get_auth_url() {
        if ( empty( $this->client_id ) || empty( $this->redirect_uri ) ) {
            return new WP_Error( 'missing_config', __( 'Instagram API credentials not configured.', 'oauth-influencer-onboarding' ) );
        }

        $state = $this->generate_state();
        $params = http_build_query([
            'client_id'     => $this->client_id,
            'redirect_uri'  => $this->redirect_uri,
            'scope'         => 'instagram_basic,instagram_graph_user_profile',
            'state'         => $state,
            'response_type' => 'code'
        ]);

        return 'https://api.instagram.com/oauth/authorize?' . $params;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchange_token( string $code ) {
        if ( empty( $code ) ) {
            return new WP_Error( 'missing_code', __( 'Authorization code is missing.', 'oauth-influencer-onboarding' ) );
        }

        $response = wp_remote_post( 'https://api.instagram.com/oauth/access_token', [
            'body' => [
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->redirect_uri,
                'code'          => $code,
            ],
            'timeout' => 15,
        ]);

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['error'] ) ) {
            return new WP_Error( 'instagram_api_error', $body['error']['message'] );
        }

        return $body;
    }
}