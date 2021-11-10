/*
 * Novalnet Credit Card script
 * @author 		Novalnet AG <technic@novalnet.de>
 * @copyright 	Novalnet
 * @license 	https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */
if (typeof(jQuery) == 'undefined' || typeof(jQuery) == undefined) {
    var s       = document.createElement("script");
    s.type      = "text/javascript";
    var nn_root = document.getElementById('nn_root_cc_catalog').value;
    s.src       = nn_root + "ext/modules/payment/novalnet/js/jquery.js";
    document.getElementsByTagName("head")[0].appendChild(s);
}

if (window.addEventListener) { // For all major browsers, except IE 8 and earlier
    window.addEventListener("load", novalnet_cc_load);
} else if (window.attachEvent) { // For IE 8 and earlier versions
    window.attachEvent("onload", novalnet_cc_load);
}

function novalnet_cc_load() {
	
    var formid = '';
	jQuery('form').each(function() {
		if (jQuery(this).attr('name') == 'checkout_payment') {
			formid = jQuery(this).attr('name');
		}
	});

    jQuery("form[name='" + formid + "']").submit(function(evt) {
        var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();
        if (selected_payment == 'novalnet_cc') {
            if (jQuery('#novalnet_ccchange_account').length && jQuery('#novalnet_ccchange_account').val() == '1') {                
                jQuery("form[name='" + formid + "']").submit();
            } else {
                if (jQuery('#nn_cc_hash').val() == '' && jQuery('#nn_cc_uniqid').val() == '') {
                   NovalnetUtility.getPanHash();
                   return false;
                } else {
                    return true;
                }
            }
        }
    });

    jQuery('#novalnet_cc_new_acc').click(function() {
		NovalnetUtility.setCreditCardFormHeight();
        jQuery('#nn_cc_acc').css('display', 'block');
        jQuery('#novalnet_cc_new_acc').css('display', 'none');
        jQuery('#nn_cc_ref_details').css('display', 'none');
        jQuery('#novalnet_ccchange_account').val('0');        
    });

    jQuery('#novalnet_cc_given_acc').click(function() {
        jQuery('#nn_cc_acc').css('display', 'none');
        jQuery('#nn_cc_ref_details').css('display', 'block');
        jQuery('#novalnet_cc_new_acc').css('display', 'block');
        jQuery('#novalnet_ccchange_account').val('1');  
        jQuery('#novalnet_cc_new_acc').html('<u><b>' + jQuery('#nn_lang_cc_new_account').val() + '</b></u>');      
    });

    if (jQuery('#novalnet_ccchange_account').val() != undefined) {
        if (jQuery('#novalnet_ccchange_account').val() == 1 || jQuery('#novalnet_ccchange_account').val() == '') {
            jQuery('#nn_cc_acc').css('display', 'none');
            jQuery('#nn_cc_ref_details').css('display', 'block');
            jQuery('#novalnet_cc_new_acc').html('<u><b>' + jQuery('#nn_lang_cc_new_account').val() + '</b></u>');
        } else {
            jQuery('#nn_cc_acc').css('display', 'block');
            jQuery('#nn_cc_ref_details').css('display', 'none');
            jQuery('#novalnet_cc_new_acc').html('<u><b>' + jQuery('#nn_lang_cc_given_account').val() + '</b></u>');
        }
    }
    
	jQuery(document).ready(function () {
		getCCForm( formid );
	});

}

function getCCForm( formid ) {	
	
	var nn_cc_iframe_data  = jQuery('#nn_cc_iframe_data').val();
    var iframe_details = JSON.parse(nn_cc_iframe_data);
   
	NovalnetUtility.setClientKey( (iframe_details.client_key !== undefined) ? iframe_details.client_key : '');
	
	var configurationObject = {
	
		// You can handle the process here, when specific events occur.
		callback: {
		
			// Called once the pan_hash (temp. token) created successfully.
			on_success: function (data) {
				jQuery('#nn_cc_do_redirect').val(data['do_redirect']);
                jQuery('#nn_cc_hash').val(data['hash']);
                jQuery('#nn_cc_uniqid').val(data['unique_id']);
                jQuery("form[name='" + formid + "']").submit();
			},
			
			// Called in case of an invalid payment data or incomplete input. 
			on_error:  function (data) {
				if ( undefined !== data['error_message'] ) {
				  alert(data['error_message']);
				  return false;
				}
			},
			
			// Called in case the Challenge window Overlay (for 3ds2.0) displays 
			on_show_overlay:  function (data) {
				document.getElementById("nnIframe").classList.add("nn_cc_overlay");
			},
			
			// Called in case the Challenge window Overlay (for 3ds2.0) hided
			on_hide_overlay:  function (data) {
				document.getElementById("nnIframe").classList.remove("nn_cc_overlay");
			}
		},
		
		// You can customize your Iframe container styel, text etc. 
		iframe: {
		
			// It is mandatory to pass the Iframe ID here.  Based on which the entire process will took place.
			id: "nnIframe",
			
			// Set to 1 to make you Iframe input container more compact (default - 0)
			inline: 0,
			
			// Add the style (css) here for either the whole Iframe contanier or for particular label/input field
			style: {
				// The css for the Iframe container
				container: jQuery('#nn_cc_standard_style_css').val(),
				
				// The css for the input field of the Iframe container
				input: jQuery('#nn_cc_standard_style_input').val(),
				
				// The css for the label of the Iframe container
				label: jQuery('#nn_cc_standard_style_label').val()
			},
		},
		
		// Add Customer data
		customer: {
		
			// Your End-customer\'s First name which will be prefilled in the Card Holder field
			first_name: (iframe_details.first_name !== undefined) ? iframe_details.first_name : '',
			
			// Your End-customer\'s Last name which will be prefilled in the Card Holder field
			last_name: (iframe_details.last_name !== undefined) ? iframe_details.last_name : '',
			
			// Your End-customer\'s Email ID. 
			email: (iframe_details.email !== undefined) ? iframe_details.email : '',
			
			// Your End-customer\'s billing address.
			billing: {
			
				// Your End-customer\'s billing street (incl. House no).
				street: (iframe_details.street !== undefined) ? iframe_details.street : '',
				
				// Your End-customer\'s billing city.
				city: (iframe_details.city !== undefined) ? iframe_details.city : '',
				
				// Your End-customer\'s billing zip.
				zip: (iframe_details.zip !== undefined) ? iframe_details.zip : '',
				
				// Your End-customer\'s billing country ISO code.
				country_code: (iframe_details.country_code !== undefined) ? iframe_details.country_code : ''
			}
		},
		
		// Add transaction data
		transaction: {
		
			// The payable amount that can be charged for the transaction (in minor units), for eg:- Euro in Eurocents (5,22 EUR = 522).
			amount: (iframe_details.amount !== undefined) ? iframe_details.amount : '',
			
			// The three-character currency code as defined in ISO-4217.
			currency: (iframe_details.currency !== undefined) ? iframe_details.currency : '',
			
			// Set to 1 for the TEST transaction (default - 0).
			test_mode: (iframe_details.test_mode !== undefined) ? iframe_details.test_mode : 0,
			
			enforce_3d: (iframe_details.enforce_3d !== undefined) ? iframe_details.enforce_3d : ''
		},
		custom: {
			
			// Shopper\'s selected language in shop
			lang: (iframe_details.lang !== undefined) ? iframe_details.lang : ''
		}
	};
	
	// Create the Credit Card form
	NovalnetUtility.createCreditCardForm(configurationObject);

}


