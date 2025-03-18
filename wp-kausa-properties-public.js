(function( $ ) {
	'use strict';

	jQuery(document).ready(function($) {

		if ($('.gallery-slider').length) {
			$('.gallery-slider').slick({
				infinite: true,
				slidesToShow: 1,
				slidesToScroll: 1,
				dots: false,
				arrows: true
			});
		}

		$('#galleryModal').on('shown.bs.modal', function () {
			$('.gallery-slider').slick('setPosition');
		});

		$('#book-now-property').on('click', function() {
			var property_id = $(this).data('property-id');
			var user_id = $(this).data('user-id');
	
			// Make an AJAX request to reserve the property
			$.ajax({
				url: kausaPropertiesPublicAjax.ajax_url, // The admin-ajax.php URL
				type: 'POST',
				data: {
					action: 'reserve_property', // The action hook
					property_id: property_id,
					user_id: user_id
				},
				success: function(response) {
					if (response.success) {
						alert('¡Propiedad reserva con éxito!');
						location.reload(); // Reload the page to reflect the updated status
					} else {
						alert(response.message); // Show the message if the reservation failed
					}
				}
			});
		});

		$('.property-booking-details-countdown').each(function() {
			const reservedTime = parseInt($(this).attr('data-reserved-time'));
			const countdownDisplay = $(this).find('#countdown-timer');
	
			// Calculate the end time for the countdown (7 days from reserved time)
			const endTime = reservedTime + (7 * 24 * 60 * 60); // Add 7 days in seconds
			
			function updateCountdown() {
				const currentTime = Math.floor(Date.now() / 1000); // Get current time in seconds
				const timeRemaining = endTime - currentTime;
				
				if (timeRemaining <= 0) {
					countdownDisplay.html("The apartment is available!");
					clearInterval(countdownInterval);
				} else {
					const hours = Math.floor(timeRemaining / 3600);
					const minutes = Math.floor((timeRemaining % 3600) / 60);
					const seconds = timeRemaining % 60;
					countdownDisplay.html(`${hours} Hours ${minutes} Minutes ${seconds} Seconds`);
				}
			}
	
			const countdownInterval = setInterval(updateCountdown, 1000);
			updateCountdown();
		});
	
		$('.property-single-time-spent').each(function() {
			const reservedTime = parseInt($(this).attr('data-reserved-time')); // Time when reservation started
			let currentTime = parseInt($(this).attr('data-current-time')); // Initial current time
		
			const countdownDisplay = $(this).find('.countdown-spent-time-timer');
		
			function updateTimeSpent() {
				const timeSpentSeconds = currentTime - reservedTime;
				const hours = Math.floor(timeSpentSeconds / 3600);
				const minutes = Math.floor((timeSpentSeconds % 3600) / 60);
				const seconds = timeSpentSeconds % 60;
		
				countdownDisplay.text(`${hours}h ${minutes}m ${seconds}s`);
				
				// Increment the current time to simulate the passage of real time
				currentTime += 1;
			}
		
			// Update the timer every second
			setInterval(updateTimeSpent, 1000);
			updateTimeSpent(); // Initial call to display immediately
		});
		

		$('.property-booking-panelty-countdown').each(function() {
			const countdownElement = $(this).find('#countdown-panelty-timer');
			const countdownEndTime = parseInt($(this).data("countdown-end-time"));
			let currentTime = parseInt($(this).data("current-time"));
		
			console.log('End Time:', countdownEndTime);  // Timestamp for end time
			console.log('Current Time:', currentTime);   // Timestamp for current time
		
			// Function to update the countdown
			function updateCountdown() {
				let timeRemaining = countdownEndTime - currentTime;  // Time remaining in seconds
				
				// If time is left, calculate hours, minutes, and seconds
				if (timeRemaining > 0) {
					const hours = Math.floor(timeRemaining / 3600);  // Convert to hours
					const minutes = Math.floor((timeRemaining % 3600) / 60);  // Remaining minutes
					const seconds = timeRemaining % 60;  // Remaining seconds
					
					countdownElement.text(`${hours}h ${minutes}m ${seconds}s`);
				} else {
					countdownElement.text("Penalización ha expirado.");
					clearInterval(timerInterval); // Stop the countdown
				}
			}
		
			// Update the countdown every second
			const timerInterval = setInterval(function() {
				currentTime = Math.floor(Date.now() / 1000);  // Update current time every second
				updateCountdown();  // Call the update function
			}, 900);
		
			// Initialize immediately
			updateCountdown(); 
		});

		$('.property-booking-reservation-end-countdown').each(function() {
			const countdownElement = $(this).find('#countdown-reservation-end-timer');
			const countdownEndTime = parseInt($(this).data("countdown-end-time"));
			let currentTime = parseInt($(this).data("current-time"));
		
			console.log('End Time:', countdownEndTime);  // Timestamp for end time
			console.log('Current Time:', currentTime);   // Timestamp for current time
		
			// Function to update the countdown
			function updateCountdown() {
				let timeRemaining = countdownEndTime - currentTime;  // Time remaining in seconds
				
				// If time is left, calculate hours, minutes, and seconds
				if (timeRemaining > 0) {
					const hours = Math.floor(timeRemaining / 3600);  // Convert to hours
					const minutes = Math.floor((timeRemaining % 3600) / 60);  // Remaining minutes
					const seconds = timeRemaining % 60;  // Remaining seconds
					
					countdownElement.text(`${hours}h ${minutes}m ${seconds}s`);
				} else {
					countdownElement.text("Reservation time End.");
					clearInterval(timerInterval); // Stop the countdown
				}
			}
		
			// Update the countdown every second
			const timerInterval = setInterval(function() {
				currentTime = Math.floor(Date.now() / 1000);  // Update current time every second
				updateCountdown();  // Call the update function
			}, 900);
		
			// Initialize immediately
			updateCountdown(); 
		});
	});
	

})( jQuery );
