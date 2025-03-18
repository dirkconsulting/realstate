<section class="agency-section-box reserved-section">
    <?php 
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    if (in_array('agency', $current_user->roles)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kausa_properties_meta';

        // Obtener las propiedades asociadas al usuario, seleccionando solo la entrada más reciente por property_id
        $properties = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT t.* 
                FROM $table_name t
                INNER JOIN (
                    SELECT property_id, MAX(property_reserve_time) as max_reserve_time
                    FROM $table_name
                    WHERE user_id = %d 
                    AND property_status IN ('reserved', 'pending', 'confirmed', 'denied')
                    GROUP BY property_id
                ) latest 
                ON t.property_id = latest.property_id 
                AND t.property_reserve_time = latest.max_reserve_time
                WHERE t.user_id = %d 
                AND t.property_status IN ('reserved', 'pending', 'confirmed', 'denied')",
                $user_id,
                $user_id
            )
        );

        echo '<div class="reserved-properties-list">';
        echo '<h2 class="reserved-properties-main-heading">Tus propiedades reservadas</h2>';

        if ($properties) {
            echo '<table id="reserved-properties-table" class="reserved-properties-table display responsive nowrap">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Imagen</th>';
            echo '<th>Nombre de la propiedad</th>';
            echo '<th>Reservado el</th>';
            echo '<th>Tiempo invertido</th>';
            echo '<th>Tiempo de penalización</th>';
            echo '<th>Fin de la penalización por defecto</th>';
            echo '<th>Estado de venta</th>';
            echo '<th>Procesar la venta</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($properties as $row) {
                $property_id = $row->property_id;
                $property_status = $row->property_status;
                $property = get_post($property_id);

                if (!$property) continue; // Saltar si la propiedad no existe

                $property_title = get_the_title($property_id);
                $property_permalink = get_permalink($property_id);
                $property_image_url = get_the_post_thumbnail_url($property_id, 'full');
                $reserved_time = $row->property_reserve_time;
                $no_penalty_end_time = get_post_meta($property_id, '_kausa_property_reserved_no_penalty_time', true);
                $reserved_penalty_time = get_post_meta($property_id, '_kausa_property_reserved_penalty_time', true);

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
                } else {
                    echo '<td>-</td>';
                }

                // Mostrar estado de venta
                echo '<td class="property-sale-status">';
                if ($property_status === 'pending') {
                    echo 'Venta pendiente de aprobación';
                } elseif ($property_status === 'confirmed') {
                    echo 'Venta confirmada';
                } elseif ($property_status === 'denied') {
                    echo 'Venta denegada';
                } elseif ($property_status === 'reserved') {
                    echo 'Sin solicitud';
                } else {
                    echo 'Sin estado';
                }
                echo '</td>';

                // Mostrar acciones
                echo '<td class="property-actions">';
                if ($property_status === 'reserved') {
                    // Solo permitir "Efectuar venta" si está reservada y no hay solicitud en curso
                    echo '<button class="sold-property" data-property-id="' . esc_attr($property_id) . '" data-user-id="' . esc_attr($user_id) . '">Efectuar venta</button>';
                } else {
                    echo '<button class="sold-property" disabled>Solicitud enviada</button>';
                }

                if (in_array($property_status, ['reserved', 'denied'])) {
                    // Permitir anular reserva si está reservada o la venta fue denegada
                    echo '<button class="unreserve-property" data-property-id="' . $property_id . '">Anular Reserva</button>';
                } else {
                    echo '<button class="unreserve-property" disabled>Anular Reserva</button>';
                }
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
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

        // Asegurarnos de que ajaxurl esté definido
        if (typeof ajaxurl === 'undefined') {
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        }

        // Actualizar tiempos dinámicamente (si es necesario)
        function updateTimes() {
            $('.property-time-spent').each(function() {
                var $this = $(this);
                var reservedTime = parseInt($this.data('reserved-time'));
                var currentTime = parseInt($this.data('current-time'));
                currentTime += 1; // Incrementar el tiempo actual cada segundo
                $this.data('current-time', currentTime);

                var timeSpentSeconds = currentTime - reservedTime;
                var hours = Math.floor(timeSpentSeconds / 3600);
                var minutes = Math.floor((timeSpentSeconds % 3600) / 60);
                var seconds = timeSpentSeconds % 60;
                $this.text(hours + 'h ' + minutes + 'm ' + seconds + 's');
            });

            $('.no-penalty-end-time').each(function() {
                var $this = $(this);
                var noPenaltyEnd = parseInt($this.data('no-penalty-end'));
                var currentTime = parseInt($this.data('current-time'));
                currentTime += 1;
                $this.data('current-time', currentTime);

                var countdownSeconds = noPenaltyEnd - currentTime;
                if (countdownSeconds > 0) {
                    var hours = Math.floor(countdownSeconds / 3600);
                    var minutes = Math.floor((countdownSeconds % 3600) / 60);
                    var seconds = countdownSeconds % 60;
                    $this.text(hours + 'h ' + minutes + 'm ' + seconds + 's');
                } else {
                    location.reload(); // Recargar para actualizar el estado de penalización
                }
            });

            $('.penalty-hours-spent').each(function() {
                var $this = $(this);
                var noPenaltyEnd = parseInt($this.data('no-penalty-end'));
                var currentTime = parseInt($this.data('current-time'));
                currentTime += 1;
                $this.data('current-time', currentTime);

                var timeSpentAfterPenalty = Math.max(0, currentTime - noPenaltyEnd);
                var hours = Math.floor(timeSpentAfterPenalty / 3600);
                $this.text(hours + ' hours');
            });
        }

        setInterval(updateTimes, 1000);
    });
</script>