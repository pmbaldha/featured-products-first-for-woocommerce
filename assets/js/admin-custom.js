// JavaScript Document
jQuery( document ).ready(function($) {
	if(  $('#wff_woocommerce_featured_first_enabled_everywhere').length ) {
		setTimeout(function(){ window.scrollTo(0, document.body.scrollHeight); }, 500);
	}
	if ( $('.wff-pro-upgrade-desc').length ) {
		$('.wff-pro-upgrade-desc').parents('label').css('color', '#C0C0C0');
	}
    
	$( '#wff_woocommerce_featured_first_enabled_everywhere, #wff_woocommerce_featured_first_enabled_on_shop, #wff_woocommerce_featured_first_enabled_on_shortcode, #wff_woocommerce_featured_first_sort_on_menu_order, #wff_woocommerce_no_of_featured_product_first').parents('tr').addClass("highlight");

	$('#wff_woocommerce_featured_first_enabled_everywhere').trigger( 'change' );
	if(  $('#wff_woocommerce_featured_first_enabled_everywhere').length && !$('#wff_woocommerce_featured_first_enabled_everywhere').is(":disabled") ) {
		$('#wff_woocommerce_featured_first_enabled_everywhere').change( function(){
			if( !$(this).is(":checked") ) {
				$( '#wff_woocommerce_featured_first_enabled_on_shop, #wff_woocommerce_featured_first_enabled_on_shortcode, #wff_woocommerce_featured_first_sort_on_menu_order' ).parents('tr').fadeIn( 'fast' );
			}
			else {
				$( '#wff_woocommerce_featured_first_enabled_on_shop, 	#wff_woocommerce_featured_first_enabled_on_shortcode, #wff_woocommerce_featured_first_sort_on_menu_order' ).parents('tr').fadeOut( 'fast' );
			}			
		});
	}
});
