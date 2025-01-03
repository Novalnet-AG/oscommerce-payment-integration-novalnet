{**
 * If you wish to customize Novalnet payment extension for your needs,
 * please contact technic@novalnet.de for more information.
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Novalnet Extension template
*}

<div class="bootbox modal fade show" tabindex="-1" role="dialog" aria-modal="true" id="messagediv">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"></h5>
            </div>
            <div class="modal-body">
                <div class="bootbox-body"><br>
                    <div class="alert fade in" id="message_plce"><span>
                            <p id="messagetext"></p>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-info bootbox-accept" id="btn_ok">Ok</button>
            </div>
        </div>
    </div>
</div>

{assign var="transactiondetails" value=\common\modules\orderPayment\lib\novalnet\NovalnetHelper::get_novalnet_transaction_details($order->order_id)}
{if $transactiondetails.status == ON_HOLD}
    <div class="ms-4 me-4 p-3 mb-2" style="border: 1px solid var(--border-color-tiny);">
        <p style="margin:0% 0% 5px 2px"><b>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_MANAGE_TRANSACTION}</b></p>
        <div>
            <span id="nn_void_capture_error" style="color:red"></span>
            <table style="width:33%">
                <tbody>
                    <tr>
                        <td>
                            {$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_SELECT_STATUS_TEXT}
                        </td>
                        <td>
                            <select class="p-1 rounded form-control" name="nn_trans_status" id="nn_trans_status">
                                <option value="">--{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_SELECT_TEXT}--</option>
                                <option value="CONFIRM">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CONFIRM_TEXT}
                                </option>
                                <option value="CANCEL">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_TEXT}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input class="btn-primary border-0 p-2 m-1 rounded" type="submit" name="nn_manage_confirm"
                                value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CONFIRM_TEXT}"
                                onclick="return void_capture_status();" style="float:left">
                            <a class="btn btn-cancel-foot border-0 px-3 py-2 pe m-1 rounded" style="float:left"
                                href="orders">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_BACK_TEXT}</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
{/if}

{if $transactiondetails.status == CONFIRMED && $transactiondetails.amount != 0 && $transactiondetails.payment_type != INSTALMENT_INVOICE && $transactiondetails.payment_type != INSTALMENT_DIRECT_DEBIT_SEPA && $transactiondetails.payment_type != MULTIBANCO}
    <div class="ms-4 me-4 p-3 mb-2" style="border: 1px solid var(--border-color-tiny);">
        <p style="margin:0% 0% 5px 2px"><b>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TITLE}</b></p>
        {assign var="refund_value" value=$transactiondetails.amount}
        {if $transactiondetails.refund_amount != 0}
            {assign var="refund_value" value=$transactiondetails.amount - $transactiondetails.refund_amount}
        {/if}
        {if $transactiondetails.credited_amount != NULL}
            {if $transactiondetails.refund_amount != 0}
                {assign var="refund_value" value=$transactiondetails.credited_amount - $transactiondetails.refund_amount}
            {else}
                {assign var="refund_value" value=$transactiondetails.credited_amount}
            {/if}
        {/if}

        <table class="w-100">
            <tbody>
                <tr>
                    <td>
                        {$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TITLE_TEXT} <input type="text"
                            class="rounded mb-2" name="nn_refund_trans_amount" value="{$refund_value}"
                            id="nn_refund_trans_amount"
                            style="width:100px;margin:0 0 0 1.4%;border: 1px solid var(--border-color-tiny);"
                            autocomplete="off"><span
                            class="ps-2">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_FORMAT}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        {$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_REASON_TEXT} <input type="text"
                            class="rounded"
                            placeholder="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_REASON_TEXT}"
                            name="nn_refund_reason" value="" id="nn_refund_reason"
                            style="margin:0 0 0 2%; border: 1px solid var(--border-color-tiny);" autocomplete="off"></td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" class="btn-primary border-0 p-2 m-1 rounded" name="nn_refund_confirm"
                            value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CONFIRM_TEXT}"
                            onclick="return refund_amount_validation();" style="float:left">
                        <a class="btn btn-cancel-foot border-0 px-3 py-2 pe m-1 rounded" style="float:left"
                            href="orders">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_BACK_TEXT}</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>
{/if}

{if $transactiondetails.amount == 0 && $transactiondetails.status == CONFIRMED}
    <div class="ms-4 me-4 p-3 mb-2" style="border: 1px solid var(--border-color-tiny);">
        <p style="margin:0% 0% 5px 2px"><b>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOK_TRANSACTION_TEXT}</b></p>
        {assign var="prefill_amt" value=\common\modules\orderPayment\lib\novalnet\NovalnetHelper::get_prefill_amount($order->order_id)}
        <div>
            <table class="w-100">
                <tbody>
                    <tr>
                        <td>
                            {$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOKING_AMOUNT}<input type="text"
                                class="rounded mb-2" name="nn_book_amount" value="{$prefill_amt}" id="nn_book_amount"
                                style="width:100px;margin:0 0 0 1.4%;border: 1px solid var(--border-color-tiny);"
                                autocomplete="off"><span
                                class="ps-2">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_FORMAT}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="submit" class="btn-primary border-0 p-2 m-1 rounded" name="nn_book_confirm"
                                value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CONFIRM_TEXT}"
                                onclick="return zero_amount_validation();" style="float:left">
                            <a class="btn btn-cancel-foot border-0 px-3 py-2 pe m-1 rounded" style="float:left"
                                href="orders">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_BACK_TEXT}</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
{/if}
<div id="nn_loading"
    style="display: none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding:20px; border-radius:5px;">
    <button class="btn-primary border-0 p-3 rounded" type="button" disabled>
        <span class="spinner-border spinner-border-lg" role="status" aria-hidden="true"></span>
        {$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_WAIT_TEXT}
    </button>
</div>
{if $transactiondetails.status == CONFIRMED && ($transactiondetails.payment_type == INSTALMENT_INVOICE || $transactiondetails.payment_type == INSTALMENT_DIRECT_DEBIT_SEPA)}
    <script>
        function novalnetRefundbuttonsHandler(cycle) {
            var refundRow = document.getElementById("nn_instalment_refund_" + cycle);
            if (refundRow.style.display === "none") {
                refundRow.style.display = "table-row"; // Change to table-row to match table structure
            } else {
                refundRow.style.display = "none";
            }
        }
    </script>
    {assign var='flag' value=true}
    {assign var='status' value=[]}
    {foreach from=$instalmentdetails item=details}
        {append var='status' value=$details.status}
        {if $details.status == 'Canceled' || $details.status == 'Refunded' && $details.reference_tid}
            {assign var='flag' value=false}
        {/if}
        {if $details.status == 'Pending'}
            {assign var='flag' value=true}
        {/if}
    {/foreach}
    <input type="hidden" id="nn_instalment_status" value={$status|@json_encode nofilter}>
    <div class="ms-4 me-4 p-3 mb-2">
        <h2 class="mb-4 font-weight-bold">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_SUMMARY_TEXT}</h2>
        <div class="table-responsive">
            <div class="p-3 border border-secondary border-opacity-50">
                {if $flag == true}
                    <button class="btn btn-cancel-foot"
                        id="instalment_cancel">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_TEXT}</button>
                {/if}
                <button class="btn btn-primary d-none"
                    id="instalment_cancel_All">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALL}</button>
                <button class="btn btn-primary d-none"
                    id="instalment_cancel_remaining">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_REMAINING}</button>
            </div>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_SNO}</th>
                        <th>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_DETAILS_TID}</th>
                        <th>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TEXT}</th>
                        <th>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_NEXT_DATE}</th>
                        <th>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_STATUS}</th>
                        <th>{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_REFUND_TITLE}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $instalmentdetails as $index => $details}
                        {assign var="constant_name" value="MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_`$details.status|upper`_TEXT"}
                        <tr>
                            <td>{$index + 1}</td>
                            <td>{$details.reference_tid}</td>
                            <td>{$details.instalment_cycle_amount_orginal_amount}</td>
                            <td>{$details.next_instalment_date}</td>
                            <td>{$smarty.const.$constant_name}</td>
                            <td>
                                {if $details.status == Paid}
                                    <button id="nn_refund{$index}" class="btn btn-primary"
                                        onclick="novalnetRefundbuttonsHandler({$index})">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TEXT}</button>
                                {/if}
                                <div id="nn_instalment_refund_{$index}" style="display: none; align-items: center;"
                                    class="ms-5">
                                    <input type="hidden" id="nn_refund_tid" value="{$details.reference_tid}">
                                    <input type="hidden" id="instalment_cycle" value="{$index}">
                                    <input type="text" id="nn_refund_trans_amount" value="{$details.instalment_cycle_amount}"
                                        class="form-control mt-3"
                                        style="width: 100px; display: inline-block; margin-right: 10px;">
                                    <input type="button" name="nn_refund_confirm" id=""
                                        value={$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CONFIRM_TEXT}
                                        class="btn btn-primary mt-3"
                                        onclick="return instalment_refund_amount_validation({$index});">
                                    <a style="text-decoration: none;" class="btn btn-cancel-foot ms-1 mt-3"
                                        href="orders/process-order?orders_id={$order->order_id}">{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_TEXT}</a>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}
<input type="hidden" name="nn_order_id" id="nn_order_id" value="{$order->order_id}">
<input type="hidden" name="dir" id="dir" value="{$smarty.const.DIR_WS_CATALOG}">
<input type="hidden" name="nn_capture_text" id="nn_capture_text"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CAPTURE_TEXT}">
<input type="hidden" name="nn_cancel_text" id="nn_cancel_text"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_ALERT_TEXT}">
<input type="hidden" name="nn_refund_amount" id="nn_refund_amount"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_ALERT_TEXT}">
<input type="hidden" name="nn_zero_amount_book_confirm" id="nn_zero_amount_book_confirm"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOK_AMOUNT_ALERT_TEXT}">
<input type="hidden" name="nn_amount_invalid" id="nn_amount_invalid"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_INVALID_TEXT}">
<input type="hidden" name="nn_select_status" id="nn_select_status"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_SELECT_STATUS_TEXT}">
<input type="hidden" name="nn_cancel_remaining" id="nn_cancel_remaining"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_REMAINING_ALERT_TEXT}">
<input type="hidden" name="nn_cancel_all" id="nn_cancel_all"
    value="{$smarty.const.MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_ALL_ALERT_TEXT}">

<script type="text/javascript" integrity="sha384-RJgNo8DSli170oQBMMffoO+VaFVyRAbzZ5nWCdVT78qphg8cUMB9G7YTUzrBj8YE"
    src="{$smarty.const.DIR_WS_CATALOG}lib/common/modules/orderPayment/lib/novalnet/js/novalnet_extension.min.js">
</script>