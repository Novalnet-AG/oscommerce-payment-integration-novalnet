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
 * Script : novalnet_subscription_stop.php
 */
ob_start();
include_once('includes/application_top.php');
require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');
NovalnetCore::loadConstants();
$inputs = $_REQUEST;
if( isset($inputs['novalnet_subscription_update']) && !empty($inputs['order_id']) ) {
  if ( empty($inputs['novalnet_subscribe_termination_reason']) ) {
	$_SESSION['novalneterror'] = true;
  } else {
	$local_nn_trans_info = NovalnetCore::getNovalnetTransDetails($inputs['order_id']);
	NovalnetInterface::subscriptionTransStop(array(
		'tid'				 => $local_nn_trans_info['tid'],
		'payment_id' 		 => $local_nn_trans_info['payment_id'],
		'termination_reason' => $inputs['novalnet_subscribe_termination_reason'],
		'order_id' 			 => $inputs['order_id'],
		'vendor' 			 => $local_nn_trans_info['vendor'],
		'product' 			 => $local_nn_trans_info['product'],
		'tariff' 			 => $local_nn_trans_info['tariff'],
		'auth_code' 		 => $local_nn_trans_info['auth_code']
		));
  }
  header('Location: '.$inputs['current_request_url']);
  exit;
}

/**
 * To process the supscription stop
 * @param $order_no
 *
 * @return string
 */
function NovalnetSubscriptionStop($order_no) {
  $local_nn_trans_info 	   = NovalnetCore::getNovalnetTransDetails($order_no);
  $local_subscription_info = NovalnetCore::getNovalnetSubscriptionTransDetails($order_no);
  $func_output 			   = '';
  if ( isset($local_nn_trans_info) && $local_nn_trans_info['subs_id'] != 0 && isset($local_subscription_info) && $local_subscription_info['termination_reason'] == '' && $local_nn_trans_info['gateway_status'] != 103 ) {
	$subs_termination_reason = array(MODULE_PAYMENT_NOVALNET_SUBS_OFFER_TOO_EXPENSIVE,MODULE_PAYMENT_NOVALNET_SUBS_FRAUD, MODULE_PAYMENT_NOVALNET_SUBS_PARTNER_HAS_INTERVENED,MODULE_PAYMENT_NOVALNET_SUBS_FINANCIAL_DIFFICULTIES, MODULE_PAYMENT_NOVALNET_SUBS_CONTENT_DIDNOT_MEET_EXPECT, MODULE_PAYMENT_NOVALNET_SUBS_CONTENT_NOT_SUFFICIENT, MODULE_PAYMENT_NOVALNET_SUBS_INTEREST_ONLY_TEST_ACCESS, MODULE_PAYMENT_NOVALNET_SUBS_PAGE_TOO_SLOW, MODULE_PAYMENT_NOVALNET_SUBS_SATISFIED_CUSTOMER,MODULE_PAYMENT_NOVALNET_SUBS_ACCESS_PROBLEMS,MODULE_PAYMENT_NOVALNET_SUBS_OTHER);
	$func_output = "<div class='pageHeading'>".MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TEXT.":</div>";
	if(isset($_SESSION['novalneterror'])) {
	  unset($_SESSION['novalneterror']);
	  $func_output .= "<script>alert('" . html_entity_decode(MODULE_PAYMENT_NOVALNET_PLEASE_SELECT_TER_REASON_MESSAGE, ENT_QUOTES, "UTF-8") . "');</script>";
	}
	$func_output .= "<div class='main'><p>" . MODULE_PAYMENT_NOVALNET_PLEASE_SELECT_TER_REASON_MESSAGE . "</p>";
	$func_output .="<form method='post' action='" . DIR_WS_CATALOG . "novalnet_subscription_stop.php' name='novalnet_subscriptionstop'>
				<input type='hidden' name='order_id' value='".$order_no."'/>
				<input type='hidden' name='current_request_url' value='".$_SERVER['REQUEST_URI']."'/>
				<div><select name='novalnet_subscribe_termination_reason' id='novalnet_subscribe_termination_reason'><option value=''>".MODULE_PAYMENT_NOVALNET_SELECT_REASON_MESSAGE."</option>";
	foreach($subs_termination_reason as $key => $val) {
	  $func_output .= "<option value='$val'>$val</option>";
	}
	$func_output .= "</select></div>";
	$func_output .= "<div><input type='submit' name='novalnet_subscription_update' value='".MODULE_PAYMENT_NOVALNET_UPDATE_MESSAGE."' /></div><br/></form></div>";
  }
  return $func_output;
}
?>
