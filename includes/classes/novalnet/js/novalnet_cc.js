/*
 * Novalnet Credit Card script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet AG
 */
if(typeof(jQuery) == 'undefined' || typeof(jQuery) == undefined) {
  var s = document.createElement("script");
  s.type = "text/javascript";
  var nn_cc_root = document.getElementById('nn_root_cc_catalog').value;
  s.src = nn_cc_root+"includes/classes/novalnet/js/jquery.js";
  document.getElementsByTagName("head")[0].appendChild(s);
}

if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
  window.addEventListener('load', novalnet_cc_load);
} else if (window.attachEvent) { // For IE 8 and earlier versions
  window.attachEvent('onload', novalnet_cc_load);
}

function novalnet_cc_load() {
  var selected_payment = "";
  if(jQuery("input[name='payment']").attr('type') == 'hidden') {
    selected_payment = jQuery("input[name='payment']").val();
  } else {
    selected_payment = jQuery("input[name='payment']:checked").val();
  }
  if(selected_payment == 'novalnet_cc') {
	ccrefillcall();
  }
  
  var formid = '';
  formid = jQuery('#novalnet_cc_cvc').closest('form').attr('id');

  if (formid == undefined || formid == '') {
	var get_form_element = document.getElementById('novalnet_cc_cvc');
	get_form_element.form.setAttribute('id', 'checkout_payment');
	formid = jQuery('#novalnet_cc_cvc').closest('form').attr('id');
  }

  jQuery('#'+formid).submit(function (evt) {
	var selected_payment = jQuery("input[name='payment']:checked").val();
	if(selected_payment == undefined)	{
	  selected_payment = jQuery("input[name='payment']").val();
	}
	if(selected_payment == 'novalnet_cc' && (jQuery('#novalnet_ccchange_account').val() == undefined || jQuery('#novalnet_ccchange_account').val() == 0)) {
	  if(jQuery('#nn_cc_hash').val() == "") {
		evt.preventDefault();
		cchashcall();
	  }
    }
  });
  
  jQuery('#novalnet_cc_new_acc').click(function() {
    if (jQuery('#nn_cc_acc').css('display') == 'none') {
	  jQuery('#nn_cc_acc').css('display', 'block');
	  jQuery('#nn_cc_ref_details').css('display', 'none');
	  jQuery('#novalnet_ccchange_account').val('0');
	  jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_given_account').val()+'</b></u>');
    } else {
	  jQuery('#nn_cc_acc').css('display', 'none');
	  jQuery('#nn_cc_ref_details').css('display', 'block');
	  jQuery('#novalnet_ccchange_account').val('1');
	  jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_new_account').val()+'</b></u>');
    }
  });
 
  if (jQuery('#novalnet_ccchange_account').val() != undefined) {
    if (jQuery('#novalnet_ccchange_account').val() == 1 || jQuery('#novalnet_ccchange_account').val() == '') {
	  jQuery('#nn_cc_acc').css('display', 'none');
	  jQuery('#nn_cc_ref_details').css('display', 'block');
      jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_new_account').val()+'</b></u>');
    } else {
	  jQuery('#nn_cc_acc').css('display', 'block');
	  jQuery('#nn_cc_ref_details').css('display', 'none');
      jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_given_account').val()+'</b></u>');
    }
  }
  
  jQuery("#novalnet_cc_new_acc").hover(function() {
    jQuery(this).css('cursor','pointer');
  }, function() {
    jQuery(this).css('cursor','auto');
  });
}

function ccCrossDomainAjax (input_data, callfrom) {
  var domain = window.location.protocol;
  var nnurl = domain+"//payport.novalnet.de/payport_cc_pci";
  if ('XDomainRequest' in window && window.XDomainRequest !== null) {
	var xdr = new XDomainRequest();
	xdr.open('POST' , nnurl);
	xdr.onload = function () {
	  getCCHashResult(jQuery.parseJSON(this.responseText), callfrom);
	  return false;
	};
	xdr.onerror = function() {
	  jQuery('#loader').hide();
	  alert(jQuery('#nn_cc_valid_error_ccmessage').val());
	};
	xdr.send(input_data);
  }
  else {
	jQuery.ajax({
	  type    : 'POST',
	  url     : nnurl,
	  data    : input_data,
	  dataType: 'json',
	  success : function(data) {
				getCCHashResult(data, callfrom);
				return false;
			},
	  error: function(evt, message){
				jQuery('#loader').hide();
				alert(jQuery('#nn_cc_valid_error_ccmessage').val());
			}
	});
  }
}

function getCCHashResult(response, callfrom) {
  if(response.hash_result == 'success') {
	if(callfrom == 'cchash') {
	  jQuery('#nn_cc_hash').val(response.pan_hash);
	  jQuery('#loader').hide();
	  jQuery('#novalnet_cc_holder').closest('form').submit();
	}
	else if(callfrom == 'ccrefill') {
	  var hash_string = response.hash_string.split('&');
	  var arrayResult={};
	  var cc_month='';
	  for (var i=0,len=hash_string.length;i<len;i++) {
		if(hash_string[i]=='' || hash_string[i].indexOf("=") == -1)	{
		  hash_string[i] = hash_string[i-1] +'&'+ hash_string[i];
		}
		var hash_result_val = hash_string[i].split('=');
		arrayResult[hash_result_val[0]] = hash_result_val[1];
	  }
	  cc_month = arrayResult.cc_exp_month;
	  if(arrayResult.cc_exp_month<=9)
		cc_month = '0' + arrayResult.cc_exp_month;
	  try {
		var holder = decodeURIComponent(escape(arrayResult.cc_holder));
	  } catch(e) {
		var holder = arrayResult.cc_holder;
	  }
	  jQuery('#novalnet_cc_holder').val(holder);
	  jQuery('#novalnet_cc_no').val(arrayResult.cc_no);
	  jQuery('#novalnet_cc_exp_month').val(cc_month);
	  jQuery('#novalnet_cc_exp_year').val(arrayResult.cc_exp_year);
	  jQuery('#loader').hide();
	}
  }
}

function cchashcall()
{
  var cc_holder = "";var cc_no = "";
  var cc_exp_month = "";var cc_exp_year = "";var cc_cvc = "";
  var nn_vendor = "";var nn_auth_code = "";var nn_cc_uniqueid = "";
  if(jQuery('#novalnet_cc_holder')) { cc_holder = removeUnwantedSpecialChars(jQuery.trim(jQuery('#novalnet_cc_holder').val())); }
  if(jQuery('#novalnet_cc_no')) { cc_no = getNumbersOnly(jQuery('#novalnet_cc_no').val()); }
  if(jQuery('#novalnet_cc_exp_month')) { cc_exp_month = jQuery('#novalnet_cc_exp_month').val(); }
  if(jQuery('#novalnet_cc_exp_year')) { cc_exp_year = jQuery('#novalnet_cc_exp_year').val(); }
  if(jQuery('#novalnet_cc_cvc')) { cc_cvc = getNumbersOnly(jQuery('#novalnet_cc_cvc').val()); }
  if(jQuery('#nn_vendor')) { nn_vendor = jQuery('#nn_vendor').val(); }
  if(jQuery('#nn_auth_code')) { nn_auth_code = jQuery('#nn_auth_code').val(); }
  if(jQuery('#nn_cc_uniqueid')) { nn_cc_uniqueid = jQuery('#nn_cc_uniqueid').val(); }
  if(nn_vendor == '' || nn_auth_code == '') {
	alert(jQuery('#nn_merchant_valid_error_ccmessage').val());
	return false;
  }
  var currentDateVal = new Date();
  if( cc_holder == '' || cc_no == ''|| cc_no <= 0 || cc_exp_month == '' || cc_exp_year == '' || cc_cvc == '' || (cc_exp_year == currentDateVal.getFullYear() && cc_exp_month < (currentDateVal.getMonth()+1))) {
	alert(jQuery('#nn_cc_valid_error_ccmessage').val());
	return false;
  }
  var nnurl_val = { 'noval_cc_exp_month' : cc_exp_month, 'noval_cc_exp_year' : cc_exp_year, 'noval_cc_holder' : cc_holder, 'noval_cc_no' : cc_no, 'unique_id' : nn_cc_uniqueid, 'vendor_authcode' : nn_auth_code, 'vendor_id' : nn_vendor };
  var nn_url = jQuery.param(nnurl_val);
  jQuery('#loader').show();
  ccCrossDomainAjax(nn_url, "cchash");
}

function ccrefillcall() {
  var cc_panhash = '';
  if(jQuery('#nn_cc_input_panhash')){cc_panhash = jQuery('#nn_cc_input_panhash').val();}
  if(cc_panhash == '' || cc_panhash == 'undefined') {return false;}
  if(jQuery('#nn_vendor')){nn_vendor = jQuery('#nn_vendor').val();}
  if(jQuery('#nn_auth_code')){nn_auth_code = jQuery('#nn_auth_code').val();}
  if(jQuery('#nn_cc_uniqueid')){nn_cc_uniqueid = jQuery('#nn_cc_uniqueid').val();}
  if(nn_vendor == '' || nn_auth_code == '' || nn_cc_uniqueid == '' ) {return false;}

  var nnurl_val = { 'pan_hash' : cc_panhash, 'unique_id' : nn_cc_uniqueid, 'vendor_authcode' : nn_auth_code, 'vendor_id' : nn_vendor };
  jQuery('#loader').show();
  ccCrossDomainAjax(nnurl_val, "ccrefill");
}

function removeUnwantedSpecialChars(input_val) {
  if( input_val != 'undefined' || input_val != '') {
	return input_val.replace(/[\/\\|\]\[|#,@+()$~%.":;*?<>!^{}=_]/g,'');
  }
}

function getNumbersOnly(input_val) {
  var input_val = input_val.replace(/^\s+|\s+$/g, '');
  return input_val.replace(/[^0-9]/g,'');
}

function isNumberKey(evt, allowSpace) {
  var charCode = (evt.which) ? evt.which : evt.keyCode
  var keycode = ('which' in evt) ? evt.which : evt.keyCode;
  var reg = /^(?:[0-9]+$)/;
  return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (evt.ctrlKey == true && keycode == 114) || (allowSpace == true && charCode == 32))? true : false;
}

function splCharValidate(evt){
  var keycode = ('which' in evt) ? evt.which : evt.keyCode;
  var reg = /^(?:[A-Za-z0-9&\s]+$)/;
  return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (evt.ctrlKey == true && keycode == 114) || keycode == 32 || keycode == 45)? true : false;
}

function show_cvc_info(key, id, parent) {
  if(key == true) {
    jQuery('#'+id).css('display', 'block');
    var position = jQuery('#'+parent).position();
    jQuery('#'+id).css('position','absolute');
    jQuery('#'+id).css('top', (position.top-160));
    jQuery('#'+id).css('left', position.left);
  } else {
    jQuery('#'+id).css('display', 'none');
  }
}
