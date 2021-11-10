/*
 * Novalnet Credit Card script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet AG
*/
if(typeof(jQuery) == 'undefined') {
  var s = document.createElement("script");
  s.type = "text/javascript";
  var nn_cc_root = document.getElementById('nn_root_cc_catalog').value;
  s.src = nn_cc_root+"includes/classes/novalnet/js/jquery.js";
  document.getElementsByTagName("head")[0].appendChild(s);
}

if (window.addEventListener) {                // For all major browsers, except IE 8 and earlier
  window.addEventListener("load", novalnet_cc_load);
} else if (window.attachEvent) {             // For IE 8 and earlier versions
  window.attachEvent("onload", novalnet_cc_load);
}
function novalnet_cc_load() {
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
    if (jQuery('#novalnet_sepachange_account').val() == 1 || jQuery('#novalnet_sepachange_account').val() == '') {
      jQuery('#nn_sepa_acc').css('display', 'none');
      jQuery('#nn_sepa_ref_details').css('display', 'block');
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_new_account').val()+'</b></u>');
    } else {
	  jQuery('#nn_sepa_acc').css('display', 'block');
      jQuery('#nn_sepa_ref_details').css('display', 'none');
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_given_account').val()+'</b></u>');
    }
  }
  
  jQuery("#novalnet_cc_new_acc").hover(function() {
    jQuery(this).css('cursor','pointer');
  }, function() {
    jQuery(this).css('cursor','auto');
  });
  document.getElementById('loading_cc').style.display = 'none'; 
  var formid = '';
  formid = jQuery('#novalnet_cc_cvc').closest('form').attr('id');

  if (formid == undefined || formid == '') {
	var get_form_element = document.getElementById('novalnet_cc_cvc');
	get_form_element.form.setAttribute('id', 'checkout_payment');
	formid = jQuery('#novalnet_cc_cvc').closest('form').attr('id');
  }
  
  jQuery('#'+formid).submit(function () {
    var cc_type = cc_owner = cc_no = cc_hash = cc_month = cc_year = cc_cid = 0;
    var ifr = document.getElementById("payment_form_novalnetCc");
    var ccIframe = (ifr.contentWindow || ifr.contentDocument);
  
    if (ccIframe.document) ccIframe=ccIframe.document;
    if (ccIframe.getElementById("novalnetCc_cc_type").value!= '') cc_type = 1;
    if (ccIframe.getElementById("novalnetCc_cc_owner").value!= '') cc_owner = 1;
    if (ccIframe.getElementById("novalnetCc_cc_number").value!= '') cc_no = 1;
    if (ccIframe.getElementById("novalnetCc_expiration").value!= '') cc_month = 1;
    if (ccIframe.getElementById("novalnetCc_expiration_yr").value!= '') cc_year = 1;
    if (ccIframe.getElementById("novalnetCc_cc_cid").value!= '') cc_cid = 1;  
    var selected_payment = jQuery("input[name='payment']:checked").val();
	if(selected_payment == undefined)	{
	  selected_payment = jQuery("input[name='payment']").val();
	} 
    if (selected_payment == 'novalnet_cc' && (cc_type != 1 || cc_owner != 1 || cc_no != 1 || cc_month != 1 ||  cc_year != 1 ||  cc_cid != 1) && jQuery('#novalnet_ccchange_account').val() != 1) {
	  alert(jQuery('#nn_cc_valid_error_ccmessage').val());
	  return false;
    }
    document.getElementById('cc_fldvalidator').value = cc_type+','+cc_owner+','+cc_no+','+cc_month+','+cc_year+','+cc_cid;
    if ( ccIframe.getElementById("nncc_hash_id").value != null ) { 
	  document.getElementById("nn_cc_hash").value 		     = ccIframe.getElementById("nncc_hash_id").value;
	  document.getElementById("nn_cc_uniqueid").value 	     = ccIframe.getElementById("nncc_unique_id").value;
	  document.getElementById("novalnet_cc_cvc").value 	     = ccIframe.getElementById("novalnetCc_cc_cid").value;
	  document.getElementById("novalnet_cc_exp_year").value  = ccIframe.getElementById("novalnetCc_expiration_yr").value;
	  document.getElementById("novalnet_cc_exp_month").value = ccIframe.getElementById("novalnetCc_expiration").value;
    }
  });
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
