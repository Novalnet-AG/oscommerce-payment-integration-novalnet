<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
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

  require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');
  NovalnetCore::loadConstants();

  /**
   * To append API call options
   * @param $oInfo
   *
   * @return array
   */
  function appendNovalnetOrderProcess($oInfo) {
    $func_output = '';
    $local_nn_trans_info = NovalnetCore::getNovalnetTransDetails($oInfo->orders_id);
	$data['nn_vendor'] = $local_nn_trans_info['vendor'];
	$data['nn_auth_code'] = $local_nn_trans_info['auth_code'];
	$data['nn_product'] = $local_nn_trans_info['product'];
	$data['tid'] = $local_nn_trans_info['tid'];
	$trans_details = NovalnetInterface::getTransDetails($data);
	NovalnetInterface::apiAmountCalculation($local_nn_trans_info, (array)$trans_details);
	
    // Amount update for sepa
    if ($local_nn_trans_info['payment_id'] == 37 && $local_nn_trans_info['gateway_status'] == 99 && $local_nn_trans_info['amount'] != 0) {
	  $func_output[] = array('align' => 'center', 'text' => '<br/><a style="background-color:#0080c9;color:#fff;" class="button" href="'.DIR_WS_CATALOG.'admin/novalnet.php?amount_change=1&oID='.$oInfo->orders_id.'">'.MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TEXT.'</a><br/>');
    }

    // Amount & duedate update block for invoice/prepayment
    if (isset($local_nn_trans_info) && $local_nn_trans_info['amount'] != 0 && $local_nn_trans_info['payment_id'] == 27 && $local_nn_trans_info['gateway_status'] == 100) {
	  $orderTotalQry = tep_db_query("select sum(amount) as amount_total from novalnet_callback_history where order_no =". tep_db_input($oInfo->orders_id));
	  $dbCallbackTotalVal = tep_db_fetch_array($orderTotalQry);
	  $local_nn_trans_info['order_paid_amount'] = $local_nn_trans_info['total_amount'];
	  $local_nn_trans_info['total_amount'] = ((isset($dbCallbackTotalVal['amount_total'])) ? $dbCallbackTotalVal['amount_total'] : 0);

	  if ($local_nn_trans_info['total_amount'] < $local_nn_trans_info['order_paid_amount']) {
		$func_output[] = array('align' => 'center', 'text' => '<br/><a style="background-color:#0080c9;color:#fff;" class="button" href="'.DIR_WS_CATALOG.'admin/novalnet.php?amount_change=1&oID='.$oInfo->orders_id.'">'.MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_MESSAGE.'</a><br/>');
	  }
    }
    
    // Refund block
    if (isset($local_nn_trans_info) && $local_nn_trans_info['amount'] != 0 && $local_nn_trans_info['total_amount'] != 0 && $local_nn_trans_info['total_amount'] > $local_nn_trans_info['refund_amount'] && $local_nn_trans_info['gateway_status'] == 100) {
	  $func_output[] = array('align' => 'center', 'text' => '<br/><a style="background-color:#0080c9;color:#fff;" class="button" href="'.DIR_WS_CATALOG.'admin/novalnet.php?amount_refund=1&oID='.$oInfo->orders_id.'">'.MODULE_PAYMENT_NOVALNET_TRANS_REFUND_TEXT.'</a><br/>');
    }

    // Transaction management block
    if (isset($local_nn_trans_info) && $local_nn_trans_info['amount'] != 0 && (in_array($local_nn_trans_info['gateway_status'], array(98,99))	|| ($local_nn_trans_info['gateway_status'] == 91 && $local_nn_trans_info['payment_id'] == 27))) {
	  $func_output[] = array('align' => 'center', 'text' => '<br/><a style="background-color:#0080c9;color:#fff;" class="button" href="'.DIR_WS_CATALOG.'admin/novalnet.php?trans_confirm=1&oID='.$oInfo->orders_id.'">'.MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_MESSAGE.'</a><br/>');
    }
    
    // Subscription cancellation block
    if (isset($local_nn_trans_info) && $local_nn_trans_info['subs_id'] != 0) {
	  $local_subscription_info = NovalnetCore::getNovalnetSubscriptionTransDetails($oInfo->orders_id);
	  if (isset($local_subscription_info) && $local_subscription_info['termination_reason'] == ''&& $local_nn_trans_info['gateway_status'] != 103 ) {
		$func_output[] = array('align' => 'center', 'text' => '<br/><a style="background-color:#0080c9;color:#fff;" class="button" href="'.DIR_WS_CATALOG.'admin/novalnet.php?subs_cancel=1&oID='.$oInfo->orders_id.'">'.MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TEXT.'</a><br/>');
	  }
    }
    
	// Amount book block
	if(isset($local_nn_trans_info) && $local_nn_trans_info['amount'] == 0
        && in_array($local_nn_trans_info['payment_id'], array(6, 37)) && empty($local_nn_trans_info['subs_id']) && $local_nn_trans_info['gateway_status'] != '103') {
      $func_output[] = array ('align' => 'center', 'text' => '<a style="background-color:#0080c9;color:#fff;" class="button" href="'.DIR_WS_CATALOG.'admin/novalnet.php?book_amount=1&oID='.$oInfo->orders_id.'">'.MODULE_PAYMENT_NOVALNET_BOOK_BUTTON.'</a>');
	}
    return $func_output;
  }
?>
<style>
.button {
	width: auto;
	border: 1px solid Black;
	background-color: #F1F1F1;
	padding: 2px 4px 4px 4px;
	margin: 6px 2px 0px 2px;
	text-decoration: none;
	font-size: 10px;
	cursor: pointer;
	height: 22px;
}
</style>
