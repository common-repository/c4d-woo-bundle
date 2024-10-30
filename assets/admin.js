(function($){
	"use strict";
	$(document).ready(function(){
		$('body').on('click', '.c4d-woo-bundle-item .item-title-link', function(event){
			event.preventDefault();
			$(this).parents('.c4d-woo-bundle-item').find('.item-panel').fadeToggle(200, 'linear');
		});

		// remove
		$('body').on('click', '.c4d-woo-bundle-item .item-remove', function(event){
			event.preventDefault();
			var parent = $(this).parents('.c4d-woo-bundle-item'),
			selectedBundle = $('#c4d_woo_bundle_grouped_products'),
			productID = parent.data('id');
			parent.hide().delay(1000).remove();
			selectedBundle.find('option[value="'+productID+'"]').remove();
		});

		$('body').on('change', '#inside-main-product-1', function(event){
			if ($('#product-type').val() != 'simple') {
				alert('You should change product type to simple when use inside position');
			}
		});

		if ($('.c4d-woo-bundle-date-picker').length > 0) {
			$('.c4d-woo-bundle-date-picker').datepicker({defaultDate: '',
					dateFormat: 'yy-mm-dd',
					numberOfMonths: 1,
					showButtonPanel: true
			});
		}

		$('[name="c4d_woo_bundle[discount_type]"]').on('change', function(event){
			if ($(this).val() == 'user_role') {
				$('.discount-type-user-role').show();
				$('.discount-type-default').hide();
			} else {
				$('.discount-type-user-role').hide();
				$('.discount-type-default').show();
			}
		});

		$('[name="c4d_woo_bundle[discount_type]"]:checked').trigger('change');


	});

})(jQuery);