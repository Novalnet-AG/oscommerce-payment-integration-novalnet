
if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
		window.addEventListener('load', load);
	} else if (window.attachEvent) { // For IE 8 and earlier versions
		window.attachEvent('onload', load);
	}


function load() {
	
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
		 (cc_check.val()        == '' &&  cc_auth.val()      != 'authorize' ) ? cc_check.hide()      : '';
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
