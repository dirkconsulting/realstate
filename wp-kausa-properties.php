<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://wppb.me
 * @since             1.0.0
 * @package           Wp_Kausa_Properties
 *
 * @wordpress-plugin
 * Plugin Name:       WP Kausa Properties
 * Plugin URI:        https://https://wppb.me
 * Description:       For list real estate properties on website and work with agencies
 * Version:           1.0.0
 * Author:            Kausa 
 * Author URI:        https://https://wppb.me/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-kausa-properties
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

if ( ! defined( 'WP_KAUSA_PROPERTIES_VERSION' ) ) {
    define( 'WP_KAUSA_PROPERTIES_VERSION', time() );
}

if ( ! defined( 'WP_KAUSA_PROPERTIES_PATH' ) ) {
    define( 'WP_KAUSA_PROPERTIES_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WP_KAUSA_PROPERTIES_PLUGIN_URL' ) ) {
    define( 'WP_KAUSA_PROPERTIES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WP_KAUSA_PROPERTIES_PLUGIN_FILE' ) ) {
    define( 'WP_KAUSA_PROPERTIES_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WP_KAUSA_PROPERTIES_PLUGIN_FILE_URL' ) ) {
    define( 'WP_KAUSA_PROPERTIES_PLUGIN_FILE_URL', plugins_url( basename( __FILE__ ), __FILE__ ) );
}

if ( ! defined( 'WP_KAUSA_PROPERTIES_ERROR_REPORTING' ) ) {
    define( 'WP_KAUSA_PROPERTIES_ERROR_REPORTING', true );
}

if ( ! defined( 'WP_KAUSA_PROPERTIES_UPLOAD_DIR' ) ) {
    $upload_dir = wp_upload_dir();
    define( 'WP_KAUSA_PROPERTIES_UPLOAD_DIR', $upload_dir['basedir'] . '/wp-kausa-properties' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-kausa-properties-activator.php
 */
function activate_wp_kausa_properties() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-kausa-properties-activator.php';
	Wp_Kausa_Properties_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-kausa-properties-deactivator.php
 */
function deactivate_wp_kausa_properties() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-kausa-properties-deactivator.php';
	Wp_Kausa_Properties_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_kausa_properties' );
register_deactivation_hook( __FILE__, 'deactivate_wp_kausa_properties' );

/**
 * The code that runs during plugin load.
 * This action is documented in includes/class-wp-kausa-properties-onload.php
 */
function onload_wp_kausa_properties() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-kausa-properties-onload.php';
    Wp_Kausa_Properties_Onload::onLoad();
}
onload_wp_kausa_properties();

/**
 * The code that runs during plugin load.
 * This action is documented in includes/class-wp-kausa-properties-ajaxcallback.php
 */
function ajaxCallbackLoads_wp_kausa_properties() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-kausa-properties-ajaxcallback.php';
    Wp_Kausa_Properties_AjaxCallback::ajaxCallbackLoads();
}
ajaxCallbackLoads_wp_kausa_properties();

if ( WP_KAUSA_PROPERTIES_ERROR_REPORTING ) {
	wp_kausa_properties_error_reporting_function();
}

function wp_kausa_properties_error_reporting_function() {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

function wp_kausa_properties_create_upload_dir() {
    $upload_dir = WP_KAUSA_PROPERTIES_UPLOAD_DIR;
    if ( ! file_exists( $upload_dir ) ) { wp_mkdir_p( $upload_dir ); }
}
add_action( 'init', 'wp_kausa_properties_create_upload_dir' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-kausa-properties.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_wp_kausa_properties() {

	$plugin = new Wp_Kausa_Properties();
	$plugin->run();

}
run_wp_kausa_properties();
