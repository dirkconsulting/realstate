<?php

/**
 * Fired during plugin loading
 *
 * @link       https://wppb.me
 * @since      1.0.0
 *
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/includes
 */

/**
 * Class for plugin loading actions.
 *
 * This class defines all code necessary to run during the plugin's loading.
 *
 * @since      1.0.0
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/includes
 */
class Wp_Kausa_Properties_Onload {

    /**
     * Initialize the plugin and register the custom post type.
     */
    public static function onLoad() {
        self::add_agency_role();
        add_action('wp_loaded', [__CLASS__, 'add_floating_dashboard_button']);
        add_filter('admin_body_class', [__CLASS__, 'add_agency_profile_class']);

        add_action('init', [__CLASS__, 'register_kausa_properties_post_type']);
        add_action('admin_footer', [__CLASS__, 'hide_custom_fields_screen_option']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'add_custom_admin_sidebar_script']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_property_status_script']); // Asegurar que se encola el script

        add_filter('manage_edit-kausa_properties_columns', [__CLASS__, 'add_kausa_property_columns']);
        add_action('manage_kausa_properties_posts_custom_column', [__CLASS__, 'display_kausa_property_meta_column'], 10, 2);
        add_filter('manage_edit-kausa_properties_sortable_columns', [__CLASS__, 'kausa_property_sortable_columns']);

        add_action('show_user_profile', [__CLASS__, 'add_custom_address_fields']);
        add_action('edit_user_profile', [__CLASS__, 'add_custom_address_fields']);

        add_action('personal_options_update', [__CLASS__, 'save_custom_address_fields']);
        add_action('edit_user_profile_update', [__CLASS__, 'save_custom_address_fields']);

        add_filter('template_include', [__CLASS__, 'load_agency_dashboard_template']);
        add_filter('template_include', [__CLASS__, 'single_page_template_for_kausa_properties']);
        
        add_action('add_meta_boxes', [self::class, 'kausa_add_property_status_metabox']);
        add_action('add_meta_boxes', [self::class, 'kausa_properties_add_gallery_metabox']);
        add_action('save_post', [self::class, 'kausa_properties_save_gallery']);
        
        add_action('add_meta_boxes', [self::class, 'kausa_properties_add_documents_metabox']);
        add_action('save_post', [self::class, 'kausa_properties_save_documents']);
        
        add_action('add_meta_boxes', [self::class, 'kausa_property_meta_boxes']);
        add_action('save_post', [self::class, 'save_kausa_property_meta_box_data']);

        add_shortcode('kausa_properties_grid', [__CLASS__, 'kausa_properties_grid_shortcode']);
        add_shortcode('kausa_properties_grid_with_filter', [__CLASS__, 'kausa_properties_grid_with_filter_shortcode']);

        add_action('wp', [__CLASS__, 'kausa_property_check_cron']);
        add_filter('cron_schedules', [__CLASS__, 'kausa_property_check_cron_add_schedule']);
        add_action('kausa_property_check_cron_hook', [__CLASS__, 'kausa_property_check']);
        
        // Register admin menu
        add_action('admin_menu', [__CLASS__, 'register_kausa_properties_admin_menu']);

        // Register FAQS page
        add_action('admin_init', [__CLASS__, 'kausa_properties_register_faq_settings']);
        add_shortcode('kausa_properties_display_faqs', [__CLASS__, 'kausa_properties_display_faqs_shortcode']);

        // Añadir acción AJAX para manejar solicitudes de venta
        add_action('wp_ajax_kausa_request_sale', [__CLASS__, 'kausa_request_sale']);
    }

    /**
     * Add a custom user role for Agency.
     */
    public static function add_agency_role() {
        add_role(
            'agency',
            __('Agency', 'textdomain'),
            [
                'read' => true,           
                'edit_posts' => false,    
                'delete_posts' => false,
            ]
        );
    }

    /**
     * Add custom classes to the profile page
     */
    public static function add_agency_profile_class($classes) {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if (in_array('agency', $current_user->roles)) {
                if ('profile.php' === basename($_SERVER['PHP_SELF'])) {
                    $classes .= 'agency-section-box agency-profile-dashboard profile-section';
                }
            }
        }
        return $classes;
    }
    
    /**
     * Add the sidebar design for profile page
     */
    public static function add_custom_admin_sidebar_script() {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if (in_array('agency', $current_user->roles)) {
                $user_id = $current_user->ID;
                $user_avatar = get_avatar($user_id, 25);
                wp_enqueue_script('jquery');

                $inline_script = "jQuery(document).ready(function ($) {";
                $inline_script .= "if ($('body').hasClass('agency-profile-dashboard')) {";
                $inline_script .= "$('#adminmenu').html('');";
                $inline_script .= "const displayName = $('#wp-admin-bar-my-account .display-name').text().trim() || 'Agency';";
                $inline_script .= "$('#adminmenu').append(";
                $inline_script .= "'<link rel=\"stylesheet\" href=\"" . esc_url(WP_KAUSA_PROPERTIES_PLUGIN_URL . "public/css/agency/wp-kausa-properties-agency.css") . "\">' +";
                $inline_script .= "'<div class=\"sidebar open\">' +";
                $inline_script .= "'<div class=\"logo-details\">' +";
                $inline_script .= '"' . $user_avatar . '" +';
                $inline_script .= "'<div class=\"logo_name\">" . esc_html($current_user->display_name) . "</div>' +";
                $inline_script .= "'<i class=\"bx bx-menu-alt-right\" id=\"btn\"></i>' +";
                $inline_script .= "'</div>' +";
                $inline_script .= "'<ul class=\"nav-list scroll\">' +";
                $inline_script .= "'<li><a href=\"".esc_url(site_url('/agency-dashboard'))."\"><i class=\"bx bx-grid-alt\"></i><span class=\"links_name\">Panel</span></a><span class=\"tooltip\">Panel</span></li>' +";
                $inline_script .= "'<li><a class=\"active\" href=\"".esc_url(admin_url("profile.php")). "\"><i class=\"bx bx-user\"></i><span class=\"links_name\">Perfil</span></a><span class=\"tooltip\">Perfil</span></li>' +";
                $inline_script .= "'<li><a href=\"".esc_url(site_url('/agency-dashboard?section=sold'))."\"><i class=\"bx bx-cart-alt\"></i><span class=\"links_name\">Vendida</span></a><span class=\"tooltip\">Vendida</span></li>' +";
                $inline_script .= "'<li><a href=\"".esc_url(site_url('/agency-dashboard?section=reserved'))."\"><i class=\"bx bx-heart\"></i><span class=\"links_name\">Reservada</span></a><span class=\"tooltip\">Reservada</span></li>' +";
                $inline_script .= "'<li><a href=\"".esc_url(site_url('/agency-dashboard?section=faqs'))."\"><i class=\"bx bx-question-mark\"></i><span class=\"links_name\">Preguntas</span></a><span class=\"tooltip\">Preguntas</span></li>' +";
                $inline_script .= "'<li class=\"profile\"><div class=\"profile-details\"><div class=\"name_job\"><a href=\"" . esc_url(site_url()) . "\" class=\"agency-Back-home-button\" target=\"_blank\"><i class=\"bx bx-home\"></i><span>Volver al sitio web</span></a></div></div></li>' +";
                $inline_script .= "'</ul>' +";
                $inline_script .= "'</div>' +";
                $inline_script .= "'<a href=\"" . esc_url(wp_logout_url(site_url())) . "\" class=\"agency-Back-button\" target=\"_blank\"><i class=\"bx bx-log-out\" id=\"log_out\"></i></a>'";
                $inline_script .= ");";
                $inline_script .= "$.getScript('" . esc_url(WP_KAUSA_PROPERTIES_PLUGIN_URL . "public/js/agency/wp-kausa-properties-agency.js") . "');";
                $inline_script .= "}";
                $inline_script .= "});";

                wp_add_inline_script('jquery', $inline_script);

                // Asegurarnos de que el script wp-kausa-properties-agency.js tenga acceso al nonce y ajax_url
                wp_localize_script('jquery', 'kausaPropertiesAgencyAjax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('kausa_property_status_nonce')
                ));
            }
        }
    }

    /**
     * Add a floating button for Agency users.
     */
    public static function add_floating_dashboard_button() {
        add_action('wp_footer', function() {
            $property_details_translation = get_option("property_details_transalation", "");
            $go_to_dashboard = isset($property_details_translation['kausa-property-go-to-dashboard']) ? $property_details_translation['kausa-property-go-to-dashboard'] : 'Go to Dashboard'; 
            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                if (in_array('agency', $current_user->roles)) {
                    echo '<a href="' . esc_url(site_url('/agency-dashboard')) . '" class="agency-dashboard-btn">' . $go_to_dashboard . '</a>';
                }
            }
        });
    }

    /**
     * Add a Filter to Locate the Template in the Plugin Directory for Agency
     */
    public static function load_agency_dashboard_template($template) {
        if (is_page() && get_page_template_slug() === 'wp-kausa-properties-agency-dashboard.php') {
            $plugin_template = WP_KAUSA_PROPERTIES_PATH . 'public/partials/agency/wp-kausa-properties-agency-dashboard.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }

    /**
     * Register the 'kausa_properties' custom post type.
     */
    public static function register_kausa_properties_post_type() {
        register_post_type('kausa_properties', [
            'labels' => [
                'name' => 'Properties',
                'singular_name' => 'Property',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Property',
                'edit_item' => 'Edit Property',
                'new_item' => 'New Property',
                'view_item' => 'View Property',
                'search_items' => 'Search Properties',
                'not_found' => 'No properties found',
                'not_found_in_trash' => 'No properties found in Trash',
                'parent_item_colon' => '',
                'all_items' => 'All Properties',
                'archives' => 'Property Archives',
                'insert_into_item' => 'Insert into property',
                'uploaded_to_this_item' => 'Uploaded to this property',
                'filter_items_list' => 'Filter properties list',
                'items_list_navigation' => 'Properties list navigation',
                'items_list' => 'Properties list',
            ],
            'public' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'kausa_properties'),
        ]);
    }

    /**
     * Hide custom fields columns for 'kausa_properties' single edit
     */
    public static function hide_custom_fields_screen_option() {
        if (get_post_type() === 'kausa_properties') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    $('#screen-options-wrap input[name="custom_fields"]').prop('checked', false).closest('label').hide();
                });
            </script>
            <?php
        }
    }

    /**
     * Add custom columns for 'kausa_properties'
     */
    public static function add_kausa_property_columns($columns) {
        $date_column = isset($columns['date']) ? $columns['date'] : null;
        unset($columns['date']);
        $columns['property_status'] = 'Estado de la Propiedad';
        $columns['users'] = 'Users';
        $columns['property_time'] = 'Sold/Reserved Time';
        if ($date_column !== null) {
            $columns['date'] = $date_column;
        }
        return $columns;
    }

    /**
     * Populate the custom columns with data
     */
    public static function display_kausa_property_meta_column($column, $post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';

        switch ($column) {
            case 'property_status':
                $property_status = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT property_status FROM $table_name WHERE property_id = %d ORDER BY property_reserve_time DESC LIMIT 1",
                        $post_id
                    )
                );

                if ($property_status === 'pending') {
                    echo 'Pending Sale';
                } elseif ($property_status === 'confirmed') {
                    echo 'Sold';
                } elseif ($property_status === 'denied') {
                    echo 'Sale Denied';
                } elseif ($property_status === 'reserved') {
                    echo 'Reserved';
                } elseif ($property_status === 'unreserved') {
                    echo 'Available';
                } else {
                    $available = get_post_meta($post_id, '_kausa_property_available_to_reserve', true);
                    echo ($available === 'yes') ? 'Available' : 'Not Available';
                }
                break;

            case 'users':
                $property_status = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT property_status FROM $table_name WHERE property_id = %d ORDER BY property_reserve_time DESC LIMIT 1",
                        $post_id
                    )
                );

                if ($property_status === 'reserved') {
                    $reserved_by_user_id = get_post_meta($post_id, '_kausa_property_reserved_by_user', true);
                    $reserved_by_user = get_user_by('id', $reserved_by_user_id);
                    if ($reserved_by_user) {
                        echo '<a href="' . get_edit_user_link($reserved_by_user->ID) . '">' . esc_html($reserved_by_user->display_name) . '</a>';
                    }
                } elseif (in_array($property_status, ['pending', 'confirmed', 'denied'])) {
                    $sold_by_user_id = get_post_meta($post_id, '_kausa_property_sold_by_user', true);
                    $sold_by_user = get_user_by('id', $sold_by_user_id);
                    if ($sold_by_user) {
                        echo '<a href="' . get_edit_user_link($sold_by_user->ID) . '">' . esc_html($sold_by_user->display_name) . '</a>';
                    }
                } else {
                    echo '-';
                }
                break;

            case 'property_time':
                $property_status = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT property_status FROM $table_name WHERE property_id = %d ORDER BY property_reserve_time DESC LIMIT 1",
                        $post_id
                    )
                );

                if (in_array($property_status, ['pending', 'confirmed'])) {
                    $sold_time = get_post_meta($post_id, '_kausa_property_sold_time', true);
                    if ($sold_time) {
                        echo date('l, d M, Y H:i:s A', strtotime($sold_time));
                    } else {
                        echo 'N/A';
                    }
                } elseif ($property_status === 'reserved') {
                    $reserved_time = get_post_meta($post_id, '_kausa_property_reserved_time', true);
                    if ($reserved_time) {
                        echo date('l, d M, Y H:i:s A', strtotime($reserved_time));
                    } else {
                        echo 'N/A';
                    }
                } else {
                    echo '-';
                }
                break;
        }
    }

    /**
     * Make custom columns sortable
     */
    public static function kausa_property_sortable_columns($columns) {
        $columns['property_status'] = '_kausa_property_sold';
        $columns['users'] = '_kausa_property_reserved_by_user';
        $columns['property_time'] = '_kausa_property_sold_time';
        return $columns;
    }

    /**
     * Custom template for single 'kausa_properties' post type.
     */
    public static function single_page_template_for_kausa_properties($template) {
        if (is_singular('kausa_properties')) {
            $plugin_template = WP_KAUSA_PROPERTIES_PATH . 'public/partials/wp-kausa-properties-single-page.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }

    /**
     * Shortcode to display 'kausa_properties' grid with filter.
     */
    public static function kausa_properties_grid_with_filter_shortcode() {
        $file_path = WP_KAUSA_PROPERTIES_PATH . 'public/partials/wp-kausa-properties-archive-page.php';
        if (file_exists($file_path)) {
            ob_start();
            include $file_path;
            return ob_get_clean();
        } else {
            return '<p>Template file not found.</p>';
        }
    }

    /**
     * Hook to add custom status change meta boxes to property post type
     */
    public static function kausa_add_property_status_metabox() {
        add_meta_box(
            'kausa_property_status_metabox',
            __('Estado de la Propiedad', 'textdomain'),
            [self::class, 'kausa_property_status_metabox_callback'],
            'kausa_properties',
            'side'
        );
    }

    public static function kausa_property_status_metabox_callback($post) {
        global $wpdb;

        $role = '%"agency"%';
        $query = $wpdb->prepare(
            "SELECT u.ID, u.user_login, u.user_email, um.meta_value AS roles
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->usermeta} um
            ON u.ID = um.user_id
            WHERE um.meta_key = %s
            AND um.meta_value LIKE %s",
            $wpdb->prefix . 'capabilities',
            $role
        );

        $users = $wpdb->get_results($query);

        $property_id = $post->ID;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';
        $property_status = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT property_status FROM $table_name WHERE property_id = %d ORDER BY property_reserve_time DESC LIMIT 1",
                $property_id
            )
        );

        $avaliable_for_reserve = get_post_meta($property_id, '_kausa_property_avaliable_to_reserve', true);
        $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);
        $sale_status = $property_status ?: '';
        $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
        $sold_time = get_post_meta($property_id, '_kausa_property_sold_time', true);
        $unreserved_time = get_post_meta($property_id, '_kausa_property_unreserved_time', true);
        $property_details_translation = get_option("property_details_transalation", "");
        $additional_features_translation = $property_details_translation;

        // Determinar la fecha y hora actuales según el estado
        $current_date = '';
        $current_time = '';
        $current_status = $sale_status;

        if ($property_status === 'reserved') {
            $current_date = $reserved_time ? date('Y-m-d', strtotime($reserved_time)) : '';
            $current_time = $reserved_time ? date('H:i', strtotime($reserved_time)) : '';
        } elseif (in_array($property_status, ['pending', 'confirmed'])) {
            $current_date = $sold_time ? date('Y-m-d', strtotime($sold_time)) : '';
            $current_time = $sold_time ? date('H:i', strtotime($sold_time)) : '';
        } elseif ($property_status === 'unreserved') {
            $current_date = $unreserved_time ? date('Y-m-d', strtotime($unreserved_time)) : '';
            $current_time = $unreserved_time ? date('H:i', strtotime($unreserved_time)) : '';
        }

        if ($property_id) { ?>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <div class="status-update-main-box">
                <div class="status-update-top-box">
                    <div class="status-update-field-box">
                        <label for="kausa_property_date"><?php echo isset($additional_features_translation['kausa-property-date']) ? $additional_features_translation['kausa-property-date'] : 'Date'; ?></label>
                        <input type="date" id="kausa_property_date_input" placeholder="Select Date" value="<?php echo esc_attr($current_date); ?>">
                    </div>
                    <div class="status-update-field-box">
                        <label for="kausa_property_date"><?php echo isset($additional_features_translation['kausa-property-time']) ? $additional_features_translation['kausa-property-time'] : 'Time'; ?></label>
                        <input type="time" id="kausa_property_time_input" placeholder="Select Time" value="<?php echo esc_attr($current_time); ?>">
                        <input type="hidden" name="kausa_property_date" id="kausa_property_date" value="<?php echo esc_attr($current_date . ' ' . $current_time); ?>">
                    </div>
                    <div class="status-update-field-box">
                        <label for="kausa_property_status"><?php echo isset($additional_features_translation['kausa-property-property-status']) ? $additional_features_translation['kausa-property-property-status'] : 'Property Status'; ?></label>
                        <select id="kausa_property_status" name="kausa_property_status">
                            <option value="">Select Status</option>
                            <?php if (current_user_can('administrator')) { ?>
                                <option value="unreserved" <?php selected($property_status, 'unreserved'); ?>>Libre</option>
                                <option value="reserved" <?php selected($property_status, 'reserved'); ?>>Reservada</option>
                                <option value="unreserve" <?php selected($property_status, 'unreserve'); ?>>Desbloquear</option>
                                <option value="pending" <?php selected($property_status, 'pending'); ?>>Solicitud de venta</option>
                                <option value="confirmed" <?php selected($property_status, 'confirmed'); ?>>Aprobar venta</option>
                                <option value="denied" <?php selected($property_status, 'denied'); ?>>Denegar venta</option>
                            <?php } else { ?>
                                <?php if ($property_status === 'reserved') { ?>
                                    <option value="unreserve" <?php selected($property_status, 'unreserved'); ?>>Desbloquear</option>
                                <?php } elseif (!$property_status || $property_status === 'unreserved') { ?>
                                    <option value="reserved" <?php selected($property_status, 'reserved'); ?>>Reservada</option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="status-update-field-box">
                        <label for="kausa_property_user">Assign to Agency</label>
                        <select id="kausa_property_user" name="kausa_property_user">
                            <option value="">Select Agency</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?php echo $user->ID; ?>" <?php selected($reserved_by_user, $user->ID); ?>><?php echo $user->user_login; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="status-update-bottom-box">
                    <input type="hidden" id="kausa_property_id" value="<?php echo $post->ID; ?>">
                    <button type="button" class="button property_status_admin"><?php echo isset($additional_features_translation['kausa-property-update-status']) ? $additional_features_translation['kausa-property-update-status'] : 'Update Status'; ?></button>
                </div>
            </div>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('.property_status_admin').on('click', function() {
                        var property_id = $('#kausa_property_id').val();
                        var status = $('#kausa_property_status').val();
                        var date_input = $('#kausa_property_date_input').val();
                        var time_input = $('#kausa_property_time_input').val();
                        var property_date = date_input + ' ' + time_input;
                        var user_id = $('#kausa_property_user').val() || <?php echo get_current_user_id(); ?>;

                        if (!status) {
                            alert('Please select a status.');
                            return;
                        }

                        if (status === 'reserved' && !user_id) {
                            alert('Please select an agency to reserve the property.');
                            return;
                        }

                        var action = '';
                        if (status === 'pending' || status === 'confirmed' || status === 'denied') {
                            action = 'kausa_approve_deny_sale_admin';
                        } else {
                            action = 'kausa_change_property_status_admin';
                        }

                        console.log('Sending AJAX request:', {
                            action: action,
                            property_id: property_id,
                            user_id: user_id,
                            status: status,
                            property_date: property_date,
                            nonce: kausaAjax.nonce
                        });

                        $.ajax({
                            url: kausaAjax.ajaxurl,
                            type: 'POST',
                            data: {
                                action: action,
                                nonce: kausaAjax.nonce,
                                property_id: property_id,
                                user_id: user_id,
                                sale_status: status,
                                property_date: property_date
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('portalkausa.com says\n' + response.data.message);
                                    location.reload();
                                } else {
                                    alert('portalkausa.com says\nError: ' + (response.data ? response.data.message : 'Unknown error'));
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log('AJAX Error:', xhr.responseText);
                                alert('portalkausa.com says\nError occurred while updating status: ' + error);
                            }
                        });
                    });

                    $('#kausa_property_date_input, #kausa_property_time_input').on('change', function() {
                        var date_input = $('#kausa_property_date_input').val();
                        var time_input = $('#kausa_property_time_input').val();
                        $('#kausa_property_date').val(date_input + ' ' + time_input);
                    });
                });
            </script>
            <?php
        }
    }

    /**
     * Enqueue script for Property Status metabox
     */
    public static function enqueue_property_status_script() {
        if (get_post_type() === 'kausa_properties') {
            wp_enqueue_script('kausa-property-status', plugin_dir_url(__FILE__) . '../admin/js/kausa-property-status.js', array('jquery'), '1.0', true);
            wp_localize_script('kausa-property-status', 'kausaAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('kausa_property_status_nonce')
            ));
        }
    }

    /**
     * Handle AJAX request to update property status
     */
    public static function kausa_update_property_status() {
        check_ajax_referer('kausa_property_status_nonce', 'nonce');

        if (!isset($_POST['property_id']) || !isset($_POST['status'])) {
            wp_send_json_error('Datos incompletos');
        }

        $property_id = intval($_POST['property_id']);
        $status = sanitize_text_field($_POST['status']);
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : current_time('mysql');
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        if (!current_user_can('edit_post', $property_id)) {
            wp_send_json_error('No tienes permiso para editar esta propiedad');
        }

        if (in_array($status, ['pending', 'confirmed', 'denied'])) {
            update_post_meta($property_id, '_kausa_property_sale_status', $status);
            update_post_meta($property_id, '_kausa_property_sale_requested_by', get_current_user_id());
            update_post_meta($property_id, '_kausa_property_sale_requested_at', $date);
        } elseif ($status === 'sold') {
            update_post_meta($property_id, '_kausa_property_sold', 'yes');
            update_post_meta($property_id, '_kausa_property_sold_time', $date);
            update_post_meta($property_id, '_kausa_property_sold_by_user', $user_id);
            update_post_meta($property_id, '_kausa_property_reserved', 'no');
            update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
            update_post_meta($property_id, '_kausa_property_reserved_time', null);
            update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'no');
        } elseif ($status === 'unreserve') {
            update_post_meta($property_id, '_kausa_property_reserved', 'no');
            update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
            update_post_meta($property_id, '_kausa_property_reserved_time', null);
            update_post_meta($property_id, '_kausa_property_unreserved_time', $date);
            update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'yes');
        } elseif ($status === 'reserve') {
            update_post_meta($property_id, '_kausa_property_reserved', 'yes');
            update_post_meta($property_id, '_kausa_property_reserved_by_user', $user_id);
            update_post_meta($property_id, '_kausa_property_reserved_time', $date);
            update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'no');
        }

        wp_send_json_success('Estado actualizado');
    }
    
    /**
     * Hook to add custom gallery meta boxes to property post type
     */
    public static function kausa_properties_add_gallery_metabox() {
        add_meta_box(
            'kausa_properties_gallery',
            __('Galería de la Propiedad', 'textdomain'),
            [self::class, 'kausa_properties_gallery_metabox_callback'],
            'kausa_properties',
            'side'
        );
    }

    /**
     * Meta box content for the property gallery
     */
    public static function kausa_properties_gallery_metabox_callback($post) {
        wp_nonce_field('kausa_properties_save_gallery', 'kausa_properties_gallery_nonce');
        $gallery = get_post_meta($post->ID, '_kausa_properties_gallery', true);
        ?>
        <div id="kausa-gallery-wrapper">
            <ul>
                <?php if (!empty($gallery)) : ?>
                    <?php foreach ($gallery as $image_id) : ?>
                        <li>
                            <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                            <input type="hidden" name="kausa_gallery_ids[]" value="<?php echo esc_attr($image_id); ?>" />
                            <button type="button" class="remove-image">x</button>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <button type="button" class="add-gallery-image button">Add Image</button>
        </div>
        <?php
    }

    /**
     * Save the gallery meta box data when the post is saved
     */
    public static function kausa_properties_save_gallery($post_id) {
        if (!isset($_POST['kausa_properties_gallery_nonce']) || !wp_verify_nonce($_POST['kausa_properties_gallery_nonce'], 'kausa_properties_save_gallery')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        $gallery_ids = isset($_POST['kausa_gallery_ids']) ? array_map('intval', $_POST['kausa_gallery_ids']) : [];
        update_post_meta($post_id, '_kausa_properties_gallery', $gallery_ids);
    }

    /**
     * Hook to Add the document meta box for Kausa property post type
     */
    public static function kausa_properties_add_documents_metabox($post) {
        add_meta_box(
            'kausa_properties_documents',
            __('Property Documents', 'textdomain'),
            [self::class, 'kausa_properties_documents_metabox_callback'],
            'kausa_properties',
            'side'
        );
    }

    /**
     * Meta box content Display the documents meta box content
     */
    public static function kausa_properties_documents_metabox_callback($post) {
        wp_nonce_field('kausa_properties_save_documents', 'kausa_properties_documents_nonce');
        $documents = get_post_meta($post->ID, '_kausa_properties_documents', true);
        ?>
        <div id="kausa-documents-wrapper">
            <ul>
                <?php if (!empty($documents)) : ?>
                    <?php foreach ($documents as $document_id) :
                        $doc_url = wp_get_attachment_url($document_id);
                        $doc_title = get_the_title($document_id);
                        $docdefultIcon = site_url() . '/wp-includes/images/media/document.svg';
                        $doc_icon = wp_attachment_is_image($document_id) ? wp_get_attachment_image_url($document_id, 'thumbnail') : $docdefultIcon;
                        
                        echo '<li><div class="uploaded-document">';
                        echo '<img src="' . esc_url($doc_icon) . '" alt="Document Icon">';
                        echo '<a href="' . esc_url($doc_url) . '" target="_blank">' . esc_html($doc_title) . '</a>';
                        echo '</div>';
                        echo '<input type="hidden" name="kausa_documents_ids[]" value="' . esc_attr($document_id) . '" />';
                        echo '<button type="button" class="remove-document">x</button>';
                        echo '</li>';
                    endforeach; ?>
                <?php endif; ?>
            </ul>
            <button type="button" class="add-document button">Add Document</button>
        </div>
        <?php
    }

    /**
     * Save the selected document IDs to post meta
     */
    public static function kausa_properties_save_documents($post_id) {
        if (!isset($_POST['kausa_properties_documents_nonce']) || !wp_verify_nonce($_POST['kausa_properties_documents_nonce'], 'kausa_properties_save_documents')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $document_ids = isset($_POST['kausa_documents_ids']) ? array_map('intval', $_POST['kausa_documents_ids']) : [];
        update_post_meta($post_id, '_kausa_properties_documents', $document_ids);
    }

    /**
     * Hook to add custom meta boxes to property post type
     */
    public static function kausa_property_meta_boxes() {
        add_meta_box(
            'kausa_property_details',
            'Property Details',
            [self::class, 'kausa_property_meta_box_content'],
            'kausa_properties',
            'normal',
            'high'
        );
    }

    /**
     * Meta box content for the property details
     */
    public static function kausa_property_meta_box_content($post) {
        $fields = [
            'kausa_property_price' => get_post_meta($post->ID, 'kausa_property_price', true),
            'kausa_property_short_description' => get_post_meta($post->ID, 'kausa_property_short_description', true),
            'kausa_property_price_description' => get_post_meta($post->ID, 'kausa_property_price_description', true),
            'kausa_property_type' => get_post_meta($post->ID, 'kausa_property_type', true),
            'kausa_property_condition' => get_post_meta($post->ID, 'kausa_property_condition', true),
            'kausa_property_built_area' => get_post_meta($post->ID, 'kausa_property_built_area', true),
            'kausa_property_preference' => get_post_meta($post->ID, 'kausa_property_preference', true),
            'kausa_property_bedrooms' => get_post_meta($post->ID, 'kausa_property_bedrooms', true),
            'kausa_property_bathrooms' => get_post_meta($post->ID, 'kausa_property_bathrooms', true),
            'kausa_property_facade' => get_post_meta($post->ID, 'kausa_property_facade', true),
            'kausa_property_elevator' => get_post_meta($post->ID, 'kausa_property_elevator', true),
            'kausa_property_features' => get_post_meta($post->ID, 'kausa_property_features', true),
            'kausa_property_building_features' => get_post_meta($post->ID, 'kausa_property_building_features', true),
            'kausa_property_usable_area' => get_post_meta($post->ID, 'kausa_property_usable_area', true),
            'kausa_property_year_of_construction' => get_post_meta($post->ID, 'kausa_property_year_of_construction', true),
            'kausa_property_community_fees' => get_post_meta($post->ID, 'kausa_property_community_fees', true),
            'kausa_property_street' => get_post_meta($post->ID, 'kausa_property_street', true),
            'kausa_property_city' => get_post_meta($post->ID, 'kausa_property_city', true),
            'kausa_property_state' => get_post_meta($post->ID, 'kausa_property_state', true),
            'kausa_property_zipcode' => get_post_meta($post->ID, 'kausa_property_zipcode', true),
            'kausa_property_country' => get_post_meta($post->ID, 'kausa_property_country', true),
            'kausa_property_location_on_map' => get_post_meta($post->ID, 'kausa_property_location_on_map', true),
            'kausa_property_commission' => get_post_meta($post->ID, 'kausa_property_commission', true),
        ];
        $address_fields = [
            'street' => get_post_meta($post->ID, 'kausa_property_street', true),
            'city' => get_post_meta($post->ID, 'kausa_property_city', true),
            'state' => get_post_meta($post->ID, 'kausa_property_state', true),
            'zipcode' => get_post_meta($post->ID, 'kausa_property_zipcode', true),
            'country' => get_post_meta($post->ID, 'kausa_property_country', true),
        ];

        $additional_features_translation = $property_details_translation = get_option("property_details_transalation", "");
        ?>
    
        <form method="post">
            <table class="form-table first-table-property-details">
                <tr class="short-decription-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-short-description']) ? $additional_features_translation['kausa-property-short-description'] : 'Short Description'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_short_description" value="<?php echo esc_attr($fields['kausa_property_short_description']); ?>" />
                    </td>
                </tr>
            </table>
            <table class="form-table second-table-property-details table-box-outer">
                <tr class="price-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-price']) ? $additional_features_translation['kausa-property-price'] : 'Price'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_price" value="<?php echo esc_attr($fields['kausa_property_price']); ?>" />
                    </td>
                </tr>
                <tr class="price-decription-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-price-description']) ? $additional_features_translation['kausa-property-price-description'] : 'Price Description'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_price_description" value="<?php echo esc_attr($fields['kausa_property_price_description']); ?>" />
                    </td>
                </tr>
                <tr class="commission-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-commission']) ? $additional_features_translation['kausa-property-commission'] : 'Comisión'; ?></th>
                    <td>
                        <input type="number" step="0.01" min="0" name="kausa_property_commission" value="<?php echo esc_attr($fields['kausa_property_commission']); ?>" />
                    </td>
                </tr>
                <tr class="built-area-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-build-area']) ? $additional_features_translation['kausa-property-build-area'] : 'Built Area (m²)'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_built_area" value="<?php echo esc_attr($fields['kausa_property_built_area']); ?>" />
                    </td>
                </tr>
                <tr class="property-type-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-property-type']) ? $additional_features_translation['kausa-property-property-type'] : 'Property Type'; ?></th>
                    <td class="radio-button-outer">
                        <?php 
                        $property_type_options = ['House', 'Apartment', 'Villa', 'Condo'];
                        $selected_type = is_array($fields['kausa_property_type']) ? $fields['kausa_property_type'][0] : $fields['kausa_property_type'];
                        foreach ($property_type_options as $type): ?>
                            <label class="label-box-item">
                                <input type="radio" name="kausa_property_type" value="<?php echo esc_attr($type); ?>" 
                                <?php checked($selected_type, $type); ?> />
                                <?php echo esc_html($type); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr class="property-condition-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-property-condition']) ? $additional_features_translation['kausa-property-property-condition'] : 'Property Condition'; ?></th>
                    <td class="radio-button-outer">
                        <?php 
                        $property_condition_options = ['New', 'Renovated', 'Old'];
                        $selected_condition = is_array($fields['kausa_property_condition']) ? $fields['kausa_property_condition'][0] : $fields['kausa_property_condition'];
                        foreach ($property_condition_options as $condition): ?>
                            <label class="label-box-item">
                                <input type="radio" name="kausa_property_condition" value="<?php echo esc_attr($condition); ?>" 
                                <?php checked($selected_condition, $condition); ?> />
                                <?php echo esc_html($condition); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr class="preference-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-preferences']) ? $additional_features_translation['kausa-property-preferences'] : 'Preferences'; ?></th>
                    <td class="radio-button-outer">
                        <div class="preference-inner-box radio-box-outer-item">
                            <label class="label-box-item">
                                <input type="radio" name="kausa_property_preference" value="sale" <?php checked($fields['kausa_property_preference'], 'sale'); ?> />
                                Sale
                            </label>
                            <label class="label-box-item">
                                <input type="radio" name="kausa_property_preference" value="rent" <?php checked($fields['kausa_property_preference'], 'rent'); ?> />
                                Rent
                            </label>
                        </div>
                    </td>
                </tr>
                <tr class="bedrooms-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-num-bedrooms']) ? $additional_features_translation['kausa-property-num-bedrooms'] : 'Number of Bedrooms'; ?></th>
                    <td class="bedrooms-inner-box radio-button-outer">
                        <?php 
                        $bedroom_options = ['0', '1', '2', '3', '4 or more'];
                        foreach ($bedroom_options as $option): ?>
                            <label class="label-box-item">
                                <input type="radio" name="kausa_property_bedrooms" value="<?php echo esc_attr($option); ?>" <?php checked($fields['kausa_property_bedrooms'], $option); ?> />
                                <?php echo esc_html($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr class="bathrooms-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-num-bathrooms']) ? $additional_features_translation['kausa-property-num-bathrooms'] : 'Number of Bathrooms'; ?></th>
                    <td class="radio-button-outer">
                        <?php 
                        $bathroom_options = ['0', '1', '2', '3', '4 or more'];
                        foreach ($bathroom_options as $option): ?>
                            <label class="label-box-item">
                                <input type="radio" name="kausa_property_bathrooms" value="<?php echo esc_attr($option); ?>" <?php checked($fields['kausa_property_bathrooms'], $option); ?> />
                                <?php echo esc_html($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr class="property-facade-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-facade']) ? $additional_features_translation['kausa-property-facade'] : 'Property Facade'; ?></th>
                    <td class="radio-box-outer-item">
                        <?php 
                        $facade_options = ['Exterior', 'Interior'];
                        foreach ($facade_options as $option): ?>
                            <label class="label-box-item">
                                <input type="radio" name="kausa_property_facade" value="<?php echo esc_attr($option); ?>" <?php checked($fields['kausa_property_facade'], $option); ?> />
                                <?php echo esc_html($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <table class="form-table third-table-property-details table-box-outer">

                <tr class="elevator-main-box">
                    <th class="label-box-item heading-box"><?php echo isset($additional_features_translation['kausa-property-elevator']) ? $additional_features_translation['kausa-property-elevator'] : 'Elevator'; ?></th>
                    <td class="radio-box-outer-item">
                        <label class="label-box-item">
                            <input type="radio" name="kausa_property_elevator" value="yes" <?php checked($fields['kausa_property_elevator'], 'yes'); ?> />
                            Sí
                        </label>
                        <label class="label-box-item">
                            <input type="radio" name="kausa_property_elevator" value="no" <?php checked($fields['kausa_property_elevator'], 'no'); ?> />
                            No
                        </label>
                    </td>
                </tr>
                <tr class="additional-feature-main-box">
                    <th class="additional-feature-heading-box"><?php echo isset($additional_features_translation['kausa-additional-features']) ? $additional_features_translation['kausa-additional-features'] : 'Additional Features'; ?></th>
                    <td class="radio-button-outer">
                        <?php                                                 
                        $additional_features = [
                            'Built-in Wardrobes' => isset($additional_features_translation['kausa-buildin-wardrobes']) ? $additional_features_translation['kausa-buildin-wardrobes'] : 'Built-in Wardrobes', 
                            'Air Conditioning' => isset($additional_features_translation['kausa-airconditioning']) ? $additional_features_translation['kausa-airconditioning'] : 'Air Conditioning',  
                            'Terrace' => isset($additional_features_translation['kausa-terrace']) ? $additional_features_translation['kausa-terrace'] : 'Terrace', 
                            'Balcony' => isset($additional_features_translation['kausa-balcony']) ? $additional_features_translation['kausa-balcony'] : 'Balcony', 
                            'Storage Room' => isset($additional_features_translation['kausa-storage-room']) ? $additional_features_translation['kausa-storage-room'] : 'Storage Room', 
                            'Parking Space' => isset($additional_features_translation['kausa-parking-space']) ? $additional_features_translation['kausa-parking-space'] : 'Parking Space', 
                        ];
                        foreach ($additional_features as $featurelabel => $feature): ?>
                            <div class="additional-feature-inner-box">
                                <label class="label-box-item heading-box">
                                    <?php echo esc_html($additional_features[$featurelabel]); ?>: 
                                </label>
                                <div class="radio-box-outer-item">
                                    <label class="label-box-item">
                                        <input type="radio" name="kausa_property_features[<?php echo esc_attr($featurelabel); ?>]" value="yes" <?php checked($fields['kausa_property_features'][$featurelabel] ?? '', 'yes'); ?> />
                                        Sí
                                    </label>
                                    <label class="label-box-item">
                                        <input type="radio" name="kausa_property_features[<?php echo esc_attr($featurelabel); ?>]" value="no" <?php checked($fields['kausa_property_features'][$featurelabel] ?? '', 'no'); ?> />
                                        No
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr class="building-feature-main-box">
                    <th class="building-feature-heading-box"><?php echo isset($additional_features_translation['kausa-building-features']) ? $additional_features_translation['kausa-building-features'] : 'Building Features'; ?></th>
                    <td class="radio-button-outer">
                        <?php 
                        $building_features = [
                            'Swimming Pool' => isset($additional_features_translation['kausa-swimming-pool']) ? $additional_features_translation['kausa-swimming-pool'] : 'Swimming Pool', 
                            'Green Area' => isset($additional_features_translation['kausa-green-area']) ? $additional_features_translation['kausa-green-area'] : 'Green Area' 
                        ];
                        foreach ($building_features as $featurelabel => $feature): ?>
                            <div class="building-feature-inner-box">
                                <label class="label-box-item heading-box">
                                    <?php echo esc_html($building_features[$featurelabel]); ?>: 
                                </label>
                                <div class="radio-box-outer-item">
                                    <label class="label-box-item">
                                        <input type="radio" name="kausa_property_building_features[<?php echo esc_attr($featurelabel); ?>]" value="yes" <?php checked($fields['kausa_property_building_features'][$featurelabel] ?? '', 'yes'); ?> />
                                        Sí
                                    </label>
                                    <label class="label-box-item">
                                        <input type="radio" name="kausa_property_building_features[<?php echo esc_attr($featurelabel); ?>]" value="no" <?php checked($fields['kausa_property_building_features'][$featurelabel] ?? '', 'no'); ?> />
                                        No
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr class="usable-area-box-item">
                    <th class="label-box-item"><?php echo isset($additional_features_translation['kausa-usable-area']) ? $additional_features_translation['kausa-usable-area'] : 'Usable Area (m²)'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_usable_area" value="<?php echo esc_attr($fields['kausa_property_usable_area']); ?>" />
                    </td>
                </tr>
                <tr class="constructionyear-box-item">
                    <th class="label-box-item"><?php echo isset($additional_features_translation['kausa-year-construction']) ? $additional_features_translation['kausa-year-construction'] : 'Year of Construction'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_year_of_construction" value="<?php echo esc_attr($fields['kausa_property_year_of_construction']); ?>" />
                    </td>
                </tr>
                <tr class="community-fees-box-item">
                    <th class="label-box-item"><?php echo isset($additional_features_translation['kausa-community-fees']) ? $additional_features_translation['kausa-community-fees'] : 'Community Fees'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_community_fees" value="<?php echo esc_attr($fields['kausa_property_community_fees']); ?>" />
                    </td>
                </tr>
                <tr class="address-group-box-item">
                    <th class="group-heading-box-item"><?php echo isset($additional_features_translation['kausa-property-address']) ? $additional_features_translation['kausa-property-address'] : 'Address'; ?>:</th>
                    <td>
                        <div class="address-boxes"><label class="label-box-item"><?php echo isset($additional_features_translation['kausa-street-name']) ? $additional_features_translation['kausa-street-name'] : 'Street Name'; ?>: </label><input type="text" name="kausa_property_street" value="<?php echo esc_attr($address_fields['street']); ?>"></div>
                        <div class="address-boxes"><label class="label-box-item"><?php echo isset($additional_features_translation['kausa-city-name']) ? $additional_features_translation['kausa-city-name'] : 'City Name'; ?>: </label><input type="text" name="kausa_property_city" value="<?php echo esc_attr($address_fields['city']); ?>"></div>
                        <div class="address-boxes">
                            <label class="label-box-item">
                                <?php echo isset($additional_features_translation['kausa-state-name']) ? $additional_features_translation['kausa-state-name'] : 'Provincia'; ?>:
                            </label>
                            <select name="kausa_property_state">
                                <option value="">Selecciona una provincia</option>
                                <?php
                                $provincias = [
                                    "Álava", "Albacete", "Alicante", "Almería", "Asturias", "Ávila",
                                    "Badajoz", "Barcelona", "Bizkaia", "Burgos", "Cáceres", "Cádiz", "Cantabria",
                                    "Castellón", "Ciudad Real", "Córdoba", "Cuenca", "Gerona", "Granada",
                                    "Guadalajara", "Guipúzcoa", "Huelva", "Huesca", "Islas Baleares",
                                    "Jaén", "La Coruña", "La Rioja", "Las Palmas", "León", "Lérida",
                                    "Lugo", "Madrid", "Málaga", "Murcia", "Navarra", "Orense", "Palencia",
                                    "Pontevedra", "Salamanca", "Santa Cruz de Tenerife", "Segovia",
                                    "Sevilla", "Soria", "Tarragona", "Teruel", "Toledo", "Valencia",
                                    "Valladolid", "Zamora", "Zaragoza"
                                ];
                                
                                $provincia_actual = get_post_meta($post->ID, 'kausa_property_state', true);
                                foreach ($provincias as $provincia) {
                                    echo '<option value="' . esc_attr($provincia) . '" ' . selected($provincia_actual, $provincia, false) . '>' . esc_html($provincia) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="address-boxes"><label class="label-box-item"><?php echo isset($additional_features_translation['kausa-property-zipcode']) ? $additional_features_translation['kausa-property-zipcode'] : 'Zipcode'; ?>: </label><input type="text" name="kausa_property_zipcode" value="<?php echo esc_attr($address_fields['zipcode']); ?>"></div>
                        <div class="address-boxes"><label class="label-box-item"><?php echo isset($additional_features_translation['kausa-property-country']) ? $additional_features_translation['kausa-property-country'] : 'Country'; ?>: </label><input type="text" name="kausa_property_country" value="<?php echo esc_attr($address_fields['country']); ?>"></div>
                    </td>
                </tr>
                <tr class="location-map-box-item">
                    <th class="label-box-item"><?php echo isset($additional_features_translation['kausa-property-map-location']) ? $additional_features_translation['kausa-property-map-location'] : 'Location on Map'; ?></th>
                    <td>
                        <input type="text" name="kausa_property_location_on_map" value="<?php echo esc_attr($fields['kausa_property_location_on_map']); ?>" />
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }
    
    /**
     * Save the property status meta box data when the post is saved
     */
    public static function save_kausa_property_meta_box_data($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ('kausa_properties' !== get_post_type($post_id)) return;

        $fields = [
            'kausa_property_price',
            'kausa_property_short_description',
            'kausa_property_price_description',
            'kausa_property_type',
            'kausa_property_condition',
            'kausa_property_built_area',
            'kausa_property_preference',
            'kausa_property_bedrooms',
            'kausa_property_bathrooms',
            'kausa_property_facade',
            'kausa_property_elevator',
            'kausa_property_features',
            'kausa_property_building_features',
            'kausa_property_usable_area',
            'kausa_property_year_of_construction',
            'kausa_property_community_fees',
            'kausa_property_street',
            'kausa_property_city',
            'kausa_property_state',
            'kausa_property_zipcode',
            'kausa_property_country',
            'kausa_property_location_on_map',
            'kausa_property_commission',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if (is_array($_POST[$field])) {
                    update_post_meta($post_id, $field, array_map('sanitize_text_field', $_POST[$field]));
                } else {
                    update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                }
            } else {
                delete_post_meta($post_id, $field);
            }
        }

        // Guardar el estado de la venta
        if (isset($_POST['kausa_property_status'])) {
            $status = sanitize_text_field($_POST['kausa_property_status']);
            if (in_array($status, ['pending', 'confirmed', 'denied'])) {
                update_post_meta($post_id, '_kausa_property_sale_status', $status);
                update_post_meta($post_id, '_kausa_property_sale_requested_by', get_current_user_id());
                update_post_meta($post_id, '_kausa_property_sale_requested_at', current_time('mysql'));
            }
        }
    }
    
    /**
     * Shortcode for property grid.
     */
    public static function kausa_properties_grid_shortcode($atts) {
        $atts = shortcode_atts([
            'grid' => '4',
            'properties-count' => '8',
        ], $atts);

        $grid_class = 'kausa-properties-grid-' . esc_attr($atts['grid']);
        $properties_count = intval($atts['properties-count']);
    
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                
        $kausa_args = [
            'post_type' => 'kausa_properties',
            'posts_per_page' => esc_html($properties_count),
            'paged' => 1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_kausa_property_sold',
                    'value' => 'yes',
                    'compare' => '!=',
                ],
                [
                    'key' => '_kausa_property_sold',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];
        
        $kausa_properties = new WP_Query($kausa_args);
    
        ob_start();
    
        echo '<div class="kausa-properties-listing-box">';
        echo '<div class="kausa-properties-listing ' . esc_attr($grid_class) . '" data-count="' . $properties_count . '">';
    
        if ($kausa_properties->have_posts()) {
            while ($kausa_properties->have_posts()) {
                $kausa_properties->the_post();
                $price = get_post_meta(get_the_ID(), 'kausa_property_price', true);
                $short_description = get_post_meta(get_the_ID(), 'kausa_property_short_description', true);
                $soldstatus = get_post_meta(get_the_ID(), '_kausa_property_sold', true);

                if ($soldstatus != 'yes') {
                    ?>
                    <div class="kausa-property-item">
                        <a href="<?php the_permalink(); ?>" class="kausa-property-item-wrapper">
                            <?php if (has_post_thumbnail()) { ?>
                                <div class="kausa-property-image">
                                    <?php the_post_thumbnail('full', ['style' => 'width: 100%; height: auto;']); ?>
                                </div>
                            <?php } ?>
                            <div class="kausa-property-details">
                                <div class="kausa-property-title"><?php // the_title(); ?></div>
                                <div class="kausa-property-price-wrapper">
                                    <?php
                                    $precio_formateado = number_format($price, 0, ',', '.');
                                    ?>
                                    <p class="kausa-property-price"><?php echo esc_html($precio_formateado); ?>€</p>
                                </div>
                                <?php
                                if ($short_description) {
                                    $content = $short_description;
                                }
                                if (get_the_content()) {
                                    $content = get_the_content();
                                }
                                if ($content) {
                                    echo '<div class="kausa-property-description">';
                                    $limit = 10;
                                    $words = explode(' ', wp_strip_all_tags($content));
                                    if (count($words) > $limit) {
                                        $content = implode(' ', array_slice($words, 0, $limit)) . '...';
                                    }
                                    echo '<p>' . esc_html($content) . '</p>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </a>
                    </div>
                    <?php
                }
            }
        }
    
        echo '</div>';
        echo '</div>';

        return ob_get_clean();
    }

    /**
     * Add custom address fields to the profile page
     */
    public static function add_custom_address_fields($user) {
        ?>
        <h3><?php _e('Address Information', 'your-plugin-textdomain'); ?></h3>
        
        <table class="form-table">
            <tr>
                <th><label for="address_line1"><?php _e('Address Line 1', 'your-plugin-textdomain'); ?></label></th>
                <td>
                    <input type="text" name="address_line1" id="address_line1" value="<?php echo esc_attr(get_the_author_meta('address_line1', $user->ID)); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="address_line2"><?php _e('Address Line 2', 'your-plugin-textdomain'); ?></label></th>
                <td>
                    <input type="text" name="address_line2" id="address_line2" value="<?php echo esc_attr(get_the_author_meta('address_line2', $user->ID)); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="city"><?php _e('City', 'your-plugin-textdomain'); ?></label></th>
                <td>
                    <input type="text" name="city" id="city" value="<?php echo esc_attr(get_the_author_meta('city', $user->ID)); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="state"><?php _e('State', 'your-plugin-textdomain'); ?></label></th>
                <td>
                    <input type="text" name="state" id="state" value="<?php echo esc_attr(get_the_author_meta('state', $user->ID)); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="postal_code"><?php _e('Postal Code', 'your-plugin-textdomain'); ?></label></th>
                <td>
                    <input type="text" name="postal_code" id="postal_code" value="<?php echo esc_attr(get_the_author_meta('postal_code', $user->ID)); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="country"><?php _e('Country', 'your-plugin-textdomain'); ?></label></th>
                <td>
                    <input type="text" name="country" id="country" value="<?php echo esc_attr(get_the_author_meta('country', $user->ID)); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save the custom address fields when the user updates their profile
     */
    public static function save_custom_address_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_user_meta($user_id, 'address_line1', sanitize_text_field($_POST['address_line1']));
        update_user_meta($user_id, 'address_line2', sanitize_text_field($_POST['address_line2']));
        update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));
        update_user_meta($user_id, 'state', sanitize_text_field($_POST['state']));
        update_user_meta($user_id, 'postal_code', sanitize_text_field($_POST['postal_code']));
        update_user_meta($user_id, 'country', sanitize_text_field($_POST['country']));
    }

    /**
     * Schedule the cron job if not already scheduled
     */
    public static function kausa_property_check_cron() {
        if (!wp_next_scheduled('kausa_property_check_cron_hook')) {
            wp_schedule_event(time(), 'five_minutes', 'kausa_property_check_cron_hook');
        }
    }

    /**
     * Add custom cron schedule for every 5 minutes
     */
    public static function kausa_property_check_cron_add_schedule($schedules) {
        $schedules['five_minutes'] = array(
            'interval' => 5 * 60,
            'display' => __('Every 5 Minutes'),
        );
        return $schedules;
    }

    /**
     * Function that runs every 5 minutes via cron job
     */
    public static function kausa_property_check() {
        global $wpdb;
        $current_time = current_time('mysql');
        $table_name = $wpdb->prefix . 'kausa_properties_meta';
        $properties = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE property_status = 'reserved' AND property_panelty_time IS NULL AND property_sold_time IS NULL AND property_unreserve_time IS NULL AND property_spent_time IS NULL"
        );

        foreach ($properties as $row) {
            $property_id = $row->property_id;
            $user_id = $row->user_id;
            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);
            $penalty_free_reservation_time = get_option("kausa-penalty-free-reservation-time", "");
            $penalty_free_time = $penalty_free_reservation_time ? explode(" ", $penalty_free_reservation_time) : array(48, 'hours');
            $penalty_free_time_array = explode(" ", $penalty_free_time);
            $penalty_free_hours = 48;
            if ($penalty_free_time_array[1] == "days") {
                $penalty_free_hours = $penalty_free_time_array[0] * 24;
            } else {
                $penalty_free_hours = $penalty_free_time_array[0];
            }
             
            if ($reserved_by_user == $user_id && $reserved_time == $row->property_reserve_time) {
                $penalty_time_str = get_post_meta($property_id, '_kausa_property_reserved_penalty_time', true);
                $penalty_time = DateTime::createFromFormat('YmdHis', $penalty_time_str);
                $current_time_obj = new DateTime($current_time);
                if ($penalty_time && $current_time_obj > $penalty_time) {
                    $time_spent_seconds = strtotime($current_time) - strtotime($reserved_time);
                    $time_spent_hours = floor($time_spent_seconds / 3600);
                    $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
                    $time_spent_remaining_seconds = $time_spent_seconds % 60;
                    $time_spent_formatted = sprintf('%02dh %02dm %02ds', $time_spent_hours, $time_spent_minutes, $time_spent_remaining_seconds);
                    $grace_period_seconds = $penalty_free_hours * 3600;
                    $penalty_seconds = max(0, $time_spent_seconds - $grace_period_seconds);
                    $penalty_hours = floor($penalty_seconds / 3600);
                    $penalty_minutes = floor(($penalty_seconds % 3600) / 60);
                    $penalty_remaining_seconds = $penalty_seconds % 60;
                    $penalty_formatted = sprintf('%02dh %02dm %02ds', $penalty_hours, $penalty_minutes, $penalty_remaining_seconds);
                    $wpdb->update(
                        $table_name,
                        array(
                            'property_status' => 'unreserved',
                            'property_panelty_time' => $penalty_formatted,
                            'property_unreserve_time' => $current_time,
                            'property_spent_time' => $time_spent_formatted,
                        ),
                        array(
                            'user_id' => $user_id,
                            'property_id' => $property_id,
                        ),
                        array('%s', '%s', '%s', '%s'),
                        array('%d', '%d')
                    );
                    update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'yes');
                    update_post_meta($property_id, '_kausa_property_reserved', 'no');
                    update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
                    update_post_meta($property_id, '_kausa_property_reserved_time', null);
                    update_post_meta($property_id, '_kausa_property_unreserved_time', $current_time);
                    update_post_meta($property_id, '_kausa_property_sold_time', null);
                    update_post_meta($property_id, '_kausa_property_reserved_penalty_time', null);
                }
            }
        }
    }

    /**
     * Register admin menu
     */
    public static function register_kausa_properties_admin_menu() {
        add_menu_page(
            'Kausa Properties',
            'Kausa Properties',
            'manage_options',
            'kausa-properties',
            [self::class, 'register_kausa_properties_settings_page'],          
            'dashicons-building',
            20
        );

        add_submenu_page(
            'kausa-properties',
            'Heading Translation',
            'Heading Translation',
            'manage_options',
            'heading-translation',
            [self::class, 'register_kausa_properties_string_translation']
        );

        add_submenu_page(
            'kausa-properties',
            'Reserved Properties',
            'Reserved Properties',
            'manage_options',
            'reserved-properties',
            [self::class, 'kausa_properties_manage_reserved_properties']
        );

        add_submenu_page(
            'kausa-properties',
            'Manage FAQs',
            'Manage FAQs',
            'manage_options',
            'manage-faqs',
            [self::class, 'kausa_properties_manage_faqs']
        );

        add_submenu_page(
            'kausa-properties',
            'Manage Sales',
            'Manage Sales',
            'manage_options',
            'manage-sales',
            [self::class, 'kausa_properties_manage_sales']
        );
    }

    public static function kausa_properties_manage_faqs() {
        ?>
        <div class="wrap">
            <h1>Manage FAQs</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('kausa_properties_faqs_options_group');
                do_settings_sections('manage-faqs');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function kausa_properties_register_faq_settings() {
        register_setting('kausa_properties_faqs_options_group', 'kausa_properties_faq_entries');
        add_settings_section('kausa_properties_faq_section', 'Kausa Properties FAQ Entries', null, 'manage-faqs');
        add_settings_field('kausa_properties_faq_field', 'Enter FAQs', [__CLASS__, 'kausa_properties_render_faq_field'], 'manage-faqs', 'kausa_properties_faq_section');
    }

    public static function kausa_properties_render_faq_field() {
        $faqs = get_option('kausa_properties_faq_entries', []);
        ?>
        <div id="kausa-properties-faq-repeater">
            <?php if (!empty($faqs)) : ?>
                <?php foreach ($faqs as $index => $faq) : ?>
                    <div class="kausa-properties-faq-item">
                        <textarea name="kausa_properties_faq_entries[<?php echo $index; ?>][question]" rows="1" placeholder="Question"><?php echo esc_attr($faq['question']); ?></textarea>
                        <textarea name="kausa_properties_faq_entries[<?php echo $index; ?>][answer]" rows="4" placeholder="Answer"><?php echo esc_textarea($faq['answer']); ?></textarea>
                        <button class="kausa-properties-remove-faq" type="button">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>            
        </div>
        <button id="kausa-properties-add-faq" type="button" style="margin-top: 10px;">Add FAQ</button>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const addButton = document.getElementById('kausa-properties-add-faq');
                const repeater = document.getElementById('kausa-properties-faq-repeater');
                addButton.addEventListener('click', function () {
                    const index = repeater.querySelectorAll('.kausa-properties-faq-item').length;
                    const newItem = document.createElement('div');
                    newItem.classList.add('kausa-properties-faq-item');
                    newItem.innerHTML = `
                        <textarea name="kausa_properties_faq_entries[${index}][question]" placeholder="Question"></textarea>
                        <textarea name="kausa_properties_faq_entries[${index}][answer]" rows="4" placeholder="Answer"></textarea>
                        <button class="kausa-properties-remove-faq" type="button">Remove</button>
                    `;
                    repeater.appendChild(newItem);
                });

                repeater.addEventListener('click', function (e) {
                    if (e.target.classList.contains('kausa-properties-remove-faq')) {
                        e.target.closest('.kausa-properties-faq-item').remove();
                    }
                });
            });
        </script>
        <style>
            #faq-repeater .faq-item {
                margin-bottom: 15px;
            }
        </style>
        <?php
    }

    public static function kausa_properties_display_faqs_shortcode() {
        $faqs = get_option('kausa_properties_faq_entries', []);
        $output = "";
        if (!empty($faqs)) {
            $output .= '<div class="faq-section">';
            $output .= '<h2>Preguntas frecuentes</h2>';
            $output .= '<div class="faq-accordion">';
            
            foreach ($faqs as $index => $faq) {
                $output .= '<div class="faq-item">';
                $output .= '<p class="accordion-title accordion" style="margin-bottom: 10px;border: 1px solid black;">' . esc_html($faq['question']) . '</p>';
                $output .= '<div class="panel">';
                $output .= '<p>' . esc_html($faq['answer']) . '</p>';
                $output .= '</div>'; 
                $output .= '</div>'; 
            }

            $output .= '</div>'; 
            $output .= '</div>'; 
        } else {
            $output .= '<p>No FAQs available.</p>';
        }

        return $output;
    }

    public static function kausa_properties_manage_reserved_properties() {
        global $wpdb;
        $current_time = current_time('mysql');
        $table_name = $wpdb->prefix . 'kausa_properties_meta';
        $properties = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE property_status = 'reserved' AND property_panelty_time IS NULL AND property_sold_time IS NULL AND property_unreserve_time IS NULL AND property_spent_time IS NULL"
        );
        $user_ids = [];
        foreach ($properties as $row) {            
            $user_ids[] = $row->user_id;                             
        }
       
        $reserved_properties = new WP_Query(array(
            'post_type' => 'kausa_properties',
            'meta_query' => array(
                array(
                    'key' => '_kausa_property_reserved_by_user',
                    'value' => $user_ids,
                    'compare' => 'IN'
                )
            ),
            'posts_per_page' => -1
        ));
        echo '<div class="reserved-properties-list">';
        echo '<h2 class="reserved-properties-main-heading">Propiedades reservadas</h2>';
        if ($reserved_properties->have_posts()) {
            ?>
            <table class="wp-list-table widefat fixed striped table-view-list pages">
                <thead>
                    <tr>
                        <th class="manage-column column-title column-primary sorted asc">Imagen</th>
                        <th class="manage-column column-title column-primary sorted asc">Nombre de la propiedad</th>
                        <th class="manage-column column-title column-primary sorted asc">Reservado el</th>                      
                        <th class="manage-column column-title column-primary sorted asc">Fin de la penalización por defecto</th>
                        <th class="manage-column column-title column-primary sorted asc">Nombre de usuario</th>
                        <th class="manage-column column-title column-primary sorted asc">Actualizar fin de reserva</th>
                    </tr>
                </thead>
                <tbody>                    
                    <?php
                    while ($reserved_properties->have_posts()) {
                        $reserved_properties->the_post();
               
                        $property_id = get_the_ID();
                        $property_title = get_the_title();
                        $property_permalink = get_permalink();
                        $property_image_url = get_the_post_thumbnail_url($property_id, 'full');
                        $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
                        $no_penalty_end_time = get_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', true);
                        $reserved_penalty_time = get_post_meta($property_id, '_kausa_property_reserved_penalty_time', true);
                        $is_sold = get_post_meta($property_id, '_kausa_property_sold', true);
                        $user_id = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);
                        $user_data = get_userdata($user_id);                        
                        $current_time = current_time('timestamp');
                        $countdown_seconds = strtotime($no_penalty_end_time) - $current_time;
                        $time_spent_seconds = $current_time - strtotime($reserved_time);
                     
                        $time_spent_hours = floor($time_spent_seconds / 3600);
                        $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
                        $time_spent_seconds = $time_spent_seconds % 60;

                        $no_penalty_end_timestamp = strtotime($no_penalty_end_time);
                        $time_spent_after_penalty_start = max(0, $current_time - $no_penalty_end_timestamp);

                        $penalty_hours_spent = floor($time_spent_after_penalty_start / 3600);
                        ?>
                        <tr>
                            <td><img src="<?php echo $property_image_url; ?>" width="100px" /></td>
                            <td><a href="<?php echo $property_permalink; ?>"><?php echo $property_title; ?></a></td>
                            <td><?php echo esc_html(date('l, d M, Y H:i:s A', strtotime($reserved_time))); ?></td>
                            <td><?php echo esc_html(date('l, d M, Y H:i:s A', strtotime($reserved_penalty_time))); ?></td>
                            <td><?php echo $user_data->display_name; ?></td>
                            <td><button class="primary update-reserved-penalty-date" data-id="<?php echo $property_id; ?>">Actualizar fin de reserva</button></td>
                        </tr>
                        <?php
                    }
                    wp_reset_postdata();
                    ?>
                </tbody>
            </table>
            <dialog id="favDialog">
                <form id="propertyPenaltyUpdateForm">
                    <p>
                        <label>
                            Fin de la penalización por defecto:
                            <input type="datetime-local" id="final-date-update" />
                        </label>
                    </p>
                    <p>
                        <input type="hidden" value="" id="property-id-selected-property" />
                    </p>
                    <div>
                        <button type="button" id="propertyPenaltyUpdateFormClose">Cancel</button>
                        <button type="submit" id="confirmBtn" value="default">Confirm</button>
                    </div>
                </form>
            </dialog>
        </div>
        <?php
        }

        $properties_with_penalty = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE property_status != 'reserved' AND property_panelty_time IS NOT NULL AND property_panelty_time != '00h 00m 00s' AND property_panelty_time > NOW()"
        );
        echo '<div class="reserved-properties-list">';
        echo '<h2 class="reserved-properties-main-heading">Penalización de actualización</h2>';
        ?>
        <table class="wp-list-table widefat fixed striped table-view-list pages text-center">
            <thead>
                <tr>
                    <th class="manage-column column-title column-primary sorted asc">Imagen de propiedad</th>
                    <th class="manage-column column-title column-primary sorted asc">Identificación de propiedad</th>
                    <th class="manage-column column-title column-primary sorted asc">Nombre de la propiedad</th>
                    <th class="manage-column column-title column-primary sorted asc">Nombre de usuario</th>
                    <th class="manage-column column-title column-primary sorted asc">Estado de la propiedad</th>
                    <th class="manage-column column-title column-primary sorted asc">Tiempo de panel de propiedad</th>
                    <th class="manage-column column-title column-primary sorted asc">Penalización de actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($properties_with_penalty as $output) {
                    $row = get_object_vars($output);
                    $user_data = get_userdata($row['user_id']);
                    $post_title = get_the_title($row['property_id']);
                    $post_thumbnail_url = get_the_post_thumbnail_url($row['property_id'], 'full');
                    ?>
                    <tr>
                        <td><img src="<?php echo $post_thumbnail_url; ?>" width="100px" /></td>
                        <td><?php echo $row['property_id']; ?></td>
                        <td><?php echo $post_title; ?></td>
                        <td><?php echo $user_data->display_name; ?></td>
                        <td><?php echo $row['property_status']; ?></td>
                        <td><?php echo $row['property_panelty_time']; ?></td>
                        <td><button class="primary update-penalty-datetime" data-id="<?php echo $row['id']; ?>">Actualizar tiempo de penalización</button></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <dialog id="favDialogPenalty">
            <form id="propertyPenaltytimeUpdateForm">
                <p>
                    <label>
                        Update Penalty Time:
                        <input type="datetime-local" id="penalty-date-update" />
                    </label>
                </p>
                <p>
                    <input type="hidden" value="" id="property-id-selected-property-penalty" />
                </p>
                <div>
                    <button type="button" id="propertyPenaltytimeUpdateFormClose">Cancel</button>
                    <button type="submit" id="confirmPenaltyTimeBtn" value="default">Confirm</button>
                </div>
            </form>
        </dialog>
    </div>
    <?php
    }

    public static function kausa_request_sale() {
        check_ajax_referer('kausa_property_status_nonce', 'nonce');

        if (!isset($_POST['property_id']) || !isset($_POST['user_id']) || !isset($_POST['status'])) {
            wp_send_json_error('Datos incompletos');
        }

        $property_id = intval($_POST['property_id']);
        $user_id = intval($_POST['user_id']);
        $status = sanitize_text_field($_POST['status']);

        if (!in_array($status, ['pending', 'confirmed', 'denied'])) {
            wp_send_json_error('Estado no válido');
        }

        // Verificar el estado actual para evitar conflictos
        $current_sale_status = get_post_meta($property_id, '_kausa_property_sale_status', true);
        $is_sold = get_post_meta($property_id, '_kausa_property_sold', true);

        if ($is_sold === 'yes' && $status !== 'confirmed') {
            wp_send_json_error('La propiedad ya está marcada como vendida.');
        }

        if ($status === 'pending' && ($current_sale_status === 'pending' || $current_sale_status === 'confirmed')) {
            wp_send_json_error('Ya hay una solicitud de venta en proceso para esta propiedad.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';

        // Actualizar la tabla wp_kausa_properties_meta
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE property_id = %d AND user_id = %d ORDER BY property_reserve_time DESC LIMIT 1",
                $property_id,
                $user_id
            )
        );

        if ($row) {
            $wpdb->update(
                $table_name,
                array(
                    'property_status' => $status,
                ),
                array(
                    'id' => $row->id,
                ),
                array('%s'),
                array('%d')
            );
        }

        if ($status === 'pending') {
            update_post_meta($property_id, '_kausa_property_sale_status', $status);
            update_post_meta($property_id, '_kausa_property_sold', 'pending');
            update_post_meta($property_id, '_kausa_property_reserved', 'no');
            update_post_meta($property_id, '_kausa_property_sale_requested_by', $user_id);
            update_post_meta($property_id, '_kausa_property_sale_requested_at', current_time('mysql'));
        } elseif ($status === 'confirmed') {
            update_post_meta($property_id, '_kausa_property_sale_status', $status);
            update_post_meta($property_id, '_kausa_property_sold', 'yes');
            update_post_meta($property_id, '_kausa_property_sold_time', current_time('mysql'));
            update_post_meta($property_id, '_kausa_property_sold_by_user', $user_id);
            update_post_meta($property_id, '_kausa_property_reserved', 'no');
            update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'no');
            update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
            update_post_meta($property_id, '_kausa_property_reserved_time', null);
        } elseif ($status === 'denied') {
            update_post_meta($property_id, '_kausa_property_sale_status', $status);
            update_post_meta($property_id, '_kausa_property_sold', 'no');
            update_post_meta($property_id, '_kausa_property_reserved', 'yes'); // Vuelve a estar reservada
            update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'no');
            delete_post_meta($property_id, '_kausa_property_sold_time');
            delete_post_meta($property_id, '_kausa_property_sold_by_user');
        }

        wp_send_json_success('Estado actualizado');
    }

    public static function kausa_properties_manage_sales() {
        global $wpdb;
        $properties = new WP_Query(array(
            'post_type' => 'kausa_properties',
            'meta_query' => array(
                array(
                    'key' => '_kausa_property_sale_status',
                    'value' => 'pending',
                    'compare' => '='
                )
            ),
            'posts_per_page' => -1
        ));

        echo '<div class="wrap">';
        echo '<h1>Manage Sales</h1>';
        if ($properties->have_posts()) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Property</th><th>Agency</th><th>Requested At</th><th>Actions</th></tr></thead>';
            echo '<tbody>';
            while ($properties->have_posts()) {
                $properties->the_post();
                $property_id = get_the_ID();
                $requested_by = get_post_meta($property_id, '_kausa_property_sale_requested_by', true);
                $requested_at = get_post_meta($property_id, '_kausa_property_sale_requested_at', true);
                $user = get_user_by('id', $requested_by);
                
                echo '<tr>';
                echo '<td>' . get_the_title() . '</td>';
                echo '<td>' . ($user ? $user->display_name : 'Unknown') . '</td>';
                echo '<td>' . esc_html($requested_at) . '</td>';
                echo '<td>';
                echo '<button class="button approve-sale" data-property-id="' . $property_id . '">Approve</button>';
                echo '<button class="button deny-sale" data-property-id="' . $property_id . '" style="margin-left: 10px;">Deny</button>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            wp_reset_postdata();
        } else {
            echo '<p>No pending sales.</p>';
        }
        echo '</div>';
        ?>
        <script>
            jQuery(document).ready(function($) {
                $('.approve-sale, .deny-sale').on('click', function() {
                    var property_id = $(this).data('property-id');
                    var status = $(this).hasClass('approve-sale') ? 'confirmed' : 'denied';

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'kausa_request_sale',
                            nonce: '<?php echo wp_create_nonce('kausa_property_status_nonce'); ?>',
                            property_id: property_id,
                            user_id: <?php echo get_current_user_id(); ?>,
                            status: status
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Sale status updated.');
                                location.reload();
                            } else {
                                alert('Error updating status: ' + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', xhr.responseText);
                            alert('Error occurred while updating status: ' + error);
                        }
                    });
                });
            });
        </script>
        <?php
    }

    public static function register_kausa_properties_settings_page() {
        $google_map_api_key = get_option("kausa-google-map-api-key", "");
        $penalty_free_reservation_time = get_option("kausa-penalty-free-reservation-time", "");
        $penalty_free_time = $penalty_free_reservation_time ? explode(" ", $penalty_free_reservation_time) : array(48, 'hours');
        $penalty_reservation_time = get_option("kausa-penalty-reservation-time", "");
        $penalty_time = $penalty_reservation_time ? explode(" ", $penalty_reservation_time) : array(7, 'days');
        ?>
        <div class="wrap">
            <h2 class="text-align-center">Settings</h2>
            <form action="" method="POST" class="form-table">
                <table>
                    <tr>
                        <th><label for="kausa-google-map-api-key">Google Map API Key</label></th>
                        <td><input type="text" id="kausa-google-map-api-key" class="form-control" name="kausa-google-map-api-key" value='<?php echo isset($google_map_api_key) ? $google_map_api_key : ""; ?>'></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-penalty-free-reservation-period">Reservation penalty free time</label></th>
                        <td><input type="number" id="kausa-penalty-free-reservation-period" class="form-control" name="kausa-penalty-free-reservation-period" value='<?php echo $penalty_free_time[0]; ?>'></td>
                        <td>
                            <select id="kausa-penalty-free-reservation-type">
                                <option <?php if ($penalty_free_time[1] == "days") { echo "selected"; } ?> value="days">Days</option>
                                <option <?php if ($penalty_free_time[1] == "hours") { echo "selected"; } ?> value="hours">Hours</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="kausa-penalty-reservation-period">Reservation penalty time</label></th>
                        <td><input type="number" id="kausa-penalty-reservation-period" class="form-control" name="kausa-penalty-reservation-period" value='<?php echo $penalty_time[0]; ?>'></td>
                        <td>
                            <select id="kausa-penalty-reservation-type">
                                <option <?php if ($penalty_time[1] == "days") { echo "selected"; } ?> value="days">Days</option>
                                <option <?php if ($penalty_time[1] == "hours") { echo "selected"; } ?> value="hours">Hours</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><button class="kausa-api-key-submit-button primary button-primary">Save</button></td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    public static function register_kausa_properties_string_translation() {
        $property_details_translation = get_option("property_details_transalation", "");
        $property_details = "";
        $property_details_price = "";
        $property_details_address = "";
        $property_details_map_location = "";
        $property_details_description = "";
        $property_details_additional_description = "";
        $property_details_gallery = "";
        $property_details_already_reserved_text = "";
        $property_details_time_spent = "";
        $property_details_cannot_reserve_text = "";
        $property_details_already_reserved_byagency = "";
        $property_details_booking_available_in = "";
        $property_details_calculating = "";
        $property_details_lock_apartment = "";
        $property_details_learn_more = "";
        $property_details_block = "";
        $property_details_block_for = "";
        $property_details_no_access = "";
        $property_details_no_document = "";
        $property_details_download_document = "";
        $property_details_view_all = "";
        $property_details_hours = "";
        $property_details_days = "";
        $property_details_housing_type = "";
        $property_details_rooms = "";
        $property_details_bathrooms = "";
        $property_details_property_condition = "";
        $property_details_preferences = "";
        $property_details_property_fecades = "";
        $property_details_elevator = "";
        $property_details_additional_features = "";
        $property_details_builtin_wardrobes = "";
        $property_details_airconditioning = "";
        $property_details_terrace = "";
        $property_details_balcony = "";
        $property_details_storage_room = "";
        $property_details_parking_space = "";
        $property_details_swimming_pool = "";
        $property_details_green_area = "";
        $property_details_building_features = "";
        $property_details_usable_area = "";
        $property_details_year_construction = "";
        $property_details_community_fees = "";
        $property_details_street_name = "";
        $property_details_city_name = "";
        $property_details_state_name = "";
        $property_details_zipcode = "";
        $property_details_country = "";
        $property_details_short_description = "";
        $property_details_price_description = "";
        $property_details_build_area = "";
        $property_details_property_type = "";
        $property_details_property_condition = "";
        $property_details_num_bedrooms = "";
        $property_details_num_bathrooms = "";
        $property_details_facade = "";
        $property_details_date = "";
        $property_details_time = "";
        $property_details_property_status = "";
        $property_details_sold = "";
        $property_details_unreserve = "";
        $property_details_update_status = "";
        $property_details_goto_dashboard = "";
        $property_details_commission = "";

        if (is_array($property_details_translation)) {
            $property_details = isset($property_details_translation["kausa-property-details"]) ? $property_details_translation["kausa-property-details"] : "";
            $property_details_price = isset($property_details_translation["kausa-property-price"]) ? $property_details_translation["kausa-property-price"] : "";
            $property_details_address = isset($property_details_translation["kausa-property-address"]) ? $property_details_translation["kausa-property-address"] : "";
            $property_details_map_location = isset($property_details_translation["kausa-property-map-location"]) ? $property_details_translation["kausa-property-map-location"] : "";
            $property_details_description = isset($property_details_translation["kausa-property-description"]) ? $property_details_translation["kausa-property-description"] : "";
            $property_details_additional_description = isset($property_details_translation["kausa-property-additional-description"]) ? $property_details_translation["kausa-property-additional-description"] : "";
            $property_details_gallery = isset($property_details_translation["kausa-property-gallery"]) ? $property_details_translation["kausa-property-gallery"] : "";
            $property_details_already_reserved_text = isset($property_details_translation["kausa-property-already-reserved-text"]) ? $property_details_translation["kausa-property-already-reserved-text"] : "";
            $property_details_time_spent = isset($property_details_translation["kausa-property-time-spent"]) ? $property_details_translation["kausa-property-time-spent"] : "";
            $property_details_cannot_reserve_text = isset($property_details_translation["kausa-cannot-reserve"]) ? $property_details_translation["kausa-cannot-reserve"] : "";
            $property_details_already_reserved_byagency = isset($property_details_translation["kausa-property-reserved-by-agency"]) ? $property_details_translation["kausa-property-reserved-by-agency"] : "";
            $property_details_booking_available_in = isset($property_details_translation["kausa-property-booking-available-in"]) ? $property_details_translation["kausa-property-booking-available-in"] : "";
            $property_details_calculating = isset($property_details_translation["kausa-property-calculating"]) ? $property_details_translation["kausa-property-calculating"] : "";
            $property_details_lock_apartment = isset($property_details_translation["kausa-property-lock-this-apartment"]) ? $property_details_translation["kausa-property-lock-this-apartment"] : "";
            $property_details_learn_more = isset($property_details_translation["kausa-property-learn-more"]) ? $property_details_translation["kausa-property-learn-more"] : "";
            $property_details_block = isset($property_details_translation["kausa-property-block"]) ? $property_details_translation["kausa-property-block"] : "";
            $property_details_block_for = isset($property_details_translation["kausa-property-block-for"]) ? $property_details_translation["kausa-property-block-for"] : "";
            $property_details_no_access = isset($property_details_translation["kausa-property-no-reservation-access"]) ? $property_details_translation["kausa-property-no-reservation-access"] : "";
            $property_details_no_document = isset($property_details_translation["kausa-property-no-document"]) ? $property_details_translation["kausa-property-no-document"] : "";
            $property_details_download_document = isset($property_details_translation["kausa-property-download-document"]) ? $property_details_translation["kausa-property-download-document"] : "";
            $property_details_view_all = isset($property_details_translation["kausa-property-view-all"]) ? $property_details_translation["kausa-property-view-all"] : "";
            $property_details_hours = isset($property_details_translation["kausa-property-hours"]) ? $property_details_translation["kausa-property-hours"] : "";
            $property_details_days = isset($property_details_translation["kausa-property-days"]) ? $property_details_translation["kausa-property-days"] : "";
            $property_details_housing_type = isset($property_details_translation["kausa-property-housing-type"]) ? $property_details_translation["kausa-property-housing-type"] : "";
            $property_details_rooms = isset($property_details_translation["kausa-property-rooms"]) ? $property_details_translation["kausa-property-rooms"] : "";
            $property_details_bathrooms = isset($property_details_translation["kausa-property-bathrooms"]) ? $property_details_translation["kausa-property-bathrooms"] : "";
            $property_details_property_condition = isset($property_details_translation["kausa-property-condition"]) ? $property_details_translation["kausa-property-condition"] : "";
            $property_details_preferences = isset($property_details_translation["kausa-property-preferences"]) ? $property_details_translation["kausa-property-preferences"] : "";
            $property_details_property_fecades = isset($property_details_translation["kausa-property-fecades"]) ? $property_details_translation["kausa-property-fecades"] : "";
            $property_details_elevator = isset($property_details_translation["kausa-property-elevator"]) ? $property_details_translation["kausa-property-elevator"] : "";
            $property_details_additional_features = isset($property_details_translation["kausa-additional-features"]) ? $property_details_translation["kausa-additional-features"] : "";
            $property_details_builtin_wardrobes = isset($property_details_translation["kausa-buildin-wardrobes"]) ? $property_details_translation["kausa-buildin-wardrobes"] : "";
            $property_details_airconditioning = isset($property_details_translation["kausa-airconditioning"]) ? $property_details_translation["kausa-airconditioning"] : "";
            $property_details_terrace = isset($property_details_translation["kausa-terrace"]) ? $property_details_translation["kausa-terrace"] : "";
            $property_details_balcony = isset($property_details_translation["kausa-balcony"]) ? $property_details_translation["kausa-balcony"] : "";
            $property_details_storage_room = isset($property_details_translation["kausa-storage-room"]) ? $property_details_translation["kausa-storage-room"] : "";
            $property_details_parking_space = isset($property_details_translation["kausa-parking-space"]) ? $property_details_translation["kausa-parking-space"] : "";
            $property_details_swimming_pool = isset($property_details_translation["kausa-swimming-pool"]) ? $property_details_translation["kausa-swimming-pool"] : "";
            $property_details_green_area = isset($property_details_translation["kausa-green-area"]) ? $property_details_translation["kausa-green-area"] : "";
            $property_details_building_features = isset($property_details_translation["kausa-building-features"]) ? $property_details_translation["kausa-building-features"] : "";
            $property_details_usable_area = isset($property_details_translation["kausa-usable-area"]) ? $property_details_translation["kausa-usable-area"] : "";
            $property_details_year_construction = isset($property_details_translation["kausa-year-construction"]) ? $property_details_translation["kausa-year-construction"] : "";
            $property_details_community_fees = isset($property_details_translation["kausa-community-fees"]) ? $property_details_translation["kausa-community-fees"] : "";
            $property_details_street_name = isset($property_details_translation["kausa-street-name"]) ? $property_details_translation["kausa-street-name"] : "";
            $property_details_city_name = isset($property_details_translation["kausa-city-name"]) ? $property_details_translation["kausa-city-name"] : "";
            $property_details_state_name = isset($property_details_translation["kausa-state-name"]) ? $property_details_translation["kausa-state-name"] : "";
            $property_details_zipcode = isset($property_details_translation["kausa-property-zipcode"]) ? $property_details_translation["kausa-property-zipcode"] : "";
            $property_details_country = isset($property_details_translation["kausa-property-country"]) ? $property_details_translation["kausa-property-country"] : "";
            $property_details_short_description = isset($property_details_translation["kausa-property-short-description"]) ? $property_details_translation["kausa-property-short-description"] : "";
            $property_details_price_description = isset($property_details_translation["kausa-property-price-description"]) ? $property_details_translation["kausa-property-price-description"] : "";
            $property_details_build_area = isset($property_details_translation["kausa-property-build-area"]) ? $property_details_translation["kausa-property-build-area"] : "";
            $property_details_property_type = isset($property_details_translation["kausa-property-property-type"]) ? $property_details_translation["kausa-property-property-type"] : "";
            $property_details_property_condition = isset($property_details_translation["kausa-property-property-condition"]) ? $property_details_translation["kausa-property-property-condition"] : "";
            $property_details_num_bedrooms = isset($property_details_translation["kausa-property-num-bedrooms"]) ? $property_details_translation["kausa-property-num-bedrooms"] : "";
            $property_details_num_bathrooms = isset($property_details_translation["kausa-property-num-bathrooms"]) ? $property_details_translation["kausa-property-num-bathrooms"] : "";
            $property_details_facade = isset($property_details_translation["kausa-property-facade"]) ? $property_details_translation["kausa-property-facade"] : "";
            $property_details_date = isset($property_details_translation["kausa-property-date"]) ? $property_details_translation["kausa-property-date"] : "";
            $property_details_time = isset($property_details_translation["kausa-property-time"]) ? $property_details_translation["kausa-property-time"] : "";
            $property_details_property_status = isset($property_details_translation["kausa-property-property-status"]) ? $property_details_translation["kausa-property-property-status"] : "";
            $property_details_sold = isset($property_details_translation["kausa-property-sold"]) ? $property_details_translation["kausa-property-sold"] : "";
            $property_details_unreserve = isset($property_details_translation["kausa-property-unreserve"]) ? $property_details_translation["kausa-property-unreserve"] : "";
            $property_details_update_status = isset($property_details_translation["kausa-property-update-status"]) ? $property_details_translation["kausa-property-update-status"] : "";
            $property_details_goto_dashboard = isset($property_details_translation["kausa-property-go-to-dashboard"]) ? $property_details_translation["kausa-property-go-to-dashboard"] : "";
            $property_details_commission = isset($property_details_translation["kausa-property-commission"]) ? $property_details_translation["kausa-property-commission"] : "";
        }
        ?>
        <div class="wrap">
            <h2>Heading Translation</h2>
            <form id="kausa-property-translation-submit" action="" method="post" class="form-table">
                <table>
                    <tr>
                        <th><label for="kausa-property-details">Property Details</label></th>
                        <td><input type="text" id="kausa-property-details" name="kausa-property-details" class="form-control" placeholder="Property Details" value="<?php echo $property_details ? $property_details : ""; ?>" /></td>
                        <th><label for="kausa-property-housing-type">Housing Type</label></th>
                        <td><input type="text" id="kausa-property-housing-type" name="kausa-property-housing-type" class="form-control" placeholder="Type of Housing" value="<?php echo $property_details_housing_type ? $property_details_housing_type : ""; ?>" /></td>
                        <th><label for="kausa-property-zipcode">Zipcode</label></th>
                        <td><input type="text" id="kausa-property-zipcode" name="kausa-property-zipcode" class="form-control" placeholder="Zipcode" value="<?php echo $property_details_zipcode ? $property_details_zipcode : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-price">Price</label></th>
                        <td><input type="text" id="kausa-property-price" name="kausa-property-price" class="form-control" placeholder="Price" value="<?php echo $property_details_price ? $property_details_price : ""; ?>" /></td>
                        <th><label for="kausa-property-rooms">Rooms</label></th>
                        <td><input type="text" id="kausa-property-rooms" name="kausa-property-rooms" class="form-control" placeholder="Rooms" value="<?php echo $property_details_rooms ? $property_details_rooms : ""; ?>" /></td>
                        <th><label for="kausa-property-country">Country</label></th>
                        <td><input type="text" id="kausa-property-country" name="kausa-property-country" class="form-control" placeholder="Country" value="<?php echo $property_details_country ? $property_details_country : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-address">Address</label></th>
                        <td><input type="text" id="kausa-property-address" name="kausa-property-address" class="form-control" placeholder="Address" value="<?php echo $property_details_address ? $property_details_address : ""; ?>" /></td>
                        <th><label for="kausa-property-bathrooms">Bathroom</label></th>
                        <td><input type="text" id="kausa-property-bathrooms" name="kausa-property-bathrooms" class="form-control" placeholder="Bathroom" value="<?php echo $property_details_bathrooms ? $property_details_bathrooms : ""; ?>" /></td>
                        <th><label for="kausa-property-short-description">Short Description</label></th>
                        <td><input type="text" id="kausa-property-short-description" name="kausa-property-short-description" class="form-control" placeholder="Short Description" value="<?php echo $property_details_short_description ? $property_details_short_description : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-map-location">Location on Map</label></th>
                        <td><input type="text" id="kausa-property-map-location" name="kausa-property-map-location" class="form-control" placeholder="Location on Map" value="<?php echo $property_details_map_location ? $property_details_map_location : ""; ?>" /></td>
                        <th><label for="kausa-property-condition">Property Condition</label></th>
                        <td><input type="text" id="kausa-property-condition" name="kausa-property-condition" class="form-control" placeholder="Property Condition" value="<?php echo $property_details_property_condition ? $property_details_property_condition : ""; ?>" /></td>
                        <th><label for="kausa-property-price-description">Price Description</label></th>
                        <td><input type="text" id="kausa-property-price-description" name="kausa-property-price-description" class="form-control" placeholder="Price Description" value="<?php echo $property_details_price_description ? $property_details_price_description : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-description">Description</label></th>
                        <td><input type="text" id="kausa-property-description" name="kausa-property-description" class="form-control" placeholder="Description" value="<?php echo $property_details_description ? $property_details_description : ""; ?>" /></td>
                        <th><label for="kausa-property-preferences">Preferences</label></th>
                        <td><input type="text" id="kausa-property-preferences" name="kausa-property-preferences" class="form-control" placeholder="Preferences" value="<?php echo $property_details_preferences ? $property_details_preferences : ""; ?>" /></td>
                        <th><label for="kausa-property-build-area">Built Area (m²)</label></th>
                        <td><input type="text" id="kausa-property-build-area" name="kausa-property-build-area" class="form-control" placeholder="Built Area (m²)" value="<?php echo $property_details_build_area ? $property_details_build_area : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-additional-description">Additional Details</label></th>
                        <td><input type="text" id="kausa-property-additional-descriptions" name="kausa-property-additional-description" placeholder="Additional Details" class="form-control" value='<?php echo $property_details_additional_description ? $property_details_additional_description : ""; ?>' /></td>
                        <th><label for="kausa-property-fecades">Property Facade</label></th>
                        <td><input type="text" id="kausa-property-fecades" name="kausa-property-fecades" class="form-control" placeholder="Property Facade" value="<?php echo $property_details_property_fecades ? $property_details_property_fecades : ""; ?>" /></td>
                        <th><label for="kausa-property-property-type">Property Type</label></th>
                        <td><input type="text" id="kausa-property-property-type" name="kausa-property-property-type" class="form-control" placeholder="Property Type" value="<?php echo $property_details_property_type ? $property_details_property_type : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-gallery">Gallery</label></th>
                        <td><input type="text" id="kausa-property-gallery" name="kausa-property-gallery" class="form-control" placeholder="Gallery" value='<?php echo $property_details_gallery ? $property_details_gallery : ""; ?>' /></td>
                        <th><label for="kausa-property-elevator">Elevator</label></th>
                        <td><input type="text" id="kausa-property-elevator" name="kausa-property-elevator" class="form-control" placeholder="Elevator" value="<?php echo $property_details_elevator ? $property_details_elevator : ""; ?>" /></td>
                        <th><label for="kausa-property-property-condition">Property Condition</label></th>
                        <td><input type="text" id="kausa-property-property-condition" name="kausa-property-property-condition" class="form-control" placeholder="Property Condition" value="<?php echo $property_details_property_condition ? $property_details_property_condition : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-already-reserved-text">Property Reserved Text</label></th>
                        <td><input type="text" id="kausa-property-already-reserved-text" name="kausa-property-already-reserved-text" class="form-control" placeholder="You have already reserved this apartment." value='<?php echo $property_details_already_reserved_text ? $property_details_already_reserved_text : ""; ?>' /></td>
                        <th><label for="kausa-additional-features">Additional Features</label></th>
                        <td><input type="text" id="kausa-additional-features" name="kausa-additional-features" class="form-control" placeholder="Additional Features" value="<?php echo $property_details_additional_features ? $property_details_additional_features : ""; ?>" /></td>
                        <th><label for="kausa-property-date">Date</label></th>
                        <td><input type="text" id="kausa-property-date" name="kausa-property-date" class="form-control" placeholder="Date" value="<?php echo $property_details_date ? $property_details_date : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-time-spent">Time Spent Text</label></th>
                        <td><input type="text" id="kausa-property-time-spent" name="kausa-property-time-spent" class="form-control" placeholder="time spent" value='<?php echo $property_details_time_spent ? $property_details_time_spent : ""; ?>' /></td>
                        <th><label for="kausa-buildin-wardrobes">Built-in Wardrobes</label></th>
                        <td><input type="text" id="kausa-buildin-wardrobes" name="kausa-buildin-wardrobes" class="form-control" placeholder="Built-in Wardrobes" value="<?php echo $property_details_builtin_wardrobes ? $property_details_builtin_wardrobes : ""; ?>" /></td>
                        <th><label for="kausa-property-num-bedrooms">Number of Bedrooms</label></th>
                        <td><input type="text" id="kausa-property-num-bedrooms" name="kausa-property-num-bedrooms" class="form-control" placeholder="Number of Bedrooms" value="<?php echo $property_details_num_bedrooms ? $property_details_num_bedrooms : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-cannot-reserve">Can't reserve text</label></th>
                        <td><input type="text" id="kausa-cannot-reserve" name="kausa-cannot-reserve" class="form-control" placeholder="You have already reserved an other apartment. You can't reserve another one." value='<?php echo $property_details_cannot_reserve_text ? $property_details_cannot_reserve_text : ""; ?>' /></td>
                        <th><label for="kausa-airconditioning">Air Conditioning</label></th>
                        <td><input type="text" id="kausa-airconditioning" name="kausa-airconditioning" class="form-control" placeholder="Air Conditioning" value="<?php echo $property_details_airconditioning ? $property_details_airconditioning : ""; ?>" /></td>
                        <th><label for="kausa-property-num-bathrooms">Number of Bathrooms</label></th>
                        <td><input type="text" id="kausa-property-num-bathrooms" name="kausa-property-num-bathrooms" class="form-control" placeholder="Number of Bathrooms" value="<?php echo $property_details_num_bathrooms ? $property_details_num_bathrooms : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-reserved-by-agency">Property Reserved by agency</label></th>
                        <td><input type="text" id="kausa-property-reserved-by-agency" name="kausa-property-reserved-by-agency" class="form-control" placeholder="This apartment already reserve by agency." value='<?php echo $property_details_already_reserved_byagency ? $property_details_already_reserved_byagency : ""; ?>' /></td>
                        <th><label for="kausa-terrace">Terrace</label></th>
                        <td><input type="text" id="kausa-terrace" name="kausa-terrace" class="form-control" placeholder="Terrace" value="<?php echo $property_details_terrace ? $property_details_terrace : ""; ?>" /></td>
                        <th><label for="kausa-property-facade">Property Facade</label></th>
                        <td><input type="text" id="kausa-property-facade" name="kausa-property-facade" class="form-control" placeholder="Property Facade" value="<?php echo $property_details_facade ? $property_details_facade : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-booking-available-in">Booking Available in</label></th>
                        <td><input type="text" id="kausa-property-booking-available-in" name="kausa-property-booking-available-in" class="form-control" placeholder="Booking Available in" value='<?php echo $property_details_booking_available_in ? $property_details_booking_available_in : ""; ?>' /></td>
                        <th><label for="kausa-balcony">Balcony</label></th>
                        <td><input type="text" id="kausa-balcony" name="kausa-balcony" class="form-control" placeholder="Balcony" value="<?php echo $property_details_balcony ? $property_details_balcony : ""; ?>" /></td>
                        <th><label for="kausa-property-time">Time</label></th>
                        <td><input type="text" id="kausa-property-time" name="kausa-property-time" class="form-control" placeholder="Time" value="<?php echo $property_details_time ? $property_details_time : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-calculating">Calculating</label></th>
                        <td><input type="text" id="kausa-property-calculating" name="kausa-property-calculating" class="form-control" placeholder="Calculating..." value='<?php echo $property_details_calculating ? $property_details_calculating : ""; ?>' /></td>
                        <th><label for="kausa-storage-room">Storage Room</label></th>
                        <td><input type="text" id="kausa-storage-room" name="kausa-storage-room" class="form-control" placeholder="Storage Room" value="<?php echo $property_details_storage_room ? $property_details_storage_room : ""; ?>" /></td>
                        <th><label for="kausa-property-property-status">Estado de la Propiedad</label></th>
                        <td><input type="text" id="kausa-property-property-status" name="kausa-property-property-status" class="form-control" placeholder="Estado de la Propiedad" value="<?php echo $property_details_property_status ? $property_details_property_status : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-lock-this-apartment">Lock this apartment</label></th>
                        <td><input type="text" id="kausa-property-lock-this-apartment" name="kausa-property-lock-this-apartment" class="form-control" placeholder="You can lock this apartment exclusively for" value='<?php echo $property_details_lock_apartment ? $property_details_lock_apartment : ""; ?>' /></td>
                        <th><label for="kausa-parking-space">Parking Space</label></th>
                        <td><input type="text" id="kausa-parking-space" name="kausa-parking-space" class="form-control" placeholder="Parking Space" value="<?php echo $property_details_parking_space ? $property_details_parking_space : ""; ?>" /></td>
                        <th><label for="kausa-property-sold">Sold</label></th>
                        <td><input type="text" id="kausa-property-sold" name="kausa-property-sold" class="form-control" placeholder="Sold" value="<?php echo $property_details_sold ? $property_details_sold : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-learn-more">Learn more</label></th>
                        <td><input type="text" id="kausa-property-learn-more" name="kausa-property-learn-more" class="form-control" placeholder="Learn more" value='<?php echo $property_details_learn_more ? $property_details_learn_more : ""; ?>' /></td>
                        <th><label for="kausa-swimming-pool">Swimming Pool</label></th>
                        <td><input type="text" id="kausa-swimming-pool" name="kausa-swimming-pool" class="form-control" placeholder="Swimming Pool" value="<?php echo $property_details_swimming_pool ? $property_details_swimming_pool : ""; ?>" /></td>
                        <th><label for="kausa-property-unreserve">Unreserve</label></th>
                        <td><input type="text" id="kausa-property-unreserve" name="kausa-property-unreserve" class="form-control" placeholder="Unreserve" value="<?php echo $property_details_unreserve ? $property_details_unreserve : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-block">Block</label></th>
                        <td><input type="text" id="kausa-property-block" name="kausa-property-block" class="form-control" placeholder="Block" value='<?php echo $property_details_block ? $property_details_block : ""; ?>' /></td>
                        <th><label for="kausa-green-area">Green Area</label></th>
                        <td><input type="text" id="kausa-green-area" name="kausa-green-area" class="form-control" placeholder="Green Area" value="<?php echo $property_details_green_area ? $property_details_green_area : ""; ?>" /></td>
                        <th><label for="kausa-property-update-status">Update Status</label></th>
                        <td><input type="text" id="kausa-property-update-status" name="kausa-property-update-status" class="form-control" placeholder="Update Status" value="<?php echo $property_details_update_status ? $property_details_update_status : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-block-for">Block For</label></th>
                        <td><input type="text" id="kausa-property-block-for" name="kausa-property-block-for" class="form-control" placeholder="Block for" value='<?php echo $property_details_block_for ? $property_details_block_for : ""; ?>' /></td>
                        <th><label for="kausa-building-features">Building Features</label></th>
                        <td><input type="text" id="kausa-building-features" name="kausa-building-features" class="form-control" placeholder="Building Features" value="<?php echo $property_details_building_features ? $property_details_building_features : ""; ?>" /></td>
                        <th><label for="kausa-property-go-to-dashboard">Go to Dashboard</label></th>
                        <td><input type="text" id="kausa-property-go-to-dashboard" name="kausa-property-go-to-dashboard" class="form-control" placeholder="Go to Dashboard" value="<?php echo $property_details_goto_dashboard ? $property_details_goto_dashboard : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-no-reservation-access">No Access for Reservation text</label></th>
                        <td><input type="text" id="kausa-property-no-reservation-access" name="kausa-property-no-reservation-access" class="form-control" placeholder="You do not have access to reserve apartments." value='<?php echo $property_details_no_access ? $property_details_no_access : ""; ?>' /></td>
                        <th><label for="kausa-usable-area">Usable Area (m²)</label></th>
                        <td><input type="text" id="kausa-usable-area" name="kausa-usable-area" class="form-control" placeholder="Usable Area (m²)" value="<?php echo $property_details_usable_area ? $property_details_usable_area : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-no-document">No Document Available</label></th>
                        <td><input type="text" id="kausa-property-no-document" name="kausa-property-no-document" class="form-control" placeholder="No documents available." value='<?php echo $property_details_no_document ? $property_details_no_document : ""; ?>' /></td>
                        <th><label for="kausa-year-construction">Year of Construction</label></th>
                        <td><input type="text" id="kausa-year-construction" name="kausa-year-construction" class="form-control" placeholder="Year of Construction" value="<?php echo $property_details_year_construction ? $property_details_year_construction : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-download-document">Download Document</label></th>
                        <td><input type="text" id="kausa-property-download-document" name="kausa-property-download-document" class="form-control" placeholder="Download documents" value='<?php echo $property_details_download_document ? $property_details_download_document : ""; ?>' /></td>
                        <th><label for="kausa-community-fees">Community Fees</label></th>
                        <td><input type="text" id="kausa-community-fees" name="kausa-community-fees" class="form-control" placeholder="Community Fees" value="<?php echo $property_details_community_fees ? $property_details_community_fees : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-view-all">View all</label></th>
                        <td><input type="text" id="kausa-property-view-all" name="kausa-property-view-all" class="form-control" placeholder="View All" value='<?php echo $property_details_view_all ? $property_details_view_all : ""; ?>' /></td>
                        <th><label for="kausa-street-name">Street Name</label></th>
                        <td><input type="text" id="kausa-street-name" name="kausa-street-name" class="form-control" placeholder="Street Name" value="<?php echo $property_details_street_name ? $property_details_street_name : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-hours">Hours</label></th>
                        <td><input type="text" id="kausa-property-hours" name="kausa-property-hours" class="form-control" placeholder="hours" value='<?php echo $property_details_hours ? $property_details_hours : ""; ?>' /></td>
                        <th><label for="kausa-city-name">City Name</label></th>
                        <td><input type="text" id="kausa-city-name" name="kausa-city-name" class="form-control" placeholder="City Name" value="<?php echo $property_details_city_name ? $property_details_city_name : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-days">Days</label></th>
                        <td><input type="text" id="kausa-property-days" name="kausa-property-days" class="form-control" placeholder="days" value='<?php echo $property_details_days ? $property_details_days : ""; ?>' /></td>
                        <th><label for="kausa-state-name">State Name</label></th>
                        <td><input type="text" id="kausa-state-name" name="kausa-state-name" class="form-control" placeholder="State Name" value="<?php echo $property_details_state_name ? $property_details_state_name : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="kausa-property-commission">Commission</label></th>
                        <td><input type="text" id="kausa-property-commission" name="kausa-property-commission" class="form-control" placeholder="Comisión" value="<?php echo $property_details_commission ? $property_details_commission : ""; ?>" /></td>
                    </tr>
                    <tr>
                        <td><button type="submit" class="primary button-primary">Save Translation</button></td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
}