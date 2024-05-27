<?php
namespace TemporaryLogin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Options {

	public static function has_temporary_user() : bool {
		$users = static::get_temporary_users();

		return ! empty( $users );
	}

	public static function get_temporary_users() : array {
		return get_users( [
			'meta_key' => '_temporary_login',
			'meta_value' => 'yes',
		] );
	}

	/**
	 * @return int|\WP_Error
	 */
	public static function generate_temporary_user() {
		$username = 'temp-login-' . wp_generate_password( 15, false );
		$password = wp_generate_password( 64, true, true );
		$token = static::generate_token();

		$user_id = wp_insert_user( [
			'user_login' => $username,
			'user_pass' => $password,
			'role' => 'administrator',
		] );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// TODO: Add multisite support

		$user_metas = [
			'_temporary_login' => 'yes',
			'_temporary_login_token' => $token,
			'_temporary_login_expiration' => static::get_max_expired_time(),
			'_temporary_login_pointer_dismissed' => 1,

			'show_welcome_panel' => 0,
			'locale' => 'en_US',
		];

		foreach ( $user_metas as $meta_key => $meta_value ) {
			update_user_meta( $user_id, $meta_key, $meta_value );
		}

		if ( ! static::get_site_token() ) {
			static::create_site_token();
		}

		return $user_id;
	}

	private static function get_max_expired_time(): int {
		return current_time( 'timestamp' ) + WEEK_IN_SECONDS;
	}

	private static function generate_token( $length = 32 ): string {
		return bin2hex( random_bytes( $length ) );
	}

	public static function get_site_token() {
		return get_option( '_temporary_login_site_token' );
	}

	private static function create_site_token(): void {
		$site_token = static::generate_token( 8 );

		update_option( '_temporary_login_site_token', $site_token );
	}

	private static function delete_site_token(): void {
		delete_option( '_temporary_login_site_token' );
	}

	public static function is_temporary_user( $user_ID ) : bool {
		return (bool) get_user_meta( $user_ID, '_temporary_login', true );
	}

	public static function get_login_url( $user_ID ): string {
		$token = get_user_meta( $user_ID, '_temporary_login_token', true );

		if ( empty( $token ) ) {
			return '';
		}

		$login_url = add_query_arg( [
			'temp-login-token' => $token,
		], admin_url() );

		$site_token = static::get_site_token();
		if ( ! empty( $site_token ) ) {
			$login_url = add_query_arg( [
				'tl-site' => $site_token,
			], $login_url );
		}

		return $login_url;
	}

	public static function get_expiration_human( $user_ID ): string {
		if ( static::is_user_expired( $user_ID ) ) {
			return esc_html__( 'Expired', 'temporary-login' );
		}

		$expiration_time = get_user_meta( $user_ID, '_temporary_login_expiration', true );

		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $expiration_time );
	}

	public static function get_expiration( $user_ID ) {
		return get_user_meta( $user_ID, '_temporary_login_expiration', true );
	}

	public static function extend_expiration( $user_ID ) {
		$expiration = static::get_expiration( $user_ID );

		if ( empty( $expiration ) ) {
			return false;
		}

		if ( static::is_user_expired( $user_ID ) ) {
			$expiration = current_time( 'timestamp' );
		}

		$expiration += 3 * DAY_IN_SECONDS;

		$expiration = min( $expiration, static::get_max_expired_time() );

		return update_user_meta( $user_ID, '_temporary_login_expiration', $expiration );
	}

	/**
	 * @param $token
	 *
	 * @return \WP_User|null
	 */
	public static function get_user_by_token( $token ) {
		$users = get_users( [
			'meta_key' => '_temporary_login_token',
			'meta_value' => $token,
		] );

		if ( empty( $users ) ) {
			return null;
		}

		return $users[0];
	}

	public static function is_user_expired( $user_ID ): bool {
		$expiration = static::get_expiration( $user_ID );

		if ( empty( $expiration ) ) {
			return true;
		}

		return current_time( 'timestamp' ) > $expiration;
	}

	public static function remove_all_temporary_users() {
		$temporary_users = static::get_temporary_users();

		if ( empty( $temporary_users ) ) {
			return;
		}

		if ( ! function_exists( 'wp_delete_user' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		foreach ( $temporary_users as $user ) {
			wp_delete_user( $user->ID );
		}

		static::delete_site_token();
	}

	public static function remove_expired_temporary_users() {
		$user_query = new \WP_User_Query( [
			'meta_key' => '_temporary_login_expiration',
			'meta_value' => current_time( 'timestamp' ),
			'meta_compare' => '<=',
			'fields' => 'ID',
		] );

		$users_IDs = $user_query->get_results();

		if ( empty( $users_IDs ) ) {
			return;
		}

		foreach ( $users_IDs as $user_ID ) {
			wp_delete_user( $user_ID );
		}
	}
}
