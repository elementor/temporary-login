<?php
namespace TemporaryLogin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin_Pointer {

	const CURRENT_POINTER_SLUG = 'temporary-login-pointer';

	public static function add_hooks() {
		add_action( 'admin_print_footer_scripts-index.php', [ __CLASS__, 'admin_print_script' ] );
		add_action( 'wp_ajax_temporary_login_pointer_dismissed', [ __CLASS__, 'ajax_dismiss_pointer' ] );
	}

	public static function admin_print_script() {
		if ( ! current_user_can( Admin::USER_CAPABILITY ) ) {
			return;
		}

		if ( static::is_dismissed() ) {
			return;
		}

		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );

		$pointer_content = '<h3>' . esc_html__( 'Temporary Login', 'temporary-login' ) . '</h3>';
		$pointer_content .= '<p>' . esc_html__( 'Head over to the Temporary Login plugin to create single click access to your site admin.', 'temporary-login' ) . '</p>';

		$pointer_content .= sprintf(
			'<p><a class="button button-primary" href="%s">%s</a></p>',
			Admin::get_admin_page_url(),
			esc_html__( 'Take me there', 'temporary-login' )
		)

		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				$( '#menu-users' ).pointer( {
					content: '<?php echo wp_kses_post( $pointer_content ); ?>',
					position: {
						edge: <?php echo is_rtl() ? "'right'" : "'left'"; ?>,
						align: 'center'
					},
					close: function() {
						wp.ajax.post( 'temporary_login_pointer_dismissed', {
							data: {
								pointer: '<?php echo esc_attr( static::CURRENT_POINTER_SLUG ); ?>',
							},
							nonce: '<?php echo esc_attr( wp_create_nonce( static::CURRENT_POINTER_SLUG . '-dismissed' ) ); ?>',
						} );
					}
				} ).pointer( 'open' );
			} );
		</script>
		<?php
	}

	public static function is_dismissed(): bool {
		$dismissed = get_user_meta( get_current_user_id(), '_temporary_login_pointer_dismissed', true );
		return ! empty( $dismissed );
	}

	public static function ajax_dismiss_pointer() {
		check_ajax_referer( static::CURRENT_POINTER_SLUG . '-dismissed', 'nonce' );

		if ( empty( $_POST['data']['pointer'] ) || static::CURRENT_POINTER_SLUG !== $_POST['data']['pointer'] ) {
			wp_send_json_error( 'Invalid pointer' );
		}

		static::dismiss();

		wp_send_json_success();
	}

	public static function dismiss() {
		update_user_meta( get_current_user_id(), '_temporary_login_pointer_dismissed', 1 );
	}
}
