<?php
/**
 * Plugin Name: Instagram Block
 * Description: Gutenberg Instagram Block.
 * Author: dkjensen,cloudcatch
 * Author URI: https://cloudcatch.io
 * Version: 1.0.0
 * Requires PHP: 7.0.0
 * Request at least: 5.8
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cc-instagram
 * Domain Path: /languages
 *
 * @package CloudCatch/InstagramBlock
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLOUDCATCH_INSTAGRAM_BLOCK_DIR', plugin_dir_path( __FILE__ ) );
define( 'CLOUDCATCH_INSTAGRAM_BLOCK_URL', plugin_dir_url( __FILE__ ) );

/**
 * Composer dependencies.
 */
require_once CLOUDCATCH_INSTAGRAM_BLOCK_DIR . 'vendor/autoload.php';

/**
 * Block Initializer.
 */
require_once CLOUDCATCH_INSTAGRAM_BLOCK_DIR . 'src/block/init.php';

/**
 * Settings.
 */
require_once CLOUDCATCH_INSTAGRAM_BLOCK_DIR . 'src/settings.php';

/**
 * Functions.
 */
require_once CLOUDCATCH_INSTAGRAM_BLOCK_DIR . 'src/functions.php';

/**
 * Load plugin text domain
 *
 * @return void
 */
function instagram_block_load_textdomain() {
	load_plugin_textdomain( 'cc-instagram', false, CLOUDCATCH_INSTAGRAM_BLOCK_DIR . 'languages' );
}
add_action( 'init', 'instagram_block_load_textdomain' );

/**
 * Setup data on activation.
 *
 * @return void
 */
function instagram_block_install() {
	update_option( 'cc_instagram_uuid', bin2hex( random_bytes( 11 ) ) );
}
register_activation_hook( __FILE__, 'instagram_block_install' );

/**
 * Remove all data on deactivation.
 *
 * @return void
 */
function instagram_block_uninstall() {
	delete_transient( 'cc_instagram_media' );
	delete_option( 'cc_instagram_uuid' );
	delete_option( 'cc_instagram_auth_state' );
	delete_option( 'cc_instagram_token' );
	delete_option( 'cc_instagram_user' );
}
register_deactivation_hook( __FILE__, 'instagram_block_uninstall' );
