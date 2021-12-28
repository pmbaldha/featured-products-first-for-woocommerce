<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Main plugin class
 */
class WFF
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setup_constants();
        $this->setup_global_vars();
        add_action( 'woocommerce_loaded', [ $this, 'woocommerce_loaded' ] );
        register_activation_hook( WFF__FILE__, [ $this, 'activation' ] );
        add_filter(
            'plugin_action_links_' . plugin_basename( WFF__FILE__ ),
            [ $this, 'plugin_action_links' ],
            10,
            2
        );
        // The post_orderby filter hook is not working for popularity and average_rating sorting, so I have used post_clauses hook.
        add_filter(
            'posts_clauses',
            array( $this, 'posts_clauses' ),
            20,
            2
        );
        require_once plugin_dir_path( __FILE__ ) . '/class-wff-put-featured-product-first.php';
    }
    
    private function setup_constants()
    {
        if ( !defined( 'WFF_URL' ) ) {
            define( 'WFF_VERSION', '1.8.1' );
        }
        if ( !defined( 'WFF_URL' ) ) {
            define( 'WFF_URL', plugins_url( '', WFF__FILE__ ) );
        }
        if ( !defined( 'WFF_SETTING_PAGE_URL' ) ) {
            define( 'WFF_SETTING_PAGE_URL', get_admin_url( null, 'admin.php?page=wc-settings&tab=featured_products_first' ) );
        }
    }
    
    private function setup_global_vars()
    {
        /*global $wff_woo_product_orders;
        		$wff_woo_product_orders = apply_filters(
        			'woocommerce_default_catalog_orderby_options',
        			[
        				'menu_order' => __( 'Default sorting (custom ordering + name)',
        					'featured-products-first-for-woocommerce' ),
        				'popularity' => __( 'Popularity (sales)', 'featured-products-first-for-woocommerce' ),
        				'rating'     => __( 'Average rating', 'featured-products-first-for-woocommerce' ),
        				'date'       => __( 'Sort by most recent', 'featured-products-first-for-woocommerce' ),
        				'price'      => __( 'Sort by price (asc)', 'featured-products-first-for-woocommerce' ),
        				'price-desc' => __( 'Sort by price (desc)', 'featured-products-first-for-woocommerce' ),
        			]
        		);*/
    }
    
    /**
     * Function to set featured first widget and WooCommerce action
     * @since  0.1
     */
    public function woocommerce_loaded()
    {
        require_once WFF_DIR . '/includes/widgets/class-wff-widget-featured-product.php';
        foreach ( [ 'woocommerce_delete_product_transients', 'woocommerce_update_options_featured_products_first' ] as $action_hook_to_delete_cache ) {
            add_action( $action_hook_to_delete_cache, [ $this, 'delete_cache' ] );
        }
    }
    
    public function delete_cache()
    {
        delete_transient( 'wff_featured_products_ids' );
    }
    
    /**
     * Function to set the default settings
     * @since  0.1
     */
    public function activation()
    {
        // global $wff_woo_product_orders;
        add_option( 'wff_woocommerce_featured_first_enabled_on_shop', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_on_search', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_on_archive', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_on_admin', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_everywhere', 'yes' );
        /*
        foreach ( $wff_woo_product_orders as $woo_product_order_key => $woo_product_order_label ) {
        	add_option( 'wff_woocommerce_featured_first_sort_on_' . $woo_product_order_key, 'yes' );
        }
        */
    }
    
    public function plugin_action_links( $links )
    {
        $links[] = '<a href="' . WFF_SETTING_PAGE_URL . '">' . __( 'Settings', 'featured-products-first-for-woocommerce' ) . '</a>';
        return $links;
    }
    
    public function posts_clauses( $clauses, $query )
    {
        if ( !isset( $query->query_vars['is_featured_product_first'] ) || !$query->query_vars['is_featured_product_first'] ) {
            return $clauses;
        }
        if ( version_compare( WC()->version, 3.0 ) <= 0 ) {
            return $clauses;
        }
        global  $wpdb ;
        $feature_product_id = wff_get_featured_product_ids();
        if ( is_array( $feature_product_id ) && !empty($feature_product_id) ) {
            
            if ( empty($clauses['orderby']) ) {
                $clauses['orderby'] = "FIELD(" . $wpdb->posts . ".ID,'" . implode( "','", $feature_product_id ) . "') DESC ";
            } else {
                $clauses['orderby'] = "FIELD(" . $wpdb->posts . ".ID,'" . implode( "','", $feature_product_id ) . "') DESC, " . $clauses['orderby'];
            }
        
        }
        return $clauses;
    }

}
global  $wff_main ;
$wff_main = new WFF();