/*
 * Novalnet admin API script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet AG
 */
 
if(typeof(jQuery) == 'undefined') {
  var s = document.createElement("script");
  s.type = "text/javascript";
  var nn_api_shoproot = document.getElementById('nn_api_shoproot').value;
  s.src = nn_api_shoproot+"includes/classes/novalnet/js/jquery.js";
  document.getElementsByTagName("head")[0].appendChild(s);
}

if (window.addEventListener) {     // For all major browsers, except IE 8 and earlier
  window.addEventListener("load", novalnet_api_load)
} else if (window.attachEvent) {  // For IE 8 and earlier versions
  window.attachEvent("onload", novalnet_api_load);
}

function novalnet_api_load() {	
  jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PUBLIC_KEY]"]').attr('id', 'novalnet_public_key');
  jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_VENDOR]"]').attr('id', 'novalnet_vendor_id');
  jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_AUTH_CODE]"]').attr('id', 'novalnet_auth_code');
  jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PROJECT]"]').attr('id', 'novalnet_project');
  jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_TARIFF]"]').replaceWith('<select id="novalnet_tariff" name= "configuration[MODULE_PAYMENT_NOVALNET_TARIFF]" ><option value="">None</option></select>');
  jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_ACCESS_KEY]"]').attr('id', 'novalnet_access_key');
  var novalnet_public_key = jQuery('#novalnet_public_key').val();
  jQuery('#novalnet_vendor_id').attr("readonly", true);
  jQuery('#novalnet_auth_code').attr("readonly", true);
  jQuery('#novalnet_project').attr("readonly", true);
  jQuery('#novalnet_access_key').attr("readonly", true);
  jQuery('#novalnet_public_key').blur(function() {
    get_merchant_details();
  });
  if( novalnet_public_key != undefined && novalnet_public_key != '' ) {
    get_merchant_details();
  }
  jQuery('#novalnet_tariff option').removeAttr("selected");
  jQuery("#novalnet_tariff").change(function() {
    jQuery("#saved_tariff_id").val(jQuery("#novalnet_tariff").val());
  });
}

function get_merchant_details() {
  var domain = window.location.protocol;
  var nnurl = domain+'//payport.novalnet.de/autoconfig';
  var remote_ip = jQuery('#remote_ip').val();
  var public_key = jQuery('#novalnet_public_key').val();
  var language = jQuery('#nn_language').val();
  if(jQuery.trim(public_key) == '') {
    return false;
  }
  var data_to_send = {"system_ip" : remote_ip , "api_config_hash" : public_key, "lang" : language}
  if ('XDomainRequest' in window && window.XDomainRequest !== null) {
    var xdr = new XDomainRequest(); //Use Microsoft XDR
    xdr.open('POST', nnurl);
    xdr.onload = function () {
	  process_result(xdr.responseText);
    }
    xdr.onerror = function() { return true; }
    xdr.send(jQuery.param(data_to_send));
  }
  else {
	var xmlhttp=(window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
	xmlhttp.onreadystatechange=function() {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		process_result(xmlhttp.responseText);       
	  }
    }
    xmlhttp.open("POST", nnurl, true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send(jQuery.param(data_to_send));
  }
}

function process_result(response) {
  var hash_string = JSON.parse(response);
  if(hash_string.tariff_id != undefined) {
  var options = jQuery("#novalnet_tariff");
  jQuery("#novalnet_tariff option").remove();
  hash_string_tarrif_value = hash_string.tariff_id.split(',');
  hash_string_tarrif_name  = hash_string.tariff_name.split(',');
  hash_string_tarrif_type  = hash_string.tariff_type.split(',');
  for(i=0; i < hash_string_tarrif_value.length; i++) {
	var hash_result_name = hash_string_tarrif_name[i].split(':');
	hash_result_name = (hash_result_name[2] != undefined) ? hash_result_name[1] + ':' + hash_result_name[2] : hash_result_name[1];
	var hash_result_val  = hash_string_tarrif_value[i].split(':');
	var hash_result_type = hash_string_tarrif_type[i].split(':');
	var tariff_val 		 = hash_result_type[1] + '-' + hash_result_val[1].trim();
	options.append(jQuery("<option/>").val(tariff_val.trim()).text(hash_result_name));
  }
  if (jQuery( '#saved_tariff_id' ).val() != undefined && jQuery( '#saved_tariff_id' ).val() == hash_string['tariff_id'+i]) {
    jQuery('#novalnet_tariff option[value='+hash_string['tariff_id'+i]+']').attr("selected", "selected");
  }
  jQuery('#novalnet_vendor_id').val(hash_string.vendor_id);
  jQuery('#novalnet_auth_code').val(hash_string.auth_code);
  jQuery('#novalnet_project').val(hash_string.product_id);
  jQuery('#novalnet_access_key').val(hash_string.access_key);
  }
  else {
	alert(hash_string.config_result);
  }
}
