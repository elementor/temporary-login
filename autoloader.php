<?php
namespace TemporaryLogin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Autoloader {

	private static $default_path;

	private static $default_namespace;

	public static function run( $default_path = '', $default_namespace = '' ) : void {
		if ( '' === $default_path ) {
			$default_path = TEMPORARY_LOGIN_PATH;
		}

		if ( '' === $default_namespace ) {
			$default_namespace = __NAMESPACE__;
		}

		self::$default_path = $default_path;
		self::$default_namespace = $default_namespace;

		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	private static function load_class( $relative_class_name ) : void {
		$filename = strtolower(
			preg_replace(
				[ '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[ '$1-$2', '-', DIRECTORY_SEPARATOR ],
				$relative_class_name
			)
		);

		$filename = self::$default_path . $filename . '.php';

		if ( is_readable( $filename ) ) {
			require $filename;
		}
	}

	private static function autoload( $class ) : void {
		if ( 0 !== strpos( $class, self::$default_namespace . '\\' ) ) {
			return;
		}

		$relative_class_name = preg_replace( '/^' . self::$default_namespace . '\\\/', '', $class );

		$final_class_name = self::$default_namespace . '\\' . $relative_class_name;

		if ( ! class_exists( $final_class_name ) ) {
			self::load_class( $relative_class_name );
		}
	}
}
