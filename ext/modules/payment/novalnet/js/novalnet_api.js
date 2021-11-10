/*
 * Novalnet API script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */
 
 
if (typeof(jQuery) == 'undefined' || typeof(jQuery) == undefined) {
    var s       = document.createElement("script");
    s.type      = "text/javascript";
    var nn_root = document.getElementById('nn_api_shoproot').value;
    s.src       = nn_root + "ext/modules/payment/novalnet/js/jquery.js";
    document.getElementsByTagName("head")[0].appendChild(s);
}
if (window.addEventListener) { 
    window.addEventListener("load", novalnet_api_call)
} else if (window.attachEvent) {
    window.attachEvent("load", novalnet_api_call)
}




function novalnet_api_call() {
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY]"]').attr('id', 'novalnet_public_key');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_VENDOR_ID]"]').attr('id', 'novalnet_vendor_id');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_AUTH_CODE]"]').attr('id', 'novalnet_auth_code');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PRODUCT_ID]"]').attr('id', 'novalnet_product_id');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_TARIFF_ID]"]').attr('id', 'novalnet_tariff_id');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY]"]').attr('id', 'novalnet_access_key');    
   
    jQuery('#novalnet_vendor_id,#novalnet_auth_code,#novalnet_product_id,#novalnet_access_key').attr('readonly', true);
    jQuery('#novalnet_public_key').change(function() { 
        get_merchant_details();
        return true;
    });  
    
    get_merchant_details();
	return true;    
}

function get_merchant_details() { 
    var server_ip   = jQuery("#server_ip").val();
    var remote_ip   = jQuery("#remote_ip").val();
    var public_key  = jQuery("#novalnet_public_key").val();
    var nn_language = jQuery("#nn_language").val();
    if (jQuery.trim(public_key) == '') { 
        null_basic_params();
        return false;
    }

    var datas = {
        "system_ip": server_ip,
	"remote_ip": remote_ip,
        "api_config_hash": public_key,
        "lang": nn_language
    }
    if ("XDomainRequest" in window && window.XDomainRequest != null) {
        var xdr = new XDomainRequest();
        xdr.post('POST', 'https://payport.novalnet.de/autoconfig');
        xdr.onload = function() {
            process_result(datas);
        }
        xdr.send($datas);
    } else {
        jQuery.ajax({
            type: 'POST',
            url: 'https://payport.novalnet.de/autoconfig',
            data: datas,
            success: function(data) {
                process_result(data);
            }
        });
    }
    return true;
}

function process_result(hash_string) {
    var saved_tariff_id = jQuery('#novalnet_tariff_id').val();
    jQuery('#novalnet_tariff_id').replaceWith('<select id="novalnet_tariff_id" name= "configuration[MODULE_PAYMENT_NOVALNET_TARIFF_ID]" ></select>');
    if (hash_string.tariff_id != undefined) {
        hash_string_tarrif_value = hash_string.tariff_id.split(',');
        hash_string_tarrif_name  = hash_string.tariff_name.split(',');
        hash_string_tarrif_type  = hash_string.tariff_type.split(',');
        for (i = 0; i < hash_string_tarrif_value.length; i++) {
            var hash_result_name = hash_string_tarrif_name[i].split(':');
            hash_result_name = (hash_result_name[2] != undefined) ? hash_result_name[1] + ':' + hash_result_name[2] : hash_result_name[1];
            var hash_result_val  = hash_string_tarrif_value[i].split(':');
            var hash_result_type = hash_string_tarrif_type[i].split(':');
            var tariff_val = hash_result_type[1] + '-' + hash_result_val[1].trim();
            jQuery('#novalnet_tariff_id').append(jQuery('<option>', {
                value: jQuery.trim(tariff_val),
                text: jQuery.trim(hash_result_name)
            }));
            if (saved_tariff_id != undefined && saved_tariff_id == tariff_val) {
                jQuery('#novalnet_tariff_id').val(tariff_val);
            }
        }
        jQuery('#novalnet_vendor_id').val(hash_string.vendor_id);
        jQuery('#novalnet_auth_code').val(hash_string.auth_code);
        jQuery('#novalnet_product_id').val(hash_string.product_id);
        jQuery('#novalnet_access_key').val(hash_string.access_key);
    } else {
        null_basic_params();         
        alert(hash_string.config_result);
    }
}

function null_basic_params() {
    jQuery('#novalnet_vendor_id, #novalnet_auth_code, #novalnet_product_id, #novalnet_access_key').val('');
    jQuery('#novalnet_tariff_id').replaceWith($('<input/>',{'type':'text', 'id' : 'novalnet_tariff_id'}));      
    jQuery('#novalnet_tariff_id').find('option').remove();      
}