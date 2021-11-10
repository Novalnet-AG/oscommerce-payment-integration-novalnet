/*
 * Novalnet Credit Card script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
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
                    generateHash(evt, formid);
                } else {
                    return true;
                }
            }
        }
    });


    jQuery('#novalnet_cc_new_acc').click(function() {
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
}


function getCCForm() {	
	 
	 var textObj   = {
        card_holder: {
            labelText: jQuery('#nn_cc_holder_text').val(),
            inputText: jQuery('#nn_cc_holder_placeholder').val(),
        },
        card_number: {
            labelText: jQuery('#nn_cc_no_text').val(),
            inputText: jQuery('#nn_cc_no_text_placeholder').val(),
        },
        expiry_date: {
            labelText: jQuery('#nn_cc_expiry_text').val(),
            inputText: jQuery('#nn_cc_expiry_text_placeholder').val(),
        },
        cvc: {
            labelText: jQuery('#nn_cc_cvc_text').val(),
            inputText: jQuery('#nn_cc_cvc_text_placeholder').val(),
        },
        cvcHintText: jQuery('#nn_cc_hint_text').val(),
        errorText: jQuery('#nn_cc_error_msg').val(),
    };
    
    var styleObj = {
        labelStyle: jQuery('#nn_cc_standard_style_label').val(),
        inputStyle: jQuery('#nn_cc_standard_style_input').val(),
        card_holder: {
            labelStyle: jQuery('#nn_cc_holder_label').val(),
            inputStyle: jQuery('#nn_cc_holder_textfield').val(),
        },
        card_number: {
            labelStyle: jQuery('#nn_cc_number_label').val(),
            inputStyle: jQuery('#nn_cc_number_textfield').val(),
        },
        expiry_date: {
            labelStyle: jQuery('#nn_cc_expiry_label').val(),
            inputStyle: jQuery('#nn_cc_expiry_textfield').val(),
        },
        cvc: {
            labelStyle: jQuery('#nn_cc_cvc_label').val(),
            inputStyle: jQuery('#nn_cc_cvc_textfield').val(),
        },
        styleText: jQuery('#nn_cc_standard_style_css').val()
    };

    var iframe      = document.getElementById('nnIframe').contentWindow;
    var targetOrgin = 'https://secure.novalnet.de';

    var requestObj = {
        callBack: 'createElements',
        customText: textObj,
        customStyle: styleObj
    }
    iframe.postMessage(requestObj, targetOrgin);

    var requestObj = {
        callBack: 'getHeight'
    }
    iframe.postMessage(requestObj, targetOrgin);

    window.addEventListener('message', function(e) {
        var data = eval('(' + e.data + ')');
        if (e.origin === targetOrgin) {
            if (data['callBack'] == 'getHeight') {				               
                document.getElementById('nnIframe').width = '483px';
            }
        }
    }, false);
}

function generateHash(evt, formid) {
    evt.preventDefault();
    var iframe      = document.getElementById('nnIframe').contentWindow;
    var targetOrgin = 'https://secure.novalnet.de';

    var messageObj = {
        callBack: 'getHash'
    }
    iframe.postMessage(messageObj, targetOrgin);

    window.addEventListener('message', function(e) {
        var data = eval('(' + e.data + ')');
        if(data['error_message'] != undefined) {
			alert(data['error_message']);
		}
        else if (data['callBack'] == 'getHash') {
            if (data['hash'] != '' && data['unique_id'] != '') {
                jQuery('#nn_cc_hash').val(data['hash']);
                jQuery('#nn_cc_uniqid').val(data['unique_id']);
                jQuery("form[name='" + formid + "']").submit();
            }
        }
    }, false);
}
