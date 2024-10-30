<?php
// category
add_action( 'woocommerce_before_shop_loop_item_title', 'c4d_woo_bundle_badge' );
add_filter( 'woocommerce_loop_add_to_cart_link', 'c4d_woo_bundle_view_bundle_button', 10, 3 );

// single
add_action( 'woocommerce_single_product_summary', 'c4d_woo_bundle_frontend_bundle_position', 0);
add_action( 'wp_ajax_c4d_woo_bundle_add_to_cart', 'c4d_woo_bundle_add_to_cart' );
add_action( 'wp_ajax_nopriv_c4d_woo_bundle_add_to_cart', 'c4d_woo_bundle_add_to_cart' );
add_action( 'woocommerce_before_calculate_totals', 'c4d_woo_bundle_calculate_totals' );;
add_filter( 'woocommerce_widget_cart_item_quantity', 'c4d_woo_bundle_main_product_price', 10, 3 );
add_filter( 'woocommerce_widget_cart_item_quantity', 'c4d_woo_bundle_mini_cart', 10, 3 );
add_action( 'woocommerce_after_cart_item_name', 'c4d_woo_bundle_after_cart_item_name', 10, 2 );

// checkout hook
add_filter( 'woocommerce_cart_item_name', 'c4d_woo_bundle_checkout_item_name', 10, 3 );
add_filter( 'woocommerce_checkout_cart_item_quantity', 'c4d_woo_bundle_checkout_item_quantity', 10, 3 );

// order hook
add_filter( 'woocommerce_order_item_name', 'c4d_woo_bundle_order_item_name', 10, 3 );
add_filter( 'woocommerce_order_item_quantity_html', 'c4d_woo_bundle_order_item_quantity_html', 10, 2 );
add_action( 'woocommerce_add_order_item_meta', 'c4d_woo_bundle_add_order_item_meta', 10, 2);
add_action( 'woocommerce_order_item_meta_end', 'c4d_woo_bundle_order_bundle_view', 10, 4 );
add_action( 'woocommerce_after_order_itemmeta', 'c4d_woo_bundle_admin_order_itemmeta', 10, 3 );
// order hook

//shortcode 
add_shortcode( 'c4d_woo_bundle', 'c4d_woo_bundle_shortcode' );

function c4d_woo_bundle_shortcode( $atts ) {
	return c4d_woo_bundle_frontend_bundle_items();
}

function c4d_woo_bundle_frontend_bundle_position($id = 0, $echo = true) {
	global $product, $c4d_plugin_manager;
	$productType = $product->get_type();
	if ($productType == 'external') return;

	$productID = $product->get_id();
	$bundleDatas = c4d_woo_bundle_get_post_meta( $productID, 'c4d_woo_bundle', true );
	if (isset($bundleDatas['items'])) {
		$saveItems = isset($bundleDatas['items']) ? $bundleDatas['items'] : array();
		if (isset($saveItems['position'])) {
			$position = $saveItems['position'] == 'inside' ? 26 : 35;
			if ($saveItems['position'] == 'inside') {
				remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
				remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			}
			add_action( 'woocommerce_single_product_summary', 'c4d_woo_bundle_frontend_bundle_items', $position);
		}
	}
}

function c4d_woo_bundle_frontend_bundle_item($item, $productId, $bundleOptions) {
	global $c4d_plugin_manager;
	$html = array();
	$pitem = wc_get_product( $productId );
	$product_status = get_post_status( $productId );
	$defaultLayout = c4d_woo_bundle_default_layout();
	$bundlePrice = (isset($bundleOptions['bundle_price']) && $bundleOptions['bundle_price'] != '') ? $bundleOptions['bundle_price'] : '';

	if ( is_object( $pitem ) && $product_status == 'publish') {
		// product with empty price -> out stock not backorder , but if admin want this product with price 0$ ? [solved]
		// product with manager stock -> check qty for order and backorder
		$html['class'] = '';
		$html['stock'] = '';
		$manageStock = $pitem->managing_stock();
		$stockStatus = $pitem->get_stock_status();
		$backorder = $pitem->get_backorders();
		if ($pitem->is_type('variable')) {
			$variations = $pitem->get_available_variations();
			$attributes = $pitem->get_variation_attributes();
		} else {
			$attributes = $pitem->get_attributes();
		}

		if ($stockStatus == 'outofstock') {
			$html['stock'] = '<span class="item-stock">' . esc_html__('Out Of Stock', 'c4d-woo-bundle') . '</span>';
			$html['class'] .= ' outofstock';
		}

		if ($manageStock) {
			if ($stockStatus == 'onbackorder' && $backorder == 'notify') {
				$html['stock'] = '<span class="item-stock">' . esc_html__('On Backorder', 'c4d-woo-bundle') . '</span>';
			}
		}
		$html['pid'] = $productId;
		$mustBuy = ( isset( $item['must_by'] ) ) ? $item['must_by'] : 0;
		$mustBuy = $bundlePrice !== '' ? 1 : $mustBuy;
		$html['product-link'] = get_permalink($productId);

		////// MUST BUY & INIT CHECK
		// default does not need must by
		$mustBuy = ( isset( $item['must_by'] ) ) ? $item['must_by'] : 0;

		// if bundle is set fixed price, so all items are must included.
		$mustBuy = $bundlePrice !== '' ? 1 : $mustBuy;

		// checked items when init, can set by global option, default 1
		$initCheck = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-init-check'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-init-check'] : 1;
		$initCheck = ( isset( $item['init_check'] ) && $item['init_check'] !== '' ) ? $item['init_check'] : $initCheck;
		if ($mustBuy) {
			$initCheck = 1;
		}

		$html['mustby'] = '<div class="item-must-by"><input name="mustby[]" type="checkbox" value="'.$productId.'" '.checked(1, $initCheck, false).' ' .disabled($mustBuy, 1, false). ' ></div>';
		if ($stockStatus == 'outofstock') {
			$html['mustby'] = '<div class="item-must-by"><input name="mustby[]" type="checkbox" value="0" disabled="disabled" ></div>';
		}

		///////// TAXONOMIES ///////////
		$showTax = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-tax'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-tax'] : 1;
		$showTax = ( isset( $item['show_tax'] ) && $item['show_tax'] !== '') ? $item['show_tax'] : $showTax;
		preg_match('/\[tax=([^\[\]]+)\]/', $defaultLayout, $matches);
		if (isset($matches[1])) {
			$tax = $matches[1];
			$taxName = '';
			$html['tax=' . $tax] = '';
			if ($showTax) {
				$taxs = wc_get_product_terms( $productId, $tax, array( 'orderby' => 'parent' ) );
				foreach ($taxs as $taxItem) {
					$taxName .= ' ' . $taxItem->name;
				}
			}
			$html['tax=' . $tax] = '<div class="item-taxonomies">'.$taxName.'</div>';
		}

		////// ATTRIBUTE NAME /////
		$showCate = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-attr'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-attr'] : 0;
		$showCate = ( isset( $item['show_attr'] ) && $item['show_attr'] !== '') ? $item['show_attr'] : $showCate;

		preg_match('/\[attr=([^\[\]]+)\]/', $defaultLayout, $matches);
		if (isset($matches[1])) {
			$attr = $matches[1];
			$attrName = '';
			$html['attr=' . $attr] = '';
			if ($showCate) {
				if ($pitem->is_type('variable')) {
					foreach ($attributes as $key => $attribute) {
						$value = '';
						foreach ($attribute as $v) {
							if ($attr == str_replace('pa_', '', $key)) {
								$value .= ' '. $v;
							}
						}
						$attrName .= $value;
					}
				} else {
					foreach ($attributes as $key => $attribute) {
						$terms = $attribute->get_terms();
						$value = '';
						foreach ($terms as $key => $term) {
							if ($attr == str_replace('pa_', '', $term->taxonomy)) {
								$value .= ' '. $term->name;
							}
						}
						$attrName .= $value;
					}
				}
				$html['attr=' . $attr] = '<span class="item-attribute">'.$attrName.'</span>';
			}
		}

		///// THUMB ////
		$showThumb = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-thumb'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-thumb'] : 1;
		$showThumb = ( isset( $item['show_thumbnail'] ) && $item['show_thumbnail'] !== '') ? $item['show_thumbnail'] : $showThumb;
		$html['thumbnail'] = $showThumb ? '<a class="item-link" target="blank" href="'.$html['product-link'].'"><div class="item-thumbnail">'.$pitem->get_image('woocommerce_thumbnail', array(), true ).'</div></a>' : '';

		//// TITLE ////
		$title = ( isset( $item['title'] ) && $item['title'] != '' ) ? $item['title'] : $pitem->get_title();
		$title = isset($item['main_product']) ? esc_html__('This product', 'c4d-woo-bundle') . ': ' . $title : $title;
		$showTitle = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-title'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-title'] : 1;
		$showTitle = ( isset( $item['show_title'] ) && $item['show_title'] !== '' ) ? $item['show_title'] : $showTitle;
		$html['title'] = $showTitle ? '<a class="item-link" target="blank" href="'.$html['product-link'].'"><h3 class="item-title">'.$title.'</h3></a>' : '';
		$html['titleonly'] = $title;

		///// DESC /////
		$desc = ( isset( $item['description'] ) && $item['description'] != '' ) ? $item['description'] : $pitem->get_short_description();
		$showDesc = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-desc'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-desc'] : 1;
		$showDesc = ( isset( $item['show_desc'] ) && $item['show_desc'] !== '') ? $item['show_desc'] : $showDesc;
		$html['description'] = $showDesc ? '<div class="item-description">'.$desc.'</div>' : '';

		////// QTY ///////
		$qty = ( isset( $item['qty'] ) ) ? $item['qty'] : 1;
		$showQty = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-qty'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-qty'] : 1;
		$showQty = ( isset( $item['show_qty'] ) && $item['show_qty'] !== '') ? $item['show_qty'] : $showQty;
		$showQty = $bundlePrice !== '' ? 0 : $showQty;

		if (!$showQty || $qty > 1) {
			$html['qty'] = '<div class="item-qty"><span class="item-label">'.esc_html__('Qty', 'c4d-woo-bundle').'</span><div class="qty-button"><span class="qty-number">'.$qty.'</span><input class="qty" type="hidden" size="4" pattern="[0-9]*" value="'.$qty.'"></div></div>';
		} else if ($qty && $qty == 1) {
			$html['qty'] = $showQty ? '<div class="item-qty"><span class="item-label">'.esc_html__('Qty', 'c4d-woo-bundle').'</span><div class="qty-button"><span class="prev">-</span><input class="qty" type="text" size="4" pattern="[0-9]*" value="'.$qty.'"><span class="next">+</span></div></div>' : '';
		}

		$html['qtyonly'] = '<span class="item-qty-only">'.$qty. '</span>';

		/////// PRICE////////
		$html['priceonly'] = $pitem->get_price() ? $pitem->get_price() : 0;
		$html['price'] = '<div data-set_price="0" class="item-price">'.$pitem->get_price_html().'</div>';

		if (isset($item['price']) && $item['price'] != '') {
			$html['priceonly'] = $item['price'];
			$html['price'] = '<div data-set_price="1" class="item-price">'.wc_price($item['price']).'</div>';
		}

		if (isset($item['sale']) && $item['sale'] != '') {
			$html['priceonly'] = $item['sale'];
			$html['price'] = '<div data-set_price="1" class="item-price">'.wc_format_price_range($item['sale'], $item['price']).'</div>';
		}

		//////// CATEGORY ////////
		$category = wc_get_product_category_list($productId, ' ', '', '');
		$showCate = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-cate'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-cate'] : 0;
		$showCate = ( isset( $item['show_cate'] ) && $item['show_cate'] !== '') ? $item['show_cate'] : $showCate;
		$html['category'] = $showCate ? '<span class="item-category">'.$category.'</span>' : '';

		///////// TAGS ///////////
		$tag = wc_get_product_tag_list($productId, ' ', '', '');
		$showTag = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-tag'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-tag'] : 0;
		$showTag = ( isset( $item['show_tag'] ) && $item['show_tag'] !== '') ? $item['show_tag'] : $showCate;
		$html['tag'] = $showTag ? '<span class="item-tag">'.$tag.'</span>' : '';

		//////// VARIATION///////

		if ($pitem->is_type('variable')) {
			$select = '';
			$showStockStatus = isset($c4d_plugin_manager['c4d-woo-bundle-item-variable-stock']) && $c4d_plugin_manager['c4d-woo-bundle-item-variable-stock'] ? $c4d_plugin_manager['c4d-woo-bundle-item-variable-stock'] : 1;
			$setVariable = isset( $item['set_variable'] ) ? $item['set_variable'] : array();
			foreach ($attributes as $attribute => $options) {
				$select .= '<label class="item-variation-label"><span class="item-label">'.str_replace('pa_', '', $attribute).'</span>';
				$select .= '<div class="c4d-woo-bundle-select-border"><select class="item-variation" data-attribute="'.sanitize_title($attribute).'">';
				$default = $pitem->get_variation_default_attribute($attribute);

				foreach ( $options as $option ) {
					$selected = $default == $option ? 'selected' : '';
					if ($showStockStatus == 1) {
						$stock = c4d_woo_bundle_frontend_check_stock_by_attribute($variations, $attribute, $option);
					} else {
						$stock = '';
					}
					$echoSelect = false;
					if (isset($setVariable[sanitize_title($attribute)]) && $setVariable[sanitize_title($attribute)] != '-1' && $setVariable[sanitize_title($attribute)] == $option) {
						$echoSelect = true;
					}
					if (isset($setVariable[sanitize_title($attribute)]) && $setVariable[sanitize_title($attribute)] == '-1') {
						$echoSelect = true;
					}
					if (!isset($setVariable[sanitize_title($attribute)])) {
						$echoSelect = true;
					}
					if ($echoSelect) {
						$select .= '<option value="'.sanitize_title($option).'" '.$selected.'>'.$option.$stock.'</option>';
					}
				}
				$select .= '</select></div>';
				$select .= '</label>';
			}

			$showVariation = ( isset( $c4d_plugin_manager['c4d-woo-bundle-item-variable'] ) ) ? $c4d_plugin_manager['c4d-woo-bundle-item-variable'] : 1;
			$showVariation = ( isset( $item['show_variable'] ) && $item['show_variable'] !== '') ? $item['show_variable'] : $showVariation;

			$html['variation'] = '<div class="item-variations '.( $showVariation ? '' : 'c4d-woo-bundle-is-hide' ).'" data-variations="'.htmlspecialchars(json_encode($variations)).'" >'.$select.'</div>';
		} else {
			$html['variation'] = '';
		}
		if ($bundlePrice !== '') {
			$html['price'] = '';
		}
	}
	return $html;
}

function c4d_woo_bundle_frontend_check_stock_by_attribute($variations, $attribute, $value) {
	$stock = '';
	foreach($variations as $variation) {
		$att = 'attribute_' . sanitize_title($attribute);

		if(isset($variation['attributes'][$att]) && $variation['attributes'][$att] == $value ) {
			$stock = $variation['is_in_stock'];

			if (!$stock) {
				$stock = ' - ' . $variation['availability_html'];
			} else {
				$stock = ' - ' . ($variation['availability_html'] != '' ? $variation['availability_html'] : esc_html__('In Stock', 'c4d-woo-bundle'));
			}

		}
	}
	return $stock;
}

function c4d_woo_bundle_frontend_bundle_items($id = 0, $echo = true) {
	ob_start();
	global $product, $c4d_plugin_manager;
	if ($id) {
		$product = wc_get_product($id);
	}
	$productType = $product->get_type();
	if ($productType == 'external') return;

	$wcSetting = array(
			'currency_pos' => get_option( 'woocommerce_currency_pos' ),
			'price_decimal_sep' => get_option( 'woocommerce_price_decimal_sep'),
			'price_num_decimals' => get_option( 'woocommerce_price_num_decimals'),
			'price_thousand_sep' => get_option( 'woocommerce_price_thousand_sep')
	);

	$productID = $product->get_id();
	$bundleDatas = c4d_woo_bundle_get_post_meta( $productID, 'c4d_woo_bundle', true );
	$ids = isset($bundleDatas['ids']) ? $bundleDatas['ids'] : array();
	$saveItems = isset($bundleDatas['items']) ? $bundleDatas['items'] : array();
	$position = isset($saveItems['position']) ? $saveItems['position'] : 'outside';
	$dateNow = current_time( 'timestamp' );
	$bundlePrice = (isset($saveItems['bundle_price']) && $saveItems['bundle_price'] !== '') ? $saveItems['bundle_price'] : '';
	$disocuntTypeGlobal = isset($c4d_plugin_manager['c4d-woo-bundle-discount-type']) ? $c4d_plugin_manager['c4d-woo-bundle-discount-type'] : 'percent';
	$discountType = isset($saveItems['discount_type']) && $saveItems['discount_type'] != '' ? $saveItems['discount_type'] : $disocuntTypeGlobal;

	$class = 'c4d-woo-bundle-wrap';
	$class .= ' '. (($position == 'outside') ? 'outside' : 'inside');
	$class .= ' ' . ($discountType == 'price' ? 'discount-type-price' : '');
	$htmls = array();

	if (count($ids) < 1) { return; }

	$dateStart = isset($saveItems['start_date']) ? strtotime($saveItems['start_date']) : false;
	$dateEnd = isset($saveItems['end_date']) ? strtotime($saveItems['end_date']) : false;

	if ($dateStart && $dateStart > $dateNow) {
		return;
	}
	if ($dateEnd && $dateEnd < $dateNow) {
		return;
	}

	$htmls[] = c4d_woo_bundle_frontend_bundle_item(array('init_check' => 1, 'must_by' => 1, 'main_product' => 1), $productID, $saveItems);

	foreach( $ids as $itemID ) {
		$item = isset($saveItems[$itemID]) ? $saveItems[$itemID] : false;
		$htmls[] = c4d_woo_bundle_frontend_bundle_item($item, $itemID, $saveItems);
		if (count($htmls) > 3) {
			break;
		}
	}

	echo '<div class="'.$class.'" data-pid="'.$productID.'" data-wc_setting="'.htmlspecialchars(json_encode($wcSetting)).'" data-discount-type-global="'.$disocuntTypeGlobal.'" data-options="'.htmlspecialchars(json_encode($bundleDatas)).'">';

	$title = (isset($c4d_plugin_manager['c4d-woo-bundle-title']) && $c4d_plugin_manager['c4d-woo-bundle-title'] != '') ? $c4d_plugin_manager['c4d-woo-bundle-title'] : '';
	$title = (isset($saveItems['title']) && $saveItems['title'] != '') ? $saveItems['title'] : $title;

	if ($title) {
		echo '<div class="c4d-woo-bundle-title"><h3>'.$title.'</h3></div>';
	}

	$description = (isset($c4d_plugin_manager['c4d-woo-bundle-desc']) && $c4d_plugin_manager['c4d-woo-bundle-desc'] != '') ? $c4d_plugin_manager['c4d-woo-bundle-desc'] : '';
	$description = (isset($saveItems['description']) && $saveItems['description'] != '') ? $saveItems['description'] : $description;
	if ($description) {
		echo '<div class="c4d-woo-bundle-description">'.$description.'</div>';
	}

	$defaultLayout = c4d_woo_bundle_default_layout();

	$wrapLayout = '<div data-id="[pid]" data-price="[priceonly]" class="item">'.$defaultLayout.'</div>';
	echo '<div class="c4d-woo-bundle-items">';

	foreach ($htmls as $key => $html) {

		echo '<div class="c4d-woo-bundle-item">';
		$item = $wrapLayout;
		foreach ($html as $key => $el) {
			$item = str_replace('['.$key.']', $el, $item);
			$item = str_replace('{'.$key.'}', $el, $item);
		}
		echo do_shortcode($item);
		echo '</div>';
	}

	echo '</div>';

	$price = array(
			'main' => array(
				'price' => $product->get_price(),
				'regular' => $product->get_regular_price(),
				'sale' => $product->get_sale_price()
			)
		);

		echo '<div data-price="'.htmlspecialchars(json_encode($price)).'" class="c4d-woo-bundle-total">
				<h3 class="total-label">'.esc_html__('Total', 'c4d-woo-bundle').':</h3>
				<span class="total-discount"></span>
				<span class="total-price"></span>
				<span class="total-save"><span class="total-save-text">'.esc_html__('save', 'c4d-woo-bundle').'</span><span class="total-save-number"></span></span>
			</div>';

	// if ($position == 'outside') {
		echo '<div class="c4d-woo-bundle-buttons">
				<div class="qty-button">
					<span class="prev">-</span>
					<input class="qty" type="text" size="4" pattern="[0-9]*" value="1">
					<span class="next">+</span>
				</div>
				<a class="c4d-woo-bundle-add-to-cart" href="#">'.(isset($c4d_plugin_manager['c4d-woo-bundle-button']) && $c4d_plugin_manager['c4d-woo-bundle-button'] !== '' ? $c4d_plugin_manager['c4d-woo-bundle-button'] : esc_html__('Add Bundle To Cart', 'c4d-woo-bundle')).'
				<span class="loading-icon">
				<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
					  <path fill="#fff" d="M25.251,6.461c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615V6.461z" transform="rotate(350 25 25)">
					    <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"></animateTransform>
					    </path>
					  </svg></span>
				</a>
				<a href="' . esc_url( wc_get_cart_url() ) . '" class="c4d-woo-bundle-view-cart">' . esc_html__( 'View cart', 'c4d-woo-bundle' ) . '</a>
			</div>';
	// }

	if (isset($saveItems['countdown']) && $saveItems['countdown']) {
		$defaultDateCount = isset($c4d_plugin_manager['c4d-woo-bundle-end-time']) ? $c4d_plugin_manager['c4d-woo-bundle-end-time'] : '3';
		$endDate = (isset($saveItems['end_date']) && $saveItems['end_date'] != '') ? strtotime($saveItems['end_date']) : strtotime(' +'.$defaultDateCount.' day');
		echo isset($saveItems['countdown_title']) ? '<h3 class="c4d-woo-bundle-countdown-title">'.$saveItems['countdown_title'].'</h3>' : '';
		echo do_shortcode('<div class="c4d-woo-bundle-countdown">[c4d_wcd_countdown to="'.$endDate.'"]</div>');
	}
	echo '</div>';
	$html = ob_get_contents();
	ob_end_clean();
	if ($echo) {
		echo $html;
	} else {
		return $html;
	}
}

function c4d_woo_bundle_default_layout() {
	return isset($c4d_plugin_manager['c4d-woo-bundle-layout']) ? $c4d_plugin_manager['c4d-woo-bundle-layout'] :
					'<div class="item-row [class]">
                        <div class="item-col width-30">
                            [mustby]
                            [thumbnail]
                        </div>
                        <div class="item-col width-70">
                            [category][tag][attr=brand][tax=product_brand][title][stock]
                            [price]
                            <div class=item-qty-variations>
                            [qty]
                            [variation]
                            </div>
                            [description]
                        </div>
                    </div>';
}

function c4d_woo_bundle_add_to_cart() {
		$datas 			   		 = isset($_POST['datas']) ? $_POST['datas'] : array();
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $datas['product_id'] ) );
		$product           = wc_get_product( $product_id );
		$bundleQuantity          = empty( $datas['bundle_quantity'] ) ? 1 : wc_stock_amount( $datas['bundle_quantity'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variation_id      = !empty( $datas['variation_id'] ) ?  $datas['variation_id'] : 0 ;
		$variation         = array();

		if ( $product_status == 'publish' && 'variable' === $product->get_type() && $variation_id && isset($datas['variation_id'])) {
			// Gather posted attributes.
			foreach ($datas['variations'] as $key => $value) {
				$variation[$value['name']] = $value['value'];
			}
		}

		if (isset($datas['items']) && count($datas['items'])) {
			foreach($datas['items'] as $key => $item) {
				$pitem = wc_get_product($item['pid']);
				$product_status = $pitem->get_status();
				$stockStatus = $pitem->get_stock_status();
				if ($stockStatus == 'outofstock' && $product_status == 'publish') {
					unset($datas['items'][$key]);
				}
			}
		}

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $bundleQuantity, $variation_id, $variation, array('bundle' => $datas) ) && 'publish' === $product_status ) {
			// Return fragments
			WC_AJAX::get_refreshed_fragments();
		} else {
			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
			);
			wp_send_json( $data );
		}
}
function c4d_woo_bundle_calculate_totals ($cart) {
	global $c4d_plugin_manager;
	$discountType = isset($c4d_plugin_manager['c4d-woo-bundle-discount-type']) ? $c4d_plugin_manager['c4d-woo-bundle-discount-type'] : 'percent';

	foreach ( $cart->cart_contents as $key => $cart_item ) {
		if ( ! empty( $cart_item['bundle'] ) && ! empty( $cart_item['bundle']['items'] ) ) {
			$data = $cart_item['bundle'];
			$mainProduct = wc_get_product($data['product_id']);
			$mainPrice = $mainProduct->get_price();
			$mainQty = isset($data['quantity']) ? $data['quantity'] : 1;
			$bundleDatas = c4d_woo_bundle_get_post_meta( $data['product_id'], 'c4d_woo_bundle', true );
			$saveItems = isset($bundleDatas['items']) ? $bundleDatas['items'] : array();
			$bundlePrice = isset($saveItems['bundle_price']) && $saveItems['bundle_price'] !== '' ? $saveItems['bundle_price'] : '';
			$discountType = isset($saveItems['discount_type']) && $saveItems['discount_type'] !== '' ? $saveItems['discount_type'] : $discountType;
			$discountBundleOnly = isset($data['discount_bundle_only']) ? $data['discount_bundle_only'] : 0;
			$position = isset($saveItems['position']) ? $saveItems['position'] : 'outside';
			$discount = explode( ',', $saveItems['discount']);
			$total = floatval(0);
			$totalBundle = floatval(0);
			$percent = 0;

			if ($mainProduct->get_type() == 'variable' && isset($cart_item['variation_id'])) {
				$variation = wc_get_product($cart_item['variation_id']);
				if ($variation) {
					$mainPrice = $variation->get_price();
				}
			}

			$discountNumber = count($discount);
			$includeNumber = count($data['items']);

			if ($discountNumber > 0 && $includeNumber > 0) {
				if ($includeNumber > $discountNumber) {
					$percent = floatval(trim($discount[$discountNumber - 1]));
				} else {
					$percent = floatval(trim($discount[$includeNumber - 1]));
				}
			}

			foreach($data['items'] as $item) {
				$pitem = wc_get_product($item['pid']);
				$productType = $pitem->get_type();
				$product_status = $pitem->get_status();
				$stockStatus = $pitem->get_stock_status();
				if ($stockStatus == 'outofstock' && $product_status == 'publish') {
					continue;
				}
				$qty = isset($item['qty']) ? $item['qty'] : 1;
				$itemPrice = 0;
				if ($productType == 'variable') {
					$variation = wc_get_product($item['variation']);
					if ($variation) {
						$itemPrice = $variation->get_price();
					}
				} else {
					$itemPrice = $pitem->get_price();
				}

				if (isset($saveItems[$item['pid']])) {
					$itemb = (array) $saveItems[$item['pid']];
					if (isset($itemb['price']) && $itemb['price'] !== '') {
						$itemPrice = $itemb['price'];
					}
					if (isset($itemb['sale']) && $itemb['sale'] !== '') {
						$itemPrice = $itemb['sale'];
					}
				}

				$total = $total + ($itemPrice * $item['qty']);
			}

			if ($position == 'inside') {
				$mainPrice = 0;
				$mainQty = 0;
			}

			if ($bundlePrice !== '') {
				$total = $bundlePrice;
				$mainPrice = 0;
				$mainQty = 0;
			}

			if ($discountType == 'percent') {
				if ($discountBundleOnly) {
					$total = $total + (floatval($mainPrice) * $mainQty) - (($total * $percent)/100);
				} else {
					$total = $total + floatval($mainPrice) * $mainQty;
					$total = $total - (($total * $percent)/100);
				}

			} else {
				$total = $total + floatval($mainPrice) * $mainQty;
				if ($total >= $percent) {
					$total = $total - $percent;
				}
			}

			$cart_item['data']->set_price( $total );
		}
	}
}

function c4d_woo_bundle_main_product_price($html, $cart_item, $cart_item_key) {
	global $c4d_plugin_manager;
	if ( ! empty( $cart_item['bundle'] ) ) {
		$data = $cart_item['bundle'];
		if (isset($data['main_product'])) {
			return c4d_woo_bundle_item_name__($data);
		}
	}
	return $html;
}
function c4d_woo_bundle_cart_view($cart_item) {
	$bundle = '';
	if (isset($cart_item['bundle']) && isset($cart_item['bundle']['items'])) {
		$class = 'c4d-woo-bundle-cart-wrap ';
		$class .= ($cart_item['bundle']['percent'] > 0) ? '' : ' discount-0-percent ';
		$class .= ($cart_item['bundle']['percent_type'] == 'price') ? ' fixed-price ' : '';
		$bundlePrice = (isset($cart_item['bundle']['bundle_price'])  && $cart_item['bundle']['bundle_price'] !== '') ? $cart_item['bundle']['bundle_price'] : '';
		$bundle = '<div class="'.$class.'">';

		foreach($cart_item['bundle']['items'] as $item) {
			$pitem = wc_get_product($item['pid']);
			$bundle .= '<div class="c4d-woo-bundle-cart-item">';
			$bundle .= '<div class="item-image"><a target="blank" href="'.get_permalink($item['pid']).'">'.$pitem->get_image('woocommerce_thumbnail', array(), true ).'</a></div>';
			$bundle .= '<h4 class="item-title"><a target="blank" href="'.get_permalink($item['pid']).'">'. $pitem->get_title() . '</a></h4>';

			$bundle .= '<div class="item-qty-price">';

			if ($bundlePrice == '') {
				$bundle .= '<span class="item-label-qty">'.$item['qty'] . '</span> <span class="c4d-woo-bundle-quantity-x">x</span> ';
				if ($cart_item['bundle']['percent_type'] == 'percent') {
					$bundle .= '<span class="c4d-woo-bundle-price-discount">' . wc_price($item['discount']) . '</span>';
				}
				$bundle .= ' <span class="c4d-woo-bundle-price-original">' . wc_price($item['price']) . '</span>';
				if ($cart_item['bundle']['percent_type'] == 'percent') {
					$bundle .= ' <span class="c4d-woo-bundle-price-percent"> -' . trim($item['percent']) . '%</span>';
				}
			} else {
				$bundle .= '<span class="item-label-qty"><strong>'.esc_html__('Qty', 'c4d-woo-bundle').'</strong>: '.$item['qty'] . '</span>';
			}
			$bundle .= '</div>';

			if ($pitem->is_type('variable') && $item['variation'] != '') {
				$variation = wc_get_product($item['variation']);
				if ($variation) {
					$attributes = $variation->get_attributes();
					$bundle .= '<div class="item-variations">';
					foreach($attributes as $attr => $value) {
						if ($value != '') {
							$bundle .= '<span class="item-attribute">';
							$bundle .= '<strong>'.str_replace('pa_', '', $attr).'</strong>: '.$value;
							$bundle .= '</span>';
						}
					}
					$bundle .= '</div>';
				}
			}

			$bundle .= '</div>';
		}
		$bundle .= '</div>';
	}
	return $bundle;
}
function c4d_woo_bundle_mini_cart($name, $cart_item, $cart_item_key) {
	$bundle = '';
	if (isset($cart_item['bundle'])) {
		$bundle .= c4d_woo_bundle_cart_view($cart_item, $cart_item_key);
		$bundle .= '<div class="c4d-woo-bundle-qty-wrap">' . $cart_item['quantity'] . ' <span class="c4d-woo-bundle-quantity-x">x</span> ' . esc_html__('this bundle', 'c4d-woo-bundle'). '</div>';
	}
	return $name.$bundle;
}

function c4d_woo_bundle_after_cart_item_name($cart_item, $cart_item_key) {
	$bundle = '';
	if (isset($cart_item['bundle'])) {
		$bundle = '<div>' . c4d_woo_bundle_main_product_price('', $cart_item, $cart_item_key) . '</div>';
		$bundle .= c4d_woo_bundle_cart_view($cart_item, $cart_item_key);
	}
	echo $bundle;
}

function c4d_woo_bundle_checkout_item_name($name, $cart_item, $cart_item_key){
	$bundle = '';
	if (is_checkout() && isset($cart_item['bundle'])) {
		$bundle .= ' ('. esc_html('this bundle', 'c4d-woo-bundle'). ' x ' .$cart_item['quantity']. ' )';
		$bundle .= '<div>' . c4d_woo_bundle_main_product_price('', $cart_item, $cart_item_key) . '</div>';
		$bundle .= c4d_woo_bundle_cart_view($cart_item, $cart_item_key);
	}

	return $name.$bundle;
}

function c4d_woo_bundle_checkout_item_quantity($qty, $cart_item, $cart_item_key) {
	if (isset($cart_item['bundle'])) {
		return '';
	}
	return $qty;
}
function c4d_woo_bundle_item_name__($meta) {
	global $c4d_plugin_manager;
	$name = '';
	$discountBundleOnly = $meta['discount_bundle_only'];
	$discountType = isset($c4d_plugin_manager['c4d-woo-bundle-discount-type']) ? $c4d_plugin_manager['c4d-woo-bundle-discount-type'] : 'percent';
	$discountType = $meta['percent_type'] == '' ? $discountType : $meta['percent_type'];
	$bundleDatas = c4d_woo_bundle_get_post_meta( $meta['product_id'], 'c4d_woo_bundle', true );
	$saveItems = isset($bundleDatas['items']) ? $bundleDatas['items'] : array();
	$bundlePrice = isset($saveItems['bundle_price']) && $saveItems['bundle_price'] !== '' ? $saveItems['bundle_price'] : '';
	$position = isset($saveItems['position']) ? $saveItems['position'] : 'outside';
	$class = 'c4d-woo-bundle-quantity-price ';
	$class .= ($meta['main_product']['percent'] > 0) ? '' : ' discount-0-percent ';
	$class .= ($discountType == 'price' && $bundlePrice == '') ? ' fixed-price ' : '';
	$class .= $discountBundleOnly ? ' discount-bundle-item-only ' : '';
	$percent = $meta['main_product']['percent'];
	$discount = $meta['main_product']['discount'];
	$price = $meta['main_product']['price'];
	$qty = $meta['main_product']['qty'];

	if ($bundlePrice !== '' || $position == 'inside') {
		if ($bundlePrice !== '') {
			$discount = $bundlePrice;
			if ($discountType == 'percent') {
				$discount = $bundlePrice - (($bundlePrice * $percent)/100);
				$percent = ' -' . trim($percent) . '%';
			} else {
				$discount = $bundlePrice - $percent;
				$percent = '-'. wc_price($percent);
			}
			$name .= ' <div class="'.$class.'">' . sprintf( ' <span class="c4d-woo-bundle-price-discount">%s</span> <span class="c4d-woo-bundle-price-original">%s</span> <span class="c4d-woo-bundle-price-percent">%s</span>', wc_price($discount), wc_price($bundlePrice), $percent) . '</div>';
		} else {
			$name .= '';
		}
	} elseif ($discountType == 'price') {
		$name .= ' <div class="'.$class.'">' . sprintf( '%s <span class="c4d-woo-bundle-quantity-x">&times;</span> <span class="c4d-woo-bundle-price-discount">%s</span> <span class="c4d-woo-bundle-price-original">%s</span> <span class="c4d-woo-bundle-price-percent">%s</span>', $qty, wc_price($discount), wc_price($price), '-'. wc_price($percent) ) . '</div>';
	} else if ($discountBundleOnly == 1 ) {
		$name .= ' <div class="'.$class.'">' . sprintf( '%s <span class="c4d-woo-bundle-quantity-x">&times;</span> <span class="c4d-woo-bundle-price-original">%s</span>', $qty, wc_price($price)) . '</div>';
	} else if ($discountBundleOnly == 0) {
		$name .= ' <div class="'.$class.'">' . sprintf( '%s <span class="c4d-woo-bundle-quantity-x">&times;</span> <span class="c4d-woo-bundle-price-discount">%s</span> <span class="c4d-woo-bundle-price-original">%s</span> <span class="c4d-woo-bundle-price-percent">%s</span>', $qty, wc_price($discount), wc_price($price), '-'.trim($percent).'%' ) . '</div>';
	}

	return $name;
}
function c4d_woo_bundle_order_item_name($name, $item, $is_visible) {
	$meta = wc_get_order_item_meta($item->get_id(), 'c4d_woo_bundle');
	$bundle = '';
	if ($meta && isset($meta['product_id'])) {
		if (isset($meta['main_product'])) {
			$bundle .= ' <strong class="product-quantity">('. esc_html__('this bundle', 'c4d-woo-bundle') . ' <span class="c4d-woo-bundle-quantity-x">×</span> '.$item->get_quantity().')</strong>';
			$bundle .= c4d_woo_bundle_item_name__($meta);
		}
	}
	return $name.$bundle;
}

function c4d_woo_bundle_order_item_quantity_html($qty, $item) {
	$meta = wc_get_order_item_meta($item->get_id(), 'c4d_woo_bundle');
	if ($meta && isset($meta['product_id'])) {
		return '';
	}

	return $qty;
}

function c4d_woo_bundle_order_bundle_view($item_id, $item, $order, $true) {
	$meta = wc_get_order_item_meta($item_id, 'c4d_woo_bundle');
	if ($meta && isset($meta['product_id'])) {
		echo c4d_woo_bundle_cart_view(array('bundle' => $meta));
	}
}

function c4d_woo_bundle_add_order_item_meta($item_id, $values)
{
  if(isset($values['bundle'])) {
    wc_add_order_item_meta($item_id,'c4d_woo_bundle', $values['bundle']);
  }
}

function c4d_woo_bundle_admin_order_itemmeta($item_id, $item, $product) {
	$meta = wc_get_order_item_meta($item_id, 'c4d_woo_bundle');
	if ($meta && isset($meta['product_id'])) {
		echo '<strong class="c4d-woo-bundle-admin-order-meta-label">'.esc_html__('Qty', 'c4d-woo-bundle').'</strong>: '.c4d_woo_bundle_main_product_price('', array('bundle' => $meta), array());
		echo c4d_woo_bundle_cart_view(array('bundle' => $meta));
	}
}

function c4d_woo_bundle_badge() {
	global $product, $c4d_plugin_manager;
	$bundleDatas = c4d_woo_bundle_get_post_meta( $product->get_ID(), 'c4d_woo_bundle', true );
	if ($bundleDatas) {
		if (isset($bundleDatas['ids'])) {
			if (isset($c4d_plugin_manager['c4d-woo-bundle-badge-show']) && $c4d_plugin_manager['c4d-woo-bundle-badge-show'] == 1) {
				$class = isset($c4d_plugin_manager['c4d-woo-bundle-badge-class']) ? $c4d_plugin_manager['c4d-woo-bundle-badge-class'] : '';
				$text = isset($c4d_plugin_manager['c4d-woo-bundle-badge-text']) ? $c4d_plugin_manager['c4d-woo-bundle-badge-text'] : esc_html__('Bundle', 'c4d-woo-bundle');
				if (isset($c4d_plugin_manager['c4d-woo-bundle-badge-price-show']) && $c4d_plugin_manager['c4d-woo-bundle-badge-price-show'] == 1) {

					if ($bundleDatas['items']['discount'] != '') {
						$discount = explode(',', $bundleDatas['items']['discount']);
						$discountType = isset($c4d_plugin_manager['c4d-woo-bundle-discount-type']) ? $c4d_plugin_manager['c4d-woo-bundle-discount-type'] : '';
						$discountType = $bundleDatas['items']['discount_type'] == '' ? $discountType : $bundleDatas['items']['discount_type'];
						if ($discountType == 'price') {
							$text = ' -' .wc_price($discount[0]);
						} else if ($discountType == 'percent') {
							$text = ' -' .end($discount) . '%';
						}
					}
				}
				echo sprintf( '<span class="c4d-woo-bundle-badge %s">%s</span>',
						esc_html( $class ),
						$text
				);
			}
		}
	}
}
function c4d_woo_bundle_view_bundle_button($link, $product, $args) {
	global $product, $c4d_plugin_manager;
	$bundleDatas = c4d_woo_bundle_get_post_meta( $product->get_ID(), 'c4d_woo_bundle', true );
	if ($bundleDatas) {
		if (isset($bundleDatas['ids'])) {
			if (isset($c4d_plugin_manager['c4d-woo-bundle-listing-view-bundle']) && $c4d_plugin_manager['c4d-woo-bundle-listing-view-bundle'] == 1) {
				$class = isset($c4d_plugin_manager['c4d-woo-bundle-listing-view-bundle-class']) ? $c4d_plugin_manager['c4d-woo-bundle-listing-view-bundle-class'] : '';
				$text = isset($c4d_plugin_manager['c4d-woo-bundle-listing-view-bundle-text']) ? $c4d_plugin_manager['c4d-woo-bundle-listing-view-bundle-text'] : esc_html__('View Bundle', 'c4d-woo-bundle');
				return sprintf( '<a class="c4d-woo-bundle-view-bundle-button %s" href="%s" %s>%s</a>',
					esc_html( $class ),
					esc_url( $product->get_permalink() ),
					isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
					esc_html( $text )
				);
			}
		}
	}
	return $link;
}