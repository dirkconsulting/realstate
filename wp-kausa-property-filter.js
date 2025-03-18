(function( $ ) {
	'use strict';

	jQuery(document).ready(function($) {

		const $burger = $('.filter-burger-menu');
        const $sidebar = $('.kausa-properties-filter-sidebar');
		const $archiveBox = $('.kausa-properties-archive-container-wrapper');
        
        $burger.on('click', function() {
            $(this).toggleClass('active');
            $sidebar.toggleClass('show-filter-box');
			$archiveBox.toggleClass('filter-popup-opened');
        });

		var $range = $("#price-range-slider"),
			$minInput = $("#price-range-min"),
			$maxInput = $("#price-range-max"),
			$priceDisplay = $("#price-range-value"),
			range,
			min = $range.data('min'),
			max = $range.data('max'),
			from = min,
			to = max;

		$range.ionRangeSlider({
			type: "double",
			min: min,
			max: max,
			from: from,
			to: to,
			skin: "round",
			grid: false,
			onChange: function (data) {
				from = data.from;
				to = data.to;
				updateInputs();
			}
		});

		range = $range.data("ionRangeSlider");

		function updateInputs() {
			$minInput.val(from);
			$maxInput.val(to);
			$priceDisplay.text(from + "€ - " + to + "€");
		}

		function updateSlider() {
			range.update({
				from: from,
				to: to
			});
		}

		$minInput.on("input", function () {
			from = +$(this).val();
			if (from < min) from = min;
			if (from > to) from = to;
			updateInputs();
			updateSlider();
		});

		$maxInput.on("input", function () {
			to = +$(this).val();
			if (to > max) to = max;
			if (to < from) to = from;
			updateInputs();
			updateSlider();
		});
	
		function triggerFilter() {
			let filters = {
				price: {
					min: from,
					max: to
				},
				attributes: {}
			};

			$('.property-filter-select').each(function() {
				let filter = $(this).attr('data-meta-key');
				let value = $(this).val();
				if (value) {
					filters.attributes[filter] = value;
				}
			});
		
			$.ajax({
				url: kausaPropertiesAjax.ajax_url,
				type: 'POST',
				data: {
					action: 'filter_properties',
					filters: filters
				},
				success: function(response) {
					$('.kausa-properties-archive-container .kausa-properties-listing').html(response);
					const $burgerMobile = $('.filter-burger-menu');
					const $sidebarMobile = $('.kausa-properties-filter-sidebar');
					const $archiveMainBox = $('.kausa-properties-archive-container-wrapper');
					$burgerMobile.toggleClass('active');
					$sidebarMobile.toggleClass('show-filter-box');
					$archiveMainBox.toggleClass('filter-popup-opened');
				}
			});
		}

		$('.property-filter-select').on('change', triggerFilter);

		let filterTimeout;
		$range.on("change", debounceTriggerFilter);
		$minInput.on("input", debounceTriggerFilter);
		$maxInput.on("input", debounceTriggerFilter);
		
		function debounceTriggerFilter() {
			clearTimeout(filterTimeout);
			filterTimeout = setTimeout(triggerFilter, 500);
		}
	});

})( jQuery );
