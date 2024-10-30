(function($){
	"use strict";

	var c4d_woo_bundle = {symbol: '$'};

	c4d_woo_bundle.EventItemChange = function() {
		$('body').on('change', '.c4d-woo-bundle-item input[name*="mustby"], .c4d-woo-bundle-item .item-qty .qty, .c4d-woo-bundle-item .item-variations select', function(index, el){
			setTimeout(function(){
				c4d_woo_bundle.updatePrice();
			}, 200);
		});
	};

	c4d_woo_bundle.qty = function() {
		$('body').on('click', '.c4d-woo-bundle-item .item-qty span', function(event){
			var input = $(this).parent().find('input.qty'),
			current = parseInt(input.val());
			if ($(this).hasClass('next')) {
				input.val(current + 1);
			} else {
				if (current > 1) {
					input.val(current - 1);
				}
			}
			c4d_woo_bundle.updatePrice();
			return false;
		});
	};

	c4d_woo_bundle.bundleQty = function() {
		$('body').on('click', '.c4d-woo-bundle-buttons .qty-button span', function(event){
			var input = $(this).parent().find('input.qty'),
			current = parseInt(input.val());
			if ($(this).hasClass('next')) {
				input.val(current + 1);
			} else {
				if (current > 1) {
					input.val(current - 1);
				}
			}
			return false;
		});
	}

	c4d_woo_bundle.updatePrice = function() {
		var bundles = $('.c4d-woo-bundle-wrap');
		bundles.each(function(index, bundle){
			var total 						= $(this).find('.c4d-woo-bundle-total'),
			options 							= $(this).data('options'),
			wcSetting 						= $(this).data('wc_setting'),
			quantity 							= 1,
			price 								= total.data('price'),
			bundlePrice 					= options['items']['bundle_price'] != '' ? parseFloat(options['items']['bundle_price']) : '',
			discount 							= options['items']['discount'] != '' ? options['items']['discount'].split(',') : [],
			discountType 					= options['items']['discount_type'] != '' ? options['items']['discount_type'] : $(this).data('discount-type-global'),
			discountBundleOnly 		= options['items']['discount_bundle_only'] ? options['items']['discount_bundle_only'] : 0,
			userDiscountType 			= options['items']['discount_user_type'] ? options['items']['discount_user_type'] : 'percent',
			userRole 							= options['user_role'] ? options['user_role'] : 'guest',
			userRoleDiscount 			= options['items']['role_' + userRole],
			position 							= options['items']['position'] != '' ? options['items']['position'] : 'outside',
			discountPrice 				= 0,
			includes 							= 0,
			percent 							= 0,
			percentPrefix 				= '',
			pricePrefix 					= '',
			totalPrice 						= 0,
			totalMainPrice 				= bundlePrice == '' ? 0 : bundlePrice,
			totalBundleItemPrice 	= 0,
			space 								= wcSetting.currency_pos == 'right_space' ? ' ' : '';

			// check included and discount
			includes = $(this).find('.c4d-woo-bundle-item input[name*="mustby"]:checked').length - 1;
			if (discount.length > 0 && includes > 0) {
				if (includes > discount.length) {
					percent = discount[discount.length - 1];
				} else {
					percent = discount[includes - 1];
				}
				percent = Number(percent);
			}

			// check for case discount by user role, set the percent and type. quickly way
		 	if (discountType == 'user_role') {
				percent = userRoleDiscount;
				discountType = userDiscountType;
			}

			// calculate total bundle price
			$(this).find('.c4d-woo-bundle-item .item').each(function(index, item){
				var itemPrice 				= parseFloat($(item).data('price')),
				itemQty 							= $(this).find('.item-qty input.qty').val() > 0 ? parseFloat($(this).find('.item-qty input.qty').val()) : 1,
				itemDiscountPriceHtml = '',
				currentPrice 					= $(this).find('.item-price'),
				setPrice 							= currentPrice.data('set_price'),
				currentPriceHtml 			= currentPrice.find('.c4d-woo-item-price-discount').remove(),
				currentPriceHtml 			= currentPrice.find('.c4d-woo-item-price-percent').remove(),
				currentPriceHtml 			= currentPrice.find('.c4d-woo-item-price-original').length == 0 ? '<span class="c4d-woo-item-price-original">' + currentPrice.html() + '</span>' : currentPrice.html(),
				variations 						= $(this).find('.item-variations').data('variations'),
				selects 							= $(this).find('.item-variations select'),
				variationId 					= 0,
				selectedVariation 		= [];

				$(this).find('.item-qty-only').html(itemQty);

				// check is this item is variable and sale
				if (variations && typeof variations == 'object') {
					$.each(variations, function(index, variation){
						selectedVariation[index] = 0;
						selects.each(function(ids, select){
							var value = $(this).val(),
							attribute = $(this).data('attribute');
							if (typeof variation['attributes']['attribute_' + attribute] != 'undefined' && (variation['attributes']['attribute_' + attribute] == value || variation['attributes']['attribute_' + attribute] == '')) {
								selectedVariation[index] += 1;
							}
							if (typeof variation['attributes']['attribute_' + attribute] != 'undefined' && variation['attributes']['attribute_' + attribute] != value) {
								selectedVariation[index] -= 1;
							}
						});
					});

					var matchVaration = variations[selectedVariation.indexOf(Math.max.apply(Math, selectedVariation))];
					if (setPrice == 0) {
						itemPrice = matchVaration['display_price'];
					}
					variationId = matchVaration['variation_id'];
				}

				//total price for checked items only, still show the discount for unchecked items
				if ($(this).find('input[name*="mustby"]:checked').length > 0) {
					if (bundlePrice == '') {
						if (index == 0) {
							if (position == 'outside') {
								totalMainPrice += itemPrice * itemQty;
							}
						} else {
							totalBundleItemPrice += itemPrice * itemQty;
						}
					}
				}

				// only calculate price for item with percent discount
				if (discountType == 'percent') {
					var itemDiscountPrice = itemPrice - ((itemPrice * percent)/100);
					itemDiscountPrice = itemDiscountPrice.toFixed(2);
				} else if (discountType == 'price') {
					var itemDiscountPrice = itemPrice.toFixed(2);
				}

				// apply discount
				itemDiscountPriceHtml = '<span class="c4d-woo-item-price-discount">' +
											'<span class="woocommerce-Price-amount amount">';
				if (wcSetting.currency_pos == 'right' || wcSetting.currency_pos == 'right_space' ) {
					itemDiscountPriceHtml += itemDiscountPrice + space + '<span class="woocommerce-Price-currencySymbol">' +  c4d_woo_bundle.symbol  + '</span>';
				} else {
					itemDiscountPriceHtml += '<span class="woocommerce-Price-currencySymbol">' +  c4d_woo_bundle.symbol  + '</span>' + space + itemDiscountPrice;
				}

				itemDiscountPriceHtml += '</span></span>';

				if ((index == 0 && discountBundleOnly == '1')) {
					// do not update price for main product when discount for bundle items only
				} else {
					if (discountType == 'percent') {
						currentPrice.html(itemDiscountPriceHtml + currentPriceHtml + '<span class="c4d-woo-item-price-percent">-'+(typeof percent == 'string' ? percent.trim() : percent )+'%</span>');
					} else if (discountType == 'price') {
						currentPrice.html(itemDiscountPriceHtml + currentPriceHtml);
					}

				}

				$(this).attr('data-data', JSON.stringify({
					'pid': $(this).attr('data-id'),
					'price': itemPrice,
					'discount': itemDiscountPrice,
					'total': itemDiscountPrice * itemQty,
					'qty': itemQty,
					'variation': variationId,
					'percent': percent,
					'percent_type': discountType,
					'discount_bundle_only': discountBundleOnly
				}));
			});

			// set total price
			if (wcSetting.currency_pos == 'right' || wcSetting.currency_pos == 'right_space' ) {
				total.find('.total-price').html((totalMainPrice + totalBundleItemPrice) + space + c4d_woo_bundle.symbol);
			} else {
				total.find('.total-price').html(c4d_woo_bundle.symbol + space + (totalMainPrice + totalBundleItemPrice));
			}


			// set discount price
			if (discountType == 'percent') {
				percentPrefix = '%';
				if (discountBundleOnly == '1') {
					discountPrice = (totalMainPrice + totalBundleItemPrice) - ((totalBundleItemPrice * percent)/100);
				} else {
					totalPrice = totalMainPrice + totalBundleItemPrice;
					discountPrice = totalPrice - ((totalPrice * percent)/100);
				}
			} else {
				pricePrefix = c4d_woo_bundle.symbol;
				totalPrice = totalMainPrice + totalBundleItemPrice;
				discountPrice = totalPrice - percent;
			}

			discountPrice = discountPrice.toFixed(2);

			if (wcSetting.currency_pos == 'right' || wcSetting.currency_pos == 'right_space' ) {
				total.find('.total-discount').html(discountPrice + space + c4d_woo_bundle.symbol);
			} else {
				total.find('.total-discount').html(c4d_woo_bundle.symbol + space + discountPrice);
			}

			if (percent > 0) {
				$(this).removeClass('discount-0-percent');
				total.find('.total-save-number').html(' ' + pricePrefix + percent + percentPrefix);
			} else {
				$(this).addClass('discount-0-percent');
			}
		});
	};

	c4d_woo_bundle.addToCart = function() {
		$('body').on('click', '.c4d-woo-bundle-add-to-cart', function(event){
			event.preventDefault();
			var button = $(this),
			parent = button.parents('.c4d-woo-bundle-wrap'),
			options = parent.data('options'),
			bundlePrice = options['items']['bundle_price'] != '' ? parseFloat(options['items']['bundle_price']) : '',
			bundleQty = parent.find('.c4d-woo-bundle-buttons input.qty').val(),
			items = parent.find('.c4d-woo-bundle-item .item'),
			mainProduct = items[0],
			datas = {
				'product_id': 0,
				'variation_id': 0,
				'quantity': 1,
				'bundle_quantity': bundleQty,
				'variations': [],
				'discount_bundle_only': 0,
				discount: parent.find('.c4d-woo-bundle-total .total-discount').text(),
				price: parent.find('.c4d-woo-bundle-total .total-price').text(),
				items: []
			};

			// main product
			var mainproductData   = $.parseJSON($(mainProduct).attr('data-data'));
			datas['product_id']   = mainproductData.pid;
			datas['variation_id'] = typeof mainproductData.variation != 'undefined' ? mainproductData.variation : 0;
			datas['main_product'] = mainproductData;
			datas['quantity'] = mainproductData.qty;
			datas['percent'] 	  = mainproductData.percent;
			datas['percent_type'] = mainproductData.percent_type;
			datas['discount_bundle_only'] = mainproductData.discount_bundle_only;
			datas['bundle_price'] = bundlePrice;

			// get main product variations
			$(mainProduct).find('.item-variation').each(function(index, select){
				datas['variations'].push({name: 'attribute_' + $(this).attr('data-attribute'), value: $(this).val()});
			});

			// bundle product, remove main product from list
			items = items.slice(1);

			$.each(items, function(index, item){
				if ($(this).find('input[name*="mustby"]:checked').length > 0) {
					datas['items'].push($.parseJSON($(this).attr('data-data')));
				}
			});

			button.addClass('loading');

			$.ajax({
			  type: "POST",
			  url: woocommerce_params.ajax_url,
			  data: {
			  	'action': 'c4d_woo_bundle_add_to_cart',
			  	'datas': datas
			  },
			  complete: function(response) {
			  	button.removeClass('loading');
			  	if (typeof response.responseJSON.fragments != 'undefined') {
			  		var minicart = $('.widget_shopping_cart');
			  		minicart.find('.widget_shopping_cart_content').remove();
			  		minicart.append(response.responseJSON.fragments['div.widget_shopping_cart_content']);
			  		$('.c4d-woo-bundle-view-cart').addClass('active');
			  		$( document.body ).trigger( 'added_to_cart' );
			  	}
			  	if (typeof response.responseJSON.error != 'undefined') {
			  		window.location.href = response.responseJSON.product_url;
			  	}
			  }
			});

			return false;
		});
	};

	c4d_woo_bundle.reorderBundleInCartAndCheckoutView = function() {
		$('.woocommerce-cart-form .c4d-woo-bundle-cart-wrap, .woocommerce-checkout-review-order .c4d-woo-bundle-cart-wrap').each(function(){
			var parent = $(this).parent(),
			clone =	$(this).clone().addClass('active');
			$(this).remove();
			parent.append(clone);
		});
	}

	$(document).ready(function(){
		// get symbol
		c4d_woo_bundle.symbol = $('.woocommerce-Price-currencySymbol:first').html();

		// init
		c4d_woo_bundle.updatePrice();

		c4d_woo_bundle.qty();

		c4d_woo_bundle.bundleQty();

		// events
		c4d_woo_bundle.EventItemChange();

		// init add to cart bundle
		c4d_woo_bundle.addToCart();

		// cart view
		c4d_woo_bundle.reorderBundleInCartAndCheckoutView();

		// detect ajax complete
		$( document ).ajaxComplete(function( event, xhr, settings ) {
			if (typeof settings.url != 'undefined' && typeof settings.data != 'undefined') {
				if ( (settings.url.indexOf('cart') > -1 && settings.data.indexOf('update_cart') > -1) || settings.url.indexOf('update_order_review') > -1){
					c4d_woo_bundle.reorderBundleInCartAndCheckoutView();
				}
			}
		});
	});
})(jQuery);