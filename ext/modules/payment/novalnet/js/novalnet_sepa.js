/*
 * Novalnet Direct Debit SEPA script
 * @author 		Novalnet AG <technic@novalnet.de>
 * @copyright 	Novalnet
 * @license 	https://www.novalnet.de/payment-plugins/kostenlos/lizenz
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
		var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();

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
	
		var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();
		jQuery("[name='checkout_payment']" ).submit(function (event) {
	
	var name = jQuery('#novalnet_sepa_account_holder').val();
	if(jQuery( "input[type=radio][name=payment]:checked" ).val() == 'novalnet_sepa'){
	if(jQuery('#novalnet_sepachange_account').val() == '1' && (jQuery('#novalnet_sepa_iban').val() == '' || name == '' )){
	    alert(jQuery('#nn_lang_valid_account_details').val());
        return false;
	}
}
	});
	}

 $(document).ready(function(){
	 $( "#authorize_text" ).hide();
  $("#mandate_confirm").click(function(){
    $("#authorize_text").toggle();
  });
  jQuery('#novalnet_sepa_iban').keyup(function() {
		$(this).val($(this).val().toUpperCase());
	});
});
