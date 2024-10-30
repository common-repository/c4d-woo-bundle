<?php
/**
 * Bundle product options.
 *
 * @package WooCommerce/admin
 */

defined( 'ABSPATH' ) || exit;
global $post, $thepostid, $product_object;
$bundleDatas = c4d_woo_bundle_get_post_meta( $product_object->get_id(), 'c4d_woo_bundle', true );
$saveItems = (isset($bundleDatas['items']) && is_array($bundleDatas['items'])) ? $bundleDatas['items'] : array();
$selectedOptions = '';
$bundleItemsHtml = '';

if (is_array($bundleDatas) && isset($bundleDatas['ids'])) {
	foreach ($bundleDatas['ids'] as $key => $product_id) {

		$product = wc_get_product( $product_id );
		if ( is_object( $product ) ) {
			$fieldName = 'c4d_woo_bundle['.$product_id.']';
			$classItem = 'product-image-' .$product_id;
			$id = 'label-for-id-'.$product_id;
			$item = isset($saveItems[$product_id]) ? $saveItems[$product_id] : false;
			$premiumOnly = '';
			// selceted options html
			$selectedOptions .= '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . c4d_woo_bundle_value('title', wp_kses_post( $product->get_title(), $item)) . '</option>';
			if ($key >= 3) {
				$premiumOnly = 'premium-only';
			}
			// display list bundled html
			$bundleItemsHtml .= '<div id="'.$classItem.'" class="c4d-woo-bundle-item '.$premiumOnly.'" data-id="'.$product_id.'">';

			$bundleItemsHtml .= '<h3 class="item-title">
													<div class="item-title-link">
														<span class="item-image">' . $product->get_image('woocommerce_thumbnail', array(), true ) . '</span>
														#'. $product_id . ' - ' . c4d_woo_bundle_value('title', wp_kses_post( $product->get_title(), $item)) .' - ' . $product->get_price_html();
			$bundleItemsHtml .= '</div>';
			if ($key >= 3) {
			 	$bundleItemsHtml .= '<a target="blank" href="http://coffee4dev.com/woocommerce-product-bundle/" class="c4d-woo-bundle-premium-only">'.esc_html__('Premium Only', 'c4d-woo-bundle').'</a>';
			}
			$bundleItemsHtml .= '<div class="item-buttons">
														<a target="blank" href="post.php?post='.$product_id.'&action=edit" class="button item-view">'.esc_html__('Edit', 'c4d-woo-bundle').'</a>
														<span class="button item-remove">'.esc_html__('Remove', 'c4d-woo-bundle').'</span>
													</div>';
			$bundleItemsHtml .= '</h3>';

			$bundleItemsHtml .= '<div class="item-panel">';

				$bundleItemsHtml .= '<div class="item-left-col"><div class="item-left-col-inner">';

				$bundleItemsHtml .= '<div class="item-data">
											<div>
												<strong>'.esc_html__('Init Check', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-init-check-0"><input id="'.$id.'-init-check-0" name="'.$fieldName.'[init_check]" type="radio" value="" '.checked(c4d_woo_bundle_value('init_check', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-init-check-1"><input id="'.$id.'-init-check-1" name="'.$fieldName.'[init_check]" type="radio" value="1" '.checked(c4d_woo_bundle_value('init_check', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-init-check-2"><input id="'.$id.'-init-check-2" name="'.$fieldName.'[init_check]" type="radio" value="0" '.checked(c4d_woo_bundle_value('init_check', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				$bundleItemsHtml .= '<div class="item-data">
										<div><strong>'.esc_html__('Title', 'c4d-woo-bundle').':</strong> <input type="text" name="'.$fieldName.'[title]" placeholder="'.wp_kses_post( $product->get_title()).'" value="' . c4d_woo_bundle_value('title', '', $item ) . '"></div>
										<div>
											<strong>'.esc_html__('Show Title', 'c4d-woo-bundle').':</strong>
											<label for="'.$id.'-show-title-0">
												<input id="'.$id.'-show-title-0" name="'.$fieldName.'[show_title]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_title', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
											<label for="'.$id.'-show-title-1">
												<input id="'.$id.'-show-title-1" name="'.$fieldName.'[show_title]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_title', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
											<label for="'.$id.'-show-title-2">
												<input id="'.$id.'-show-title-2" name="'.$fieldName.'[show_title]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_title', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
										</div>
								</div>';

				$bundleItemsHtml .= '<div class="item-data">
											<div><strong>'.esc_html__('Short Description', 'c4d-woo-bundle').':</strong> <textarea name="'.$fieldName.'[description]" placeholder="'.wp_kses_post( $product->get_short_description()).'">' . c4d_woo_bundle_value('description', '', $item ) . '</textarea>
											</div>
											<div>
												<strong>'.esc_html__('Show Description', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-show-desc-0"><input id="'.$id.'-show-desc-0" name="'.$fieldName.'[show_desc]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_desc', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-desc-1"><input id="'.$id.'-show-desc-1" name="'.$fieldName.'[show_desc]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_desc', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-desc-2"><input id="'.$id.'-show-desc-2" name="'.$fieldName.'[show_desc]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_desc', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				$bundleItemsHtml .= '<div class="item-data">
											<div>
												<strong>'.esc_html__('Show Category', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-show-cate-0"><input id="'.$id.'-show-cate-0" name="'.$fieldName.'[show_cate]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_cate', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-cate-1"><input id="'.$id.'-show-cate-1" name="'.$fieldName.'[show_cate]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_cate', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-cate-2"><input id="'.$id.'-show-cate-2" name="'.$fieldName.'[show_cate]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_cate', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				$bundleItemsHtml .= '<div class="item-data">
											<div>
												<strong>'.esc_html__('Show Attributes', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-show-attr-0"><input id="'.$id.'-show-attr-0" name="'.$fieldName.'[show_attr]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_attr', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-attr-1"><input id="'.$id.'-show-attr-1" name="'.$fieldName.'[show_attr]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_attr', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-attr-2"><input id="'.$id.'-show-attr-2" name="'.$fieldName.'[show_attr]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_attr', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';
				$bundleItemsHtml .= '<div class="item-data">
											<div>
												<strong>'.esc_html__('Show Taxonomy', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-show-tax-0"><input id="'.$id.'-show-tax-0" name="'.$fieldName.'[show_tax]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_tax', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-tax-1"><input id="'.$id.'-show-tax-1" name="'.$fieldName.'[show_tax]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_tax', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-tax-2"><input id="'.$id.'-show-tax-2" name="'.$fieldName.'[show_tax]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_tax', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				$bundleItemsHtml .= '</div></div>'; // end left col;
				$bundleItemsHtml .= '<div class="item-right-col"><div class="item-right-col-inner">';

				$bundleItemsHtml .= '<div class="item-data">
											<div><strong>Qty:</strong> <input name="'.$fieldName.'[qty]" type="text" value="'.c4d_woo_bundle_value('qty', 1, $item).'"></div>
											<div>
												<strong>'.esc_html__('Show Qty', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-show-qty-0"><input id="'.$id.'-show-qty-0" name="'.$fieldName.'[show_qty]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_qty', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-qty-1"><input id="'.$id.'-show-qty-1" name="'.$fieldName.'[show_qty]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_qty', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-qty-2"><input id="'.$id.'-show-qty-2" name="'.$fieldName.'[show_qty]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_qty', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				$bundleItemsHtml .= '<div class="item-data">
											<div><strong>Regular Price:</strong> <input name="'.$fieldName.'[price]" type="text" value="'.c4d_woo_bundle_value('price', '', $item).'"></div>
										</div>';

				$bundleItemsHtml .= '<div class="item-data">
											<div><strong>Sale Price:</strong> <input name="'.$fieldName.'[sale]" type="text" value="'.c4d_woo_bundle_value('sale', '', $item).'"></div>
										</div>';

				$bundleItemsHtml .= '<div class="item-data">
											<div>
												<strong>'.esc_html__('Show Variable', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-show-variable-0"><input id="'.$id.'-show-varialbe-0" name="'.$fieldName.'[show_variable]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_variable', '', $item), '' , false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-variable-1"><input id="'.$id.'-show-varialbe-1" name="'.$fieldName.'[show_variable]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_variable', '', $item), 1 , false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-variable-2"><input id="'.$id.'-show-varialbe-2" name="'.$fieldName.'[show_variable]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_variable', '', $item), 0 , false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				if('variable' == $product->get_type())	{
					$bundleItemsHtml .= '<div class="item-data">
										<div>
											<strong>'.esc_html__('Set Default Variable', 'c4d-woo-bundle').':</strong>';
					$attributes = $product->get_variation_attributes();
					foreach ($attributes as $attribute => $options) {
						$setted = isset($item['set_variable']) ? $item['set_variable'] : array();
						$bundleItemsHtml .= '<label><select name="'.$fieldName.'[set_variable]['.sanitize_title($attribute).']">';
						$bundleItemsHtml .= '<option value="-1">'.esc_html__('Not Set', 'c4d-woo-bundle').'</option>';
						foreach ($options as $key => $option) {
							$bundleItemsHtml .= '<option value="'.sanitize_title($option).'" '.selected(c4d_woo_bundle_value(sanitize_title($attribute), '-1', $setted), sanitize_title($option) , false).'>'.$option.'</option>';
						}
						$bundleItemsHtml .= '</select></label>';
					}
					$bundleItemsHtml .= '</div></div>';
				}

				$bundleItemsHtml .= '<div class="item-data">
											<div class="">
												<strong>'.esc_html__('Must Included', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-must-buy-1"><input id="'.$id.'-must-buy-1" name="'.$fieldName.'[must_by]" type="radio" value="1" '.checked(c4d_woo_bundle_value('must_by', 0, $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-must-buy-2"><input id="'.$id.'-must-buy-2" name="'.$fieldName.'[must_by]" type="radio" value="0" '.checked(c4d_woo_bundle_value('must_by', 0, $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				$bundleItemsHtml .= '<div class="item-data">
											<div>
												<strong>'.esc_html__('Show Thumbnail', 'c4d-woo-bundle').':</strong>
												<label for="'.$id.'-show-thumb-0"><input id="'.$id.'-show-thumb-0" name="'.$fieldName.'[show_thumbnail]" type="radio" value="" '.checked(c4d_woo_bundle_value('show_thumbnail', '', $item), '', false).'> '.esc_html__('Global', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-thumb-1"><input id="'.$id.'-show-thumb-1" name="'.$fieldName.'[show_thumbnail]" type="radio" value="1" '.checked(c4d_woo_bundle_value('show_thumbnail', '', $item), 1, false).'> '.esc_html__('Yes', 'c4d-woo-bundle').'</label>
												<label for="'.$id.'-show-thumb-2"><input id="'.$id.'-show-thumb-2" name="'.$fieldName.'[show_thumbnail]" type="radio" value="0" '.checked(c4d_woo_bundle_value('show_thumbnail', '', $item), 0, false).'> '.esc_html__('No', 'c4d-woo-bundle').'</label>
											</div>
									</div>';

				$bundleItemsHtml .= '</div></div>';// end right col
			$bundleItemsHtml .= '</div>';// end panel
			$bundleItemsHtml .= '</div>';// end wrap

		}
	}
}
?>
<div id="c4d-woo-bundle-tab-panel" class="panel woocommerce_options_panel hidden">

	<div class="options_group">
		<p class="form-field">
			<label for="grouped_products"><strong><?php esc_html_e( 'Search Products', 'c4d-woo-bundle' ); ?></strong></label>
			<select class="wc-product-search short" multiple="multiple" style="width: 50%;" id="c4d_woo_bundle_grouped_products" name="c4d_woo_bundle_ids[]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'c4d-woo-bundle' ); ?>" data-action="woocommerce_json_search_products" data-exclude="<?php echo intval( $post->ID ); ?>">
				<?php echo $selectedOptions; ?>
			</select> <?php echo wc_help_tip( __( 'This lets you choose which products are part of this bundle.', 'c4d-woo-bundle' ) ); // WPCS: XSS ok. ?>
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Inside Main Product', 'c4d-woo-bundle' ); ?></strong></label>
				<label class="c4d-woo-bundle-label" for="inside-main-product-1">
					<input id="inside-main-product-1" type="radio" name="c4d_woo_bundle[position]" value="inside" <?php echo checked(c4d_woo_bundle_value('position', 'outside', $saveItems), 'inside', false) ?>> <?php esc_html_e('Inside', 'c4d-woo-bundle'); ?>
				</label>
				<label class="c4d-woo-bundle-label" for="inside-main-product-2">
					<input id="inside-main-product-2" type="radio" name="c4d_woo_bundle[position]" value="outside" <?php echo checked(c4d_woo_bundle_value('position', 'outside', $saveItems), 'outside', false) ?>> <?php esc_html_e('Outside', 'c4d-woo-bundle'); ?>
				</label>
				<?php echo wc_help_tip( __( 'Inside: bundle is show like main product. Outside: bundle is show like buy option.', 'c4d-woo-bundle' ) ); ?>
		</p>
		<p class="form-field">
			<label><strong><?php esc_html_e( 'Bundle Price', 'c4d-woo-bundle' ); ?></strong></label>
			<input type="text" name="c4d_woo_bundle[bundle_price]" class="short" value="<?php echo c4d_woo_bundle_value('bundle_price', '', $saveItems); ?>">
			<?php echo wc_help_tip( __( 'Set a fixed price for this bundle. Other price\'s items will be ignored. Leave blank if you want auto calculate by price\'s items'  , 'c4d-woo-bundle' ) ); ?>
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Discount', 'c4d-woo-bundle' ); ?></strong></label>
			<input type="text" name="c4d_woo_bundle[discount]" class="short" value="<?php echo c4d_woo_bundle_value('discount', '', $saveItems); ?>">
			<?php echo wc_help_tip( __( 'Set discount for this bundle. Ex: one discount for all: 5, Ex: multi discount for buy from 3 products: 5, 10, 15', 'c4d-woo-bundle' ) ); ?>
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Discount Type', 'c4d-woo-bundle' ); ?></strong></label>
				<label class="c4d-woo-bundle-label" for="discount-type-0">
					<input id="discount-type-0" type="radio" name="c4d_woo_bundle[discount_type]" value="" <?php echo checked(c4d_woo_bundle_value('discount_type', '', $saveItems), '', false) ?>> <?php esc_html_e('Global', 'c4d-woo-bundle'); ?>
				</label>
				<label class="c4d-woo-bundle-label" for="discount-type-1">
					<input id="discount-type-1" type="radio" name="c4d_woo_bundle[discount_type]" value="percent" <?php echo checked(c4d_woo_bundle_value('discount_type', '', $saveItems), 'percent', false) ?>> <?php esc_html_e('Percent', 'c4d-woo-bundle'); ?>
				</label>
				<label class="c4d-woo-bundle-label" for="discount-type-2">
					<input id="discount-type-2" type="radio" name="c4d_woo_bundle[discount_type]" value="price" <?php echo checked(c4d_woo_bundle_value('discount_type', '', $saveItems), 'price', false) ?>> <?php esc_html_e('Price', 'c4d-woo-bundle'); ?>
				</label>
				<label class="c4d-woo-bundle-label" for="discount-type-user-role">
            <input id="discount-type-user-role" type="radio" name="c4d_woo_bundle[discount_type]" value="user_role" <?php echo checked(c4d_woo_bundle_value('discount_type', '', $saveItems), 'user_role', false) ?>> <?php esc_html_e('User Role', 'c4d-woo-bundle'); ?>
         </label>
				<?php echo wc_help_tip( __( 'Default discount by percent(%), check this box if you want change to price or global setting. If you set this value is price, so you have to set discount to one value.', 'c4d-woo-bundle' ) ); ?>
		</p>

    <div class="discount-type-user-role">
      <p class="form-field ">
        <label><strong><?php esc_html_e( 'Discount User', 'c4d-woo-bundle' ); ?></strong></label>
        <?php
          $editableRoles = array_reverse( get_editable_roles() );
          $userRoles = array();

          foreach ( $editableRoles as $role => $details ) {
              $name = translate_user_role($details['name'] );

              $userRoles[] = array('value' => $role, 'text' => $name);
              echo '<label class="c4d-woo-bundle-label">';
              echo $name . ': ';
              echo '<input class="small-width" type="text" name="c4d_woo_bundle[role_'.esc_attr($role).']" value="'.c4d_woo_bundle_value('role_'. esc_attr($role), '0', $saveItems).'">';
              echo '</label>';
          }
        ?>
      </p>
      <p class="form-field">
        <label><strong><?php esc_html_e( 'Discount User Type', 'c4d-woo-bundle' ); ?></strong></label>
          <label class="c4d-woo-bundle-label" for="discount-user-type-0">
            <input id="discount-user-type-0" type="radio" name="c4d_woo_bundle[discount_user_type]" value="" <?php echo checked(c4d_woo_bundle_value('discount_user_type', 'percent', $saveItems), 'price', false) ?>> <?php esc_html_e('Price', 'c4d-woo-bundle'); ?>
          </label>
          <label class="c4d-woo-bundle-label" for="discount-user-type-1">
            <input id="discount-user-type-1" type="radio" name="c4d_woo_bundle[discount_user_type]" value="percent" <?php echo checked(c4d_woo_bundle_value('discount_user_type', 'percent', $saveItems), 'percent', false) ?>> <?php esc_html_e('Percent', 'c4d-woo-bundle'); ?>
          </label>
      </p>
    </div>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Discount For Bundle Items Only', 'c4d-woo-bundle' ); ?></strong></label>
				<label class="c4d-woo-bundle-label" for="discount-for-bundle-only-1">
					<input id="discount-for-bundle-only-1" type="radio" name="c4d_woo_bundle[discount_bundle_only]" value="1" <?php echo checked(c4d_woo_bundle_value('discount_bundle_only', '0', $saveItems), '1', false) ?>> <?php esc_html_e('Yes', 'c4d-woo-bundle'); ?>
				</label>
				<label class="c4d-woo-bundle-label" for="discount-for-bundle-only-2">
					<input id="discount-for-bundle-only-2" type="radio" name="c4d_woo_bundle[discount_bundle_only]" value="0" <?php echo checked(c4d_woo_bundle_value('discount_bundle_only', '0', $saveItems), '0', false) ?>> <?php esc_html_e('No', 'c4d-woo-bundle'); ?>
				</label>
				<?php echo wc_help_tip( __( 'Apply discount for bundle items, do not apply for main product', 'c4d-woo-bundle' ) ); ?>
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Title', 'c4d-woo-bundle' ); ?></strong></label>
				<input type="text" class="short" name="c4d_woo_bundle[title]" value="<?php echo c4d_woo_bundle_value('title', '', $saveItems); ?>" placeholder="<?php esc_html_e( 'Buy this bundle and get off to 30%', 'c4d-woo-bundle' ); ?>" />
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Description', 'c4d-woo-bundle' ); ?></strong></label>
				<textarea class="short" name="c4d_woo_bundle[description]" placeholder="<?php esc_html_e( 'Buy more save more. Save 15% when you purchase 3 products, save 10% when you purchase 2 products', 'c4d-woo-bundle' ); ?>"/><?php echo c4d_woo_bundle_value('description', '', $saveItems); ?></textarea>
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Apply Date', 'c4d-woo-bundle' ); ?></strong></label>
				<input type="text" class="short c4d-woo-bundle-date-picker" name="c4d_woo_bundle[start_date]" value="<?php echo c4d_woo_bundle_value('start_date', '', $saveItems); ?>" placeholder="<?php esc_html_e( 'From&hellip;', 'c4d-woo-bundle' ); ?> YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
				<input type="text" class="short c4d-woo-bundle-date-picker" name="c4d_woo_bundle[end_date]" value="<?php echo c4d_woo_bundle_value('end_date', '', $saveItems); ?>" placeholder="<?php esc_html_e( 'To&hellip;', 'c4d-woo-bundle' ); ?>  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Show Countdown Time', 'c4d-woo-bundle' ); ?></strong></label>
				<label class="c4d-woo-bundle-label" for="countdown-time-1">
					<input id="countdown-time-1" type="radio" name="c4d_woo_bundle[countdown]" value="1" <?php echo checked(c4d_woo_bundle_value('countdown', '0', $saveItems), '1', false) ?>> <?php esc_html_e('Yes', 'c4d-woo-bundle'); ?>
				</label>
				<label class="c4d-woo-bundle-label" for="countdown-time-2">
					<input id="countdown-time-2" type="radio" name="c4d_woo_bundle[countdown]" value="0" <?php echo checked(c4d_woo_bundle_value('countdown', '0', $saveItems), '0', false) ?>> <?php esc_html_e('No', 'c4d-woo-bundle'); ?>
				</label>
				<?php echo wc_help_tip( __( 'Show count down base on end date. If end date is not set, show count down clock for 3 days', 'c4d-woo-bundle' ) ); ?>
		</p>

		<p class="form-field">
			<label><strong><?php esc_html_e( 'Count Down Title', 'c4d-woo-bundle' ); ?></strong></label>
				<input type="text" class="short" name="c4d_woo_bundle[countdown_title]" value="<?php echo c4d_woo_bundle_value('countdown_title', '', $saveItems); ?>" placeholder="<?php esc_html_e( 'Hurry Up! Last time to order', 'c4d-woo-bundle' ); ?>" />
		</p>

	</div>
	<div class="options_group">
		<p class="form-field">
			<?php echo $bundleItemsHtml; ?>
		</p>
	</div>
</div>
