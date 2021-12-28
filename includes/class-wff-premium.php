<?php

/**
 * @fs_premium_only
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
define( 'WFF_IS_PREMIUM', true );
class WFF_Premium
{
    /**
     * Constructor of class
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
    
    public function wff_no_of_featured_product_first_default_value( $default_value )
    {
        $default_value = 0;
        return $default_value;
    }

}
global  $wff_premium ;
$wff_premium = new WFF_Premium();