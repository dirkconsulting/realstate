<section class="agency-section-box sold-section">
    <?php 
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    if (in_array('agency', $current_user->roles)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';

        // Consulta para propiedades confirmadas asociadas al usuario
        $sold_properties = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT p.ID, p.post_title, pm.* 
                 FROM {$wpdb->posts} p 
                 JOIN $table_name pm ON p.ID = pm.property_id 
                 WHERE pm.property_status = 'confirmed' 
                 AND (pm.user_id = %d OR pm.property_sold_by_user = %d)",
                $user_id,
                $user_id
            )
        );

        echo '<div class="sold-properties-list">';
        echo '<h2 class="sold-properties-main-heading">Tus propiedades vendidas</h2>';
        if (!empty($sold_properties)) {
            echo '<table id="sold-properties-table" class="sold-properties-table display responsive nowrap">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Imagen</th>';
            echo '<th>Nombre de la propiedad</th>';
            echo '<th>Vendido en</th>';
            echo '<th>Tiempo invertido</th>';
            echo '<th>Fin de penalización</th>';
            echo '<th>Estado de venta</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($sold_properties as $property) {
                $property_id = $property->ID;
                $property_title = $property->post_title;
                $property_permalink = get_permalink($property_id);
                $property_image_url = get_the_post_thumbnail_url($property_id, 'full');
                $sold_time = $property->property_sold_time ?: get_post_meta($property_id, '_kausa_property_sold_time', true);
                $penalty_end_time = $property->property_panelty_time ?: get_post_meta($property_id, '_kausa_property_reserved_penalty_time', true);
                $time_spent = $property->property_spent_time ?: get_post_meta($property_id, '_kausa_property_spent_time', true);
                $sale_status = $property->property_status ?: get_post_meta($property_id, '_kausa_property_sale_status', true);

                if (!$time_spent && $sold_time) {
                    $current_time = current_time('timestamp');
                    $time_spent_seconds = $current_time - strtotime($sold_time);
                    $time_spent_hours = floor($time_spent_seconds / 3600);
                    $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
                    $time_spent_seconds = $time_spent_seconds % 60;
                    $time_spent = sprintf('%02dh %02dm %02ds', $time_spent_hours, $time_spent_minutes, $time_spent_seconds);
                }

                echo '<tr class="sold-property-item">';
                echo '<td class="property-image-cell">';
                if ($property_image_url) {
                    echo '<div class="property-image-background" style="background-image: url(' . esc_url($property_image_url) . ');"></div>';
                } else {
                    echo '<div class="property-image-background">No Image</div>';
                }
                echo '</td>';
                echo '<td class="property-title-cell">';
                echo '<a href="' . esc_url($property_permalink) . '">' . esc_html($property_title) . '</a>';
                echo '</td>';
                echo '<td class="property-sold-time">';
                echo $sold_time ? esc_html(date('l, d M, Y H:i:s A', strtotime($sold_time))) : '-';
                echo '</td>';
                echo '<td class="sold-property-time-spent">';
                echo $time_spent ? $time_spent : '-';
                echo '</td>';
                echo '<td class="penalty-end-time">';
                echo $penalty_end_time ? esc_html($penalty_end_time) : '-';
                echo '</td>';
                echo '<td class="property-sale-status">';
                echo ($sale_status == 'confirmed') ? 'Venta confirmada' : 'Sin estado';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No has vendido ninguna propiedad.</p>';
        }
        echo '</div>';
    } else {
        echo '<p>No tienes permiso para ver propiedades vendidas.</p>';
    }
    ?>
</section>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#sold-properties-table').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "language": {
                "emptyTable": "No hay datos disponibles en la tabla",
                "lengthMenu": "Espectáculo _MENU_ entradas",
                "info": "Demostración _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Demostración 0 a 0 de 0 entradas",
                "search": "Buscar:",
                "paginate": {
                    "first": "primera",
                    "last": "última",
                    "next": "próxima",
                    "previous": "Previa"
                },
            }
        });
    });
</script>