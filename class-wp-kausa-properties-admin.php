<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://wppb.me
 * @since      1.0.0
 *
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/admin
 * @author     Kausa  <testingemailer1212@gmail.com>
 */
class Wp_Kausa_Properties_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Kausa_Properties_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Kausa_Properties_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.	
		 */

		wp_enqueue_style( 'kausa-properties', plugin_dir_url( __FILE__ ) . 'css/wp-kausa-properties-admin.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.	
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Kausa_Properties_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Kausa_Properties_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'kausa-properties', plugin_dir_url( __FILE__ ) . 'js/wp-kausa-properties-admin.js', array( 'jquery' ), WP_KAUSA_PROPERTIES_VERSION, false );
		wp_localize_script('kausa-properties', 'kausaPropertiesAdminAjax', ['ajax_url' => admin_url('admin-ajax.php'),'nonce' => wp_create_nonce('kausa_property_status_nonce'),]);
		wp_enqueue_script( 'kausa-property-gallery', plugin_dir_url( __FILE__ ) . 'js/wp-kausa-properties-gallery.js', array( 'jquery' ), WP_KAUSA_PROPERTIES_VERSION, false );
		wp_enqueue_script( 'kausa-property-documents', plugin_dir_url( __FILE__ ) . 'js/wp-kausa-properties-documents.js', array( 'jquery' ), WP_KAUSA_PROPERTIES_VERSION, false );

	}

}
