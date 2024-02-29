<?php
namespace TemporaryLogin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	public static $instance = null;

	public function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf( 'Cloning instances of the singleton "%s" class is forbidden.', get_class( $this ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'1.0.0'
		);
	}

	public function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf( 'Unserializing instances of the singleton "%s" class is forbidden.', get_class( $this ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'1.0.0'
		);
	}

	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->register_autoloader();
		$this->initial_components();
	}

	private function register_autoloader(): void {
		require_once TEMPORARY_LOGIN_PATH . '/autoloader.php';
		Autoloader::run();
	}

	private function initial_components() {
		Core\Admin::register_hooks();
	}
}

Plugin::instance();
