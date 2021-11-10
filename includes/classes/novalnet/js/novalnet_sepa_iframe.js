/*
 * Novalnet Direct Debit SEPA script
 * By Novalnet (http://www.novalnet.de)
 * Copyright (c) Novalnet AG
 */
if(typeof(jQuery) == 'undefined') {
  var s = document.createElement("script");
  s.type = "text/javascript";
  var nn_cc_root = document.getElementById('nn_root_sepa_catalog').value;
  s.src = nn_cc_root+"includes/classes/novalnet/js/jquery.js";
  document.getElementsByTagName("head")[0].appendChild(s);
}
if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
  window.addEventListener('load', novalnet_sepa_load);
} else if (window.attachEvent) { // For IE 8 and earlier versions
  window.attachEvent('onload', novalnet_sepa_load);
}

function novalnet_sepa_load() {
  jQuery('#novalnet_sepa_new_acc').click(function() {
    if (jQuery('#nn_sepa_acc').css('display') == 'none') {
      jQuery('#nn_sepa_acc').css('display', 'block');
      jQuery('#nn_sepa_ref_details').css('display', 'none');
      jQuery('#novalnet_sepachange_account').val(0);
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_given_account').val()+'</b></u>');
    } else {
      jQuery('#nn_sepa_acc').css('display', 'none');
      jQuery('#nn_sepa_ref_details').css('display', 'block');
      jQuery('#novalnet_sepachange_account').val(1);
      jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_new_account').val()+'</b></u>');
    }
  });

  if (jQuery('#novalnet_sepachange_account').val() != undefined) {
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

  jQuery("#novalnet_sepa_new_acc").hover(function() {
    jQuery(this).css('cursor','pointer');
  }, function() {
    jQuery(this).css('cursor','auto');
  });
  document.getElementById('loading_sepa').style.display = 'none';
  var formid = '';
  formid = jQuery('#original_sepa_vendor_id').closest('form').attr('id');

  if (formid == undefined || formid == '') {
	var get_form_element = document.getElementById('original_sepa_vendor_id');
	get_form_element.form.setAttribute('id', 'checkout_payment');
	formid = jQuery('#original_sepa_vendor_id').closest('form').attr('id');
  }

  jQuery('#'+formid).submit(function () {
    var selected_payment = jQuery("input[name='payment']:checked").val();
    if(selected_payment == undefined)	{
	  selected_payment = jQuery("input[name='payment']").val();
    } 
    if(selected_payment == 'novalnet_sepa') {
      var ifr_sepa = document.getElementById("payment_form_novalnetSepa");
      var sepaIframe = (ifr_sepa.contentWindow || ifr_sepa.contentDocument);
      if (sepaIframe.document) {
	    sepaIframe=sepaIframe.document;
	    var sepa_owner = sepa_accountno = sepa_bankcode = sepa_iban = sepa_swiftbic = sepa_hash = sepa_country = 0;
		if (sepaIframe.getElementById("novalnet_sepa_owner").value!= '') sepa_owner=1;
		if (sepaIframe.getElementById("novalnet_sepa_accountno").value!= '') sepa_accountno=1;
		if (sepaIframe.getElementById("novalnet_sepa_bankcode").value!= '') sepa_bankcode=1;
		if (sepaIframe.getElementById("novalnet_sepa_iban").value!= '') sepa_iban=1;
		if (sepaIframe.getElementById("novalnet_sepa_swiftbic").value!= '') sepa_swiftbic=1;
		if (sepaIframe.getElementById("nnsepa_hash").value!= '') sepa_hash=1;
		if (sepaIframe.getElementById("novalnet_sepa_country").value!= '') {
		  var country = sepaIframe.getElementById("novalnet_sepa_country");
		  sepa_country = 1+'-'+country.options[country.selectedIndex].value;
		}
		if ((sepa_owner != 1 || sepa_accountno != 1 || sepa_bankcode != 1 || sepa_iban != 1 || sepa_swiftbic != 1 || sepa_hash != 1) && jQuery('#novalnet_sepachange_account').val() !=1) {
		  alert(jQuery('#nn_lang_valid_account_details').val());
		  return false;
		}
		document.getElementById('sepa_field_validator').value = sepa_owner+','+sepa_accountno+','+sepa_bankcode+','+sepa_iban+','+sepa_swiftbic+','+sepa_hash+','+sepa_country;
		if ( sepaIframe.getElementById("nnsepa_hash").value != null ) {
		  document.getElementById('novalnet_sepa_account_holder').value = sepaIframe.getElementById("novalnet_sepa_owner").value;
		  document.getElementById("nn_sepa_hash").value = sepaIframe.getElementById("nnsepa_hash").value;
		  document.getElementById("nn_sepa_uniqueid").value = sepaIframe.getElementById("nnsepa_unique_id").value;
		}
	  }
	}
  });
}
