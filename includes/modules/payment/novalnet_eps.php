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
 * Script : novalnet_eps.php
 */
require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');

class novalnet_eps extends NovalnetCore {

  public $code, $title, $description, $enabled;

  public function __construct() {
    parent::loadConstants();
    $this->code = 'novalnet_eps';
    $this->form_action_url = NovalnetInterface::getPaygateURL($this->code);
    $this->title = $this->public_title = MODULE_PAYMENT_NOVALNET_EPS_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_NOVALNET_REDIRECT_DESC;
    $this->sort_order = defined('MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER') && MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER != '' ? MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER : 0;
    if ( strpos(MODULE_PAYMENT_INSTALLED,$this->code) !== false ) {
      $this->enabled = (MODULE_PAYMENT_NOVALNET_EPS_ENABLE_MODULE == 'True');
    }
  }

  function selection() {
	global $order, $payment;
	parent::validatecallbacksession();
	if ( !parent::validateMerchantAPIConf((array)$order, $this->code, $this->enabled) ) {
	  return false;
	}
	if ( empty($payment) && MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION == 'True' )	{
	  if ( parent::getLastSuccessTransPayment($order->customer['email_address'], $this->code) ) {
		$payment = $this->code;
	  }
	}
	$endcustomerinfo = trim(strip_tags(MODULE_PAYMENT_NOVALNET_EPS_ENDCUSTOMER_INFO));
	$test_mode = NovalnetInterface::getPaymentTestModeStatus($this->code);
	$description = '<br>'. $this->description;
	$description .= ($endcustomerinfo != '') ? '<br/>'.$endcustomerinfo : '';
    $description .= ($test_mode == 1) ? '<br>' . utf8_encode(MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG) : '';

	$selection['id'] = $this->code;
	$selection['module'] = MODULE_PAYMENT_NOVALNET_EPS_PUBLIC_TITLE . $description;
	return $selection;
  }

  function pre_confirmation_check() {
	return false;
  }

  function confirmation()	{
	global $order;

	$_SESSION['novalnet'][$this->code]['payment_amount'] = NovalnetInterface::getPaymentAmount((array)$order, $this->code);
	return false;
  }

  function process_button() {
	global $order;

	if ( isset($_SESSION['novalnet'][$this->code]['payment_amount']) ) {
	  $_SESSION['novalnet'][$this->code]['order_obj'] = array_merge((array)$order, array('payment' => $this->code), array('payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']));
	  $before_process_response  = parent::novalnet_before_process($_SESSION['novalnet'][$this->code]['order_obj']);  // Perform real time payment transaction
	  $before_process_response .= NovalnetValidation::confirmButtonDisableActivate();
	  return $before_process_response;
	}
	else {
	   $payment_error_return = 'payment_error=' . $this->code . '&error=' . MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	  tep_redirect(self::setUTFText(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
  }

  function before_process() {
	global $order;
	$post = $_REQUEST;
	if (isset($post['tid'])) { // Response validation
	  $before_process_response = parent::validateRedirectResponse((array)$order, $this->code, $post);
	  $order->info['comments'] = $before_process_response['comments'];
	}
  }

  function after_process() {
	global $insert_id;

	parent::updateOrderStatus($insert_id, $this->code);
	// Perform paygate second call for transaction confirmations / order_no update
	parent::doSecondCallProcess(array( 'payment' => $this->code,
															   'order_no' => $insert_id
															 ));
  }

  function javascript_validation() {
	return false;
  }

  function check() {
	return parent::checkInstalledStatus($this->code);
  }

  function install() {
	parent::installModule($this->code);
  }

  function remove() {
	parent::uninstallModule($this->code);
  }

  function keys() {
	return parent::novalnetKeys($this->code);
  }

  function get_error() {
    global $HTTP_GET_VARS;
    $error = array('title' => '',
                   'error' => ((isset($HTTP_GET_VARS['error'])) ? stripslashes(urldecode($HTTP_GET_VARS['error'])) : ''));
    return $error;
  }
}
?>
