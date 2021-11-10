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
 * Script : novalnet_invoice.php
 */
require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');

class novalnet_invoice extends NovalnetCore {

  public $code, $title, $description, $enabled, $fraud_module_status, $fraud_module;

  public function __construct() {
	parent::loadConstants();
	$this->code = 'novalnet_invoice';
	$this->title = $this->public_title =  MODULE_PAYMENT_NOVALNET_INVOICE_TEXT_TITLE;
	$this->description = MODULE_PAYMENT_NOVALNET_INVOICE_DESC;
	$this->sort_order = defined('MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER') && MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER != '' ? MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER : 0;
	if ( strpos(MODULE_PAYMENT_INSTALLED,$this->code) !== false ) {
	  $this->enabled = (MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE == 'True');
	  $this->fraud_module = ((MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE == 'False') ? false : MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE);
	}
  }

  function selection() {
	global $order, $payment;
	parent::validatecallbacksession();
	if ( !parent::validateMerchantAPIConf((array)$order, $this->code, $this->enabled) || !parent::validateCallbackStatus($this->code, $this->fraud_module) ) {
		return false;
	}
	if ( !parent::validateMerchantAPIConf((array)$order, $this->code, $this->enabled) || !parent::validateCallbackStatus($this->code, $this->fraud_module) ) {
	  return false;
	}
	$this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);
	if ( empty($payment) && MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION == 'True' )	{
	  if ( parent::getLastSuccessTransPayment($order->customer['email_address'], $this->code) ) {
		$payment = $this->code;
	  }
	}
	if(isset($_SESSION['payment']) && $_SESSION['payment'] != $this->code && isset($_SESSION['novalnet'][$this->code])) { unset($_SESSION['novalnet'][$this->code]); }
	$endcustomerinfo = trim(strip_tags(MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO));
	$test_mode = NovalnetInterface::getPaymentTestModeStatus($this->code);
	$description = '<br>'. $this->description;
	$description .= ($endcustomerinfo != '') ? '<br/>'.$endcustomerinfo : '';
    $description .= ($test_mode == 1) ? '<br>' . utf8_encode(MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG) : '';

	$selection['id'] = $this->code;
	$selection['module'] = MODULE_PAYMENT_NOVALNET_INVOICE_PUBLIC_TITLE . $description;
	if ( $this->fraud_module  &&  $this->fraud_module_status ) {
	  if ( isset($_SESSION['novalnet'][$this->code]['tid'])) {
	    $selection['fields'] = NovalnetInterface::buildCallbackFieldsAfterResponse($this->fraud_module,$this->code);
	  }
	  else {
	    $selection['fields'][] = NovalnetInterface::buildCallbackInputFields($this->fraud_module, $this->code);
	  }
	}
	return $selection;
  }

  function pre_confirmation_check() {
	global $order;
	$post = $_POST;
	$this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);
	if ( !empty($_SESSION['novalnet'][$this->code]['secondcall']) ) {
	  NovalnetValidation::validateUserInputsOnCallback($this->fraud_module, $this->code, $post);
	} else {
	  NovalnetValidation::validateUserInputs($this->fraud_module, $this->fraud_module_status, $this->code, $post);
	}
	return false;
  }

  function confirmation()	{
	global $order;

	if ( !empty($_SESSION['novalnet'][$this->code]['secondcall']) ) {
	  NovalnetValidation::validateAmountOnCallback($this->code, $this->fraud_module);
	}
	$_SESSION['novalnet'][$this->code]['payment_amount'] = NovalnetInterface::getPaymentAmount((array)$order, $this->code);
	return false;
  }

  function process_button() {
	$post = $_POST;

	if ( !empty($post[$this->code . '_new_pin']) ) {
	  $new_pin_response = NovalnetInterface::doCallbackRequest('TRANSMIT_PIN_AGAIN', $this->code);
	  NovalnetValidation::validateNewPinResponse($new_pin_response, 'TRANSMIT_PIN_AGAIN', $this->code);
	} elseif ( isset($_SESSION['novalnet'][$this->code]['payment_amount']) ) {
	  $novalnet_order_details = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
	  $_SESSION['novalnet'][$this->code] = array_merge($novalnet_order_details, $post, array('payment' => $this->code), array('payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']));
	} else {
	  $payment_error_return = 'payment_error=' . $this->code . '&error=' . MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	  tep_redirect(self::setUTFText(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
	return NovalnetValidation::confirmButtonDisableActivate();
  }

  function before_process() {
	global $order;

	$this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);
	$param_inputs = array_merge((array)$order, $_SESSION['novalnet'][$this->code], array('fraud_module' => $this->fraud_module, 'fraud_module_status' => $this->fraud_module_status));
	if (!empty($param_inputs['secondcall'])) {
	  NovalnetInterface::doConfirmPayment($this->code ,$this->fraud_module);
	} else {
	  parent::novalnet_before_process($param_inputs);
	  NovalnetInterface::gotoPaymentOnCallback($this->code, $this->fraud_module, $this->fraud_module_status);
	}  // Perform real time payment transaction

	$order->info['comments'] = $_SESSION['novalnet'][$this->code]['nntrxncomments'];
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
