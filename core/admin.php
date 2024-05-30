<?php
namespace TemporaryLogin\Core;

use TemporaryLogin\Core\Elementor\Connect as Elementor_Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin {

	const USER_CAPABILITY = 'manage_options';

	const ADMIN_SLUG = 'temporary-login';

	public static function register_hooks() {
		Ajax::register_hooks();
		Admin_Pointer::add_hooks();

		register_deactivation_hook( TEMPORARY_LOGIN__FILE__, [ __CLASS__, 'on_deactivate_plugin' ] );

		add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );

		add_filter( 'plugin_action_links_' . TEMPORARY_LOGIN_PLUGIN_BASE, [ __CLASS__, 'plugin_action_links' ] );

		add_filter( 'allow_password_reset', [ __CLASS__, 'disable_password_reset' ], 10, 2 );
		add_filter( 'wp_authenticate_user', [ __CLASS__, 'disallow_temporary_user_login' ] );

		add_action( 'init', [ __CLASS__, 'maybe_login_temporary_user' ] );
		add_action( 'init', [ __CLASS__, 'maybe_logout_expired_users' ] );

		add_action( 'admin_init', [ __CLASS__, 'maybe_remove_temporary_users' ] );

		// Elementor Connect integration
		add_action( 'elementor/connect/apps/register', function ( $connect_module ) {
			$connect_module->register_app( 'temp-login', Elementor_Connect::get_class_name() );
		} );
	}

	public static function on_deactivate_plugin() {
		Options::remove_all_temporary_users();
	}

	public static function register_menu() {
		$hook = add_submenu_page(
			static::get_parent_page_id(),
			__( 'Temporary Login', 'temporary-login' ),
			__( 'Temporary Login', 'temporary-login' ),
			static::USER_CAPABILITY,
			static::ADMIN_SLUG,
			[ __CLASS__, 'render_page' ]
		);

		add_action( "load-$hook", [ __CLASS__, 'enqueue_scripts' ] );
	}

	private static function get_parent_page_id() {
		return 'users.php';
	}

	public static function render_page() {
		?>
		<div class="wrap">
			<div id="temporary-login-admin"></div>
		</div>
		<?php
	}

	public static function disable_password_reset( $allow, $user_ID ) {
		if ( ! empty( $user_ID ) && Options::is_temporary_user( $user_ID ) ) {
			$allow = false;
		}

		return $allow;
	}

	public static function disallow_temporary_user_login( $user ) {
		if ( $user instanceof \WP_User && Options::is_temporary_user( $user->ID ) ) {
			$user = new \WP_Error(
				'invalid_username',
				__( '<strong>Error:</strong> The username is not registered on this site. If you are unsure of your username, try your email address instead.', 'temporary-login' )
			);
		}

		return $user;
	}

	public static function enqueue_scripts() {
		$script_asset_path = TEMPORARY_LOGIN_ASSETS_PATH . 'admin.asset.php';
		if ( ! file_exists( $script_asset_path ) ) {
			throw new \Error( 'You must to run `npm run build`.' );
		}

		$script_asset = require( $script_asset_path );
		$app_js_url = TEMPORARY_LOGIN_ASSETS_URL . 'admin.js';

		wp_register_script(
			'e-premium-support-admin',
			$app_js_url,
			$script_asset['dependencies'],
			$script_asset['version']
		);
		wp_set_script_translations( 'e-premium-support-admin', 'temporary-login' );
		wp_enqueue_script( 'e-premium-support-admin' );

		wp_localize_script(
			'e-premium-support-admin',
			'ePremiumSupportSettings',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'e-premium-support-admin-' . get_current_user_id() ),
			]
		);

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'wp-editor' );

		if ( ! Admin_Pointer::is_dismissed() ) {
			Admin_Pointer::dismiss();
		}
	}

	public static function plugin_action_links( $links ) {
		$settings_link = '<a href="' . static::get_admin_page_url() . '">' . esc_html__( 'Create Temporary Login', 'temporary-login' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public static function get_admin_page_url(): string {
		return admin_url( static::get_parent_page_id() . '?page=' . static::ADMIN_SLUG );
	}

	public static function maybe_login_temporary_user() {
		if ( empty( $_GET['temp-login-token'] ) ) {
			return;
		}

		$token = sanitize_key( $_GET['temp-login-token'] );

		$is_site_token_validated = true;

		$site_token = Options::get_site_token();
		if ( ! empty( $site_token ) ) {
			$is_site_token_validated = ! empty( $_GET['tl-site'] ) && $site_token === $_GET['tl-site'];
		}

		$user = Options::get_user_by_token( $token );

		if ( ! $user || ! $is_site_token_validated || Options::is_user_expired( $user->ID ) ) {
			wp_safe_redirect( home_url() );
			die;
		}

		if ( is_user_logged_in() ) {
			$current_user_id = get_current_user_id();
			if ( $user->ID !== $current_user_id ) {
				wp_logout();
			}
		}

		$action = '';
		if ( ! empty( $_GET['temp-login-action'] ) ) {
			$action = sanitize_key( $_GET['temp-login-action'] );
		}

		if ( 'info' === $action ) {
			static::print_token_details( $user );
		}

		if ( 'revoke' === $action ) {
			static::process_remote_revoke_access();
		}

		static::process_login( $user );
	}

	private static function process_login( \WP_User $user ) {
		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID );

		do_action( 'wp_login', $user->user_login, $user );

		wp_safe_redirect( admin_url() );
		die;
	}

	private static function print_token_details( \WP_User $user ) {
		$data = [
			'expiration' => Options::get_expiration( $user->ID ),
		];

		wp_send_json_success( $data );
	}

	private static function process_remote_revoke_access() {
		Options::remove_all_temporary_users();

		wp_send_json_success();
	}

	public static function maybe_logout_expired_users() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! Options::is_temporary_user( $user_id ) ) {
			return;
		}

		if ( ! Options::is_user_expired( $user_id ) ) {
			return;
		}

		wp_logout();
		wp_safe_redirect( home_url() );
		die;
	}

	public static function maybe_remove_temporary_users() {
		Options::remove_expired_temporary_users();
	}
}
