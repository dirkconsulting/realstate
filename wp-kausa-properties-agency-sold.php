<section class="agency-section-box sold-section">
      <?php 
      $current_user = wp_get_current_user();
      $user_id = $current_user->ID;

      if (in_array('agency', $current_user->roles)) {
         
         $sold_properties = new WP_Query(array(
               'post_type' => 'kausa_properties',
               'meta_query' => array(
                  array(
                     'key' => '_kausa_property_sold_by_user',
                     'value' => $user_id,
                     'compare' => '='
                  )
               ),
               'posts_per_page' => -1
         ));
         
            echo '<div class="sold-properties-list">';
               echo '<h2 class="sold-properties-main-heading">Tus propiedades vendidas</h2>';
               if ($sold_properties->have_posts()) {
                  
                  echo '<table id="sold-properties-table" class="sold-properties-table display responsive nowrap">';
                     echo '<thead>';
                        echo '<tr>';
                           echo '<th>Imagen</th>';
                           echo '<th>Nombre de la propiedad</th>';
                           echo '<th>Vendido en</th>';
                           echo '<th>Tiempo invertido</th>';
                           echo '<th>Fin de penalización</th>';
                           echo '<th>Estado</th>';
                        echo '</tr>';
                     echo '</thead>';
                  echo '<tbody>';

                  while ($sold_properties->have_posts()) {
                     $sold_properties->the_post();
            
                     $property_id = get_the_ID();
                     $property_title = get_the_title();
                     $property_permalink = get_permalink();
                     $property_image_url = get_the_post_thumbnail_url($property_id, 'full');
                     $sold_time = get_post_meta($property_id, '_kausa_property_sold_time', true);
                     $no_penalty_end_time = get_post_meta($property_id, '_kausa_property_sold_no_penalty_time', true);
                     $sold_penalty_time = get_post_meta($property_id, '_kausa_property_sold_penalty_time', true);
                     $is_sold = get_post_meta($property_id, '_kausa_property_sold', true);
            
                     $current_time = current_time('timestamp');
                     $countdown_seconds = strtotime($no_penalty_end_time) - $current_time;
                     $time_spent_seconds = $current_time - strtotime($sold_time);
                  
                     $time_spent_hours = floor($time_spent_seconds / 3600);
                     $time_spent_minutes = floor(($time_spent_seconds % 3600) / 60);
                     $time_spent_seconds = $time_spent_seconds % 60;

                     global $wpdb;
                     $table_name = $wpdb->prefix . 'kausa_properties_meta';

                     $row = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM $table_name WHERE property_id = %d AND property_status = %s",
                            $property_id,
                            'sold'
                        )
                    );

                    $sold_penalty_time = $row->property_sold_time;
                    $penalty_end_time = $row->property_panelty_time;
                    

                     
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
                           echo esc_html(date('l, d M, Y H:i:s A', strtotime($sold_time)));
                        echo '</td>';
                        echo '<td class="sold-property-time-spent">';
                           echo $row->property_spent_time;
                        echo '</td>';
                        
                        if ($penalty_end_time) {
                           echo '<td class="penalty-end-time">';
                              echo $penalty_end_time;
                           echo '</td>';
                        }

                        echo '<td class="property-actions">';
                           echo '<button class="sold-out" data-property-id="' . $property_id . '">Agotado</button>';
                        echo '</td>';
                     echo '</tr>';
                  }
            
                  echo '</tbody>';
                  echo '</table>';
                  
                  wp_reset_postdata();
               } else {
                  echo '<p>You have not sold any properties.</p>';
               }
            echo '</div>';
      } else {
         echo '<p>You do not have permission to view sold properties.</p>';
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
