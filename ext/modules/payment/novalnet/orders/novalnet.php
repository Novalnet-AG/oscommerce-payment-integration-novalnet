<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to Novalnet End User License Agreement
 *
 * DISCLAIMER
 *
 * If you wish to customize Novalnet payment extension for your needs, please contact technic@novalnet.de for more information.
 *
 * @author      Novalnet AG
 * @copyright   Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script : novalnet.php
 *
 */
include_once(DIR_FS_ADMIN . 'includes/classes/class.novalnet.php');
include_once(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/novalnet/novalnet.php');
function appendNovalnetOrderProcess($oInfo)
{
    $func_output = [];
    $datas       = NovalnetAdmin::getNovalnetTransDetails($oInfo->orders_id);

    // Transaction management block
    if (in_array($datas['gateway_status'], array(98, 99, 91, 85))) {
        $func_output[] = array(
                        'align' => 'center',
                        'text'  => '<br><a class="button" href="' . DIR_WS_ADMIN . 'novalnet.php?trans_confirm=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_BUTTON . '</a>');
    }

    // Refund block
    if (isset($datas) && $datas['amount'] != 0 && $datas['gateway_status'] == 100) {

        $func_output[] = array(
                        'align' => 'center',
                        'text'  => '<br><a class="button" href="' . DIR_WS_ADMIN . 'novalnet.php?amount_refund=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_REFUND_BUTTON . '</a>');
    }

    // Amount book block
    if (isset($datas) && $datas['amount'] == 0 && $datas['zero_transaction'] == 1 && in_array($datas['payment_id'], array(6, 37, 34)) && $datas['gateway_status'] != 103) {
        $func_output[] = array(
                        'align' => 'center',
                        'text'  => '<br><a class="button" href="' . DIR_WS_ADMIN . 'novalnet.php?book_amount=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_BOOK_BUTTON . '</a>');
    }

    // Amount update for Direct Debit SEPA
    if (in_array($datas['payment_id'], array(37)) && $datas['gateway_status'] == 99) {
        $func_output[] = array(
            'align' => 'center',
            'text'  => '<br><a class="button" href="' . DIR_WS_ADMIN . 'novalnet.php?amount_change=1&oID=' . $oInfo->orders_id . '">' . MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE . '</a>');
    }

    // Amount & duedate update block for invoice/prepayment
    if (in_array($datas['payment_id'], array(27, 59)) && $datas['gateway_status'] == 100 && $datas['amount'] > $datas['callback_amount']) {
		$due_date_text = $datas['payment_id'] == 27 ? MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_BUTTON:MODULE_PAYMENT_NOVALNET_TRANS_SLIP_EXPIRY_DATE_TITLE;
        $func_output[] = array(
            'align' => 'center',
            'text'  => '<br><a class="button" href="' . DIR_WS_ADMIN . 'novalnet.php?amount_change=1&oID=' . $oInfo->orders_id . '">' . $due_date_text . '</a>');
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
