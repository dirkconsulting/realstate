jQuery(document).ready(function($) {
    console.log("ready");
    $(document).on('click', function(event) {
        console.log(event.target.className);

        if(event.target.className == 'unreserve-property'){
          var property_id = event.target.getAttribute('data-property-id');
    
          $.ajax({
              url: kausaPropertiesAgencyAjax.ajax_url,
              type: 'POST',
              data: {
                  action: 'unreserve_property',
                  property_id: property_id,
              },
              success: function(response) {
                if (response.success) {
                        console.log('Time spent: ' + response.data.time_spent);
                        console.log('Penalty time: ' + response.data.penalty_time);
                        alert('¡Anulada la reserva de la Propiedad!');
                        // location.reload();
                } else {
                        alert('Ha ocurrido un error.');
                }
              },
              error: function() {
                  alert('AJAX request failed.');
              }
          });
        }
        if(event.target.className == 'sold-property'){
            var property_id = event.target.getAttribute('data-property-id');
            var user_id = event.target.getAttribute('data-user-id');
        
            $.ajax({
                url: kausaPropertiesAgencyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sold_property',
                    property_id: property_id,
                    user_id: user_id
                },
                success: function(response) {
                    if (response.success) {
                        alert('¡Propiedad marcada como vendida!');
                        location.reload();
                    } else {
                        alert('Ha ocurrido un error.');
                    }
                },
                error: function() {
                    alert('AJAX request failed.');
                }
            });
        }
            
    });

    function updateTimers() {
        const currentTimestamp = Math.floor(Date.now() / 1000); // Current time in seconds

        // Update Time Spent
        $('.property-time-spent').each(function () {
            const reservedTime = parseInt($(this).data('reserved-time'), 10);
            const timeSpent = currentTimestamp - reservedTime;

            const hours = Math.floor(timeSpent / 3600);
            const minutes = Math.floor((timeSpent % 3600) / 60);
            const seconds = timeSpent % 60;

            $(this).text(`${hours}h ${minutes}m ${seconds}s`);
        });

        // Update No-Penalty Countdown
        $('.no-penalty-end-time').each(function () {
            const noPenaltyEnd = parseInt($(this).data('no-penalty-end'), 10);
            const countdown = noPenaltyEnd - currentTimestamp;

            if (countdown > 0) {
                const hours = Math.floor(countdown / 3600);
                const minutes = Math.floor((countdown % 3600) / 60);
                const seconds = countdown % 60;

                $(this).text(`${hours}h ${minutes}m ${seconds}s`);
            } else {
                $(this).text('Penalty started');
            }
        });

        // Update Penalty Hours
        $('.penalty-hours-spent').each(function () {
            const noPenaltyEnd = parseInt($(this).data('no-penalty-end'), 10);
            const timeAfterPenalty = Math.max(0, currentTimestamp - noPenaltyEnd);

            const penaltyHours = Math.floor(timeAfterPenalty / 3600);
            const penaltyMinutes = Math.floor((timeAfterPenalty % 3600) / 60);
            const penaltySeconds = timeAfterPenalty % 60;

            $(this).text(`${penaltyHours}h ${penaltyMinutes}m ${penaltySeconds}s`);
        });
    }

    // Update every second
    setInterval(updateTimers, 1000);

    $(document).on('click', '.upload-document', function() {
        console.log("test");
    });

});

document.addEventListener('DOMContentLoaded', function () {
    var acc = document.querySelectorAll('.accordion');        
    acc.forEach(function (el, index) {        
        if(index !== 0){            
            el.classList.remove('active');
            el.nextElementSibling.style.display = 'none';
        } else {
            el.classList.add('active');
        }    
        
        el.addEventListener('click', function () {  
            acc.forEach(function (el) {  
                el.classList.remove('active');
                el.nextElementSibling.style.display = 'none';
            });         
            this.classList.toggle('active'); 
            
            var panel = this.nextElementSibling;   
          
            if (panel.style.display === 'block') {
                panel.style.display = 'none';
            } else {
                panel.style.display = 'block';
            }
        });
    });
});

