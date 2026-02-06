<?php
/**
 * Plugin Name: WP Admin Dark Mode
 * Plugin URI: https://alexanderwagnerdev.com/wp-admin-dark-mode-plugin/
 * Description: Simple, lightweight Dark Mode toggle for the WordPress Admin Dashboard.
 * Version: 0.0.1
 * Requires at least: 6.0
 * Tested up to: 6.9.1
 * Requires PHP: 7.4
 * Author: AlexanderWagnerDev
 * Author URI: https://alexanderwagnerdev.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-admin-dark-mode
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
		'wp-admin-dark-mode',
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
		ADM_URL . 'assets/css/wp-admin-dark.css',
		array(),
		ADM_VERSION
	);
} );

/**
 * Settings page (Settings -> Dark Mode).
 */
add_action( 'admin_menu', function () {
	add_options_page(
		__( 'WP Admin Dark Mode', 'wp-admin-dark-mode' ),
		__( 'Dark Mode', 'wp-admin-dark-mode' ),
		'manage_options',
		'wp-admin-dark-mode',
		'adm_settings_page'
	);
} );

function adm_settings_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'WP Admin Dark Mode', 'wp-admin-dark-mode' ); ?></h1>

		<div class="adm-card">
			<h2 style="margin-top:0;"><?php echo esc_html__( 'Dark Mode Settings', 'wp-admin-dark-mode' ); ?></h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'adm_settings' );
				do_settings_sections( 'adm_settings' );
				submit_button();
				?>
			</form>
		</div>
	</div>

	<style>
		.adm-card{
			max-width:720px;
			background:#fff;
			border:1px solid #dcdcde;
			border-radius:12px;
			padding:18px 18px 6px;
			box-shadow:0 1px 2px rgba(0,0,0,.06);
		}

		.adm-field-row{
			display:flex;
			align-items:center;
			justify-content:space-between;
			gap:16px;
			padding:12px 0;
		}

		.adm-field-title{
			font-size:14px;
			font-weight:600;
			margin:0;
		}

		.adm-toggle{
			position:relative;
			width:54px;
			height:30px;
			flex:0 0 auto;
		}

		.adm-toggle input{
			opacity:0;
			width:0;
			height:0;
		}

		.adm-slider{
			position:absolute;
			inset:0;
			background:#c3c4c7;
			border-radius:999px;
			transition:all .2s ease;
			cursor:pointer;
		}

		.adm-slider::before{
			content:"";
			position:absolute;
			width:24px;
			height:24px;
			left:3px;
			top:3px;
			background:#fff;
			border-radius:50%;
			transition:all .2s ease;
			box-shadow:0 1px 2px rgba(0,0,0,.25);
		}

		.adm-toggle input:checked + .adm-slider{
			background:#2271b1;
		}

		.adm-toggle input:checked + .adm-slider::before{
			transform:translateX(24px);
		}
	</style>
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
		__( 'Settings', 'wp-admin-dark-mode' ),
		'__return_null',
		'adm_settings'
	);

	add_settings_field(
		'adm_enabled',
		__( 'Enable Dark Mode', 'wp-admin-dark-mode' ),
		'adm_render_enabled_field',
		'adm_settings',
		'adm_section'
	);
} );

function adm_render_enabled_field() {
	$enabled = (bool) get_option( 'adm_dark_mode_enabled', false );
	?>
	<div class="adm-field-row">
		<p class="adm-field-title"><?php echo esc_html__( 'Enable Dark Mode', 'wp-admin-dark-mode' ); ?></p>

		<label class="adm-toggle">
			<input
				type="checkbox"
				id="adm_dark_mode_enabled"
				name="adm_dark_mode_enabled"
				value="1"
				<?php checked( true, $enabled ); ?>
			/>
			<span class="adm-slider" aria-hidden="true"></span>
		</label>
	</div>
	<?php
}

/**
 * Admin notice after saving settings.
 */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	if ( $page !== 'wp-admin-dark-mode' ) {
		return;
	}

	if ( empty( $_GET['settings-updated'] ) ) {
		return;
	}

	if ( ! get_option( 'adm_dark_mode_enabled', false ) ) {
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>';
	echo esc_html__( 'Admin Dark Mode is enabled.', 'wp-admin-dark-mode' );
	echo '</p></div>';
} );

/**
 * Settings Link im Plugins-Menü (auch wenn deaktiviert).
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $actions ) {
	$settings_url = admin_url( 'options-general.php?page=wp-admin-dark-mode' );
	$actions['settings'] = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'wp-admin-dark-mode' ) . '</a>';
	return $actions;
} );