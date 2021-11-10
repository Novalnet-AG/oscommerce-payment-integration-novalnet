/*
 * Novalnet Direct Debit SEPA script
 * By Novalnet (http://www.novalnet.de)
 * Copyright (c) Novalnet AG
 */
if(typeof(jQuery) == 'undefined') {
  var s = document.createElement("script");
  s.type = "text/javascript";
  var nn_sepa_root = document.getElementById('nn_root_sepa_catalog').value;
  s.src = nn_sepa_root+"includes/classes/novalnet/js/jquery.js";
  document.getElementsByTagName("head")[0].appendChild(s);
}

if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
  window.addEventListener('load', novalnet_sepa_load);
} else if (window.attachEvent) { // For IE 8 and earlier versions
  window.attachEvent('onload', novalnet_sepa_load);
}

function novalnet_sepa_load() {
  var selected_payment = "";
  if(jQuery("input[name='payment']").attr('type') == 'hidden') {
    selected_payment = jQuery("input[name='payment']").val();
  } else {
    selected_payment = jQuery("input[name='payment']:checked").val();
  }
  if(selected_payment == 'novalnet_sepa') {
	separefillformcall();
  }
  var formid = '';
  formid = jQuery('#novalnet_sepa_bank_country').closest('form').attr('id');
  if(formid == undefined || formid == '') {
    var getInputForm = document.getElementById("novalnet_sepa_bank_country");
    getInputForm.form.setAttribute("id",'checkout_payment');
    formid = jQuery('#novalnet_sepa_bank_country').closest('form').attr('id');
  }
  jQuery("#"+formid).submit(function (evt) {
	var selected_payment = jQuery("input[name='payment']:checked").val();
    if(selected_payment == undefined)
    {
	  selected_payment = jQuery("input[name='payment']").val();
    }
    if(selected_payment == 'novalnet_sepa') {
	  if(!jQuery('#novalnet_sepa_mandate_confirm').is(":checked") && (jQuery('#novalnet_sepachange_account').val() == undefined || jQuery('#novalnet_sepachange_account').val() !=1))
	  {
	    alert(jQuery('#nn_lang_mandate_confirm').val());
	    return false;
	  }
    }
  });
  separefillformcall();
  jQuery('#novalnet_sepa_mandate_confirm').click(function() {
    if(jQuery('#novalnet_sepa_mandate_confirm').is(':checked')) {
      sepaibanbiccall();
    }
  });

  jQuery('#novalnet_sepa_new_acc').click(function() {
    if (jQuery('#nn_sepa_acc').css('display') == 'none') {
      document.getElementById('nn_sepa_acc').style.display='block';
      document.getElementById('nn_sepa_ref_details').style.display='none';
      document.getElementById('novalnet_sepachange_account').value='0';
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_given_account').val()+'</b></u>');
    } else {
      document.getElementById('nn_sepa_acc').style.display='none';
      document.getElementById('nn_sepa_ref_details').style.display='block';
      document.getElementById('novalnet_sepachange_account').value='1';
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_new_account').val()+'</b></u>');
    }
  });
  if (jQuery('#novalnet_sepachange_account').val() != undefined) {
    if (jQuery('#novalnet_sepachange_account').val() == 1 || jQuery('#novalnet_sepachange_account').val() == '') {
	  document.getElementById('nn_sepa_acc').style.display='none';
      document.getElementById('nn_sepa_ref_details').style.display='block';
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_new_account').val()+'</b></u>');
    } else {
      document.getElementById('nn_sepa_acc').style.display='block';
      document.getElementById('nn_sepa_ref_details').style.display='none';
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_given_account').val()+'</b></u>');       
    }
  }
  jQuery("#novalnet_sepa_new_acc").hover(function() {
    jQuery(this).css('cursor','pointer');
  }, function() {
    jQuery(this).css('cursor','auto');
  });
  
  jQuery('#novalnet_sepa_iban, #novalnet_sepa_bic, #novalnet_sepa_bank_country, #novalnet_sepa_account_holder').change(function() {
	sepa_mandate_unconfirm_process();
  });
}

function validateSpace(input_val) {
    var input = jQuery.trim(input_val.replace(/\b \b/g, ''));
    return jQuery.trim(input.replace(/\s{2,}/g, ''));
}

function removeUnwantedSpecialCharsSepa(input_val) {
    return input_val.replace(/[\/\\|\]\[|#,+()$@'~%."`~:;*?<>!^{}=_]/g,'');
}

function validateSpecialChars(input_val) {
    var re = /[\/\\#,+!^()$~%.":*?<>{}]/g;
    return re.test(input_val);
}

function sepahashrequestcall() {
  var bank_country = "";var account_holder = "";var account_no = "";var nn_sepa_iban = "";var nn_sepa_bic = "";
  var iban = "";var bic = "";var bank_code = "";var nn_sepa_uniqueid = "";
  var nn_vendor = "";var nn_auth_code = "";var mandate_confirm = 0;
  if(jQuery('#novalnet_sepa_bank_country').length) {bank_country = jQuery('#novalnet_sepa_bank_country').val();}
  if(jQuery('#novalnet_sepa_account_holder').length) {account_holder = removeUnwantedSpecialCharsSepa(jQuery.trim(jQuery('#novalnet_sepa_account_holder').val()));}
  if(jQuery('#novalnet_sepa_iban').length) {iban = validateSpace(jQuery('#novalnet_sepa_iban').val());}
  if(jQuery('#novalnet_sepa_bic').length) {bic = validateSpace(jQuery('#novalnet_sepa_bic').val());}
  if(jQuery('#nn_sepa_iban').length) {nn_sepa_iban = validateSpace(jQuery('#nn_sepa_iban').val());}
  if(jQuery('#nn_sepa_bic').length) {nn_sepa_bic = validateSpace(jQuery('#nn_sepa_bic').val());}
  if(jQuery('#nn_vendor').length) {nn_vendor = jQuery('#nn_vendor').val();}
  if(jQuery('#nn_auth_code').length) {nn_auth_code = jQuery('#nn_auth_code').val();}
  if(jQuery('#nn_sepa_uniqueid').length) {nn_sepa_uniqueid = jQuery('#nn_sepa_uniqueid').val();}
  if (nn_vendor == '' || nn_auth_code == '') {
    alert(jQuery('#nn_lang_valid_merchant_credentials').val());
    sepa_mandate_unconfirm_process();
    return false;
  }
  iban = iban.replace(/^\s+|\s+$/g, '');
  bic = bic.replace(/^\s+|\s+$/g, '');
  if(validateSpecialChars(iban) || validateSpecialChars(bic) || bank_country == '' || account_holder == '' || iban == '' || isNaN(nn_vendor) || nn_vendor == '' || nn_auth_code == '' || nn_sepa_uniqueid == '') {
    alert(jQuery('#nn_lang_valid_account_details').val()); sepa_mandate_unconfirm_process(); return false;
  }
  if(bank_country != 'DE' && bic == '') {
    alert(jQuery('#nn_lang_valid_account_details').val()); sepa_mandate_unconfirm_process(); return false;
  } else if(bank_country == 'DE' && !isNaN(iban) && bic == '') {
    alert(jQuery('#nn_lang_valid_account_details').val()); sepa_mandate_unconfirm_process(); return false;
  }
  if(bank_country == 'DE' && (bic == ''|| !isNaN(bic)) && isNaN(iban)) {
    bic = '123456';
  }
  if(!isNaN(iban) && !isNaN(bic))  {
    account_no = iban;
    bank_code = bic;
    iban = bic = '';
  }
  if(nn_sepa_iban != '' && nn_sepa_bic != '')  {
    iban = nn_sepa_iban;
    bic = nn_sepa_bic;
  }
  var nnurl_val = { 'account_holder' : account_holder, 'bank_account' : account_no, 'bank_code' : bank_code, 'vendor_id' : nn_vendor, 'vendor_authcode' : nn_auth_code, 'bank_country' : bank_country, 'unique_id' : nn_sepa_uniqueid, 'sepa_data_approved' : '1', 'mandate_data_req' : '1', 'iban' : iban, 'bic' : bic };
  jQuery('#nn_loader').css('display', 'block');
  jQuery('#nn_loader').attr('tabIndex',-1).focus();
  domainRequestSepa(nnurl_val, 'hash_call');
}

function sepaibanbiccall() {
  var bank_country = "";var account_holder = "";var account_no = "";
  var bank_code = "";var nn_sepa_uniqueid = "";
  var nn_vendor = "";var nn_auth_code = "";
  if(jQuery('#novalnet_sepa_bank_country').length) {bank_country = jQuery('#novalnet_sepa_bank_country').val();}
  if(jQuery('#novalnet_sepa_account_holder').length) {account_holder = removeUnwantedSpecialCharsSepa(jQuery.trim(jQuery('#novalnet_sepa_account_holder').val()));}
  if(jQuery('#novalnet_sepa_iban').length) {account_no = validateSpace(jQuery('#novalnet_sepa_iban').val());}
  if(jQuery('#novalnet_sepa_bic').length) {bank_code = validateSpace(jQuery('#novalnet_sepa_bic').val());}
  if(jQuery('#nn_vendor').length) {nn_vendor = jQuery('#nn_vendor').val();}
  if(jQuery('#nn_auth_code').length) {nn_auth_code = jQuery('#nn_auth_code').val();}
  if(jQuery('#nn_sepa_uniqueid').length) {nn_sepa_uniqueid = jQuery('#nn_sepa_uniqueid').val();}
  jQuery('#nn_sepa_iban').val('');
  jQuery('#nn_sepa_bic').val('');
  account_no = account_no.replace(/^\s+|\s+$/g, '');
  bank_code = bank_code.replace(/^\s+|\s+$/g, '');
  if(isNaN(account_no) && isNaN(bank_code))  {
    jQuery('#novalnet_sepa_iban_span').html('');
    jQuery('#novalnet_sepa_bic_span').html('');
    sepahashrequestcall();
    return false;
  }
  if(bank_code == '' && isNaN(account_no)) {
    sepahashrequestcall();
    return false;
  }
  if(nn_vendor == '' || nn_auth_code == '') {
    alert(jQuery('#nn_lang_valid_merchant_credentials').val());
    sepa_mandate_unconfirm_process();
    return false;
  }
  if (isNaN(bank_code) || isNaN(account_no)) {
    alert(jQuery('#nn_lang_valid_account_details').val()); sepa_mandate_unconfirm_process(); return false;
  }
  if(bank_country == '' || account_holder == '' || account_no == '' || bank_code == '' || nn_vendor == '' || nn_auth_code == '' || nn_sepa_uniqueid == '') {
    alert(jQuery('#nn_lang_valid_account_details').val()); sepa_mandate_unconfirm_process(); return false;
  }
  var nnurl_val = { 'account_holder' : account_holder, 'bank_account' : account_no, 'bank_code' : bank_code, 'vendor_id' : nn_vendor, 'vendor_authcode' : nn_auth_code, 'bank_country' : bank_country, 'unique_id' : nn_sepa_uniqueid, 'get_iban_bic' : '1' };
  jQuery('#nn_loader').css('display', 'block');
  jQuery('#nn_loader').attr('tabIndex',-1).focus();
  domainRequestSepa(nnurl_val, 'iban_bic');
}

function normalizeDate(input) {
  if(input != undefined && input != '') {
    var parts = input.split('-');
    return (parts[2] < 10 ? '0' : '') + Number(parts[2]) + '.'
      + (parts[1] < 10 ? '0' : '') + Number(parts[1]) + '.'
      + parseInt(parts[0]);
  }
}

// AJAX call for refill sepa form elements
function separefillformcall() {
  var refillpanhash = '';
  if(jQuery('#nn_sepa_input_panhash').length) {refillpanhash = jQuery('#nn_sepa_input_panhash').val();}
  if(refillpanhash == '' || refillpanhash == undefined){  return false; }
  var nn_vendor = ""; var nn_auth_code = ""; var nn_uniqueid = "";
  if(jQuery('#nn_vendor').length) {nn_vendor = jQuery('#nn_vendor').val();}
  if(jQuery('#nn_auth_code').length) {nn_auth_code = jQuery('#nn_auth_code').val();}
  if(jQuery('#nn_sepa_uniqueid').length) {nn_uniqueid = jQuery('#nn_sepa_uniqueid').val();}

  if(isNaN(nn_vendor) || nn_vendor == '' || nn_auth_code == '' || nn_uniqueid == '') {return false;}
  var nnurl_val = { 'vendor_id' : nn_vendor, 'vendor_authcode' : nn_auth_code, 'unique_id' : nn_uniqueid, 'sepa_data_approved' : '1', 'mandate_data_req' : '1', 'sepa_hash' : refillpanhash };

  jQuery('#nn_loader').css('display', 'block');
  domainRequestSepa(nnurl_val, 'sepa_refill');
}

function ibanbic_validate(event, allowSpace) {
  var keycode = ('which' in event) ? event.which : event.keyCode;
  var reg = /^(?:[A-Za-z0-9]+$)/;
  if(allowSpace == true) 
    var reg = /^(?:[A-Za-z0-9&\s]+$)/;
  if(event.target.id == 'novalnet_sepa_account_holder') 
    var reg = /^(?:[A-Za-z0-9&-\s]+$)/;
  return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (event.ctrlKey == true && keycode == 114) || ( allowSpace == true && keycode == 32))? true : false;
}

function sepaFormRefill(hash_string, account_holder) {
  try {
    var holder = decodeURIComponent(escape(account_holder));
  } catch(e) {
    var holder = account_holder;
  }
  for(var i = 0; i < hash_string.length; i++) {
    var hash_result_val = hash_string[i].split('=');
    if(hash_result_val[0] == 'account_holder') { jQuery('#novalnet_sepa_account_holder').val(removeUnwantedSpecialCharsSepa(jQuery.trim(holder)));}
    if(hash_result_val[0] == 'bank_country') { jQuery('#novalnet_sepa_bank_country').val(hash_result_val[1]); }
    if(hash_result_val[0] == 'iban') { jQuery('#novalnet_sepa_iban').val(hash_result_val[1]); }
    if(hash_result_val[0] == 'bic' && hash_result_val[1] != '123456') { jQuery('#novalnet_sepa_bic').val(hash_result_val[1]); }
  }
  jQuery('#nn_loader').css('display', 'none');
}

function ibanCallAssign(data) {
  if(data.IBAN == ''|| data.IBIC == '') {
    jQuery('#nn_loader').css('display', 'none');
    alert(jQuery('#nn_lang_valid_account_details').val());
    sepa_mandate_unconfirm_process(); return false;
  }
  jQuery('#nn_sepa_iban').val(data.IBAN)
  jQuery('#nn_sepa_bic').val(data.BIC)
  if (data.IBAN != '' && data.BIC != '') {
    jQuery('#novalnet_sepa_iban_span').html('<b>IBAN:</b> '+data.IBAN);
  }
  if(data.BIC != '') {
    jQuery('#novalnet_sepa_bic_span').html('<b>BIC:</b> '+data.BIC);
  }
  sepahashrequestcall();
  return true;
}

function domainRequestSepa(nnurl_val, ajax_call){
  var nnurl = (document.location.protocol == 'http:' ) ? 'http://payport.novalnet.de/sepa_iban' : 'https://payport.novalnet.de/sepa_iban';
  if(nnurl_val == ''){return false;}
  if ('XDomainRequest' in window && window.XDomainRequest !== null) {
    var xdr = new XDomainRequest(); //Use Microsoft XDR
    xdr.open('POST', nnurl);
    xdr.onload = function () {
            var data = jQuery.parseJSON(this.responseText);
            if(data.hash_result == 'success') {
                if(ajax_call == 'hash_call') {
					jQuery('#nn_loader').css('display', 'none');
                    jQuery('#nn_sepa_hash').val(data.sepa_hash);
                    jQuery('#nn_sepa_mandate_ref').val(data.mandate_ref);
                    jQuery('#nn_sepa_mandate_date').val(data.mandate_date);
                } else if(ajax_call == 'iban_bic') {
                   ibanCallAssign(data);
                } else{
                    var hash_stringvalue = data.hash_string;
                    var acc_hold = hash_stringvalue.match('account_holder=(.*)&bank_code');
                    var holder='';
                    if(acc_hold != null && acc_hold[1] != undefined) holder = acc_hold[1];
                    hash_string = hash_stringvalue.split('&');
                    sepaFormRefill(hash_string, holder);
                }
            } else{
                jQuery('#nn_loader').css('display', 'none');
                alert(data.hash_result);
                return false;
            }
    };
    xdr.onerror = function() { return true; };
    xdr.send(jQuery.param(nnurl_val));
  } else {
    var xmlhttp=(window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    xmlhttp.onreadystatechange=function() {
      if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        var data =  JSON.parse(xmlhttp.responseText);
        if(ajax_call == 'hash_call') {
		  jQuery('#nn_loader').css('display', 'none');
          jQuery('#nn_sepa_hash').val(data.sepa_hash);
          jQuery('#nn_sepa_mandate_ref').val(data.mandate_ref);
          jQuery('#nn_sepa_mandate_date').val(data.mandate_date);
        }else if(ajax_call == 'iban_bic') {
          ibanCallAssign(data);
        }else{
          var hash_stringvalue = data.hash_string;
          var acc_hold = hash_stringvalue.match('account_holder=(.*)&bank_code');
          var holder='';
          if(acc_hold != null && acc_hold[1] != undefined) holder = acc_hold[1]
            hash_string = hash_stringvalue.split('&');
          sepaFormRefill(hash_string, holder);
        }
      }
    }
    xmlhttp.open("POST", nnurl, true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send(jQuery.param(nnurl_val));
  }
}

function sepa_mandate_unconfirm_process() {
  jQuery('#nn_sepa_hash').val('');
  jQuery('#nn_sepa_mandate_ref').val('');
  jQuery('#nn_sepa_mandate_date').val('');
  jQuery('#novalnet_sepa_iban_span').html('');
  jQuery('#novalnet_sepa_bic_span').html('');
  jQuery('#novalnet_sepa_mandate_confirm').attr('checked', false);
}
