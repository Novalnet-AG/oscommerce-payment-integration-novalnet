<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnet.php
 *
 */

include_once(DIR_FS_ADMIN . 'includes/classes/class.novalnet.php');
include_once(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/novalnet/novalnet.php');

function appendNovalnetOrderProcess($oInfo)
{
    $func_output = '';
    $datas       = NovalnetAdmin::getNovalnetTransDetails($oInfo->orders_id);

    // Transaction management block
    if (in_array($datas['gateway_status'], array(98, 99, 91, 85))) {
        $func_output[] = array(
                        'align' => 'center',
                        'text'  => '<br><a class="button" href="' . DIR_WS_CATALOG . 'admin/novalnet.php?trans_confirm=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_BUTTON . '</a>');
    }

    // Refund block
    if (isset($datas) && $datas['amount'] != 0 && $datas['gateway_status'] == 100) {
        $func_output[] = array(
                        'align' => 'center',
                        'text'  => '<br><a class="button" href="' . DIR_WS_CATALOG . 'admin/novalnet.php?amount_refund=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_REFUND_BUTTON . '</a>');
    }

    // Subscription cancellation block
    if (isset($datas) && $datas['subs_id'] != 0) {
        $subscription_info = tep_db_fetch_array(tep_db_query("SELECT subs_id, tid, signup_date, termination_reason, termination_at FROM novalnet_subscription_detail WHERE order_no='" . tep_db_input($oInfo->orders_id) . "'"));

        if (isset($subscription_info) && $subscription_info['termination_reason'] == '' && $datas['gateway_status'] != 103) {
            $func_output[] = array(
                            'align' => 'center',
                            'text'  => '<br><a class="button" href="' . DIR_WS_CATALOG . 'admin/novalnet.php?subs_cancel=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_BUTTON . '</a>');
        }
    }

    // Amount book block
    if (isset($datas) && $datas['amount'] == 0 && empty($datas['subs_id']) && $datas['zero_transaction'] == 1 && in_array($datas['payment_id'], array(6, 37, 34)) && $datas['gateway_status'] != 103) {
        $func_output[] = array(
                        'align' => 'center',
                        'text'  => '<br><a class="button" href="' . DIR_WS_CATALOG . 'admin/novalnet.php?book_amount=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_BOOK_BUTTON . '</a>');
    }

    // Amount update for Direct Debit SEPA
    if (in_array($datas['payment_id'], array(37)) && $datas['gateway_status'] == 99) {
        $func_output[] = array(
            'align' => 'center',
            'text'  => '<br><a class="button" href="' . DIR_WS_CATALOG . 'admin/novalnet.php?amount_change=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE . '</a>');
    }

    // Amount & duedate update block for invoice/prepayment
    if (in_array($datas['payment_id'], array(27)) && $datas['gateway_status'] == 100 && $datas['amount'] > $datas['callback_amount']) {
        $func_output[] = array(
            'align' => 'center',
            'text'  => '<br><a class="button" href="' . DIR_WS_CATALOG . 'admin/novalnet.php?amount_change=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_BUTTON . '</a>');
    }

    return $func_output;
}

?>

<style>
    .button {
        width: auto;
        border: 1px solid Black;
        background-color: #0080c9;
        color: #fff;
        padding: 2px 4px 4px 4px;
        margin: 6px 2px 0px 2px;
        text-decoration: none;
        font-size: 10px;
        cursor: pointer;
        height: 22px;
    }
</style>
