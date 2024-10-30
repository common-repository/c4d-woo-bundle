<?php
add_action('manage_product_posts_custom_column', 'c4d_woo_bundle_table_product_title', 10, 2);

function c4d_woo_bundle_table_product_title($column, $pid) {
	if ($column == 'name') {
		$bundle = get_post_meta( $pid, 'c4d_woo_bundle', true );
		$ids = isset($bundle['ids']) ? $bundle['ids'] : array();
		if (count($ids) > 0) {
			echo '['.esc_html__('Bundle', 'c4d-woo-bundle').'] ';
		}
	}
}