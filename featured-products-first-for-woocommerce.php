<?php

/**
 * Plugin Name: Featured Products First for WooCommerce
 * Plugin URI: https://.prashantwp.com/
 * Description: Places featured products listed first On Shop Page, Category Archive Page, and Search Page.
 * Version: 1.8.2
 * Author:Prashant Baldha - WooCommerce Woo Expert
 * Author URI: https://www.prashantwp.com/
 * Requires at least: 4.8.1
 * Tested up to: 5.7.2
 * Requires PHP: 5.6.0
 * Text Domain: featured-products-first-for-woocommerce
 * Domain Path: /languages/
 * WC requires at least: 4.0
 * WC tested up to: 6.0.0
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Featured Products First for WooCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Featured Products First for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Featured Products First for WooCommerce. If not, see <http://www.gnu.org/licenses/>.
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wff' ) ) {
    wff()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'wff' ) ) {
        
        if ( !function_exists( 'wff' ) ) {
            // Create a helper function for easy SDK access.
            function wff()
            {
                global  $wff ;
                
                if ( !isset( $wff ) ) {
                    // Activate multisite network integration.
                    if ( !defined( 'WP_FS__PRODUCT_1689_MULTISITE' ) ) {
                        define( 'WP_FS__PRODUCT_1689_MULTISITE', true );
                    }
                    // Include Freemius SDK.
                    require_once dirname( __FILE__ ) . '/freemius/start.php';
                    $wff = fs_dynamic_init( array(
                        'id'              => '1689',
                        'slug'            => 'featured-products-first-for-woocommerce',
                        'type'            => 'plugin',
                        'public_key'      => 'pk_fcce2e3f8c351f2e0dcdc012ba146',
                        'is_premium'      => false,
                        'premium_suffix'  => 'Pro',
                        'has_addons'      => false,
                        'has_paid_plans'  => true,
                        'trial'           => array(
                        'days'               => 30,
                        'is_require_payment' => true,
                    ),
                        'has_affiliation' => 'customers',
                        'menu'            => array(
                        'slug'       => 'featured-products-first-for-woocommerce',
                        'first-path' => 'admin.php?page=featured-products-first-for-woocommerce',
                    ),
                        'is_live'         => true,
                    ) );
                }
                
                return $wff;
            }
            
            // Init Freemius.
            wff();
            // Signal that SDK was initiated.
            do_action( 'wff_loaded' );
        }
    
    }
    // ... Your plugin's main file logic ...
    if ( !defined( 'WFF__FILE__' ) ) {
        define( 'WFF__FILE__', __FILE__ );
    }
    if ( !defined( 'WFF_DIR' ) ) {
        define( 'WFF_DIR', dirname( WFF__FILE__ ) );
    }
    require_once WFF_DIR . '/includes/helper.php';
    /**
     * Check if WooCommerce is active
     */
    if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        // return;
    }
    require_once WFF_DIR . '/includes/class-wff.php';
    if ( is_admin() ) {
        require_once WFF_DIR . '/includes/class-wff-admin.php';
    }
}
