/**
 * This file is used for extensions(capture, cancel, refund) using AJAX
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script: novalnet_extension.js
 *
 */

/**
 * Handling the extension capture and cancel
 *
 * @return none
 */
function void_capture_status()
{
    if (document.getElementsByName("trans_status")[0].value == '') {
        alert($("#select_status").val());
        return false;
    } else {
        var display_status = document.getElementsByName("trans_status")[0].value == "CONFIRM" ? $("#capture_text").val() : $("#cancel_text").val();
        if (confirm(display_status)) {
            ajaxCall('edit');
        }
    }
    return true;
}

/**
 * Handling the extension refund
 *
 * @return none
 */
function refund_amount_validation()
{
    if ($("#refund_trans_amount").val() == '' || $('#refund_trans_amount').val() == 0 || isNaN($('#refund_trans_amount').val())) {
        alert($("#amount_invalid").val());
        return false;
    }
    if (confirm($('#nn_refund_amount').val())) {
        ajaxCall('refund');
    };
}

/**
 * Handling the zero amount booking
 *
 * @return none
 */
function zero_amount_validation()
{
    if ($('#book_amount').val() == '' || $('#book_amount').val() == 0 || isNaN($('#book_amount').val())) {
        alert($("#amount_invalid").val());
        return false;
    }
    if (confirm($('#nn_zero_amount_book_confirm').val())) {
        ajaxCall('book_amount');
    }
}
/**
 * Handling the zero amount booking input validation
 *
 * @return none
 */
$('#book_amount').add('#refund_trans_amount').on('input', function () {
    // Get the current input value
    var inputValue = $(this).val();        // Use a regular expression to allow only numbers (0-9)
    var numericValue = inputValue.replace(/[^0-9]/g, '');        // Update the input field with the numeric value
    $(this).val(numericValue);
});

/**
 * Handling the alert message
 *
 * @return none
 */
$('#btn_ok').click(function () {
    $("#messagediv").addClass('d-none');
    window.location.reload();
});

/**
 * Handling the ajax call for extensions
 *
 * @return none
 */
function ajaxCall(action)
{
    var datas = {
        'order_id': $("#order_id").val(),
        "action": action,
    }
    if (action == 'edit') {
        datas.trans_status = $("#trans_status").val();
    }
    datas.amount = (action == 'refund') ? $("#refund_trans_amount").val() : $('#book_amount').val();
    $.ajax({
        url: $("#dir").val() + "lib/common/modules/orderPayment/lib/novalnet/novalnet_extension.php",
        method: "POST",
        data: datas,
        beforeSend: function () {
            $('#loading').show();
        },
        success: function (data) {
            var response = JSON.parse(data);
            $("#message_plce").addClass((response.result.status == 'SUCCESS') ? 'alert-success' : 'alert-danger');
            $("#messagetext").text(response.result.status_text);
            $("#messagediv").css('display', 'block');
        },
        error: function (xhr, textStatus, errorThrown) {
            console.log("Error:", textStatus, xhr.responseText);
        },
        complete: function () {
            $('#loading').hide();
        }
    });

}
