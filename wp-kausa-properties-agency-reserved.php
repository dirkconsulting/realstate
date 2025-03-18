<section class="agency-section-box reserved-section">
      <?php 
         $current_user = wp_get_current_user();
         $user_id = $current_user->ID;

         if (in_array('agency', $current_user->roles)) {
            
            $reserved_properties = new WP_Query(array(
                  'post_type' => 'kausa_properties',
                  'meta_query' => array(
                     array(
                        'key' => '_kausa_property_reserved_by_user',
                        'value' => $user_id,
                        'compare' => '='
                     )
                  ),
                  'posts_per_page' => -1
            ));
            
               echo '<div class="reserved-properties-list">';
                  echo '<h2 class="reserved-properties-main-heading">Tus propiedades reservadas</h2>';
                  if ($reserved_properties->have_posts()) {
                     
                     echo '<table id="reserved-properties-table" class="reserved-properties-table display responsive nowrap">';
                        echo '<thead>';
                           echo '<tr>';
                              echo '<th>Imagen</th>';
                              echo '<th>Nombre de la propiedad</th>';
                              echo '<th>Reservado el</th>';
                              echo '<th>Tiempo invertido</th>';
                              echo '<th>Tiempo de penalización</th>';
                              echo '<th>Fin de la penalización por defecto</th>';
                              echo '<th>Estado</th>';
                              echo '<th>Procesar la venta</th>';
                           echo '</tr>';
                        echo '</thead>';
                     echo '<tbody>';

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
               
                        $current_time = current_time('timestamp');
                        $countdown_seconds = strtotime($no_penalty_end_time) - $current_time;
                        $time_spent_seconds = $current_time - strtotime($reserved_time);
                     
                        $time_spent_hours = floor($time_spent_seconds / 3600);
                        $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
                        $time_spent_seconds = $time_spent_seconds % 60;

                        $no_penalty_end_timestamp = strtotime($no_penalty_end_time);
                        $time_spent_after_penalty_start = max(0, $current_time - $no_penalty_end_timestamp);

                        $penalty_hours_spent = floor($time_spent_after_penalty_start / 3600);
                        
                        echo '<tr class="reserved-property-item">';
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
                           echo '<td class="property-reserved-time">';
                              echo esc_html(date('l, d M, Y H:i:s A', strtotime($reserved_time)));
                           echo '</td>';
                           echo '<td class="property-time-spent" data-reserved-time="' . esc_attr(strtotime($reserved_time)) . '" data-current-time="' . esc_attr($current_time) . '">';
                              echo $time_spent_hours . 'h ' . $time_spent_minutes . 'm ' . $time_spent_seconds . 's';
                           echo '</td>';
                           
                           if ($countdown_seconds > 0) {
                              $hours = floor($countdown_seconds / 3600);
                              $minutes = floor(($countdown_seconds % 3600) / 60);
                              $seconds = $countdown_seconds % 60;
                              echo '<td class="no-penalty-end-time" data-no-penalty-end="' . esc_attr($no_penalty_end_timestamp) . '" data-current-time="' . esc_attr($current_time) . '">';
                                 echo $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
                              echo '</td>';
                           } else {
                              echo '<td class="penalty-hours-spent" data-no-penalty-end="' . esc_attr($no_penalty_end_timestamp) . '" data-current-time="' . esc_attr($current_time) . '">';
                                 echo $penalty_hours_spent . ' hours';
                              echo '</td>';
                           }

                           if ($reserved_penalty_time) {
                              echo '<td class="penalty-end-time">';
                                 echo esc_html(date('l, d M, Y H:i:s A', strtotime($reserved_penalty_time)));
                              echo '</td>';
                           }
                           
                           echo '<td class="property-actions">';
                              echo '<button class="unreserve-property" data-property-id="' . $property_id . '">Anular Reserva</button>';
                              echo '<button class="sold-property" data-property-id="' . esc_attr($property_id) . '" data-user-id="' . esc_attr($user_id) . '">Efectuar venta</button>';
                           echo '</td>';
                           echo '<td class="property-actions">';
                              echo '<button class="upload-document" data-property-id="' . esc_attr($property_id) . '" data-user-id="' . esc_attr($user_id) . '">Subir Documentación</button>';
                           echo '</td>';
                        echo '</tr>';
                     }
               
                     echo '</tbody>';
                     echo '</table>';
                     
                     wp_reset_postdata();
                  } else {
                     echo '<p>No has reservado ninguna propiedad</p>';
                  }
               echo '</div>';
         } else {
            echo '<p>No tienes permiso para ver propiedades reservadas.</p>';
         }
      ?>
</section>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#reserved-properties-table').DataTable({
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
