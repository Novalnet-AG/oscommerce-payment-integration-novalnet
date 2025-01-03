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
function void_capture_status() {
    if ($('#nn_trans_status').val() == '') {
        alert($("#nn_select_status").val());
        return false;
    } else {
        var display_status = $("#nn_trans_status").val() == "CONFIRM" ? $("#nn_capture_text").val() : $("#nn_cancel_text").val();
        if (confirm(display_status)) {
            ajaxCall({ action: 'edit', trans_status: $("#nn_trans_status").val() });
        }
    }
    return true;
}

/**
 * Handling the extension refund
 *
 * @return none
 */
function refund_amount_validation() {
    if ($("#nn_refund_trans_amount").val() == '' || $('#nn_refund_trans_amount').val() == 0 || isNaN($('#nn_refund_trans_amount').val())) {
        alert($("#nn_amount_invalid").val());
        return false;
    }
    if (confirm($('#nn_refund_amount').val())) {
        var nn_refund_reason = $('#nn_refund_reason').val() || '';
        ajaxCall({ action: 'refund', amount: $("#nn_refund_trans_amount").val(), refund_reason: nn_refund_reason });
    };
}

function instalment_refund_amount_validation(cycle) {
    var row_id = `#nn_instalment_refund_${cycle}`;
    if ($(row_id + ' #nn_refund_trans_amount').val() == '' || $(row_id + ' #nn_refund_trans_amount').val() == 0 || isNaN($(row_id + ' #nn_refund_trans_amount').val())) {
        alert($("#nn_amount_invalid").val());
        return false;
    }
    if (confirm($('#nn_refund_amount').val())) {
        ajaxCall(
            {
                action: 'refund',
                amount: $(row_id + " #nn_refund_trans_amount").val(),
                instalment_cycle: cycle,
                refund_tid: $('#nn_refund_tid').val()
            }
        );
    };
}

/**
 * Handling the zero amount booking
 *
 * @return none
 */
function zero_amount_validation() {
    if ($('#nn_book_amount').val() == '' || $('#nn_book_amount').val() == 0 || isNaN($('#nn_book_amount').val())) {
        alert($("#nn_amount_invalid").val());
        return false;
    }
    if (confirm($('#nn_zero_amount_book_confirm').val())) {
        ajaxCall({ action: 'book_amount', amount: $('#nn_book_amount').val() });
    }
}
/**
 * Handling the zero amount booking input validation
 *
 * @return none
 */
$('#nn_book_amount').add('#nn_refund_trans_amount').on('input', function () {
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
 * Handling the instalment cancel buttons
 *
 * @return none
 */
$('#instalment_cancel').click(function () {
    var status = JSON.parse($('#nn_instalment_status').val());
    $('#instalment_cancel_All').removeClass('d-none');
    $('#instalment_cancel_remaining').removeClass('d-none');
    $('#instalment_cancel').addClass('d-none');
    if (status.includes("Refunded")) {
        $('#instalment_cancel_All').addClass('d-none');
    }
    if (!["Refunded", "Pending", "Canceled"].some(statusValue => status.includes(statusValue))) {
        $('#instalment_cancel_remaining').addClass('d-none');
    }
});

$('#instalment_cancel_remaining').click(function () {
    if (confirm($('#nn_cancel_remaining').val())) {
        ajaxCall({ action: 'remaining_cycles' });
    }
});

$('#instalment_cancel_All').click(function () {
    if (confirm($('#nn_cancel_all').val())) {
        ajaxCall({ action: 'all_cycles' });
    }
});

/**
 * Handling the ajax call for extensions
 *
 * @return none
 */
function ajaxCall(datas) {
    datas.order_id = $("#nn_order_id").val();
    $.ajax({
        url: $("#dir").val() + "lib/common/modules/orderPayment/lib/novalnet/novalnet_extension.php",
        method: "POST",
        data: datas,
        beforeSend: function () {
            $('#nn_loading').show();
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
            $('#nn_loading').hide();
        }
    });

}
