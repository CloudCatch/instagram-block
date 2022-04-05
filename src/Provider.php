<?php
/**
 * OAuth Adapter
 *
 * @package CloudCatch/InstagramBlock
 */

namespace CloudCatch\InstagramBlock;

use League\OAuth2\Client\Provider\Instagram;

/**
 * Let's override a couple things...
 */
class Provider extends Instagram {

	/**
	 * Base oAuth URL.
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl() {
		return 'https://oauth.cloudcatch.io/v1/instagram';
	}

	/**
	 * Base Token URL.
	 *
	 * @param array $params Parameters to pass to endpoint.
	 * @return string
	 */
	public function getBaseAccessTokenUrl( array $params ) {
		return 'https://oauth.cloudcatch.io/v1/instagram/token';
	}
}
