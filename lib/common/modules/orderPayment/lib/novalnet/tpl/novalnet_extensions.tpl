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
            <div class="modal-footer"><button type="button" class="btn btn-info bootbox-accept" id="btn_ok">Ok</button></div>
        </div>
    </div>
</div>

{assign var="transactiondetails" value=\common\modules\orderPayment\lib\novalnet\NovalnetHelper::get_novalnet_transaction_details($order->order_id)}
{if $transactiondetails.status == ON_HOLD}
<div class="ms-4 me-4 p-3 mb-2" style="border: 1px solid var(--border-color-tiny);">
<p style="margin:0% 0% 5px 2px"><b>{$constvalue['manage_transaction']}</b></p>
    <div>
        <span id="nn_void_capture_error" style="color:red"></span>
        <table style="width:33%">
            <tbody>
                <tr>
                    <td>
                       {$constvalue['select_status']}
                    </td>
                    <td>
                        <select class="p-1 rounded form-control" name="trans_status" id="trans_status">
                            <option value="">--{$constvalue['select']}--</option>
                            <option value="CONFIRM">{$constvalue['confirm']}</option>
                            <option value="CANCEL">{$constvalue['cancel']}</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input class="btn-primary border-0 p-2 m-1 rounded" type="submit" name="nn_manage_confirm"
                            value="{$constvalue['confirm']}" onclick="return void_capture_status();" style="float:left">
                        <a class="btn btn-cancel-foot border-0 px-3 py-2 pe m-1 rounded" style="float:left"
                            href="orders">{$constvalue['back']}</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
{/if}

{if $transactiondetails.status == CONFIRMED && $transactiondetails.amount !== '0' && $transactiondetails.payment_type !== INSTALMENT_INVOICE && $transactiondetails.payment_type !== INSTALMENT_DIRECT_DEBIT_SEPA && $transactiondetails.payment_type !== MULTIBANCO}
<div class ="ms-4 me-4 p-3 mb-2" style="border: 1px solid var(--border-color-tiny);">
<p style="margin:0% 0% 5px 2px"><b>{$constvalue['refund_text']}</b></p>

{if $transactiondetails.refund_amount !== 0}
{assign var="refund_value" value=$transactiondetails.amount - $transactiondetails.refund_amount}
{else}
{assign var="refund_value" value=$transactiondetails.amount}
{/if}
{if $transactiondetails.credited_amount !== NULL}
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
                   {$constvalue['refund_title']} <input type="text" class="rounded mb-2" name="refund_trans_amount"
                        value="{$refund_value}" id="refund_trans_amount"
                        style="width:100px;margin:0 0 0 1.4%;border: 1px solid var(--border-color-tiny);"
                        autocomplete="off"><span class="ps-2">{$constvalue['amount_format']}</span>
                </td>
            </tr>
            <tr>
                <td>
                   {$constvalue['refund_reason']} <input type="text" class="rounded" placeholder="{$constvalue['refund_reason']}"
                        name="refund_reason" value="" id="refund_reason"
                        style="margin:0 0 0 2%; border: 1px solid var(--border-color-tiny);" autocomplete="off"></td>
            </tr>
            <tr>
                <td>
                    <input type="submit" class="btn-primary border-0 p-2 m-1 rounded" name="nn_refund_confirm"
                        value="{$constvalue['confirm']}" onclick="return refund_amount_validation();" style="float:left">
                    <a class="btn btn-cancel-foot border-0 px-3 py-2 pe m-1 rounded" style="float:left"
                        href="orders">{$constvalue['back']}</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>
{/if}

{if $transactiondetails.amount == '0' && $transactiondetails.status == CONFIRMED}
<div class ="ms-4 me-4 p-3 mb-2" style="border: 1px solid var(--border-color-tiny);">
<p style="margin:0% 0% 5px 2px"><b>{$constvalue['book_transaction']}</b></p>
{assign var="prefill_amt" value=\common\modules\orderPayment\lib\novalnet\NovalnetHelper::get_prefill_amount($order->order_id)}
<div>
    <table class="w-100">
        <tbody>
            <tr>
                <td>
                   {$constvalue['booking_amount_text']}<input type="text" class="rounded mb-2" name="book_amount"
                        value="{$prefill_amt}" id="book_amount"
                        style="width:100px;margin:0 0 0 1.4%;border: 1px solid var(--border-color-tiny);"
                        autocomplete="off"><span class="ps-2">{$constvalue['amount_format']}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" class="btn-primary border-0 p-2 m-1 rounded" name="nn_book_confirm"
                        value="{$constvalue['confirm']}" onclick="return zero_amount_validation();" style="float:left">
                    <a class="btn btn-cancel-foot border-0 px-3 py-2 pe m-1 rounded" style="float:left"
                        href="orders">{$constvalue['back']}</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>
{/if}
 <div class="" id="loading"
    style="display: none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding:20px; border-radius:5px;">
    <button class="btn-primary border-0 p-3 rounded" type="button" disabled>
        <span class="spinner-border spinner-border-lg" role="status" aria-hidden="true"></span>
        Please wait...
    </button>
</div>
 <input type="hidden" name="order_id" id="order_id" value="{$order->order_id}">
 <input type="hidden" name="dir" id="dir" value="{$smarty.const.DIR_WS_CATALOG}">
 <input type="hidden" name="capture_text" id="capture_text" value="{$constvalue['capture_text']}">
 <input type="hidden" name="cancel_text" id="cancel_text" value="{$constvalue['cancel_text']}">
 <input type="hidden" name="nn_refund_amount" id="nn_refund_amount" value="{$constvalue['refund_alert_text']}">
 <input type="hidden" name="nn_zero_amount_book_confirm" id="nn_zero_amount_book_confirm" value="{$constvalue['Book_amount_text']}">
 <input type="hidden" name="amount_invalid" id="amount_invalid" value="{$constvalue['invalid_text']}">
 <input type="hidden" name="select_status" id="select_status" value="{$constvalue['select_status']}">
 <script type="text/javascript" src="{$smarty.const.DIR_WS_CATALOG}lib/common/modules/orderPayment/lib/novalnet/js/novalnet_extension.js"></script>
