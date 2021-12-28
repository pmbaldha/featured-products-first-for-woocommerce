<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class WFF_Put_Featured_Product_First {

	/**
	 * Constructor of class
	 * responsible for add all actions and filters hooks for Premium
	 */
	public function __construct() {
			if( is_admin() ) {
				// add_filter( 'admin_init', array($this, 'wff_premium_admin_init'));
			}
			add_action( 'pre_get_posts', array($this, 'pre_get_posts') );
			add_filter( 'wff_is_featured_product_first_order_applicable', array($this, 'wff_is_featured_product_first_order_applicable'), 10, 2);

			// add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 20, 2 );
			add_filter( 'woocommerce_shortcode_products_query', array( $this, 'woocommerce_shortcode_products_query' ), 10, 2 );
	}
	
	public function pre_get_posts( $query ) {
		$is_product_post_type = isset( $query->query_vars[ 'post_type' ] )  &&  'product' == $query->query_vars[ 'post_type' ];
		$is_product_post_type_front_side = $is_product_post_type && !is_admin();
		$is_product_post_type_admin_side = $is_product_post_type && is_admin();
		$is_product_archive_page =   is_tax( array( 'product_cat', 'product_tag' ) );

		if ( ! ( $is_product_post_type_front_side || $is_product_post_type_admin_side || $is_product_archive_page ) )  {
			return;
		}

		$is_featured_product_first = false;
		if ( 'yes' == get_option( 'wff_woocommerce_featured_first_enabled_everywhere' ) )  {
			// echo '1';
			$is_featured_product_first = true;
		} elseif ( 'yes' == get_option( 'wff_woocommerce_featured_first_enabled_on_archive' ) && $is_product_archive_page ) {
			// echo '2';
			$is_featured_product_first = true;
		} elseif ( get_option( 'wff_woocommerce_featured_first_enabled_on_admin' ) == 'yes' && $is_product_post_type_admin_side ) {
			// echo '3';
			$is_featured_product_first = true;
		} elseif ( get_option( 'wff_woocommerce_featured_first_enabled_on_shop' ) == 'yes' && $is_product_post_type_front_side && empty( $query->query_vars[ 's' ] ) ) {
			// echo '4';
			$is_featured_product_first = true;
		} elseif ( get_option( 'wff_woocommerce_featured_first_enabled_on_search' ) == 'yes' &&  ! empty( $query->query_vars[ 's' ] ) ) {
			// echo '5';
			$is_featured_product_first = true;
		}

		if ( $is_featured_product_first ) {
			$query->set( 'is_featured_product_first', true );
		}
	}

	public function posts_clauses( $clauses, $query ) {
		
		 if( apply_filters('wff_is_featured_product_first_order_applicable',
				$query->is_main_query() && $query->is_archive &&   
				(
					(
						!empty($query->query_vars[ 'post_type' ])
						&&
						$query->query_vars[ 'post_type' ] == 'product' 
					)
				||
					(
					'yes' == get_option('wff_woocommerce_featured_first_enabled_on_archive')
					&&
					is_tax(  get_object_taxonomies( 'product', 'names' ) )
					)
				)
			&&
			(
				( get_option( 'wff_woocommerce_featured_first_enabled_on_shop' ) == 'yes' &&  empty( $query->query_vars[ 's' ] ) )		
				||
				( get_option( 'wff_woocommerce_featured_first_enabled_on_search' ) == 'yes' &&  !empty( $query->query_vars[ 's' ] ) )
				||
				( get_option( 'wff_woocommerce_featured_first_enabled_on_archive' ) == 'yes' &&  empty( $query->query_vars[ 's' ] ) && is_tax())
			 )
			 , $query ) 
			) {
				global $wff_woo_product_orders;
				$special_orderby_array = array_keys( $wff_woo_product_orders );
				$orderby_value = self::get_orderby_value();
				if (
					in_array($orderby_value, $special_orderby_array) 
					&&
					(
						'yes' == get_option('wff_woocommerce_featured_first_sort_on_'.$orderby_value)
						||
						(
						!empty($query->query_vars[ 'is_featured_product_first' ])
								&&
						$query->query_vars[ 'is_featured_product_first' ]
						)
					)
				 ) {
				
					// Do not display featured product first when "Admin Dashboard Product Listing" checkbox setting is not checked
					if ( is_admin() && 'no' == get_option( 'wff_woocommerce_featured_first_enabled_on_admin', 'no') ) {
						return $clauses;
					} 
			
					global $wpdb;
					
					$feature_product_ids = wff_get_featured_product_ids();
					if( is_array( $feature_product_ids ) && !empty($feature_product_ids) ) {
						if( empty( $clauses['orderby'] ) ) {
								$clauses['orderby'] = "FIELD(".$wpdb->posts.".ID,'".implode("','",$feature_product_ids)."') DESC ";
						} else {
							$clauses['orderby'] =  "FIELD(".$wpdb->posts.".ID,'".implode("','",$feature_product_ids)."') DESC, " . $clauses['orderby'];
						}
					}
				}
			}
		return $clauses;
	}

	/**
	 * Modifying WooCommerce' product query filter based on $orderby value given
	 * @see WC_Query->get_catalog_ordering_args()
	 */
	function woocommerce_shortcode_products_query( $args, $atts ) {
		if ( 'yes' == get_option( 'wff_woocommerce_featured_first_enabled_everywhere' )  || 'yes' == get_option( 'wff_woocommerce_featured_first_enabled_on_shortcode' ) ) {
			$args['is_featured_product_first'] = true;
		}
		return $args;
	}


}

global $wff_put_feature_product_first;
$wff_put_feature_product_first = new WFF_Put_Featured_Product_First();
		
