/*
 * Novalnet PayPal script
 * @author 		Novalnet AG <technic@novalnet.de>
 * @copyright 	Novalnet
 * @license 	https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
if (typeof(jQuery) == 'undefined' || typeof(jQuery) == undefined) {
    var s       = document.createElement("script");
    s.type      = "text/javascript";    
    var nn_root = document.getElementById('nn_root_paypal_catalog').value;
    s.src       = nn_root + "ext/modules/payment/novalnet/js/jquery.js";
    document.getElementsByTagName("head")[0].appendChild(s);
}

jQuery(document).ready(function() {
	
	novalnet_pp_message();
	
	jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE]"]').click(function() {
		novalnet_pp_message();
    });
    
    jQuery('#novalnet_paypal_new_acc').click(function() {
        jQuery('#nn_paypal_ref_details').css('display', 'none');
        jQuery('#novalnet_paypal_new_acc').css('display', 'none');
        jQuery('#nn_paypal_ref_account').css('display', 'block');
        jQuery('#nn_paypal_transaction_type').val('1');
        var normal_paypal_desc = jQuery('#nn_paypal_normal_desc').text();        
        jQuery('#nn_paypal_desc').text(normal_paypal_desc);
    });

    jQuery('#nn_paypal_ref_account').click(function() {
        jQuery('#nn_paypal_ref_details').css('display', 'block');
        jQuery('#novalnet_paypal_new_acc').css('display', 'block');
        jQuery('#nn_paypal_ref_account').css('display', 'none');
        jQuery('#nn_paypal_transaction_type').val('0');
        var normal_paypal_oneclick_desc = jQuery('#nn_paypal_one_click_desc').val();        
        jQuery('#nn_paypal_desc').text(normal_paypal_oneclick_desc);
    });
});

function novalnet_pp_message() {
	var novalnet_pp_shop_type = jQuery("input[name='configuration[MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE]']:checked").val();
	if (novalnet_pp_shop_type =='ZEROAMOUNT' ||	novalnet_pp_shop_type =='ONECLICK'){
		var message = jQuery('#nn_pp_message').val();
		jQuery('#paypal_message') .text(message);
	} else {
		jQuery('#paypal_message') .text('');
	}
}
