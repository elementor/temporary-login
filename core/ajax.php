<?php
namespace TemporaryLogin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ajax {

	const USER_CAPABILITY = 'manage_options';

	public static function register_hooks() {
		add_action( 'wp_ajax_temporary_login_get_app_data', [ __CLASS__, 'get_app_data' ] );
		add_action( 'wp_ajax_temporary_login_generate_temporary_user', [ __CLASS__, 'enable_access' ] );
		add_action( 'wp_ajax_temporary_login_revoke_temporary_users', [ __CLASS__, 'revoke_access' ] );
		add_action( 'wp_ajax_temporary_login_extend_access', [ __CLASS__, 'extend_access' ] );
	}

	public static function get_app_data() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'e-premium-support-admin-' . get_current_user_id() ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}

		if ( ! current_user_can( static::USER_CAPABILITY ) ) {
			wp_send_json_error( esc_html__( "You don't have permission to access this request", 'temporary-login' ) );
		}

		$data = [
			'status' => 'inactive',
		];

		$temporary_users = Options::get_temporary_users();
		if ( ! empty( $temporary_users ) ) {
			$temporary_user = $temporary_users[0];
			$data = static::get_active_page_data( $temporary_user );
		}

		wp_send_json_success( $data );
	}

	private static function verify_request( $post_data ) {
		if ( empty( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'e-premium-support-admin-' . get_current_user_id() ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}

		if ( ! current_user_can( static::USER_CAPABILITY ) ) {
			wp_send_json_error( esc_html__( "You don't have permission to access this request", 'temporary-login' ) );
		}
	}

	private static function get_active_page_data( \WP_User $temporary_user ): array {
		return [
			'status' => 'active',
			'login_url' => Options::get_login_url( $temporary_user->ID ),
			'expiration_human' => Options::get_expiration_human( $temporary_user->ID ),
		];
	}

	public static function enable_access() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'e-premium-support-admin-' . get_current_user_id() ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}

		if ( ! current_user_can( static::USER_CAPABILITY ) ) {
			wp_send_json_error( esc_html__( "You don't have permission to access this request", 'temporary-login' ) );
		}

		if ( Options::has_temporary_user() ) {
			// Temporary user already exists
			wp_send_json_success();
		}

		$user_id = Options::generate_temporary_user();
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( $user_id );
		}

		wp_send_json_success();
	}

	public static function revoke_access() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'e-premium-support-admin-' . get_current_user_id() ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}

		if ( ! current_user_can( static::USER_CAPABILITY ) ) {
			wp_send_json_error( esc_html__( "You don't have permission to access this request", 'temporary-login' ) );
		}

		Options::remove_all_temporary_users();

		wp_send_json_success();
	}

	public static function extend_access() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'e-premium-support-admin-' . get_current_user_id() ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}

		if ( ! current_user_can( static::USER_CAPABILITY ) ) {
			wp_send_json_error( esc_html__( "You don't have permission to access this request", 'temporary-login' ) );
		}

		$temporary_users = Options::get_temporary_users();
		if ( empty( $temporary_users ) ) {
			wp_send_json_error( new \WP_Error( 'no_temporary_users', 'No temporary users found' ) );
		}

		$user = $temporary_users[0];

		if ( ! Options::extend_expiration( $user->ID ) ) {
			wp_send_json_error( new \WP_Error( 'no_expiration', 'No expiration found' ) );
		}

		wp_send_json_success();
	}
}
