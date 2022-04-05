<?php
/**
 * OAuth Adapter
 *
 * @package CloudCatch/InstagramBlock
 */

namespace CloudCatch\InstagramBlock;

/**
 * Handles communication with Instagram API
 */
class InstagramAdapter {

	/**
	 * Instance of this class.
	 *
	 * @var InstagramAdapter
	 */
	private static $instance;

	/**
	 * Provider.
	 *
	 * @var League\OAuth2\Client\Provider\Instagram
	 */
	private static $provider;

	/**
	 * Do not instantiate.
	 */
	private function __construct() {}

	/**
	 * Get the one and only instance of this class.
	 *
	 * @return InstagramAdapter
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new InstagramAdapter();
		}

		return self::$instance;
	}

	/**
	 * Instantiate new provider.
	 *
	 * @return Provider
	 */
	public function getProvider() {
		if ( null === self::$provider ) {
			self::$provider = new Provider(
				array(
					'clientId'     => get_option( 'cc_instagram_client_id', '' ),
					'clientSecret' => get_option( 'cc_instagram_client_secret', '' ),
					'redirectUri'  => admin_url(),
					'scope'        => array( 'user_profile', 'user_media' ),
				)
			);
		}

		return self::$provider;
	}

	/**
	 * Gets access token and updates if needed.
	 *
	 * @return WP_Error|string
	 */
	public function getAccessToken() {
		$tokens = get_option( 'cc_instagram_token' );

		try {
			if ( ! empty( $_GET['code'] ) ) {
				$token = $this->getProvider()->getAccessToken( 'authorization_code', array( 'code' => $_GET['code'] ) );
				$token = $this->getProvider()->getLongLivedAccessToken( $token );

			} elseif ( isset( $tokens['expires'] ) && time() > $tokens['expires'] ) {
				$token = $this->getProvider()->getRefreshedAccessToken( $tokens['access_token'] );
			}

			if ( isset( $token ) ) {
				update_option( 'cc_instagram_token', $token->jsonSerialize() );
			}
		} catch ( \Exception $e ) {
			error_log( 'Instagram block: ' . $e->getMessage() );
			
			return new \WP_Error( 'cc_instagram', $e->getMessage() );
		}

		return isset( $tokens['access_token'] ) ? $tokens['access_token'] : '';
	}

	/**
	 * Query Instagram API for recent posts.
	 *
	 * @param array $args Arguments to pass to Instagram API.
	 * @return array|WP_Error
	 */
	public function getMedia( $args = array() ) {
		try {
			$request = $this->getProvider()->getAuthenticatedRequest(
				'GET',
				add_query_arg( $args, $this->getProvider()->getGraphHost() . '/me/media?fields=id,caption,media_type,media_url,permalink,timestamp' ),
				$this->getAccessToken()
			);

			$media = $this->getProvider()->getParsedResponse( $request );

			return $media['data'];

		} catch ( \Exception $e ) {
			error_log( 'Instagram block: ' . $e->getMessage() );

			return new \WP_Error( 'cc_instagram', $e->getMessage() );
		}
	}
}
