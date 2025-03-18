<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://https://wppb.me
 * @since      1.0.0
 *
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/public/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
if(!function_exists( 'get_property_filter_options' )){
    function get_property_filter_options() {

        $property_attributes = array(
            "Price" => array(
                'meta_key' => 'kausa_property_price',
            ),
            "Type of Housing" => array(
                'meta_key' => 'kausa_property_type',
                'values' => array('House', 'Apartment', 'Villa', 'Condo')
            ),
            "Rooms" => array(
                'meta_key' => 'kausa_property_bedrooms',
                'values' => array('0', '1', '2', '3', '4 or more')
            ),
            "Bathroom" => array(
                'meta_key' => 'kausa_property_bathrooms',
                'values' => array('0', '1', '2', '3', '4 or more')
            ),
            "Property Condition" => array(
                'meta_key' => 'kausa_property_condition',
                'values' => array('New', 'Renovated', 'Old')
            ),
            "Preferences" => array(
                'meta_key' => 'kausa_property_preference',
                'values' => array('Sell', 'Rent')
            ),
            "Property Facade" => array(
                'meta_key' => 'kausa_property_facade',
                'values' => array('Exterior', 'Interior')
            ),
            "Elevator" => array(
                'meta_key' => 'kausa_property_elevator',
                'values' => array('Yes', 'No')
            ),
        );

        global $wpdb;

        $filters = [];

        foreach ($property_attributes as $attribute => $details) {
            $meta_key = $details['meta_key'];

            if ($attribute === "Price") {

                $prices = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT DISTINCT CAST(meta_value AS UNSIGNED) AS price 
                        FROM {$wpdb->postmeta} 
                        WHERE meta_key = %s",
                        $meta_key
                    )
                );

                $prices = array_filter($prices, function($value) {
                    return is_numeric($value);
                });

                sort($prices, SORT_NUMERIC);

                if (!empty($prices)) {
                    $filters[$attribute] = [
                        'meta_key' => $meta_key,
                        'min' => min($prices),
                        'max' => max($prices),
                    ];
                } else {
                    $filters[$attribute] = [
                        'meta_key' => $meta_key,
                        'min' => 0,
                        'max' => 50000,
                    ];
                }
            } else {
                $values = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
                        $meta_key
                    )
                );

                $values = array_unique(
                    array_map('strtolower', array_merge($details['values'] ?? [], array_filter($values)))
                );
                sort($values, SORT_STRING);

                $filters[$attribute] = [
                    'meta_key' => $meta_key,
                    'values' => $values,
                ];
            }
        }

        return $filters;
    }
}

function get_rounded_bounds($min, $max){
    $rounded_min = floor($min / 500) * 500;
    $rounded_max = ceil($max / 500) * 500;
    if ($rounded_max > 5000) {
        $rounded_max = ceil($max / 5000) * 5000;
    }
    if ($rounded_max > 10000) {
        $rounded_max = ceil($max / 5000) * 5000;
    }
    return [
        'min' => $rounded_min,
        'max' => $rounded_max,
    ];
} ?>

<div class="kausa-properties-archive-container">
    <div class="filter-burger-menu-box">
        <div class="filter-burger-menu">
            <span></span>
        </div>
        <div class="filter-burger-menu-Text">
            <div>Filter</div>
        </div>
    </div>
    <div class="kausa-properties-archive-container-wrapper">
        <!-- Sidebar Filters -->
        <div class="kausa-properties-filter-sidebar">
            <?php
            $filters = get_property_filter_options();
            $property_filter_heading = get_option("property_details_transalation", "");
            $property_filter_heading_array = array(
                'Price' => isset($property_filter_heading['kausa-property-price']) ? $property_filter_heading['kausa-property-price'] : "Price",
                'Type of Housing' => isset($property_filter_heading['kausa-property-housing-type']) ? $property_filter_heading['kausa-property-housing-type'] : "Type of Housing",
                'Rooms' => isset($property_filter_heading['kausa-property-rooms']) ? $property_filter_heading['kausa-property-rooms'] : "Rooms",
                'Bathroom' => isset($property_filter_heading['kausa-property-bathrooms']) ? $property_filter_heading['kausa-property-bathrooms'] : "Bathroom",
                'Property Condition' => isset($property_filter_heading['kausa-property-condition']) ? $property_filter_heading['kausa-property-condition'] : "Property Condition",
                'Preferences' => isset($property_filter_heading['kausa-property-preferences']) ? $property_filter_heading['kausa-property-preferences'] : "Preferences",
                'Property Facade' => isset($property_filter_heading['kausa-property-fecades']) ? $property_filter_heading['kausa-property-fecades'] : "Property Facade",
                'Elevator' => isset($property_filter_heading['kausa-property-elevator']) ? $property_filter_heading['kausa-property-elevator'] : "Elevator"
            );
            foreach ($filters as $attribute => $data) {
                echo '<div class="property-filter">';
                echo '<div class="property-attribute-box">';
                echo '<div class="property-attribute-heading">';
                if ($attribute === 'Price') {
                    echo '<div class="property-price-heading">' . esc_html($property_filter_heading_array[$attribute] ?: $attribute) . '<span>(€)</span></div>';
                }else{
                    echo '<div>' . esc_html($property_filter_heading_array[$attribute] ?: $attribute) . '</div>';
                }
                echo '</div>';
                echo '<div class="property-attribute-values">';
                
                if ($attribute === 'Price') {
                    $rounded_bounds = get_rounded_bounds($data['min'], $data['max']);
                    $min_price = (float) $rounded_bounds['min'];
                    $max_price = (float) $rounded_bounds['max'];

                    echo '<div class="property-attribute-label property-attribute-price-box">';
                    echo '<input type="text" id="price-range-slider" class="js-range-slider" name="price_range" value="" ';
                    echo 'data-skin="round" data-type="double" data-min="' . esc_attr($min_price) . '" ';
                    echo 'data-max="' . esc_attr($max_price) . '" data-grid="false" />';
                    echo '<input type="hidden" id="price-range-min" value="' . esc_attr($min_price) . '" class="from" />';
                    echo '<input type="hidden" id="price-range-max" value="' . esc_attr($max_price) . '" class="to" />';
                    echo '<span id="price-range-value" style="display:none;">' . esc_html($min_price) . '€ - ' . esc_html($max_price) . '€</span>';
                    echo '</div>';
                } else {
                    echo '<select class="property-filter-select" name="' . esc_attr(strtolower(str_replace(' ', '_', $attribute))) . '" data-meta-key="' . esc_attr($data['meta_key']) . '">';
                    echo '<option value="">Seleccionar ' . esc_html($property_filter_heading_array[$attribute] ?: $attribute) . '</option>';
                    foreach ($data['values'] as $value) {
                        $display_text = "";
                        
                        if(esc_html($value) == "apartment"){
                            $display_text = "Departamento";
                        } else if(esc_html($value) == "house" || esc_html($value) == "House"){
                            $display_text = "Casa";
                        } else if(esc_html($value) == "villa" || esc_html($value) == "Villa"){
                            $display_text = "villa";
                        } else if(esc_html($value) == "condo" || esc_html($value) == "Condo"){
                            $display_text = "condominio";
                        } else if(esc_html($value) == "4 or more"){
                            $display_text = "4 o más";
                        } else if(esc_html($value) == "new" || esc_html($value) == "New"){
                            $display_text = "Nueva";
                        } else if(esc_html($value) == "renovated" || esc_html($value) == "Renovated"){
                            $display_text = "renovada";
                        } else if(esc_html($value) == "sell" || esc_html($value) == "Sell"){
                            $display_text = "vender";
                        } else if(esc_html($value) == "rent" || esc_html($value) == "Rent"){
                            $display_text = "alquilar";
                        } else if(esc_html($value) == "exterior" || esc_html($value) == "Exterior"){
                            $display_text = "exterior";
                        } else if(esc_html($value) == "interior" || esc_html($value) == "Interior"){
                            $display_text = "interior";
                        }  else if(esc_html($value) == "yes" || esc_html($value) == "Yes"){
                            $display_text = "Sí";
                        }  else if(esc_html($value) == "sale" || esc_html($value) == "Sale"){
                            $display_text = 'Venta';
                        } else if(esc_html($value) == "old" || esc_html($value) == "Old"){
                            $display_text = 'Vieja';
                        } else {
                            $display_text = esc_html($value);
                        }
                        echo '<option value="' . esc_attr($value) . '">' . esc_html($display_text) . '</option>';
                    }
                    echo '</select>';
                }

                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Property Listings -->
        <div class="kausa-properties-listing-outer">
            <div class="kausa-properties-listing">
                <?php
                $kausa_args = [
                    'post_type'      => 'kausa_properties',
                    'posts_per_page' => -1,
                ];

                $kausa_properties = new WP_Query($kausa_args);

                if ($kausa_properties->have_posts()) {
                    while ($kausa_properties->have_posts()) {
                        $kausa_properties->the_post();
                        $price = get_post_meta(get_the_ID(), 'kausa_property_price', true);
                        $short_description = get_post_meta(get_the_ID(), 'kausa_property_short_description', true);
                        $soldstatus = get_post_meta(get_the_ID(), '_kausa_property_sold', true);
                        if($soldstatus != 'yes'){
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
                        <?php }
                    }
                } ?>
            </div>
        </div>
        <div class="property-filter">
            <label for="state_filter"><?php echo isset($property_details_translation['kausa-state-name']) ? $property_details_translation['kausa-state-name'] : 'Provincia'; ?></label>
            <select name="state" id="state_filter">
                <option value="">Seleccionar Provincia</option>
                <?php
                $provincias = [
                    'alava' => 'Álava',
                    'albacete' => 'Albacete',
                    'alicante' => 'Alicante',
                    'almeria' => 'Almería',
                    // Añade el resto de provincias aquí...
                    'zaragoza' => 'Zaragoza'
                ];
                $state_seleccionada = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
                foreach ($provincias as $key => $provincia) {
                    echo '<option value="' . esc_attr($key) . '" ' . selected($state_seleccionada, $key, false) . '>' . esc_html($provincia) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
</div>

<?php
get_footer();