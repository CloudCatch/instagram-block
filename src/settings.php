<?php
/**
 * Plugin Settings
 *
 * @package CloudCatch/InstagramBlock
 */

/**
 * Add settings page.
 *
 * @return void
 */
function instagram_block_settings() {
	add_options_page( esc_html__( 'Instagram Block', 'cc-instagram' ), esc_html__( 'Instagram Block', 'cc-instagram' ), 'manage_options', 'cc-instagram', 'instagram_block_do_settings' );
}
add_action( 'admin_menu', 'instagram_block_settings' );

/**
 * Render settings page.
 *
 * @return void
 */
function instagram_block_do_settings() {
	$adapter = \CloudCatch\InstagramBlock\InstagramAdapter::getInstance();

	if ( ! isset( $_GET['code'] ) ) {
		// Get authorization URL to generate state.
		$authorization_url = $adapter->getProvider()->getAuthorizationUrl(
			array(
				'scope' => array( 'user_profile', 'user_media' ),
				'uuid'  => instagram_block_get_uuid(),
			) 
		);

		update_option( 'cc_instagram_auth_state', $adapter->getProvider()->getState() );
	}
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Instagram Block Settings', 'cc-instagram' ); ?></h1>

		<p>
			<a href="<?php echo esc_url( $authorization_url ); ?>" class="button button-secondary"><?php esc_html_e( 'Authorize with Instagram', 'cc-instagram' ); ?></a>
		</p>
	</div>

	<?php 
}

/**
 * Handle tokens once returned from oAuth.
 *
 * @return void
 */
function instagram_block_authorization_code() {
	$adapter = \CloudCatch\InstagramBlock\InstagramAdapter::getInstance();

	if ( isset( $_GET['instagram_tokens'] ) ) {
		try {
			$response = wp_unslash( $_GET );

			$access_token = json_decode( $response['instagram_tokens'], true );

			if ( $access_token['access_token'] ) {
				$token = new \League\OAuth2\Client\Token\AccessToken(
					array(
						'access_token' => $access_token['access_token'],
						'expires'      => time() + absint( $access_token['expires_in'] ),
						'token_type'   => $access_token['token_type'],
					) 
				);

				update_option( 'cc_instagram_token', $token->jsonSerialize() );

				$user = $adapter->getProvider()->getResourceOwner( $token );

				update_option( 'cc_instagram_user', $user->toArray() );

				wp_safe_redirect( admin_url( 'options-general.php?page=cc-instagram' ) );
				exit;
			}
		} catch ( Exception $e ) {
			exit( 'Oh dear... ' . $e->getMessage() );
		}
	}
}
add_action( 'admin_menu', 'instagram_block_authorization_code', 0 );

/**
 * Handle deauthorization of plugin
 *
 * @return void
 */
function instagram_block_deauthorization() {
	if ( ! isset( $_REQUEST['cc_instagram_deauthorize'] ) ) {
		return;
	}

	$uuid = $_REQUEST['uuid'] ?? null;

	if ( instagram_block_get_uuid() !== $uuid ) {
		wp_send_json_error( esc_html__( 'Invalid request', 'cc-instagram' ), 403 );
	}

	instagram_block_uninstall();

	wp_send_json_success();
}
add_action( 'plugins_loaded', 'instagram_block_deauthorization', -50 );
