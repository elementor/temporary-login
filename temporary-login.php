<?php
/**
 * Plugin Name: Temporary Login
 * Description: Create simple, no password user access with a single click.
 * Plugin URI: https://elementor.com/
 * Author: Elementor.com
 * Author URI: https://elementor.com/?utm_source=wp-plugins&utm_campaign=temp-login&utm_medium=wp-dash
 * Version: 1.3.0
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Text Domain: temporary-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'TEMPORARY_LOGIN_VERSION', '1.3.0' );

define( 'TEMPORARY_LOGIN__FILE__', __FILE__ );
define( 'TEMPORARY_LOGIN_PLUGIN_BASE', plugin_basename( TEMPORARY_LOGIN__FILE__ ) );
define( 'TEMPORARY_LOGIN_PATH', plugin_dir_path( TEMPORARY_LOGIN__FILE__ ) );

define( 'TEMPORARY_LOGIN_URL', plugins_url( '/', TEMPORARY_LOGIN__FILE__ ) );
define( 'TEMPORARY_LOGIN_ASSETS_PATH', plugin_dir_path( TEMPORARY_LOGIN__FILE__ ) . 'assets/' );
define( 'TEMPORARY_LOGIN_ASSETS_URL', TEMPORARY_LOGIN_URL . 'assets/' );


add_action( 'plugins_loaded', 'temporary_login_load_plugin_textdomain' );

if ( ! version_compare( PHP_VERSION, '7.4', '>=' ) ) {
	add_action( 'admin_notices', 'temporary_login_fail_php_version' );
} elseif ( ! version_compare( get_bloginfo( 'version' ), '6.2', '>=' ) ) {
	add_action( 'admin_notices', 'temporary_login_fail_wp_version' );
} else {
	require TEMPORARY_LOGIN_PATH . '/plugin.php';
}

function temporary_login_load_plugin_textdomain() {
	load_plugin_textdomain( 'temporary-login' );
}

function temporary_login_fail_php_version() {
	$message = sprintf(
		/* translators: 1: `<h3>` opening tag, 2: `</h3>` closing tag, 3: PHP version. 4: Link opening tag, 5: Link closing tag. */
		esc_html__( '%1$sTemporary Login isn’t running because PHP is outdated.%2$s Update to PHP version %3$s and get back to creating! %4$sShow me how%5$s', 'elementor' ),
		'<h3>',
		'</h3>',
		'7.4',
		'<a href="https://go.elementor.com/wp-dash-update-php/" target="_blank">',
		'</a>'
	);
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

function temporary_login_fail_wp_version() {
	$message = sprintf(
		/* translators: 1: `<h3>` opening tag, 2: `</h3>` closing tag, 3: WordPress version. 4: Link opening tag, 5: Link closing tag. */
		esc_html__( '%1$sTemporary Login isn’t running because WordPress is outdated.%2$s Update to WordPress version %3$s and get back to creating! %4$sShow me how%5$s', 'elementor' ),
		'<h3>',
		'</h3>',
		'6.2',
		'<a href="https://go.elementor.com/wp-dash-update-wp/" target="_blank">',
		'</a>'
	);
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}
