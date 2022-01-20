<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wff_get_featured_product_ids' ) ) {
	function wff_get_featured_product_ids() {

		// Load from cache.
		$featured_product_ids = get_transient( 'wff_featured_products_ids' );

		// Valid cache found.
		if ( false !== $featured_product_ids ) {
			return apply_filters( 'wff_featured_products_ids_sort', $featured_product_ids );
		}

		$product_visibility_term_ids = wc_get_product_visibility_term_ids();
		$featured_products_ids       = get_posts(
			array(
				'post_type'      => array( 'product', 'product_variation' ),
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'term_taxonomy_id',
						'terms'    => array( $product_visibility_term_ids['featured'] ),
					),
				),
				'fields'         => 'ids',
			)
		);

		$featured_products_ids          = array_reverse( $featured_products_ids );
		$filtered_featured_products_ids = apply_filters( 'wff_featured_products_ids', $featured_products_ids );

		set_transient( 'wff_featured_products_ids', $filtered_featured_products_ids, DAY_IN_SECONDS * 30 );

		return apply_filters( 'wff_featured_products_ids_sort', $filtered_featured_products_ids );
	}
}
