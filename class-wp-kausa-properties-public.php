<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://wppb.me
 * @since      1.0.0
 *
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/public
 * @author     Kausa  <testingemailer1212@gmail.com>
 */
class Wp_Kausa_Properties_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style('kausa-ion-css-range', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/css/ion.rangeSlider.min.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		wp_enqueue_style('kausa-datatables-style', 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		wp_enqueue_style('kausa-datatables-responsive-style', 'https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		wp_enqueue_style('kausa-datatables-bootstrap', 'https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		wp_enqueue_style('kausa-datatables-bootstrap-buttons', 'https://cdn.datatables.net/buttons/1.2.2/css/buttons.bootstrap.min.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		wp_enqueue_style('kausa-slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		wp_enqueue_style('kausa-bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all');
		wp_enqueue_style('kausa-property-style', plugin_dir_url( __FILE__ ) . 'css/wp-kausa-properties-public.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		wp_enqueue_style('kausa-property-agency-style', plugin_dir_url( __FILE__ ) . 'css/agency/wp-kausa-properties-agency.css', array(), WP_KAUSA_PROPERTIES_VERSION, 'all' );
		
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script('kausa-ion-js-range', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/js/ion.rangeSlider.min.js', ['jquery'], WP_KAUSA_PROPERTIES_VERSION, true);
		wp_enqueue_script('kausa-datatables-js', 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array('jquery'), WP_KAUSA_PROPERTIES_VERSION, false );
		wp_enqueue_script('kausa-datatables-responsive-js', 'https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js', array('jquery'), WP_KAUSA_PROPERTIES_VERSION, false );
		wp_enqueue_script('kausa-slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js', ['jquery'], WP_KAUSA_PROPERTIES_VERSION, true);
		wp_enqueue_script('kausa-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js', [], WP_KAUSA_PROPERTIES_VERSION, true);
		wp_enqueue_script('kausa-property-script', plugin_dir_url( __FILE__ ) . 'js/wp-kausa-properties-public.js', array( 'jquery' ), WP_KAUSA_PROPERTIES_VERSION, false );
		wp_localize_script('kausa-property-script', 'kausaPropertiesPublicAjax', [ 'ajax_url' => admin_url('admin-ajax.php')]);
		wp_enqueue_script('kausa-property-filter', plugin_dir_url(__FILE__) . 'js/wp-kausa-property-filter.js', ['jquery'], WP_KAUSA_PROPERTIES_VERSION, true);
		wp_localize_script('kausa-property-filter', 'kausaPropertiesAjax', [ 'ajax_url' => admin_url('admin-ajax.php')]);
		wp_enqueue_script('kausa-property-agency', plugin_dir_url(__FILE__) . 'js/agency/wp-kausa-properties-agency.js', ['jquery'], WP_KAUSA_PROPERTIES_VERSION, false);
		wp_localize_script('kausa-property-agency', 'kausaPropertiesAgencyAjax', [ 'ajax_url' => admin_url('admin-ajax.php')]);
	}

}
