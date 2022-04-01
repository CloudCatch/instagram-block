<?php
/**
 * Plugin Settings
 *
 * @package CloudCatch/InstagramBlock
 */

function instagram_block_settings() {
	add_options_page( esc_html__( 'Instagram Block', 'cc-instagram' ), esc_html__( 'Instagram Block', 'cc-instagram' ), 'manage_options', 'cc-instagram', 'instagram_block_do_settings' );
}
add_action( 'admin_menu', 'instagram_block_settings' );

function instagram_block_do_settings() {
	$adapter = \CloudCatch\InstagramBlock\InstagramAdapter::getInstance();

	if ( ! isset( $_GET['code'] ) ) {
		// Get authorization URL to generate state.
		$authorization_url = $adapter->getProvider()->getAuthorizationUrl( array( 'scope' => array( 'user_profile', 'user_media' ) ) );

		update_option( 'cc_instagram_auth_state', $adapter->getProvider()->getState() );
	}
	

	$client_id     = get_option( 'cc_instagram_client_id', '' );
	$client_secret = get_option( 'cc_instagram_client_secret', '' );
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Instagram Block Settings', 'cc-instagram' ); ?></h1>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<p>
				<label for="cc_instagram_client_id"><?php esc_html_e( 'Client ID', 'cc-instagram' ); ?></label>
				<input type="text" id="cc_instagram_client_id" name="client_id" class="regular-text" value="<?php echo esc_attr( $client_id ); ?>" />
			</p>
			<p>
				<label for="cc_instagram_client_secret"><?php esc_html_e( 'Client Secret', 'cc-instagram' ); ?></label>
				<input type="password" id="cc_instagram_client_secret" name="client_secret" class="regular-text" value="<?php echo esc_attr( $client_secret ); ?>" />
			</p>

			<input type="hidden" name="action" value="cc_instagram_settings" />
			<?php wp_nonce_field( 'cc_instagram' ); ?>
			<?php submit_button(); ?>
		</form>

		<?php if ( $client_id && $client_secret ) { ?>

		<p>
			<a href="<?php echo esc_url( $authorization_url ); ?>" class="button button-secondary"><?php esc_html_e( 'Authorize with Instagram', 'cc-instagram' ); ?></a>
		</p>

		<?php } ?>

		
	</div>

	<?php 
}

function instagram_block_save_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'cc-instagram' ) );
	}

	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'cc_instagram' ) ) {
		wp_die( esc_html__( 'Invalid nonce, please try again.', 'cc-instagram' ) );
	}

	update_option( 'cc_instagram_client_id', $_REQUEST['client_id'] ?? '' );
	update_option( 'cc_instagram_client_secret', $_REQUEST['client_secret'] ?? '' );

	wp_safe_redirect( admin_url( 'options-general.php?page=cc-instagram' ) );
	exit;
}
add_action( 'admin_post_cc_instagram_settings', 'instagram_block_save_settings' );

function instagram_block_authorization_code() {
	$adapter = \CloudCatch\InstagramBlock\InstagramAdapter::getInstance();

	if ( ! isset( $_GET['code'] ) ) {
		return;
	}

	if ( empty( $_GET['state'] ) || ( get_option( 'cc_instagram_auth_state' ) !== $_GET['state'] ) ) {
		// Do nothing.
	} else {
		try {

			$token = $adapter->getProvider()->getAccessToken(
				'authorization_code',
				array(
					'code' => $_GET['code'],
				)
			);

			$token = $adapter->getProvider()->getLongLivedAccessToken( $token );
	
			update_option( 'cc_instagram_token', $token->jsonSerialize() );
	
			$user = $adapter->getProvider()->getResourceOwner( $token );

			update_option( 'cc_instagram_user', $user->toArray() );

			wp_safe_redirect( admin_url( 'options-general.php?page=cc-instagram' ) );
			exit;

		} catch ( Exception $e ) {
			exit( 'Oh dear... ' . $e->getMessage() );
		}
	}
}
add_action( 'admin_menu', 'instagram_block_authorization_code', 0 );
