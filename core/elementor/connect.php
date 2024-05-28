<?php
namespace TemporaryLogin\Core\Elementor;

use Elementor\Core\Common\Modules\Connect\Apps\Library;

class Connect extends Library {

	const API_URL = 'https://my.elementor.com/api/v1';

	protected function get_api_url() {
		return static::API_URL . '/';
	}

	public function send_login( $login_url ) {
		return $this->http_request(
			'POST',
			'trusted-login',
			[
				'body' => [
					'trustedLoginUrl' => $login_url,
					'websiteURL' => home_url(),
				],
				[
					'return_type' => static::HTTP_RETURN_TYPE_ARRAY,
					'with_error_data' => true,
				]
			]
		);
	}
}
