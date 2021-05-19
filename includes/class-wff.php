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
        add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ) );
        register_activation_hook( WFF__FILE__, array( $this, 'activation' ) );
        add_filter(
            'plugin_action_links_' . plugin_basename( WFF__FILE__ ),
            array( $this, 'plugin_action_links' ),
            10,
            2
        );
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 999 );
        add_filter(
            'posts_orderby',
            array( $this, 'posts_orderby' ),
            9999,
            2
        );
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
        global  $wff_woo_product_orders ;
        $wff_woo_product_orders = apply_filters( 'woocommerce_default_catalog_orderby_options', array(
            'menu_order' => __( 'Default sorting (custom ordering + name)', 'featured-products-first-for-woocommerce' ),
            'popularity' => __( 'Popularity (sales)', 'featured-products-first-for-woocommerce' ),
            'rating'     => __( 'Average rating', 'featured-products-first-for-woocommerce' ),
            'date'       => __( 'Sort by most recent', 'featured-products-first-for-woocommerce' ),
            'price'      => __( 'Sort by price (asc)', 'featured-products-first-for-woocommerce' ),
            'price-desc' => __( 'Sort by price (desc)', 'featured-products-first-for-woocommerce' ),
        ) );
    }
    
    /**
     * Function to set fetured first widget and WooCommerce action
     * @since  0.1
     */
    public function woocommerce_loaded()
    {
        require_once WFF_DIR . '/includes/widgets/class-wff-widget-fetured-product.php';
        foreach ( array( 'woocommerce_delete_product_transients', 'woocommerce_update_options_featured_products_first' ) as $action_hook_to_delete_cache ) {
            add_action( $action_hook_to_delete_cache, array( $this, 'delete_cache' ) );
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
        global  $wff_woo_product_orders ;
        add_option( 'wff_woocommerce_featured_first_enabled_on_shop', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_on_search', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_on_archive', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_on_admin', 'yes' );
        add_option( 'wff_woocommerce_featured_first_enabled_everywhere', 'yes' );
        foreach ( $wff_woo_product_orders as $woo_product_order_key => $woo_product_order_label ) {
            add_option( 'wff_woocommerce_featured_first_sort_on_' . $woo_product_order_key, 'yes' );
        }
        if ( !wff()->is_plan( 'pro' ) || !wff()->is_trial() ) {
            if ( false === get_option( 'woocommerce_default_catalog_orderby', false ) ) {
                add_option( 'woocommerce_default_catalog_orderby', 'menu_order' );
            }
        }
    }
    
    public function plugin_action_links( $links )
    {
        $links[] = '<a href="' . WFF_SETTING_PAGE_URL . '">' . __( 'Settings', 'featured-products-first-for-woocommerce' ) . '</a>';
        return $links;
    }
    
    /**
     * Function to set fetured products first in product list 
     * @since  0.1
     */
    public function pre_get_posts( $query )
    {
        //This function is for old woocommerce
        //If woocommerce version is latest
        //then return as it is
        if ( version_compare( WC()->version, 3.0 ) > 0 ) {
            return $query;
        }
        
        if ( !empty($query->query_vars['wc_query']) && $query->query_vars['wc_query'] == 'product_query' && (get_option( 'wff_woocommerce_featured_first_enabled_on_shop' ) == 'yes' && empty($query->query_vars['s']) || get_option( 'wff_woocommerce_featured_first_enabled_on_search' ) == 'yes' && !empty($query->query_vars['s']) || get_option( 'wff_woocommerce_featured_first_enabled_on_archive' ) == 'yes' && empty($query->query_vars['s']) && is_tax()) && (!empty($query->query_vars['orderby']) && $query->query_vars['orderby'] == 'menu_order title' && !empty($query->query_vars['order']) && $query->query_vars['order'] == 'ASC') ) {
            $query->set( 'meta_key', '_featured' );
            $query->set( 'orderby', "meta_value " . $query->get( 'orderby' ) );
            $query->set( 'order', "DESC " . $query->get( 'order' ) );
        }
        
        return $query;
    }
    
    public function posts_orderby( $order_by, $query )
    {
        global  $wpdb ;
        //This function is for new woocommerce
        //If woocommerce version is latest
        //then return as it is
        if ( version_compare( WC()->version, 3.0 ) <= 0 ) {
            return $order_by;
        }
        $orderby_value = ( isset( $_GET['orderby'] ) ? wc_clean( (string) $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) ) );
        $orderby_value_array = explode( '-', $orderby_value );
        $orderby = esc_attr( $orderby_value_array[0] );
        $order = ( !empty($orderby_value_array[1]) ? $orderby_value_array[1] : 'ASC' );
        
        if ( apply_filters( 'wff_is_featured_product_first_order_applicable', $query->is_main_query() && $query->is_archive && (!empty($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'product' || 'yes' == get_option( 'wff_woocommerce_featured_first_enabled_on_archive' ) && is_tax( get_object_taxonomies( 'product', 'names' ) )) && (get_option( 'wff_woocommerce_featured_first_enabled_on_shop' ) == 'yes' && empty($query->query_vars['s']) || get_option( 'wff_woocommerce_featured_first_enabled_on_search' ) == 'yes' && !empty($query->query_vars['s']) || get_option( 'wff_woocommerce_featured_first_enabled_on_archive' ) == 'yes' && empty($query->query_vars['s']) && is_tax()) && (!defined( 'WFF_IS_PREMIUM' ) && (!empty($query->query_vars['orderby']) && $query->query_vars['orderby'] == 'menu_order title' && !empty($query->query_vars['order']) && $query->query_vars['order'] == 'ASC' || ($orderby == 'relevance' || empty($orderby)) && ($order == 'DESC' || $order == 'ASC')) || defined( 'WFF_IS_PREMIUM' ) && WFF_IS_PREMIUM && apply_filters( 'wff_is_featured_product_first_order_applicable_on_main_query', false, $query )), $query ) ) {
            $feture_product_id = wff_get_featured_product_ids();
            if ( is_array( $feture_product_id ) && !empty($feture_product_id) ) {
                
                if ( empty($order_by) ) {
                    $order_by = "FIELD(" . $wpdb->posts . ".ID,'" . implode( "','", $feture_product_id ) . "') DESC ";
                } else {
                    $order_by = "FIELD(" . $wpdb->posts . ".ID,'" . implode( "','", $feture_product_id ) . "') DESC, " . $order_by;
                }
            
            }
        }
        
        return $order_by;
    }

}
$GLOBALS['wff_main'] = new WFF();