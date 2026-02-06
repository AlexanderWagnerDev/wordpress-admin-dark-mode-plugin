<?php
/**
 * Plugin Name: Admin Dark Mode
 * Plugin URI: https://alexanderwagnerdev.com/wordpress/admin-dark-mode-plugin/
 * Description: Simple, lightweight Dark Mode toggle for the WordPress Admin Dashboard.
 * Version: 0.0.1
 * Requires at least: 6.0
 * Tested up to: 6.9.1
 * Requires PHP: 7.4
 * Author: AlexanderWagnerDev
 * Author URI: https://alexanderwagnerdev.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: admin-dark-mode
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADM_VERSION', '0.0.1' );
define( 'ADM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load translations from /languages.
 */
add_action( 'plugins_loaded', function () {
	load_plugin_textdomain(
		'admin-dark-mode',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
} );

/**
 * Enqueue admin CSS only when enabled.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! get_option( 'adm_dark_mode_enabled', false ) ) {
		return;
	}

	wp_enqueue_style(
		'adm-darkmode',
		ADM_URL . 'assets/css/admin-dark.css',
		array(),
		ADM_VERSION
	);
} );

/**
 * Settings page (Settings -> Dark Mode).
 */
add_action( 'admin_menu', function () {
	add_options_page(
		__( 'Admin Dark Mode', 'admin-dark-mode' ),
		__( 'Dark Mode', 'admin-dark-mode' ),
		'manage_options',
		'admin-dark-mode',
		'adm_settings_page'
	);
} );

function adm_settings_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Admin Dark Mode', 'admin-dark-mode' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'adm_settings' );
			do_settings_sections( 'adm_settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Register setting + field.
 */
add_action( 'admin_init', function () {
	register_setting(
		'adm_settings',
		'adm_dark_mode_enabled',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => function ( $value ) {
				return (bool) $value;
			},
			'default'           => false,
		)
	);

	add_settings_section(
		'adm_section',
		__( 'Settings', 'admin-dark-mode' ),
		'__return_null',
		'adm_settings'
	);

	add_settings_field(
		'adm_enabled',
		__( 'Enable Dark Mode', 'admin-dark-mode' ),
		'adm_render_enabled_field',
		'adm_settings',
		'adm_section'
	);
} );

function adm_render_enabled_field() {
	$enabled = (bool) get_option( 'adm_dark_mode_enabled', false );

	echo '<label>';
	echo '<input type="checkbox" name="adm_dark_mode_enabled" value="1" ' . checked( true, $enabled, false ) . ' />';
	echo ' ' . esc_html__( 'Enabled', 'admin-dark-mode' );
	echo '</label>';
}

/**
 * Admin notice after saving settings.
 */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_GET['page'] ) || $_GET['page'] !== 'admin-dark-mode' ) {
		return;
	}

	if ( empty( $_GET['settings-updated'] ) ) {
		return;
	}

	if ( ! get_option( 'adm_dark_mode_enabled', false ) ) {
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>';
	echo esc_html__( 'Admin Dark Mode is enabled.', 'admin-dark-mode' );
	echo '</p></div>';
} );