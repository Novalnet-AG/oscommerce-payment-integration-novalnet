/*
 * Novalnet Authorize script
 * @author 		Novalnet AG <technic@novalnet.de>
 * @copyright 	Novalnet
 * @license 	https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
		window.addEventListener('load', load);
	} else if (window.attachEvent) { // For IE 8 and earlier versions
		window.attachEvent('onload', load);
	}


function load() {
	
	jQuery('input[type="text"]').on('keyup',function(e){
        let selected_name   =   jQuery(this).attr('name'); 
        var str = this.value.toString();
		this.value = str.replace(/<(?!\s*\/?(b|i|u|p)\b)[^>]+>/ig,"");
    });
	
	var invoice_check = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT]');
	var invoice_auth  = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE]']:checked");
	var sepa_check    = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_SEPA_LIMIT]'); 
	var sepa_auth     = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_SEPA_AUTHENTICATE]']:checked");
	var cc_check      = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT]');
	var cc_auth       = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE]']:checked");
	var paypal_check  = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT]');
	var paypal_auth   = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE]']:checked");

	(invoice_check.val()   == '' && invoice_auth.val()  != 'authorize' ) ? invoice_check.hide() : ''; 
	(sepa_check.val()      == '' && sepa_auth.val()     != 'authorize' ) ? sepa_check.hide()    : '' ;
	(cc_check.val()        == '' && cc_auth.val()      != 'authorize' ) ? cc_check.hide()      : '';
	(paypal_check.val()    == '' && paypal_auth.val()   != 'authorize')  ? paypal_check.hide()  : '';
		 
$(document).ready(function(){
        $("input[type='radio']").click(function(){
    var invoice_check = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT]');
	var invoice_auth  = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE]']:checked");
	var sepa_check    = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_SEPA_LIMIT]'); 
	var sepa_auth     = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_SEPA_AUTHENTICATE]']:checked");
	var cc_check      = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT]');
	var cc_auth       = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE]']:checked");
	var paypal_check  = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT]');
	var paypal_auth   = $("input[name='configuration[MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE]']:checked");
    
		invoice_auth.val() ==  'authorize'  ?  invoice_check.show()  : (invoice_auth.val() == 'capture'  ?   invoice_check.hide() : '') ;
		 sepa_auth.val()   ==  'authorize'  ?  sepa_check.show() :  (sepa_auth.val()   == 'capture'  ?   sepa_check.hide() : '') ; 
		 cc_auth.val()     ==  'authorize'  ?  cc_check.show() : (cc_auth.val()     == 'capture'  ?   cc_check.hide() : '') ;
      paypal_auth.val()    ==  'authorize'  ?  paypal_check.show() : (paypal_auth.val()    == 'capture'  ?   paypal_check.hide() : '') ;
		 
				
        });
        
    });
}
