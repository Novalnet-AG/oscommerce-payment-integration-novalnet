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
 * Script: novalnet_subscription_stop.php
 *
 */

include_once('includes/application_top.php');

include_once(DIR_FS_CATALOG . 'admin/includes/classes/class.novalnet.php');
include_once(DIR_FS_CATALOG . 'admin/includes/languages/' . $_SESSION['language'] . '/modules/novalnet/novalnet.php');
$inputs = $_REQUEST;
if (!empty($inputs['novalnet_subscription_update'])) {

    if (empty($inputs['novalnet_subscribe_termination_reason'])) {
        $_SESSION['novalneterror'] = true;
    } else {
        $datas = NovalnetAdmin::getNovalnetTransDetails($inputs['order_id']);
        NovalnetAdmin::subscriptionTransStop(array(
            'tid'                => $datas['tid'],
            'payment_id'         => $datas['payment_id'],
            'termination_reason' => $inputs['novalnet_subscribe_termination_reason'],
            'order_id'           => $inputs['order_id'],
            'vendor'             => $datas['vendor'],
            'product'            => $datas['product'],
            'tariff_id'          => $datas['tariff'],
            'auth_code'          => $datas['auth_code']
        ));
    }
    header('Location: ' . $inputs['current_request_url']);
    exit;
}
/**
 * Perform transaction subscription stop process in front end order
 * @param $order_id
 *
 * @return string
 */
function NovalnetSubscriptionStop($order_id)
{
    $transaction_info   = NovalnetAdmin::getNovalnetTransDetails($order_id);
    $subscription_query = tep_db_query("SELECT subs_id, termination_reason FROM novalnet_subscription_detail WHERE order_no='" . tep_db_input($order_id) . "'");
    $datas              = tep_db_fetch_array($subscription_query);
    $func_output        = '';

    if ($datas['subs_id'] != 0 && $datas['termination_reason'] == '' && $transaction_info['gateway_status'] != 103) {
        $subs_termination_reason = array(
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_1,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_2,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_3,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_4,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_5,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_6,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_7,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_8,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_9,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_10,
            MODULE_PAYMENT_NOVALNET_SUBS_REASON_11
        );
        $func_output             = "<div><h2>" . MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TITLE . ":</h2>";
        if (isset($_SESSION['novalneterror'])) {
            unset($_SESSION['novalneterror']);
            $func_output .= "<script>setTimeout(function(){ alert('" . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_TITLE) . "'); }, 1); </script>";
        }
        $func_output .= "<p>" . MODULE_PAYMENT_NOVALNET_SUBS_SELECT_REASON . "</p><form method='post' action='" . DIR_WS_CATALOG . "novalnet_subscription_stop.php' name='novalnet_subscriptionstop'>
        <input type='hidden' name='order_id' value='" . $order_id . "'/>
        <input type='hidden' name='current_request_url' value='" . $_SERVER['REQUEST_URI'] . "'/>
        <select name='novalnet_subscribe_termination_reason' id='novalnet_subscribe_termination_reason'><option value=''>" . MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION . "</option>";
        foreach ($subs_termination_reason as $val) {
            $func_output .= "<option value='$val'>$val</option>";
        }
        $func_output .= "</select>";
        $func_output .= "&nbsp;&nbsp;<input type='submit' name='novalnet_subscription_update' onclick= 'load_novalnet_loading_image();' value='" . MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT . "' /></form>
        </div><div class='loader' id='loader' style='display:none'></div><link rel='stylesheet' type='text/css' href='" . DIR_WS_CATALOG . "'ext/modules/payment/novalnet/css/novalnet.css'>";
    }
    return $func_output;
}
?>
<script type='text/javascript'>
function load_novalnet_loading_image() {
    document.getElementById('loader').style.display='block';
}
</script>
