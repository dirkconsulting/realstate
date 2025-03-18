<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wppb.me
 * @since      1.0.0
 *
 * @package    Wp_Kausa_Properties
 * @subpackage Wp_Kausa_Properties/public/partials
 */

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        global $post;
        $featured_image = get_the_post_thumbnail_url($post->ID, 'full'); 
        $gallery = get_post_meta($post->ID, '_kausa_properties_gallery', true);
        $documents = get_post_meta($post->ID, '_kausa_properties_documents', true);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';

        $property_details = [
            'Price' => get_post_meta($post->ID, 'kausa_property_price', true) . '€',
            'Habitaciones' => get_post_meta($post->ID, 'kausa_property_bedrooms', true),
            'Baños' => get_post_meta($post->ID, 'kausa_property_bathrooms', true),
            'M2 construidos' => get_post_meta($post->ID, 'kausa_property_built_area', true) .'m²',
            'Street' => get_post_meta($post->ID, 'kausa_property_street', true),
            'City' => get_post_meta($post->ID, 'kausa_property_city', true),
            'State' => get_post_meta($post->ID, 'kausa_property_state', true),
            'Zipcode' => get_post_meta($post->ID, 'kausa_property_zipcode', true),
            'Country' => get_post_meta($post->ID, 'kausa_property_country', true),
            'Short Description' => get_post_meta($post->ID, 'kausa_property_short_description', true),
            'Price Description' => get_post_meta($post->ID, 'kausa_property_price_description', true),
            'Location on Map' => get_post_meta($post->ID, 'kausa_property_location_on_map', true),
        ];
        
        $property_normal_details = [
            'Habitaciones' => get_post_meta($post->ID, 'kausa_property_bedrooms', true),
            'Baños' => get_post_meta($post->ID, 'kausa_property_bathrooms', true),
            'Tipo de Propiedad' => get_post_meta($post->ID, 'kausa_property_type', true),
            'Estado' => get_post_meta($post->ID, 'kausa_property_condition', true),
            'M2 construidos' => get_post_meta($post->ID, 'kausa_property_built_area', true) .'m²',
            'Preferencia' => get_post_meta($post->ID, 'kausa_property_preference', true),
            'Fachada' => get_post_meta($post->ID, 'kausa_property_facade', true),
            'Ascensor' => get_post_meta($post->ID, 'kausa_property_elevator', true),
            'M2 útiles' => get_post_meta($post->ID, 'kausa_property_usable_area', true) .'m²',
        ];
        
        $property_addition_details = [
            'Año de construcción' => get_post_meta($post->ID, 'kausa_property_year_of_construction', true),
            'Gastos de comunidad' => get_post_meta($post->ID, 'kausa_property_community_fees', true),
            'Additional Features' => get_post_meta($post->ID, 'kausa_property_features', true),
            'Building Features' => get_post_meta($post->ID, 'kausa_property_building_features', true),
            'Comisión' => get_post_meta($post->ID, 'kausa_property_commission', true), // Añadido el campo de comisión
        ]; 
        
        $property_details_translation = get_option("property_details_transalation", "");
        $property_details_text = "Property Details";
        $property_details_price = "Price";
        $property_details_address = "Address";
        $property_details_map_location = "Location on Map";
        $property_details_description = "Description";
        $property_details_additional_description = "Additional Details"; 
        $property_details_gallery = "Gallery";  
        $property_details_already_reserved_text = "You have already reserved this apartment.";
        $property_details_time_spent = "time spent";
        $property_details_cannot_reserve_text = "You have already reserved an other apartment. You can't reserve another one.";
        $property_details_already_reserved_byagency = "This apartment already reserve by agency.";
        $property_details_booking_available_in = "Booking Avaliable in";
        $property_details_calculating = "Calculating...";
        $property_details_lock_apartment = "You can lock this apartment exclusively for";
        $property_details_learn_more = "Learn more";
        $property_details_block = "Block";
        $property_details_block_for = "Block for";
        $property_details_no_access = "You do not have access to reserve apartments.";
        $property_details_no_document = "No documents available."; 
        $property_details_download_document = "Download Document";  
        $property_details_view_all = "View All";
        $property_details_hours = "hours";
        $property_details_days = "days";
        if(is_array($property_details_translation)){
            $property_details_text = isset($property_details_translation["kausa-property-details"]) ? $property_details_translation["kausa-property-details"] : "Property Details";
            $property_details_price = isset($property_details_translation["kausa-property-price"]) ? $property_details_translation["kausa-property-price"] : "Price";
            $property_details_address = isset($property_details_translation["kausa-property-address"]) ? $property_details_translation["kausa-property-address"] : "Address";
            $property_details_map_location = isset($property_details_translation["kausa-property-map-location"]) ? $property_details_translation["kausa-property-map-location"] : "Location on Map";
            $property_details_description = isset($property_details_translation["kausa-property-description"]) ? $property_details_translation["kausa-property-description"] : "Description";
            $property_details_additional_description = isset($property_details_translation["kausa-property-additional-description"]) ? $property_details_translation["kausa-property-additional-description"] : "Additional Details";  
            $property_details_gallery = isset($property_details_translation["kausa-property-gallery"]) ? $property_details_translation["kausa-property-gallery"] : "Gallery";
            $property_details_already_reserved_text = isset($property_details_translation["kausa-property-already-reserved-text"]) ? $property_details_translation["kausa-property-already-reserved-text"] : "You have already reserved this apartment."; 
            $property_details_time_spent = isset($property_details_translation["kausa-property-time-spent"]) ? $property_details_translation["kausa-property-time-spent"] : "time spent"; 
            $property_details_cannot_reserve_text = isset($property_details_translation["kausa-cannot-reserve"]) ? $property_details_translation["kausa-cannot-reserve"] : "You have already reserved an other apartment. You can't reserve another one."; 
            $property_details_already_reserved_byagency = isset($property_details_translation["kausa-property-reserved-by-agency"]) ? $property_details_translation["kausa-property-reserved-by-agency"] : "This apartment already reserve by agency."; 
            $property_details_booking_available_in = isset($property_details_translation["kausa-property-booking-available-in"]) ? $property_details_translation["kausa-property-booking-available-in"] : "Booking Avaliable in"; 
            $property_details_calculating = isset($property_details_translation["kausa-property-calculating"]) ? $property_details_translation["kausa-property-calculating"] : "Calculating...";
            $property_details_lock_apartment = isset($property_details_translation["kausa-property-lock-this-apartment"]) ? $property_details_translation["kausa-property-lock-this-apartment"] : "You can lock this apartment exclusively for";
            $property_details_learn_more = isset($property_details_translation["kausa-property-learn-more"]) ? $property_details_translation["kausa-property-learn-more"] : "Learn more";
            $property_details_block = isset($property_details_translation["kausa-property-block"]) ? $property_details_translation["kausa-property-block"] : "Block";
            $property_details_block_for = isset($property_details_translation["kausa-property-block-for"]) ? $property_details_translation["kausa-property-block-for"] : "Block for";
            $property_details_no_access = isset($property_details_translation["kausa-property-no-reservation-access"]) ? $property_details_translation["kausa-property-no-reservation-access"] : "You do not have access to reserve apartments.";
            $property_details_no_document = isset($property_details_translation["kausa-property-no-document"]) ? $property_details_translation["kausa-property-no-document"] : "No documents available.";  
            $property_details_download_document = isset($property_details_translation["kausa-property-download-document"]) ? $property_details_translation["kausa-property-download-document"] : "Download Document";  
            $property_details_view_all = isset($property_details_translation["kausa-property-view-all"]) ? $property_details_translation["kausa-property-view-all"] : "View All";
            $property_details_hours = isset($property_details_translation["kausa-property-hours"]) ? $property_details_translation["kausa-property-hours"] : "hours";
            $property_details_days = isset($property_details_translation["kausa-property-days"]) ? $property_details_translation["kausa-property-days"] : "days";
        }
        
        ?>
        
        <div class="kausa-single-property-container">
            <!-- Featured Image and Gallery -->
            <div class="property-image-gallery-wrapper">
                <div class="property-image-gallery">
                    <div class="property-featured-image">
                        <?php if ($featured_image): ?>
                            <img src="<?php echo esc_url($featured_image); ?>" alt="Featured Image">
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($gallery) && is_array($gallery)): 
                            $galleryCounter = 1; ?>
                        <div class="property-gallery-images">
                            <div class="property-gallery">
                                <?php foreach (array_slice($gallery, 0, 2) as $image_id): ?>
                                    <div class="property-gallery-image gallery-image-<?php echo $galleryCounter; ?>">
                                        <img src="<?php echo esc_url(wp_get_attachment_url($image_id)); ?>" alt="Gallery Image">
                                        <?php if (count($gallery) > 2 && $galleryCounter == 2): ?>
                                            <div class="view-all">
                                                <button class="view-all-btn" data-bs-toggle="modal" data-bs-target="#galleryModal"><?php echo esc_attr($property_details_view_all); ?></button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (count($gallery) < 2){ ?>
                                        <div class="property-gallery-image gallery-image-2 limited-gallery">
                                            <div class="view-all">
                                                <button class="view-all-btn" data-bs-toggle="modal" data-bs-target="#galleryModal"><?php echo esc_attr($property_details_view_all); ?></button>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php $galleryCounter++; ?>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($gallery) > 0): ?>
                                <!-- Modal Popup -->
                                <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="galleryModalLabel"><?php echo esc_attr($property_details_gallery); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="gallery-slider">
                                                    <?php if ($featured_image): ?>
                                                        <div>
                                                            <img src="<?php echo esc_url($featured_image); ?>" alt="Featured Image">
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php foreach ($gallery as $image_id): ?>
                                                        <div>
                                                            <img src="<?php echo esc_url(wp_get_attachment_url($image_id)); ?>" alt="Gallery Image">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="property-content-wrapper">
                <!-- Left Column -->
                <div class="property-left-column">
                    <!-- Post Title -->
                    <h1 class="property-title"><?php the_title(); ?></h1>
                    <!-- Property Details in Two Columns -->
                    <div class="property-details">
                        <!-- Column 1: Property Features -->
                        <div class="property-features">
                            <div class="property-features-heading">
                                <h3><?php echo esc_attr($property_details_text); ?></h3>
                            </div>
                            <div class="property-features-content-wrapper">
                                <div class="property-features-content">
                                    <?php if($property_details['Short Description']){ ?><p><?php echo esc_html($property_details['Short Description']); ?></p><?php } ?>
                                </div>
                                <div class="property-features-list">
                                    <?php if($property_details['Habitaciones']){ ?><p><strong>Habitaciones</strong> <?php echo esc_html($property_details['Habitaciones']); ?></p><?php } ?>
                                    <?php if($property_details['Baños']){ ?><p><strong>Baños</strong> <?php echo esc_html($property_details['Baños']); ?></p><?php } ?>
                                    <?php if($property_details['M2 construidos']){ ?><p><strong>M2 construidos</strong> <?php echo esc_html($property_details['M2 construidos']); ?></p><?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php if($property_details['Price']){ ?>
                            <!-- Column 2: Price -->
                            <div class="property-price">
                                <div class="property-price-heading">
                                    <h3><?php echo esc_attr($property_details_price); ?></h3>
                                </div>
                                <div class="property-price-content-wrapper">
                                    <div class="property-price-list">
                                        <?php
$precio = $property_details['Price'];
// Limpiar la variable $precio
$precio = preg_replace("/[^0-9]/", "", $precio);  // Eliminar cualquier caracter que no sea un número
// Asegurarse de que $precio sea un número antes de formatear
if (is_numeric($precio)) {
  $precio_formateado = number_format( $precio, 0, ',', '.' ) . '€'; // Añadir ' €' al final
} else {
  $precio_formateado = 'Precio no disponible'; // O un mensaje de error adecuado
}
?>
<p><strong><?php echo esc_html( $precio_formateado ); ?></strong></p>
                                    </div>
                                    <div class="property-price-content">
                                        <?php if($property_details['Price Description']){ ?><p><?php echo esc_html($property_details['Price Description']); ?></p><?php } ?>    
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <!-- Address -->
                    <?php if (!empty($property_details['Country']) && !empty($property_details['State']) && !empty($property_details['City'])): ?>
                        <div class="property-address">
                            <div class="property-address-heading">
                                <h3><?php echo esc_attr($property_details_address); ?></h3>
                            </div>
                            <div class="property-address-content-wrapper">
                                <div class="property-address-list">
                                    <p>
                                        <span><?php if(!empty($property_details['Street'])){ echo $property_details['Street'].', '; }?></span>
                                        <span><?php if(!empty($property_details['City'])){ echo $property_details['City'].', '; }?></span>
                                        <span><?php if(!empty($property_details['State'])){ echo $property_details['State'].', '; }?></span>
                                        <span><?php if(!empty($property_details['Zipcode'])){ echo $property_details['Zipcode'].', '; }?></span>
                                        <span><?php if(!empty($property_details['Country'])){ echo $property_details['Country']; }?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- Location on Map -->
                        <?php if (!empty($property_details['Location on Map'])): ?>
                        <?php $google_api_key = get_option('kausa-google-map-api-key', ""); ?>
                        <div class="property-map">
                            <div class="property-map-heading">
                                <h3><?php echo esc_attr($property_details_map_location); ?></h3>
                            </div>
                            <div class="property-map-content-wrapper">
                                <div class="property-map-content">
                                    <div class="mapswrapper"><iframe width="600" height="450" loading="lazy" allowfullscreen src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr($google_api_key); ?>&q=<?php echo $property_details['Location on Map']; ?>&zoom=10&maptype=roadmap"></iframe><a href="https://www.zrivo.com/new-jersey-paycheck-calculator">New Jersey Paycheck Calculator</a><style>.mapswrapper{background:#fff;position:relative}.mapswrapper iframe{border:0;position:relative;z-index:2}.mapswrapper a{color:rgba(0,0,0,0);position:absolute;left:0;top:0;z-index:0}</style></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Post Description -->
                    <div class="property-description">
                        <div class="property-description-heading">
                            <h3><?php echo esc_attr($property_details_description); ?></h3>
                        </div>
                        <div class="property-description-content-wrapper">
                            <div class="property-description-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Additional Property Details -->
                    <div class="property-additional-details">
                        <div class="property-additional-details-heading">
                            <h3><?php echo esc_attr($property_details_additional_description); ?></h3>
                        </div>
                        <div class="property-additional-details-content-wrapper">
                            <div class="property-additional-details-list simple-details-list-box">
                                <ul>
                                    <?php foreach ($property_normal_details as $label => $value): ?>
                                        <?php if (!empty($value)): ?>
                                            <li>
                                                <div class="property-detail-item">
                                                    <div class="property-detail-label">
                                                        <strong><?php echo esc_html($label); ?></strong>
                                                    </div>
                                                    <div class="property-detail-item-content">
                                                    <?php 
                                                    if (is_array($value)) {
                                                        foreach ($value as $item) {
                                                           echo '<div class="proprty-single-item-box">' . esc_html($item) . '</div>';
                                                        }
                                                    } else {                                                        
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
                                                        echo '<div class="proprty-single-item-box">' . esc_html($display_text) . '</div>';
                                                    } ?>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="property-additional-details-list extra-details-list-box">
                                <ul>
                                    <?php foreach ($property_addition_details as $label => $value): ?>
                                        <?php if (!empty($value)): ?>
                                            <li>
                                                <?php 
                                                if (is_array($value)) {
                                                    if ($label == 'Building Features') {
                                                        echo '<div class="property-detail-group-content">';
                                                            $building_features = [
                                                                'Swimming Pool' => 'Piscina',
                                                                'Green Area' => 'Área Verde'
                                                            ];
                                                            $feature_details = [];
                                                            foreach ($building_features as $key => $feature) {
                                                                $building_features = get_post_meta($post->ID, 'kausa_property_building_features', true);
                                                                $feature_value = $building_features[$key];
                                                                if ($feature_value == 'yes') {
                                                                    echo '<div class="property-detail-item"><div class="property-detail-label"><strong>' . esc_html($feature) . '</strong></div><div class="property-detail-item-content"><div class="proprty-single-item-box">Sí</div></div></div>';
                                                                }
                                                            }
                                                        echo '</div>';
                                                    } elseif ($label == 'Additional Features') {
                                                        echo '<div class="property-detail-group-content">';
                                                            $additional_features = [
                                                                'Built-in Wardrobes' => 'Armarios empotrados',
                                                                'Air Conditioning' => 'Aire acondicionado',
                                                                'Terrace' => 'Terraza',
                                                                'Balcony' => 'Balcón',
                                                                'Storage Room' => 'Trastero',
                                                                'Parking Space' => 'Plaza de aparcamiento'
                                                            ];

                                                            $additional_feature_details = [];
                                                            foreach ($additional_features as $key => $feature) {
                                                                $additional_property_features = get_post_meta($post->ID, 'kausa_property_features', true);
                                                                $feature_value = $additional_property_features[$key];
                                                                if ($feature_value == 'yes') {
                                                                    echo '<div class="property-detail-item"><div class="property-detail-label"><strong>' . esc_html($feature) . '</strong></div><div class="property-detail-item-content"><div class="proprty-single-item-box">Sí</div></div></div>';
                                                                }
                                                            }
                                                        echo '</div>';
                                                    } else {
                                                        echo '<div class="property-detail-item">';
                                                            echo '<div class="property-detail-label">';
                                                                echo '<strong>'.$label.'</strong>';
                                                            echo '</div>';
                                                            echo '<div class="property-detail-item-content">';
                                                            foreach ($value as $item) {
                                                                echo '<div class="proprty-single-item-box">' . esc_html($item) . '</div>';
                                                            }
                                                            echo '</div>';
                                                        echo '</div>';
                                                    }
                                                } else {
                                                    echo '<div class="property-detail-item">';
                                                        echo '<div class="property-detail-label">';
                                                            echo '<strong>'.$label.'</strong>';
                                                        echo '</div>';
                                                        echo '<div class="property-detail-item-content">';
                                                            // Especialmente para la comisión, añadimos el formato con porcentaje
                                                            if ($label == 'Comisión') {
                                                                echo '<div class="proprty-single-item-box">' . esc_html(number_format($value, 2, ',', '.')) . '%</div>';
                                                            } else {
                                                                echo '<div class="proprty-single-item-box">' . esc_html($value) . '</div>';
                                                            }
                                                        echo '</div>';
                                                    echo '</div>';
                                                } ?>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="property-right-column">
                    <div class="property-booking-details-wrapper">
                        <div class="property-booking-details-heading"></div>
                        <?php if (is_user_logged_in()) { 
                                $current_user = wp_get_current_user();
                                $user_id = $current_user->ID;
                                
                                $properties_reserved = new WP_Query(array(
                                    'post_type' => 'kausa_properties',
                                    'meta_query' => array(
                                        array(
                                            'key' => '_kausa_property_reserved_by_user',
                                            'value' => $user_id,
                                            'compare' => '='
                                        )
                                    ),
                                    'posts_per_page' => 1,
                                    'fields' => 'ids'
                                ));
                                
                                if (in_array('agency', $current_user->roles) || in_array('administrator', $current_user->roles)) {
                                    $property_reserved = get_post_meta($post->ID, '_kausa_property_reserved', true);
                                    $reserved_by_user = get_post_meta($post->ID, '_kausa_property_reserved_by_user', true);

                                    if ($properties_reserved->have_posts()) {

                                        if ($reserved_by_user == $user_id) {
                                            $reserved_time = get_post_meta($post->ID, '_kausa_property_reserved_time', true);
                                            $reserved_time = strtotime($reserved_time);
                                            if ($reserved_time === false) { return; }
                                            $current_time = current_time('timestamp');
                                            $time_spent_seconds = $current_time - $reserved_time;
                                            $time_spent_hours = floor($time_spent_seconds / 3600);
                                            $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
                                            $time_spent_seconds = $time_spent_seconds % 60; ?>
                                            <div class="property-booking-details-content">
                                                <p><?php echo esc_attr($property_details_already_reserved_text); ?></p>
                                            </div>
                                            <div class="property-single-time-spent" data-reserved-time="<?php echo esc_attr($reserved_time); ?>" data-current-time="<?php echo esc_attr($current_time); ?>">
                                                <p><?php echo esc_attr($property_details_time_spent); ?>: <span class="countdown-spent-time-timer"><?php echo $time_spent_hours . 'h ' . $time_spent_minutes . 'm ' . $time_spent_seconds . 's'; ?></span></p>
                                            </div>
                                        <?php } else {?>
                                            <div class="property-booking-details-content">
                                                <p><?php echo esc_attr($property_details_cannot_reserve_text); ?></p>
                                            </div>
                                        <?php }
                                    } else {
                                        if (in_array('agency', $current_user->roles)) {
                                            if ($property_reserved == 'yes' && $reserved_by_user != $user_id) {
                                                $current_time = strtotime(current_time('mysql'));
                                                $reserved_time = get_post_meta($post->ID, '_kausa_property_reserved_time', true);
                                                $reservation_end_time_period = get_post_meta($post->ID, '_kausa_property_reserved_penalty_time', true);
                                                $reservation_end_time = strtotime($reservation_end_time_period);

                                                if ($current_time < $reservation_end_time) {
                                                    ?>
                                                    <div class="property-booking-details-content">
                                                        <p><?php echo esc_attr($property_details_already_reserved_byagency); ?></p>
                                                    </div>
                                                    <div class="property-booking-reservation-end-countdown" 
                                                        data-countdown-end-time="<?php echo esc_attr($reservation_end_time); ?>" data-current-time="<?php echo esc_attr($current_time); ?>">
                                                        <p><?php echo esc_attr($property_details_booking_available_in); ?> <span id="countdown-reservation-end-timer"><?php echo esc_attr($property_details_calculating); ?></span></p>
                                                    </div>
                                                <?php } ?>

                                                <?php
                                            } elseif ($reserved_by_user == $user_id) {
                                                $reserved_time = get_post_meta($post->ID, '_kausa_property_reserved_time', true);
                                                $reserved_time = strtotime($reserved_time);
                                                if ($reserved_time === false) { return; }
                                                $current_time = current_time('timestamp');
                                                $time_spent_seconds = $current_time - $reserved_time;
                                                $time_spent_hours = floor($time_spent_seconds / 3600);
                                                $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
                                                $time_spent_seconds = $time_spent_seconds % 60; ?>
                                                    <div class="property-booking-details-content">
                                                        <p><?php echo esc_attr($property_details_already_reserved_text); ?></p>
                                                    </div>
                                                    <div class="property-single-time-spent" data-reserved-time="<?php echo esc_attr($reserved_time); ?>" data-current-time="<?php echo esc_attr($current_time); ?>">
                                                        <p><?php echo esc_attr($property_details_time_spent); ?>: <span class="countdown-spent-time-timer"><?php echo $time_spent_hours . 'h ' . $time_spent_minutes . 'm ' . $time_spent_seconds . 's'; ?></span></p>
                                                    </div>
                                                <?php
                                            } else {

                                                $current_user_id = get_current_user_id();
                                                $property_id = $post->ID;
                                                $current_time = strtotime(current_time('mysql'));
                                                
                                                $query = $wpdb->prepare(
                                                    "SELECT * FROM $table_name WHERE user_id = %d AND property_id = %d",
                                                    $current_user_id,
                                                    $property_id
                                                );
                                                $row = $wpdb->get_row($query);
                                                $no_penalty_period = get_option("kausa-penalty-free-reservation-time", "48 hours");               
                                                $check_selected_days = explode(" ", $no_penalty_period);
                                                
                                                if($check_selected_days[1] == "hours"){
                                                    $no_penalty_period = $check_selected_days[0] . " " . $property_details_hours;
                                                } else if($check_selected_days[1] == "days") {
                                                    $no_penalty_period = $check_selected_days[0] . " " . $property_details_days;
                                                } 
                                                
                                                if ($row) {
                                                    $penalty_end_time = strtotime($row->property_panelty_time);

                                                    if ($current_time < $penalty_end_time) {
                                                        ?>
                                                        <div class="property-booking-panelty-countdown" 
                                                            data-countdown-end-time="<?php echo esc_attr($penalty_end_time); ?>" data-current-time="<?php echo esc_attr($current_time); ?>">
                                                            <p><?php echo esc_attr($property_details_booking_available_in); ?> <span id="countdown-panelty-timer"><?php echo esc_attr($property_details_calculating); ?></span></p>
                                                        </div>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <div class="property-booking-details-content">
                                                            <p><?php echo esc_attr($property_details_lock_apartment); ?> <?php echo esc_attr($no_penalty_period); ?>. <a href="#"><?php echo esc_attr($property_details_learn_more); ?></a></p>
                                                        </div>
                                                        <div class="property-booking-details-buttons">
                                                            <a id="book-now-property" 
                                                            data-property-id="<?php echo esc_attr($property_id); ?>" 
                                                            data-user-id="<?php echo esc_attr($current_user_id); ?>" 
                                                            href="javascript:void(0);"><?php echo esc_attr($property_details_block_for); ?> <?php echo $no_penalty_period; ?></a>
                                                        </div>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                   <div class="property-booking-details-content">
                                                        <p><?php echo esc_attr($property_details_lock_apartment); ?> <?php echo esc_attr($no_penalty_period); ?>. <a href="#" ><?php echo esc_attr($property_details_learn_more); ?></a></p>
                                                    </div>
                                                    <div class="property-booking-details-buttons">
                                                        <a id="book-now-property" data-property-id="<?php echo $post->ID; ?>" data-user-id="<?php echo $user_id; ?>" href="javascript:void(0);"><?php echo esc_attr($property_details_block); ?> <?php echo $no_penalty_period; ?></a>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                        } else {
                                            ?>
                                            <div class="property-booking-details-content">
                                                <p><?php echo esc_attr($property_details_no_access); ?></p>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                            }else{ ?>
                                <div class="property-booking-details-content">
                                    <p><?php echo esc_attr($property_details_no_access); ?></p>
                                </div>
                        <?php } ?>
                        <div class="property-booking-details-documents">
                            <?php if (!empty($documents) && is_array($documents)): ?>
                                <ul>
                                    <?php foreach ($documents as $document_id): ?>
                                        <li><a href="<?php echo esc_url(wp_get_attachment_url($document_id)); ?>" target="_blank"><?php echo esc_attr($property_details_download_document); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php echo esc_attr($property_details_no_document); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    endwhile;
endif;
get_footer();