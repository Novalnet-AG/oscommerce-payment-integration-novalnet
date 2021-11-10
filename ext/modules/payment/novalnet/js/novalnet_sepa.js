/*
 * Novalnet Direct Debit SEPA Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */

if (typeof(jQuery) == 'undefined' || typeof(jQuery) == undefined) {
    var s       = document.createElement("script");
    s.type      = "text/javascript";
    var nn_root = document.getElementById('nn_root_sepa_catalog').value;
    s.src       = nn_root + "ext/modules/payment/novalnet/js/jquery.js";
    document.getElementsByTagName("head")[0].appendChild(s);
}

if (window.addEventListener) { // For all major browsers, except IE 8 and earlier
    window.addEventListener('load', novalnet_sepa_load);
} else if (window.attachEvent) { // For IE 8 and earlier versions
    window.attachEvent('onload', novalnet_sepa_load);
}

function novalnet_sepa_load() {
    jQuery('#novalnet_sepa_iban,#novalnet_sepa_bic').removeAttr('name');

    var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();
    
    separefillformcall();
    

    jQuery('#novalnet_sepa_mandate_confirm').click(function() {
        var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();
        if (!jQuery('#novalnet_sepa_mandate_confirm').is(':checked')) {
            return true;
        }
        if (selected_payment == undefined || selected_payment != 'novalnet_sepa') {
            alert(jQuery('#nn_lang_choose_payment_method').val());
            return false;
        }
        if (jQuery('#nn_sepa_acc').css('display') == 'block') {
            sepaibanbiccall();
        }
    });
    jQuery('#novalnet_sepa_new_acc').click(function() {
        var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();
        if (selected_payment == 'novalnet_sepa') {
            separefillformcall();
        }
        if (jQuery('#nn_sepa_acc').css('display') == 'none') {
            jQuery('#nn_sepa_acc').css('display', 'block');
            jQuery('#nn_sepa_ref_details').css('display', 'none');
            jQuery('#novalnet_sepachange_account').val('1');
            jQuery('#novalnet_sepa_new_acc').html('<u><b>' + jQuery('#nn_lang_given_account').val() + '</b></u>');
        } else {
            jQuery('#nn_sepa_acc').css('display', 'none');
            jQuery('#nn_sepa_ref_details').css('display', 'block');
            jQuery('#novalnet_sepachange_account').val('0');
            jQuery('#novalnet_sepa_new_acc').html('<u><b>' + jQuery('#nn_lang_new_account').val() + '</b></u>');
        }

    });
    if (jQuery('#nn_sepa_shopping_type') != undefined && jQuery('#nn_sepa_shopping_type').val() == 'ONECLICK' && jQuery('#novalnet_sepachange_account') != undefined && jQuery('#novalnet_sepachange_account').val() == 0 && jQuery('#payment_ref_details').val() != '') {
        jQuery('#nn_sepa_acc').css('display', 'none');
        jQuery('#nn_sepa_ref_details').css('display', 'block');
        jQuery('#novalnet_sepa_new_acc').html('<u><b>' + jQuery('#nn_lang_new_account').val() + '</b></u>');
    } else {
        jQuery('#nn_sepa_acc').css('display', 'block');
        if (jQuery('#nn_sepa_ref_details').length > 0)
            jQuery('#nn_sepa_ref_details').css('display', 'none');

        jQuery('#novalnet_sepa_new_acc').html('<u><b>' + jQuery('#nn_lang_given_account').val() + '</b></u>');
    }
    jQuery('#novalnet_sepa_iban, #novalnet_sepa_bic, #novalnet_sepa_bank_country, #novalnet_sepa_account_holder').on('change', function() {
        sepa_mandate_unconfirm_process();
    });
}

function sepa_mandate_unconfirm_process() {
    jQuery('#nn_sepa_hash').val('');
    jQuery('#novalnet_sepa_iban_span, #novalnet_sepa_bic_span').html('');
    jQuery('#novalnet_sepa_mandate_confirm').attr('checked', false);
}

function validateSpace(input_val) {
    var input = jQuery.trim(input_val.replace(/\b \b/g, ''));
    return jQuery.trim(input.replace(/\s{2,}/g, ''));
}

function removeUnwantedSpecialCharsSepa(value, req) {
    if (value != 'undefined' || value != '') {
        value.replace(/^\s+|\s+$/g, '');
        if (req != 'undefined' && req == 'holder') {
            return value.replace(/[\/\\|\]\[|#@,+()`'$~%":;*?<>!^{}=_]/g, '');
        } else {
            return value.replace(/[\/\\|\]\[|#@,+()`'$~%.":;*?<>!^{}=_-]/g, '');
        }
    }
}

function validateSpecialChars(input_val) {
    var re = /[\/\\#,+!^()$~%.":*?<>{}]/g;
    return re.test(input_val);
}

function sepahashrequestcall() {
    var bank_country =  account_holder =  account_no =  nn_sepa_iban =  nn_sepa_bic =  iban =  bic =  bank_code =  nn_sepa_uniqueid =  nn_vendor = nn_auth_code = "";
    var bank_country     = jQuery('#novalnet_sepa_bank_country').val();
    var account_holder   = jQuery.trim(jQuery('#novalnet_sepa_account_holder').val());
    var iban             = jQuery('#novalnet_sepa_iban').val().replace(/[^a-z0-9]+/gi, '');
    var bic              = jQuery('#novalnet_sepa_bic').val().replace(/[^a-z0-9]+/gi, '');
    var nn_sepa_iban     = validateSpace(jQuery('#nn_sepa_iban').val());
    var nn_sepa_bic      = validateSpace(jQuery('#nn_sepa_bic').val());
    var nn_vendor        = jQuery('#nn_vendor').val();
    var nn_remote_ip     = jQuery('#nn_sepa_remote_ip').val();
    var nn_auth_code     = jQuery('#nn_auth_code').val();
    var nn_sepa_uniqueid = jQuery('#nn_sepa_uniqueid').val();

    if (bank_country == '') {
        alert(jQuery('#nn_sepa_country').val());
        sepa_mandate_unconfirm_process();
        return false;
    }

    if (validateSpecialChars(iban) || validateSpecialChars(bic) || account_holder == '' || nn_sepa_uniqueid == '') {
        alert(jQuery('#nn_lang_valid_account_details').val());
        sepa_mandate_unconfirm_process();
        return false;
    }

    if (bank_country != 'DE' && bic == '') {
        alert(jQuery('#nn_lang_valid_account_details').val());
        sepa_mandate_unconfirm_process();
        return false;
    } else if (bank_country == 'DE' && !isNaN(iban) && bic == '') {
        alert(jQuery('#nn_lang_valid_account_details').val());
        sepa_mandate_unconfirm_process();
        return false;
    }

    if (bank_country == 'DE' && (bic == '' || !isNaN(bic)) && isNaN(iban)) {
        bic = '123456';
    }

    iban = removeUnwantedSpecialCharsSepa(iban, '');
    bic  = removeUnwantedSpecialCharsSepa(bic, '');

    if (!isNaN(iban) && !isNaN(bic)) {
        account_no = iban;
        bank_code  = bic;
        iban       = bic = '';
    }

    if (nn_sepa_iban != '' && nn_sepa_bic != '') {
        iban = nn_sepa_iban;
        bic  = nn_sepa_bic;
    }

    jQuery('#nn_loader').css('display', 'block');
    jQuery('#nn_loader').attr('tabIndex', -1).focus();

    domainRequestSepa({
        'account_holder'     : removeUnwantedSpecialCharsSepa(account_holder, 'holder'),
        'bank_account'       : account_no,
        'bank_code'          : bank_code,
        'vendor_id'          : nn_vendor,
        'vendor_authcode'    : nn_auth_code,
        'bank_country'       : bank_country,
        'unique_id'          : nn_sepa_uniqueid,
        'remote_ip'          : nn_remote_ip,
        'sepa_data_approved' : '1',
        'mandate_data_req'   : '1',
        'iban'               : iban,
        'bic'                : bic
    }, 'hash_call');
}

function sepaibanbiccall() {
    var bank_country = account_holder = account_no = bank_code = nn_sepa_uniqueid = nn_vendor = nn_auth_code = "";
    var bank_country     = jQuery('#novalnet_sepa_bank_country').val();
    var account_holder   = jQuery.trim(jQuery('#novalnet_sepa_account_holder').val());
    var account_no       = jQuery('#novalnet_sepa_iban').val().replace(/\s+/g, '');
    var bank_code        = jQuery('#novalnet_sepa_bic').val().replace(/\s+/g, '');
    var nn_vendor        = jQuery('#nn_vendor').val();
    var nn_remote_ip     = jQuery('#nn_sepa_remote_ip').val();
    var nn_auth_code     = jQuery('#nn_auth_code').val();
    var nn_sepa_uniqueid = jQuery('#nn_sepa_uniqueid').val();

    jQuery('#nn_sepa_iban,#nn_sepa_bic').val('');

    if (isNaN(account_no) && isNaN(bank_code)) {
        jQuery('#novalnet_sepa_iban_span,#novalnet_sepa_bic_span').html('');
        sepahashrequestcall();
        return false;
    }

    if (bank_code == '' && isNaN(account_no)) {
        sepahashrequestcall();
        return false;
    }

    if (isNaN(bank_code) || isNaN(account_no)) {
        alert(jQuery('#nn_lang_valid_account_details').val());
        sepa_mandate_unconfirm_process();
        return false;
    }

    if (bank_country == '') {
        alert(jQuery('#nn_sepa_country').val());
        sepa_mandate_unconfirm_process();
        return false;
    }

    if (account_holder == '' || account_no == '' || bank_code == '' || nn_sepa_uniqueid == '') {
        alert(jQuery('#nn_lang_valid_account_details').val());
        sepa_mandate_unconfirm_process();
        return false;
    }

    jQuery('#nn_loader').css('display', 'block');
    jQuery('#nn_loader').attr('tabIndex', -1).focus();

    domainRequestSepa({
        'account_holder'  : removeUnwantedSpecialCharsSepa(account_holder, 'holder'),
        'bank_account'    : removeUnwantedSpecialCharsSepa(account_no, ''),
        'bank_code'       : removeUnwantedSpecialCharsSepa(bank_code, ''),
        'vendor_id'       : nn_vendor,
        'vendor_authcode' : nn_auth_code,
        'bank_country'    : bank_country,
        'unique_id'       : nn_sepa_uniqueid,
        'remote_ip'       : nn_remote_ip,
        'get_iban_bic'    : '1'
    }, 'iban_bic');
}

// AJAX call for refill sepa form elements
function separefillformcall() {
    var refillpanhash = '';

    if (jQuery('#nn_sepa_input_panhash').length) {
        refillpanhash = jQuery('#nn_sepa_input_panhash').val();
    }

    if (refillpanhash == '' || refillpanhash == undefined) {
        return false;
    }

    var nn_vendor = nn_auth_code = nn_uniqueid    = "";

    if (jQuery('#nn_vendor').length) {
        nn_vendor = jQuery('#nn_vendor').val();
    }

    if (jQuery('#nn_auth_code').length) {
        nn_auth_code = jQuery('#nn_auth_code').val();
    }

    if (jQuery('#nn_sepa_uniqueid').length) {
        nn_uniqueid = jQuery('#nn_sepa_uniqueid').val();
    }

    if (nn_uniqueid == '') {
        return false;
    }

    jQuery('#nn_loader').css('display', 'block');
    var nn_remote_ip = jQuery('#nn_sepa_remote_ip').val();

    domainRequestSepa({
        'vendor_id'          : nn_vendor,
        'vendor_authcode'    : nn_auth_code,
        'unique_id'          : nn_uniqueid,
        'remote_ip'          : nn_remote_ip,
        'sepa_data_approved' : '1',
        'mandate_data_req'   : '1',
        'sepa_hash'          : refillpanhash
    }, 'sepa_refill');
}

function ibanbic_validate(event) {
    var keycode = ('which' in event) ? event.which : event.keyCode;
    var reg = /^(?:[A-Za-z0-9]+$)/;

    if (event.target.id == 'novalnet_sepa_account_holder')
        var reg = /^(?:[A-Za-z-&.\s]+$)/;

    return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (event.ctrlKey == true && keycode == 114) || (event.target.id == 'novalnet_sepa_account_holder' && keycode == 32)) ? true : false;
}

function sepaFormRefill(response) {
    var hash_stringvalue = response.hash_string,
        hash_string      = hash_stringvalue.split('&'),
        acc_hold         = hash_stringvalue.match('account_holder=(.*)&bank_code'),
        account_holder   = '',
        array_result     = {},
        data_length      = hash_string.length;

    var account_holder = (null != acc_hold && undefined != acc_hold[1]) ? acc_hold[1] : '';

    for (var i = 0; i < data_length; i++) {
        var hash_result_val = hash_string[i].split('=');
        array_result[hash_result_val[0]] = hash_result_val[1];
    }

    try {
        var holder = decodeURIComponent(escape(account_holder));
    } catch (e) {
        var holder = account_holder;
    }

    jQuery('#novalnet_sepa_account_holder').val(holder);
    jQuery('#novalnet_sepa_bank_country').val(array_result.bank_country);
    jQuery('#novalnet_sepa_iban').val(array_result.iban);

    if (array_result.bic != '123456')
        jQuery('#novalnet_sepa_bic').val(array_result.bic);
    jQuery('#novalnet_sepa_refill_hash').val('');
    jQuery('#nn_loader').css('display', 'none');
}

function ibanCallAssign(data) {
    if (data.IBAN == '' || data.BIC == '') {
        jQuery('#nn_loader').css('display', 'none');
        alert(jQuery('#nn_lang_valid_account_details').val());
        sepa_mandate_unconfirm_process();
        return false;
    }

    jQuery('#nn_sepa_iban').val(data.IBAN);
    jQuery('#nn_sepa_bic').val(data.BIC);

    if (data.IBAN != '' && data.BIC != '') {
        jQuery('#novalnet_sepa_iban_span').html('<b>IBAN:</b> ' + data.IBAN);
    }

    if (data.BIC != '') {
        jQuery('#novalnet_sepa_bic_span').html('<b>BIC:</b> ' + data.BIC);
    }

    sepahashrequestcall();
    return true;
}

function domainRequestSepa(nnurl_val, ajax_call) {
    if (nnurl_val == '') {
        return false;
    }

    if ('XDomainRequest' in window && window.XDomainRequest !== null) {
        var xdr = new XDomainRequest(); //Use Microsoft XDR
        xdr.open('POST', 'https://payport.novalnet.de/sepa_iban');
        xdr.onload = function() {
            var data = jQuery.parseJSON(this.responseText);
            if (data.hash_result == 'success') {
                if (ajax_call == 'hash_call') {
                    jQuery('#nn_sepa_hash').val(data.sepa_hash);
                    jQuery('#nn_loader').css('display', 'none');
                } else if (ajax_call == 'iban_bic') {
                    ibanCallAssign(data);
                } else {
                    jQuery('#nn_sepa_hash').val(data.sepa_hash);
                    sepaFormRefill(data);
                }
            } else {
                jQuery('#nn_loader').css('display', 'none');
                alert(data.hash_result);
                return false;
            }
        };
        xdr.onerror = function() {
            return true;
        };
        xdr.send(jQuery.param(nnurl_val));
    } else {
        var xmlhttp = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var data = JSON.parse(xmlhttp.responseText);
                if (data.hash_result == 'success') {
                    if (ajax_call == 'hash_call') {
                        jQuery('#nn_sepa_hash').val(data.sepa_hash);
                        jQuery('#nn_loader').css('display', 'none');
                    } else if (ajax_call == 'iban_bic') {
                        ibanCallAssign(data);
                    } else {
                        jQuery('#nn_sepa_hash').val(data.sepa_hash);
                        sepaFormRefill(data);
                    }
                } else {
                    jQuery('#nn_loader').css('display', 'none');
                    alert(data.hash_result);
                    sepa_mandate_unconfirm_process();
                    return false;
                }
            }
        }
        xmlhttp.open("POST", 'https://payport.novalnet.de/sepa_iban', true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(jQuery.param(nnurl_val));
    }
}
