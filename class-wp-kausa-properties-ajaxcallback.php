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

class Wp_Kausa_Properties_AjaxCallback {

    /**
     * Registers AJAX actions and initializes the AJAX callback.
     */

    public static function ajaxCallbackLoads() {
        add_action('wp_ajax_change_property_status_admin', [__CLASS__, 'kausa_change_property_status_admin']);
        add_action('wp_ajax_change_property_status_admin_to_resesve', [__CLASS__, 'kausa_change_property_status_admin_to_resesve']);
        

        add_action('wp_ajax_filter_properties', [__CLASS__, 'filter_properties_callback']);
        add_action('wp_ajax_nopriv_filter_properties', [__CLASS__, 'filter_properties_callback']);

        add_action('wp_ajax_reserve_property', [__CLASS__, 'reserve_property_callback']);
        add_action('wp_ajax_nopriv_reserve_property', [__CLASS__, 'reserve_property_callback']);

        add_action('wp_ajax_unreserve_property', [__CLASS__, 'unreserve_property_callback']);
        add_action('wp_ajax_nopriv_unreserve_property', [__CLASS__, 'unreserve_property_callback']);

        add_action('wp_ajax_sold_property', [__CLASS__, 'sold_property_callback']);
        add_action('wp_ajax_nopriv_sold_property', [__CLASS__, 'sold_property_callback']); 

        add_action('wp_ajax_add_google_api_key_admin', [__CLASS__, 'kausa_add_google_api_key_admin']);
        add_action('wp_ajax_add_heading_translation', [__CLASS__, 'kausa_add_heading_translation']);
        add_action('wp_ajax_update_no_penalty_time_admin', [__CLASS__, 'kausa_update_no_penalty_time_admin']);
        add_action('wp_ajax_update_penalty_time_admin', [__CLASS__, 'kausa_update_penalty_time_admin']);
    }

    /**
     * AJAX callback function to filter properties based on submitted criteria.
     */
    public static function filter_properties_callback() {

        $filters = $_POST['filters'];

        $min_price = isset($filters['price']['min']) ? floatval($filters['price']['min']) : 0;
        $max_price = isset($filters['price']['max']) ? floatval($filters['price']['max']) : PHP_INT_MAX;

        $attributes = isset($filters['attributes']) ? $filters['attributes'] : [];

        $args = [
            'post_type' => 'kausa_properties',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'kausa_property_price',
                    'value' => [$min_price, $max_price],
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ]
            ]
        ];

        foreach ($attributes as $attribute => $values) {

            if (!is_array($values)) {
                $values = [$values];
            }

            $args['meta_query'][] = [
                'relation' => 'AND',
                array_map(function($value) use ($attribute) {
                    return [
                        'key' => strtolower($attribute),
                        'value' => $value,
                        'compare' => '=',
                    ];
                }, $values)
            ];
        }

        $property_query = new WP_Query($args);
        if ($property_query->have_posts()) {
            while ($property_query->have_posts()) {
                $property_query->the_post();

                $price = get_post_meta(get_the_ID(), 'kausa_property_price', true);
                $short_description = get_post_meta(get_the_ID(), 'kausa_property_short_description', true);
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
                                $precio_formateado = number_format( $price, 0, ',', '.' );
                                ?>
                                <p class="kausa-property-price"><?php echo esc_html( $precio_formateado ); ?>€</p>
                            </div>
                            <?php
                                if($short_description){
                                    $content = $short_description;
                                }
                                if(get_the_content()){
                                    $content = get_the_content();
                                }
                                if($content){
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
        } else {
            echo '<div class="kausa-property-item no-property-found"><div class="kausa-property-details">No properties match the selected filters.</div></div>';
			// echo '<div class="kausa-property-item box-hide"><div class="kausa-property-details">No properties match the selected filters.</div></div><div class="kausa-property-item"><div class="kausa-property-details">No properties match the selected filters.</div></div><div class="kausa-property-item box-hide"><div class="kausa-property-details">No properties match the selected filters.</div></div>';
        }
        wp_reset_postdata();
        wp_die();
    }

    /**
     * AJAX callback function to reserve properties based on user details.
     */
    public static function reserve_property_callback() {
        if (!isset($_POST['property_id']) || !isset($_POST['user_id'])) {
            wp_send_json_error(array('message' => 'Invalid request'));
        }

        $property_id = intval($_POST['property_id']);
        $user_id = intval($_POST['user_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';

        $property_reserved = get_post_meta($property_id, '_kausa_property_reserved', true);
        $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);

        if ($property_reserved == 'yes' && $reserved_by_user != $user_id) {
            wp_send_json_error(array('message' => 'This property is already reserved by another user.'));
        } elseif ($reserved_by_user == $user_id) {
            wp_send_json_error(array('message' => 'You have already reserved this property.'));
        }

        $current_time = current_time('mysql');

        $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");               
        $penalty_period = get_option("kausa-penalty-reservation-time", "7 days"); 

        $no_penalty_time = date('YmdHis', strtotime('+'.$no_penalty_period, strtotime($current_time)));
        $penalty_time = date('YmdHis', strtotime('+'.$penalty_period, strtotime($current_time)));
        
        update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'no');
        update_post_meta($property_id, '_kausa_property_reserved', 'yes');
        update_post_meta($property_id, '_kausa_property_sold', 'no');
        update_post_meta($property_id, '_kausa_property_reserved_by_user', $user_id);
        update_post_meta($property_id, '_kausa_property_reserved_time', $current_time);
        update_post_meta($property_id, '_kausa_property_sold_time', null);
        update_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', $no_penalty_time);
        update_post_meta($property_id, '_kausa_property_reserved_penalty_time', $penalty_time);
        update_post_meta($property_id, '_kausa_property_reserved_time_by_user', $penalty_time);

        $inserted = $wpdb->insert(
            $table_name,
            array(
                'user_id'               => $user_id,
                'property_id'           => $property_id,
                'property_status'       => 'reserved',
                'property_panelty_time' => null,
                'property_reserve_time' => $current_time,
                'property_sold_time'    => null,
                'property_unreserve_time' => null,
                'property_spent_time'   => null,
            ),
            array('%d','%d','%s','%s','%s','%s','%s','%s', ));

        if ($inserted === false) {
            wp_send_json_error(array('message' => 'Failed to reserve the property in the database.'));
        }
        if($inserted){
            self::trigger_send_email($property_id, $user_id, "reserved", "agency", $current_time, '');
        }

        wp_send_json_success(array('message' => 'Property reserved successfully.'));
    }

    /**
     * AJAX callback function to unreserve property based on user details.
     */
    public static function unreserve_property_callback() {
        if (isset($_POST['property_id']) && is_numeric($_POST['property_id'])) {
            $property_id = $_POST['property_id'];
            $current_time = current_time('mysql');

            global $wpdb;
            $table_name = $wpdb->prefix . 'kausa_properties_meta';

            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);

            $time_spent_seconds = strtotime($current_time) - strtotime($reserved_time);
            $time_spent_hours = floor($time_spent_seconds / 3600);
            $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
            $time_spent_remaining_seconds = $time_spent_seconds % 60;
            
            $time_spent_formatted = sprintf('%02dh %02dm %02ds', $time_spent_hours, $time_spent_minutes, $time_spent_remaining_seconds);
          
            $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");  
            $no_penalty_array = explode(" ", $no_penalty_period);
            $grace_period_seconds = $no_penalty_array[0] * 3600;
            if($no_penalty_array[1] == "days"){
                $grace_period_seconds =  $no_penalty_array[0] * 24 * 3600;
            }

            if ($time_spent_seconds > $grace_period_seconds) {
                $penalty_seconds = $time_spent_seconds - $grace_period_seconds;

                $penalty_hours = floor($penalty_seconds / 3600);
                $penalty_minutes = floor(($penalty_seconds % 3600) / 60);
                $penalty_remaining_seconds = $penalty_seconds % 60;
                $penalty_formatted = sprintf('%02dh %02dm %02ds', $penalty_hours, $penalty_minutes, $penalty_remaining_seconds);

                $penalty_end_timestamp = strtotime($current_time) + $penalty_seconds;
                $penalty_end_time = date('Y-m-d H:i:s', $penalty_end_timestamp);
            } else {
                $penalty_formatted = '00h 00m 00s';
                $penalty_end_time = '00h 00m 00s';
            }

            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM $table_name WHERE property_id = %d AND property_reserve_time = %s",
                    $property_id,
                    $reserved_time
                )
            );

            if ($row) {
                $wpdb->update(
                    $table_name,
                    array(
                        'property_status' => 'unreserved',
                        'property_panelty_time' => $penalty_end_time,
                        'property_unreserve_time' => $current_time,
                        'property_spent_time' => $time_spent_formatted,
                    ),
                    array(
                        'user_id' => $reserved_by_user,
                        'property_id' => $property_id,
                        'id' => $row->id,
                    ),
                    array('%s', '%s', '%s', '%s'),
                    array('%d', '%d', '%d')
                );
            
                update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'yes');
                update_post_meta($property_id, '_kausa_property_reserved', 'no');
                update_post_meta($property_id, '_kausa_property_sold', 'no');
                update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
                update_post_meta($property_id, '_kausa_property_reserved_time', null);
                update_post_meta($property_id, '_kausa_property_unreserved_time', $current_time);
                update_post_meta($property_id, '_kausa_property_sold_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_penalty_time', null);
self::trigger_send_email($property_id, $reserved_by_user, "unreserved", "agency", $current_time, $time_spent_formatted);
                wp_send_json_success(array(
                    'message' => 'Property unreserved successfully.',
                    'time_spent' => $time_spent_formatted,
                    'property_unreserve_time' => $current_time,
                    'penalty_time' => $penalty_formatted,
                    'penalty_end_time' => $penalty_end_time,
                ));
            }else{
                wp_send_json_success(array(
                    'message' => 'No Property Found for unreserved.'
                ));
            }

        } else {
            wp_send_json_error(array('message' => 'Invalid property ID.'));
        }
    }

    /**
     * AJAX callback function to sold property based on user details.
     */
    public static function sold_property_callback() {

        if (isset($_POST['property_id']) && is_numeric($_POST['property_id']) && isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
            $property_id = $_POST['property_id'];
            $user_id = $_POST['user_id'];
            $current_time = current_time('mysql');
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'kausa_properties_meta';

            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);

            $time_spent_seconds = strtotime($current_time) - strtotime($reserved_time);
            $time_spent_hours = floor($time_spent_seconds / 3600);
            $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
            $time_spent_remaining_seconds = $time_spent_seconds % 60;
            
            $time_spent_formatted = sprintf('%02dh %02dm %02ds', $time_spent_hours, $time_spent_minutes, $time_spent_remaining_seconds);
            
            $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");  
            $no_penalty_array = explode(" ", $no_penalty_period);
            $grace_period_seconds = $no_penalty_array[0] * 3600;
            if($no_penalty_array[1] == "days"){
                $grace_period_seconds =  $no_penalty_array[0] * 24 * 3600;
            }

            if ($time_spent_seconds > $grace_period_seconds) {
                $penalty_seconds = $time_spent_seconds - $grace_period_seconds;

                $penalty_hours = floor($penalty_seconds / 3600);
                $penalty_minutes = floor(($penalty_seconds % 3600) / 60);
                $penalty_remaining_seconds = $penalty_seconds % 60;

                $penalty_formatted = sprintf('%02dh %02dm %02ds', $penalty_hours, $penalty_minutes, $penalty_remaining_seconds);

                $penalty_end_timestamp = strtotime($current_time) + $penalty_seconds;
                $penalty_end_time = date('Y-m-d H:i:s', $penalty_end_timestamp);
            } else {

                $penalty_formatted = '00h 00m 00s';
                $penalty_end_time = '00h 00m 00s';
            }

            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM $table_name WHERE property_id = %d AND property_reserve_time = %s",
                    $property_id,
                    $reserved_time
                )
            );

            if ($row) {
                $wpdb->update(
                    $table_name,
                    array(
                        'property_status' => 'sold',
                        'property_panelty_time' => $penalty_end_time,
                        'property_sold_time' => $current_time,
                        'property_spent_time' => $time_spent_formatted,
                    ),
                    array(
                        'user_id' => $reserved_by_user,
                        'property_id' => $property_id,
                        'id' => $row->id,
                    ),
                    array('%s', '%s', '%s', '%s'),
                    array('%d', '%d', '%d')
                );
            
                update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', null);
                update_post_meta($property_id, '_kausa_property_reserved', null);
                update_post_meta($property_id, '_kausa_property_sold', 'yes');
                update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
                update_post_meta($property_id, '_kausa_property_reserved_time', null);
                update_post_meta($property_id, '_kausa_property_unreserved_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_penalty_time', $penalty_end_time);
                update_post_meta($property_id, '_kausa_property_sold_by_user', $user_id);
                update_post_meta($property_id, '_kausa_property_sold_time', $current_time);
self::trigger_send_email($property_id, $reserved_by_user, "booked", "agency", $current_time, $time_spent_formatted);
                wp_send_json_success(array(
                    'message' => 'Property Sold successfully.',
                    'time_spent' => $time_spent_formatted,
                    'property_sold_time' => $current_time,
                    'penalty_time' => $penalty_formatted,
                    'penalty_end_time' => $penalty_end_time,
                ));
            }else{
                wp_send_json_success(array(
                    'message' => 'No Property Found for sold.'
                ));
            }
        } else {
            wp_send_json_error(array('message' => 'Invalid property ID or user ID.'));
        }
    }

    /**
     * AJAX callback function to change property status based on admin selection.
     */
    public static function kausa_change_property_status_admin() {
        check_ajax_referer('kausa_property_status_nonce', 'nonce');
    
        if (!isset($_POST['property_id'], $_POST['user_id'], $_POST['property_status'], $_POST['property_date'])) {
            wp_send_json_error(['message' => 'Invalid input.']);
        }
    
        $property_id = intval($_POST['property_id']);
        $user_id = intval($_POST['user_id']);
        $property_status = sanitize_text_field($_POST['property_status']);
        $current_time = sanitize_text_field($_POST['property_date']);

        if ($property_status == 'sold') {
      
            global $wpdb;
            $table_name = $wpdb->prefix . 'kausa_properties_meta';

            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);
            if($reserved_by_user && $reserved_time){
                $reserved_by_user = $reserved_by_user;
                $reserved_time = $reserved_time;
            }else{
                $current_time = current_time('mysql');

                $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");               
                $penalty_period = get_option("kausa-penalty-reservation-time", "7 days");                         
                
                $no_penalty_time = date('YmdHis', strtotime('+'.$no_penalty_period, strtotime($current_time)));
                $penalty_time = date('YmdHis', strtotime('+'.$penalty_period, strtotime($current_time)));
                
                update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'no');
                update_post_meta($property_id, '_kausa_property_reserved', 'yes');
                update_post_meta($property_id, '_kausa_property_sold', 'no');
                update_post_meta($property_id, '_kausa_property_reserved_by_user', $user_id);
                update_post_meta($property_id, '_kausa_property_reserved_time', $current_time);
                update_post_meta($property_id, '_kausa_property_sold_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', $no_penalty_time);
                update_post_meta($property_id, '_kausa_property_reserved_penalty_time', $penalty_time);
                update_post_meta($property_id, '_kausa_property_reserved_time_by_user', $penalty_time);

                $inserted = $wpdb->insert(
                    $table_name,
                    array(
                        'user_id'               => $user_id,
                        'property_id'           => $property_id,
                        'property_status'       => 'reserved',
                        'property_panelty_time' => null,
                        'property_reserve_time' => $current_time,
                        'property_sold_time'    => null,
                        'property_unreserve_time' => null,
                        'property_spent_time'   => null,
                    ),
                    array('%d','%d','%s','%s','%s','%s','%s','%s', ));
            }

            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);

            $time_spent_seconds = strtotime($current_time) - strtotime($reserved_time);
            $time_spent_hours = floor($time_spent_seconds / 3600);
            $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
            $time_spent_remaining_seconds = $time_spent_seconds % 60;
            
            $time_spent_formatted = sprintf('%02dh %02dm %02ds', $time_spent_hours, $time_spent_minutes, $time_spent_remaining_seconds);
            $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");  
            $no_penalty_array = explode(" ", $no_penalty_period);
            $grace_period_seconds = $no_penalty_array[0] * 3600;
            if($no_penalty_array[1] == "days"){
                $grace_period_seconds =  $no_penalty_array[0] * 24 * 3600;
            }
            

            if ($time_spent_seconds > $grace_period_seconds) {
                $penalty_seconds = $time_spent_seconds - $grace_period_seconds;

                $penalty_hours = floor($penalty_seconds / 3600);
                $penalty_minutes = floor(($penalty_seconds % 3600) / 60);
                $penalty_remaining_seconds = $penalty_seconds % 60;

                $penalty_formatted = sprintf('%02dh %02dm %02ds', $penalty_hours, $penalty_minutes, $penalty_remaining_seconds);

                $penalty_end_timestamp = strtotime($current_time) + $penalty_seconds;
                $penalty_end_time = date('Y-m-d H:i:s', $penalty_end_timestamp);
            } else {

                $penalty_formatted = '00h 00m 00s';
                $penalty_end_time = '00h 00m 00s';
            }

            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM $table_name WHERE property_id = %d AND property_reserve_time = %s",
                    $property_id,
                    $reserved_time
                )
            );

            if ($row) {
                $wpdb->update(
                    $table_name,
                    array(
                        'property_status' => 'sold',
                        'property_panelty_time' => $penalty_end_time,
                        'property_sold_time' => $current_time,
                        'property_spent_time' => $time_spent_formatted,
                    ),
                    array(
                        'user_id' => $reserved_by_user,
                        'property_id' => $property_id,
                        'id' => $row->id,
                    ),
                    array('%s', '%s', '%s', '%s'),
                    array('%d', '%d', '%d')
                );
            
                update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', null);
                update_post_meta($property_id, '_kausa_property_reserved', null);
                update_post_meta($property_id, '_kausa_property_sold', 'yes');
                update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
                update_post_meta($property_id, '_kausa_property_reserved_time', null);
                update_post_meta($property_id, '_kausa_property_unreserved_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_penalty_time', $penalty_end_time);
                update_post_meta($property_id, '_kausa_property_sold_by_user', $user_id);
                update_post_meta($property_id, '_kausa_property_sold_time', $current_time);
self::trigger_send_email($property_id, $user_id, "sold", "admin user", $current_time, $time_spent_formatted);
                wp_send_json_success(array(
                    'message' => 'Property Sold successfully.',
                    'time_spent' => $time_spent_formatted,
                    'property_sold_time' => $current_time,
                    'penalty_time' => $penalty_formatted,
                    'penalty_end_time' => $penalty_end_time,
                ));
            }else{
                wp_send_json_success(array(
                    'message' => 'No Property Found for sold.'
                ));
            }
        } elseif ($property_status == 'unreserve') {

            global $wpdb;
            $table_name = $wpdb->prefix . 'kausa_properties_meta';

            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);

            $time_spent_seconds = strtotime($current_time) - strtotime($reserved_time);
            $time_spent_hours = floor($time_spent_seconds / 3600);
            $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
            $time_spent_remaining_seconds = $time_spent_seconds % 60;
            
            $time_spent_formatted = sprintf('%02dh %02dm %02ds', $time_spent_hours, $time_spent_minutes, $time_spent_remaining_seconds);

            $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");  
            $no_penalty_array = explode(" ", $no_penalty_period);
            $grace_period_seconds = $no_penalty_array[0] * 3600;
            if($no_penalty_array[1] == "days"){
                $grace_period_seconds =  $no_penalty_array[0] * 24 * 3600;
            }

            if ($time_spent_seconds > $grace_period_seconds) {
                $penalty_seconds = $time_spent_seconds - $grace_period_seconds;

                $penalty_hours = floor($penalty_seconds / 3600);
                $penalty_minutes = floor(($penalty_seconds % 3600) / 60);
                $penalty_remaining_seconds = $penalty_seconds % 60;
                $penalty_formatted = sprintf('%02dh %02dm %02ds', $penalty_hours, $penalty_minutes, $penalty_remaining_seconds);

                $penalty_end_timestamp = strtotime($current_time) + $penalty_seconds;
                $penalty_end_time = date('Y-m-d H:i:s', $penalty_end_timestamp);
            } else {
                $penalty_formatted = '00h 00m 00s';
                $penalty_end_time = '00h 00m 00s';
            }

            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM $table_name WHERE property_id = %d AND property_reserve_time = %s",
                    $property_id,
                    $reserved_time
                )
            );

            if ($row) {
                $wpdb->update(
                    $table_name,
                    array(
                        'property_status' => 'unreserved',
                        'property_panelty_time' => $penalty_end_time,
                        'property_unreserve_time' => $current_time,
                        'property_spent_time' => $time_spent_formatted,
                    ),
                    array(
                        'user_id' => $reserved_by_user,
                        'property_id' => $property_id,
                        'id' => $row->id,
                    ),
                    array('%s', '%s', '%s', '%s'),
                    array('%d', '%d', '%d')
                );
            
                update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'yes');
                update_post_meta($property_id, '_kausa_property_reserved', 'no');
                update_post_meta($property_id, '_kausa_property_sold', 'no');
                update_post_meta($property_id, '_kausa_property_reserved_by_user', null);
                update_post_meta($property_id, '_kausa_property_reserved_time', null);
                update_post_meta($property_id, '_kausa_property_unreserved_time', $current_time);
                update_post_meta($property_id, '_kausa_property_sold_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_penalty_time', null);
self::trigger_send_email($property_id, $reserved_by_user, "unreserve", "admin user", $current_time, $time_spent_formatted);
                wp_send_json_success(array(
                    'message' => 'Property unreserved successfully.',
                    'time_spent' => $time_spent_formatted,
                    'property_unreserve_time' => $current_time,
                    'penalty_time' => $penalty_formatted,
                    'penalty_end_time' => $penalty_end_time,
                ));
            }else{
                wp_send_json_success(array(
                    'message' => 'No Property Found for unreserved.'
                ));
            }
        }
    
        wp_send_json_error(['message' => 'Invalid property status.']);
    }

    /**Function to update property status to reserve by admin */

    public static function kausa_change_property_status_admin_to_resesve(){
        check_ajax_referer('kausa_property_status_nonce', 'nonce');
    
        if (!isset($_POST['property_id'], $_POST['user_id'], $_POST['property_status'], $_POST['property_date'])) {
            wp_send_json_error(['message' => 'Invalid input.']);
        }
    
        $property_id = intval($_POST['property_id']);
        $user_id = intval($_POST['user_id']);
        $property_status = sanitize_text_field($_POST['property_status']);
        $current_time = sanitize_text_field($_POST['property_date']);

        if ($property_status == 'reserve') {
      
            global $wpdb;
            $table_name = $wpdb->prefix . 'kausa_properties_meta';

            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);
            if($reserved_by_user && $reserved_time){
                $reserved_by_user = $reserved_by_user;
                $reserved_time = $reserved_time;
            }else{
                $current_time = current_time('mysql');

                $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");               
                $penalty_period = get_option("kausa-penalty-reservation-time", "7 days");                         
                
                $no_penalty_time = date('YmdHis', strtotime('+'.$no_penalty_period, strtotime($current_time)));
                $penalty_time = date('YmdHis', strtotime('+'.$penalty_period, strtotime($current_time)));
                
                update_post_meta($property_id, '_kausa_property_avaliable_to_reserve', 'no');
                update_post_meta($property_id, '_kausa_property_reserved', 'yes');
                update_post_meta($property_id, '_kausa_property_sold', 'no');
                update_post_meta($property_id, '_kausa_property_reserved_by_user', $user_id);
                update_post_meta($property_id, '_kausa_property_reserved_time', $current_time);
                update_post_meta($property_id, '_kausa_property_sold_time', null);
                update_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', $no_penalty_time);
                update_post_meta($property_id, '_kausa_property_reserved_penalty_time', $penalty_time);
                update_post_meta($property_id, '_kausa_property_reserved_time_by_user', $penalty_time);

                $inserted = $wpdb->insert(
                    $table_name,
                    array(
                        'user_id'               => $user_id,
                        'property_id'           => $property_id,
                        'property_status'       => 'reserved',
                        'property_panelty_time' => null,
                        'property_reserve_time' => $current_time,
                        'property_sold_time'    => null,
                        'property_unreserve_time' => null,
                        'property_spent_time'   => null,
                    ),
                    array('%d','%d','%s','%s','%s','%s','%s','%s', ));
            }

            $reserved_time = get_post_meta($property_id, '_kausa_property_reserved_time', true);
            $reserved_by_user = get_post_meta($property_id, '_kausa_property_reserved_by_user', true);

            $time_spent_seconds = strtotime($current_time) - strtotime($reserved_time);
            $time_spent_hours = floor($time_spent_seconds / 3600);
            $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
            $time_spent_remaining_seconds = $time_spent_seconds % 60;
            
            $time_spent_formatted = sprintf('%02dh %02dm %02ds', $time_spent_hours, $time_spent_minutes, $time_spent_remaining_seconds);
            $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");  
            $no_penalty_array = explode(" ", $no_penalty_period);
            $grace_period_seconds = $no_penalty_array[0] * 3600;
            if($no_penalty_array[1] == "days"){
                $grace_period_seconds =  $no_penalty_array[0] * 24 * 3600;
            }
            

            if ($time_spent_seconds > $grace_period_seconds) {
                $penalty_seconds = $time_spent_seconds - $grace_period_seconds;

                $penalty_hours = floor($penalty_seconds / 3600);
                $penalty_minutes = floor(($penalty_seconds % 3600) / 60);
                $penalty_remaining_seconds = $penalty_seconds % 60;

                $penalty_formatted = sprintf('%02dh %02dm %02ds', $penalty_hours, $penalty_minutes, $penalty_remaining_seconds);

                $penalty_end_timestamp = strtotime($current_time) + $penalty_seconds;
                $penalty_end_time = date('Y-m-d H:i:s', $penalty_end_timestamp);
            } else {

                $penalty_formatted = '00h 00m 00s';
                $penalty_end_time = '00h 00m 00s';
            } 
            self::trigger_send_email($property_id, $reserved_by_user, "reserved", "admin user", $current_time, "");
            wp_send_json_success(array(
                'message' => 'Property reserved successfully.',                
                'property_reserve_time' => $current_time,               
            ));           
        } else {
            wp_send_json_success(array(
                'message' => 'No Property Found for reserved.'
            ));
        }
    
        wp_send_json_error(['message' => 'Invalid property status.']);
    }


    //Add google API Key admin
    static function kausa_add_google_api_key_admin(){
        check_ajax_referer('kausa_property_status_nonce', 'nonce');
    
        if (!isset($_POST)) {
            wp_send_json_error(['message' => 'Invalid input.']);
        }

        if (isset($_POST['google_api_key']) || isset($_POST['penalty_free_reservation_time']) || isset($_POST['penalty_reservation_time'])) {
            $apikey = sanitize_text_field($_POST['google_api_key']);
            $penalty_free_reservation_time = sanitize_text_field($_POST['penalty_free_reservation_time']);
            $penalty_reservation_time = sanitize_text_field($_POST['penalty_reservation_time']);
            if(!empty($apikey)){
                update_option( 'kausa-google-map-api-key', $apikey );
            }
            
            if(!empty($penalty_free_reservation_time)){
                update_option( 'kausa-penalty-free-reservation-time', $penalty_free_reservation_time );
            }
            
            if(!empty($penalty_reservation_time)){
                update_option( 'kausa-penalty-reservation-time', $penalty_reservation_time );
            }
            

            if(get_option("kausa-google-map-api-key", true)){
                wp_send_json_success(array(
                    'message' => 'Setting updated successfully.'                    
                ));
            } else {
                wp_send_json_success(array(
                    'message' => 'Unable to update Setting.'
                ));
            }
        }
    }

    static function kausa_add_heading_translation(){         
        check_ajax_referer('kausa_property_status_nonce', 'nonce'); 
              
        if (!isset($_POST)) {
            wp_send_json_error(['message' => 'Invalid input.']);
        }     

        $update_translation = get_option( 'property_details_transalation', []);

        if (isset($_POST)) {
            //check_ajax_referer('kausa_property_status_nonce', $_POST['nonce']);
            $translation_data = $_POST;
            

            foreach($translation_data as $key => $data){
                if(!empty($data) && $key != 'nonce' && $key != 'action'){
                    $update_translation[$key] = $data;
                }                
            }

            update_option( 'property_details_transalation',  $update_translation );

            if(get_option("property_details_transalation", true)){
                wp_send_json_success(array(
                    'message' => 'Translation data updated successfully.'                    
                ));
            } else {
                wp_send_json_success(array(
                    'message' => 'Unable to update Translation data.',
                    'data' => $_POST
                ));
            }
        }
    }

    public static function kausa_update_no_penalty_time_admin() {        
        check_ajax_referer('kausa_property_status_nonce', 'nonce');   
      
        if (!isset($_POST['propertyId']) || !isset($_POST['selectedDate'])) {
            wp_send_json_error(['message' => 'Invalid input. Missing propertyId or selectedDate.']);
        }
            
        $property_id = sanitize_text_field($_POST['propertyId']);
        $selected_date = sanitize_text_field($_POST['selectedDate']);      
       
        
        $date = new DateTime($selected_date);
        $penalty_time = $date->format('Y-m-d H:i:s');
        $updated = update_post_meta($property_id, '_kausa_property_reserved_penalty_time', $penalty_time);    
    
        if ($updated) {
            wp_send_json_success(['message' => 'Translation data updated successfully.']);
        } else {
            wp_send_json_error(['message' => 'Unable to update Translation data.']);
        }
    }

    

    public static function kausa_update_penalty_time_admin() {        
        check_ajax_referer('kausa_property_status_nonce', 'nonce');   
      
        if (!isset($_POST['Id']) || !isset($_POST['selectedDate'])) {
            wp_send_json_error(['message' => 'Invalid input. Missing propertyId or selectedDate.']);
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';
        $id = sanitize_text_field($_POST['Id']);
        $selected_date = sanitize_text_field($_POST['selectedDate']);      
       
        
        $date = new DateTime($selected_date);
        $penalty_end_time = $date->format('Y-m-d H:i:s');
        
        $updated = $wpdb->update(
            $table_name,
            array(               
                'property_panelty_time' => $penalty_end_time,                
            ),
            array(                
                'id' => $id,
            ),
            array('%s'),
            array('%d')
        );

        if ($updated === false) {
            wp_send_json_error(array('message' => 'Failed to update penalty time in the database.'));
        }

        wp_send_json_success(array('message' => 'Property penalty time updated successfully.'));
       
    }
    public static function trigger_send_email($property_id, $user_id, $new_status, $done_by_text, $current_time, $time_spent_formatted = ''){
        $time_format_string = new DateTime($current_time);                
        $formatted_date_for_current_time = $time_format_string->format('Y-m-d H:i:s');
        $post_title = get_the_title( $property_id );
        $user_info = get_userdata( $user_id );
        $user_name = $user_info->display_name;
        $user_email = $user_info->user_email;
        $from = get_bloginfo('admin_email');
        $subject = 'Property '.$new_status;
        $to = "kausainmuebles@gmail.com";
        $headers = "From: KAUSA ".$from."\r\n";       
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $message = "
            <!DOCTYPE html>
            <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                        }
                        .email-container {
                            width: 100%;
                            max-width: 600px;
                            margin: 0 auto;
                            padding: 20px;
                            border: 1px solid #ddd;
                            border-radius: 10px;
                            background-color: #f9f9f9;
                        }
                        .email-header {
                            background-color: #0073aa;
                            color: #fff;
                            padding: 10px;
                            text-align: center;
                            font-size: 24px;
                            border-radius: 10px 10px 0 0;
                        }
                        .email-content {
                            padding: 20px;
                        }
                        .email-footer {
                            text-align: center;
                            font-size: 12px;
                            color: #666;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>";
            $message .=  "<div class='email-container'>
                        <div class='email-header'>
                            Property ".$new_status." by " .$done_by_text."
                        </div>
                        <div class='email-content'>";                          
            $message .= "<p>This is for information that below property ".$new_status." by ".$done_by_text.":</p>";                          
            $message .= "<p>Agency name: ".$user_name.".</p>";
            $message .= "<p>Email id: ".$user_email."</p>";
            $message .= "<p>Property id: ".$property_id."</p>";
            $message .= "<p>Property title: ".$post_title."</p>";
            $message .= "<p>Property ".$new_status." time: ". $formatted_date_for_current_time."</p>";
            if($time_spent_formatted){
                $message .= "<p>Property spend time: ".$time_spent_formatted."</p>";
            }            
            $message .=  "</div>
                        <div class='email-footer'>
                            Portalkusa.com
                        </div>
                    </div>
                </body>
            </html>
             ";
        
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        wp_mail( $to, $subject, $message, $headers );
        error_reporting(E_ALL);  
    }
}