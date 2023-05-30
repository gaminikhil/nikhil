/**
 * Data Tracking Notice.
 *
 * @namespace payment_gateway_based_fees_and_discounts
 * @since 2.6.3
 */
jQuery( document ).ready( function() {
	jQuery( '.cf-pro-tracker' ).on( 'click', '.notice-dismiss', function() {
		let data = { admin_choice: 'dismissed', action: 'cf_pro_admin_choice' };
		jQuery.post( cf_dismiss_params.ajax_url, data, function() {});
	});
});