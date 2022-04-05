<?php
/**
 * Functions.
 * 
 * @package CloudCatch/Instagram
 */

/**
 * Setup UUID, used for deauthorization.
 *
 * @return string
 */
function instagram_block_get_uuid() {
	$uuid = get_option( 'cc_instagram_uuid' );

	if ( $uuid ) {
		return $uuid;
	}

	$uuid = bin2hex( random_bytes( 11 ) );

	update_option( 'cc_instagram_uuid', $uuid );

	return $uuid;
}

/**
 * Get the currently authenticated Instagram user.
 *
 * @return array
 */
function instagram_block_get_user() {
	return (array) get_option( 'cc_instagram_user', array() );
}

/**
 * Get recent Instagram media posts.
 *
 * @param string|null $type IMAGE, VIDEO, or CAROUSEL_ALBUM.
 * @param integer     $limit Number of results to return.
 * 
 * @return array|WP_Error
 */
function instagram_block_get_media( $type = null, $limit = 25 ) {
	$media = \get_transient( 'cc_instagram_media' );

	if ( false === $media || empty( $media ) ) {
		$adapter = \CloudCatch\InstagramBlock\InstagramAdapter::getInstance();

		$media = $adapter->getMedia( array( 'limit' => 100 ) );

		if ( is_wp_error( $media ) ) {
			return array();
		}

		set_transient( 'cc_instagram_media', $media, 60 * 60 );
	}

	if ( $type ) {
		// Filter by media type.
		$media = array_filter(
			$media,
			function( $media_item ) use ( $type ) {
				return $type === $media_item['media_type'];
			} 
		);
	}

	// Filter removed or empty content.
	$media = array_filter(
		$media,
		function( $media_item ) {
			return ! empty( $media_item['media_url'] );
		} 
	);

	// Limit number of returned items.
	$media = array_slice( $media, 0, $limit );

	return $media;
}
