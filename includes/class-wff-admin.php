<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class
 */
class Wff_Admin {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 60 );
		add_filter( 'gettext', array( $this, 'gettext' ), 10, 3 );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'woocommerce_settings_tabs_array' ), 90 );
		add_action( 'woocommerce_settings_tabs_featured_products_first',
			array( $this, 'woocommerce_settings_tabs_featured_products_first' ) );
		add_action( 'woocommerce_update_options_featured_products_first',
			array( $this, 'woocommerce_update_options_featured_products_first' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function admin_menu() {
		/*
		add_menu_page(
			__( 'Featured Product First For WooCommerce', 'featured-products-first-for-woocommerce' ),
			__( 'Featured Product First', 'featured-products-first-for-woocommerce' ),
			'manage_options',
			'featured-products-first-for-woocommerce',
			array( $this, 'settings_help_page' ),
			'dashicons-tag',
			56
		);
		*/


		add_submenu_page(
			'woocommerce',
			__( 'Featured Product First For WooCommerce', 'featured-products-first-for-woocommerce' ),
			__( 'Featured Product First', 'featured-products-first-for-woocommerce' ),
			'manage_options',
			'featured-products-first-for-woocommerce',
			array( $this, 'settings_help_page' ),
			9999
		// 'dashicons-tag',
		);


	}

	public function settings_help_page() {
		?>
        <div class="wrap">
        <h1 class="wp-heading-inline">
			<?php
			echo __( 'Featured Product First For WooCommerce', 'featured-products-first-for-woocommerce' );
			?>
        </h1>

        <div style="width:800px;">
            <h2>
				<?php
				echo __( 'Getting started', 'featured-products-first-for-woocommerce' );
				?>
            </h2>
            <p>
				<?php
				printf(
					__( 'To configure featured product first, Please %1$s click here%2$s (%3$sAdmin Dashboard > WooCommerce > Settings > Products%4$s). You will find screen as below:',
						'featured-products-first-for-woocommerce' ),
					'<strong><a href="' . WFF_SETTING_PAGE_URL . '">',
					'</a></strong>',
					'<strong>',
					'</strong>'
				);
				?>
                <br/><br/>
                <img src="
					<?php
				echo WFF_URL . '/assets/images/settings.png';
				?>
		" style="width: 800px; max-width: 100%;"/>
            </p>

            <p>
				<?php
				echo __( 'Thank You for using Featured Product First For WooCommerce',
					'featured-products-first-for-woocommerce' );
				?>
            </p>
			<?php

			if ( wff()->is_not_paying() ) {
				?>
                <p>
                    <strong>
						<?php
						printf( __( 'Free version of plugin displays featured product first on shop, archive and search page with woocommerce default sorting. If you would like to sorting everyewhere, Please %1$sUpgrade Now!%2$s',
							'featured-products-first-for-woocommerce' ), '<a href="' . wff()->get_upgrade_url() . '">',
							'</a>' );
						?>
                    </strong>
                </p>
                <p>
                    <strong>
						<?php
						printf( __( '%1$sPro version%2$s of plugin can display featured product first on any sorting order',
							'featured-products-first-for-woocommerce' ), '<a href="' . wff()->get_upgrade_url() . '">',
							'</a>' );
						?>
                    </strong>
                </p>

				<?php
			}
			?>
            <p>
                <strong>
					<?php
					printf( __( 'If you face any trouble, Please feel free to email me on %s any time. I am always happy to help you.',
						'featured-products-first-for-woocommerce' ),
						'<a href="mailto:prashant@prashantwp.com">prashant@prashantwp.com</a>' );
					echo '<br/>';
					printf( __( 'Please feel free to open support ticket on %s, If you found any issue.',
						'featured-products-first-for-woocommerce' ),
						'<a href="https://wordpress.org/support/plugin/featured-products-first-for-woocommerce">' . __( 'Support Forum',
							'featured-products-first-for-woocommerce' ) . '</a>' );
					?>
                </strong>
            </p>
            </p>
        </div>

		<?php
	}

	public function gettext( $translation, $text, $domain ) {
		if ( 'woocommerce' == $domain ) {
			switch ( $text ) {
				case 'How should products be sorted in the catalog by default?':
					$translation .= apply_filters( 'wff_pro_upgrade_desc',
							'<br/><span class="wff-pro-upgrade-desc"><strong>' . sprintf( __( 'Free "Featured Product First for Woocommerce" Plugin displays feaured products first with Default sorting (custom ordering + name). <br/>Pro version  Plugin displays feaured products first with any sorting order. To take benifits of this feature, Please %1$sUpgrade Now%2$s.',
								'featured-products-first-for-woocommerce' ),
								'<a href="' . wff()->get_upgrade_url() . '">', '</a>' ) ) . '</strong></span>';
					break;
			}
		}

		return $translation;
	}

	/**
	 * Add setting tab in the WooCommerce setting page
	 *
	 * @param array $settings_tabs WooCommerce seyying tabs array
	 *
	 * @return array $settings_tabs WooCommerce seyying tabs array
	 */
	public function woocommerce_settings_tabs_array( $settings_tabs ) {
		$settings_tabs['featured_products_first'] = __( 'Featured Product First',
			'featured-products-first-for-woocommerce' );

		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses self::get_settings()
	 */
	public function woocommerce_settings_tabs_featured_products_first() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses self::get_settings()
	 */
	public function woocommerce_update_options_featured_products_first() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Function to set fetured first settings
	 *
	 * @since  0.1
	 */
	public function get_settings() {
		/**
		 * Check the current section is what we want
		 */
		if ( empty( $current_section ) || 'general' == $current_section ) {
			$pro_custom_attributes = apply_filters(
				'wff_pro_custom_attributes',
				array(
					'disabled' => 'disabled',
				)
			);
			$pro_upgrade_desc      = apply_filters( 'wff_pro_upgrade_desc',
					'&nbsp;<span class="wff-pro-upgrade-desc">' . sprintf( __( 'Pro version feature!! To use this feature, Please %1$sUpgrade Now%2$s.',
						'featured-products-first-for-woocommerce' ), '<a href="' . wff()->get_upgrade_url() . '">',
						'</a>' ) ) . '</span>';
			$settings[]            = array(
				'title' => __( 'Featured Product First', 'featured-products-first-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'wff_options',
			);
			$settings[]            = array(
				'title'   => esc_html__( 'Enable Featured Products First Everywhere',
					'featured-products-first-for-woocommerce' ),
				'desc'    => '<br/>' . __( 'If you will tick this option, The featured product will be displayed first everywhere. There are not needs to configure any other option, so other options related featured product are hidden when you tick this option.',
						'featured-products-first-for-woocommerce' ) . '<strong>' . __( 'We highly recommended to tick this checkbox',
						'featured-products-first-for-woocommerce' ) . '</strong>',
				'id'      => 'wff_woocommerce_featured_first_enabled_everywhere',
				'default' => 'yes',
				'type'    => 'checkbox',
			);
			$settings[]            = array(
				'title'         => esc_html__( 'Enable Featured Products First on Page',
					'featured-products-first-for-woocommerce' ),
				'desc'          => esc_html__( 'Shop Page', 'featured-products-first-for-woocommerce' ),
				'id'            => 'wff_woocommerce_featured_first_enabled_on_shop',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
			);
			$settings[]            = array(
				'title'         => esc_html__( 'Enable Featured Products First on Page',
					'featured-products-first-for-woocommerce' ),
				'desc'          => esc_html__( 'Product Search Page', 'featured-products-first-for-woocommerce' ),
				'id'            => 'wff_woocommerce_featured_first_enabled_on_search',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
			);
			$settings[]            = array(
				'title'         => esc_html__( 'Enable Featured Products First on Page',
					'featured-products-first-for-woocommerce' ),
				'desc'          => esc_html__( 'Archive Product Category Page',
					'featured-products-first-for-woocommerce' ),
				'id'            => 'wff_woocommerce_featured_first_enabled_on_archive',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
			);
			$settings[]            = array(
				'title'         => esc_html__( 'Enable Featured Products First on Page',
					'featured-products-first-for-woocommerce' ),
				'desc'          => esc_html__( 'Admin Dashboard Product Listing Page',
					'featured-products-first-for-woocommerce' ),
				'id'            => 'wff_woocommerce_featured_first_enabled_on_admin',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
			);

			$settings[] = array(
				'title'             => esc_html__( 'Place no. of featured product first',
					'featured-products-first-for-woocommerce' ),
				'desc'              => '<br/>' . esc_html__( '0 or empty = Unlimited, otherwise Specify any number more than 0',
						'featured-products-first-for-woocommerce' ) . $pro_upgrade_desc,
				'id'                => 'wff_woocommerce_no_of_featured_product_first',
				'default'           => apply_filters( 'wff_no_of_featured_product_first_default_value', 5 ),
				'type'              => 'number',
				'custom_attributes' => $pro_custom_attributes,
			);
			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'wff_options',
			);
		}

		return $settings;
	}

	/**
	 * Function for enqueue javascript
	 * provides easyness to admin section
	 *
	 * @since  0.1
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'woocommerce_page_wc-settings' != $hook ) {
			return;
		}

		if ( true || ! empty( $_GET['tab'] ) && 'featured_products_first' == $_GET['tab'] ) {
			wp_enqueue_style(
				'wff-admin-custom-css',
				WFF_URL . '/assets/css/admin-custom.css',
				array(),
				WFF_VERSION
			);
			wp_enqueue_script(
				'wff-admin-custom-js',
				WFF_URL . '/assets/js/admin-custom.js',
				array( 'jquery' ),
				WFF_VERSION
			);
		}
	}
}

$GLOBALS['wff_admin'] = new Wff_Admin();
