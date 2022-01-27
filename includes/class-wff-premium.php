<?php

/**
 * @fs_premium_only
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WFF_Premium
{
    /**
     * Constructor of class.
     * responsible for add all actions and filters hooks for Premium
     */
    public function __construct()
    {
    }
    
    public function wff_featured_products_ids_sort( $product_ids )
    {
        $no_of_featured_product_first = 5;
        if ( $no_of_featured_product_first > 0 ) {
            $product_ids = array_slice( $product_ids, 0, $no_of_featured_product_first );
        }
        $ordering_args = WC()->query->get_catalog_ordering_args();
        $orderby = $ordering_args['orderby'];
        $order = $ordering_args['order'];
        // $ordering_args[meta_key]
        switch ( $orderby ) {
            case 'id':
                $args['orderby'] = self::order_products_ids_by_field( $product_ids, 'ID', $order );
                break;
            case 'menu_order':
                $sorted_product_ids = self::order_products_ids_by_field( $product_ids, 'menu_order, post_title', ( 'DESC' === $order ? 'DESC' : 'ASC' ) );
                break;
            case 'title':
                $sorted_product_ids = self::order_products_ids_by_field( $product_ids, 'post_title', ( 'DESC' === $order ? 'DESC' : 'ASC' ) );
                break;
            case 'rand':
                shuffle( $product_ids );
                $sorted_product_ids = $product_ids;
                break;
            case 'date':
                $sorted_product_ids = self::order_products_ids_by_field( $product_ids, 'post_date, ID', $order );
                break;
            case 'price':
                
                if ( 'ASC' === $order ) {
                    $sorted_product_ids = self::order_products_ids_by_product_meta_lookup( $product_ids, 'min_price', 'DESC' );
                } else {
                    $sorted_product_ids = self::order_products_ids_by_product_meta_lookup( $product_ids, 'max_price', 'ASC' );
                }
                
                break;
            case 'popularity':
                // Not Working
                $sorted_product_ids = $product_ids;
                // $sorted_product_ids = self::order_products_ids_by_product_meta_lookup( $product_ids, 'total_sales', 'DESC' );
                break;
            case 'rating':
                // Not Working
                $sorted_product_ids = self::order_products_ids_by_product_meta_lookup( $product_ids, 'average_rating', 'DESC' );
                $sorted_product_ids = self::order_products_ids_by_product_meta_lookup( $sorted_product_ids, 'rating_count', 'DESC' );
                break;
                /*
                case 'relevance' :
                   $args['orderby'] = 'relevance';
                   $args['order']   = 'DESC';
                   break;
                */
            /*
            case 'relevance' :
               $args['orderby'] = 'relevance';
               $args['order']   = 'DESC';
               break;
            */
            default:
                $sorted_product_ids = $product_ids;
        }
        return $sorted_product_ids;
    }
    
    private function order_products_ids_by_meta( $product_ids, $meta_key, $order = 'ASC' )
    {
        if ( empty($product_ids) || empty($meta_key) ) {
            return $product_ids;
        }
        global  $wpdb ;
        $product_ids_for_sql = implode( ',', array_map( 'absint', $product_ids ) );
        $sorted_product_ids = $wpdb->get_col( $wpdb->prepare(
            'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE post_id IN ( %0s ) AND meta_key=%s ORDER BY meta_value %0s',
            $product_ids_for_sql,
            $meta_key,
            $order
        ) );
        return $sorted_product_ids;
    }
    
    /**
     * Sort by looking in wc_product_meta_lookup table
     *
     * @param array  $product_ids Products ids which need to be sorted
     * @param string $field product_meta_lookup field
     * @param string $order either ASC or DESC
     *
     * @return array sorted product ids
     */
    private function order_products_ids_by_product_meta_lookup( $product_ids, $field, $order = 'ASC' )
    {
        if ( empty($product_ids) || empty($field) ) {
            return $product_ids;
        }
        global  $wpdb ;
        $product_ids_for_sql = implode( ',', array_map( 'absint', $product_ids ) );
        $sorted_product_ids = $wpdb->get_col( $wpdb->prepare(
            'SELECT product_id FROM ' . $wpdb->wc_product_meta_lookup . ' WHERE product_id	IN ( %0s ) ORDER BY %0s %0s, product_id DESC',
            $product_ids_for_sql,
            $field,
            $order
        ) );
        return $sorted_product_ids;
    }
    
    private function get_orderby_value()
    {
        $orderby_value = ( isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) ) );
        return $orderby_value;
    }
    
    public function wff_no_of_featured_product_first_default_value( $default_value )
    {
        $default_value = 0;
        return $default_value;
    }

}
global  $wff_premium ;
$wff_premium = new WFF_Premium();