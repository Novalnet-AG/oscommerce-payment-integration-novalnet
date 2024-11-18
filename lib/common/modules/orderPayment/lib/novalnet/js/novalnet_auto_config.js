/**
 * This file is used for auto configuration of merchant details
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script: novalnet_auto_config.js
 *
*/

$(document).ready(function () {
    $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_JS_FILE]"]').hide();
    $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_DIR_PATH]"]').hide();
    var signature = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_SIGNATURE]"]');
    var payment_acess_key = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY]"]');
    var callback_url = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK]"]');
    if (signature.val() !== '' && payment_acess_key.val() !== '') {
        ajaxCall('merchant_details');
    } else {
        $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF]"]').val('');
    }
    signature.add(payment_acess_key).on('change', function () {
        ajaxCall('merchant_details');
    });

    var webhook_alert = 'Are you sure you want to configure the Webhook URL in Novalnet Admin Portal?';
    var webhook_error = 'Please enter the valid Webhook URL';
    var key_error = 'Please enter a valid Product Activation Key and Payment Access key';
    var activation_key_error = 'Please enter a valid Product Activation Key';
    var access_key_error = 'Please enter a valid Payment Access Key';
    var webhook_text = 'Notification / Webhook URL is configured successfully in Novalnet Admin Portal';
    if ($('#getlang').val() == 'DE') {
        webhook_alert = 'Sind Sie sicher, dass Sie die Webhook-URL im Novalnet Admin Portal konfigurieren möchten?';
        webhook_error = 'Bitte geben Sie eine gültige Webhook-URL ein';
        key_error = 'Bitte geben Sie den gültigen Produktaktivierungsschlüssel und Paymentzugriffsschlüssel ein';
        webhook_text = 'Callbackskript-/ Webhook-URL wurde erfolgreich im Novalnet Admin Portal konfiguriert';
    }

    // Handling the validating webhook url and configure in Novalnet
    $('.conf').off('click').on('click', function (event) {
        if (signature.val() !== '' && payment_acess_key.val() !== '') {
            url_val = $.trim(callback_url.val());
            var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
            if (regex.test(url_val)) {
                if (confirm(webhook_alert)) {
                    event.preventDefault();
                    event.stopPropagation();
                    ajaxCall('webhook_configure');
                    return true;
                } else {
                    return false;
                }
            } else if (!regex.test(url_val) || url_val === '' || url_val === undefined) {
                alert(webhook_error);
                return false;
            }
        } else {
            event.preventDefault();
            event.stopPropagation();
            alert(key_error);
        }
    });


    // Handling the Ajax call for global configuration
    function  ajaxCall(action) {
        var datas = {
            "activation_key": signature.val(),
            "access_key": payment_acess_key.val(),
            "action": action,
            "lang": $('#getlang').val()
        }
        if (action == 'webhook_configure') {
            datas.callback_url = $.trim(callback_url.val());
        }
        if (signature.val() !== '' && payment_acess_key.val() !== '') {
            $.ajax({
                url: $('#dir').val() + "lib/common/modules/orderPayment/lib/novalnet/novalnet_auto_config.php",
                method: "POST",
                data: datas,
                success: function (data) {
                    var response = JSON.parse(data);
                    if (action == 'merchant_details' && response.result.status == 'SUCCESS') {
                        process_response(response);
                    } else {
                        if (action == 'merchant_details') {
                            alert(response.result.status_text);
                            signature.val('');
                            payment_acess_key.val('');
                            $('select[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF]"]').text('');
                        } else {
                            (response.result.status_code == 100) ? alert(webhook_text) : alert(response.result.status_text);
                        }
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    console.log("Error:", textStatus, xhr.responseText);
                }
            });
        } else {
            $('select[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF]"]').text('');
        }
    }

    $('.btn-right button').on('click', function (e) {
        if (signature.val() == '' || payment_acess_key.val() == '') {
            e.stopPropagation();
            e.preventDefault();
            alert(signature.val() == '' ? activation_key_error : access_key_error);
        }
    });
});

/**
* Handling the response to add tariff dynamically
*
* @return none
*/
function process_response(response)
{
    var tariff = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF]"]');
    var new_tariff = $('select[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF]"]');
    var saved_tariff_id = $('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF]"]').val();
    if (new_tariff.is('select')) {
        new_tariff.empty();
    } else {
        var new_tariff = $('<select name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF]">');
    }

    var tariffs = response.merchant.tariff;

    $.each(tariffs, function (index, value) {
        var option = $('<option>').val(index).text(value.name);
        new_tariff.append(option);
        if (saved_tariff_id && saved_tariff_id == index) {
            option.prop('selected', true);
        }
    });

    tariff.replaceWith(new_tariff);
}
