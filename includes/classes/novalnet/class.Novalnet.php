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
 * Script : class.Novalnet.php
 */
ob_start();
require_once('class.NovalnetInstaller.php');
require_once('class.NovalnetTranslator.php');
require_once('class.NovalnetPaymentKeys.php');
require_once('class.NovalnetValidation.php');
require_once('class.NovalnetInterface.php');

class NovalnetCore {

  /**
   * Load all language constants
   */
  static public function loadConstants() {
	NovalnetTranslator::setConstantValues();
  }

  /**
   * Return the module keys
   * @param $module
   *
   * @return array
   */
  static public function novalnetKeys($module) {
	NovalnetValidation::checkMerchantConfiguration();
	return array_keys(NovalnetPaymentKeys::getKeyValues($module));
  }
  
  /**
   * Install module
   * @param $module
   *
   * @return boolean
   */
  static public function installModule($module) {
	return NovalnetModuleInstaller::install($module);
  }

  /**
   * Uninstall module
   * @param $module
   *
   * @return boolean
   */
  static public function uninstallModule($module) {
	return NovalnetModuleInstaller::uninstall($module);
  }

  /**
   * Checking installed module status
   * @param $module
   *
   * @return boolean
   */
  static public function checkInstalledStatus($module) {
	return NovalnetModuleInstaller::installedStatus($module);
  }

  /**
   * Generate 30 digit unique string
   *
   * @return string
   */
  static public function uniqueRandomString() {
	return NovalnetValidation::randomString();
  }

  /**
   * Perform front-end payment form display pre-validation process
   * @param $orderinfo
   * @param $payment_name
   * @param $payment_enabled
   *
   * @return boolean
   */
  static public function validateMerchantAPIConf($orderinfo, $payment_name, $payment_enabled) {
	$novalnet_version = NovalnetInterface::checkNovalnetVersion();
	if(empty($novalnet_version) || getPaymentModuleVersion() != $novalnet_version) {
	  return false;
	}
	$merchant_api_error = NovalnetValidation::merchantValidate();
	if(!empty($merchant_api_error)) {
	  return false;
	}
	$paymentname = strtoupper($payment_name);
	if ( constant('MODULE_PAYMENT_' . $paymentname . '_ENABLE_MODULE') == 'True' && ( constant('MODULE_PAYMENT_' . $paymentname . '_VISIBILITY_BYAMOUNT') != '' && !is_numeric( constant('MODULE_PAYMENT_' . $paymentname . '_VISIBILITY_BYAMOUNT') ) ) ) {
	  return false;
	}
	if ( $payment_name == 'novalnet_sepa' && (self::getSepaPaymentDuration(true) != '' && (!is_numeric(self::getSepaPaymentDuration(true)) || self::getSepaPaymentDuration() < 7)) ) {
	  return false;
	}
	if ( $payment_name == 'novalnet_cc' && (trim(MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT) != '' && !is_numeric(trim(MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT))) ) {
	  return false;
	}
	if ( $payment_name == 'novalnet_invoice' && (trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE) != '' && !is_numeric(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE)) ) {
	  return false;
	}

	if($payment_name == 'novalnet_invoice' && MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE == 'True' && MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE1 == 'False' && MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE2 == 'False' && MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE3 == 'False') {
	  return false;
	}
	if($payment_name == 'novalnet_prepayment' && MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENABLE_MODULE == 'True' && MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE1 == 'False' && MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE2 == 'False' && MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE3 == 'False') {
	  return false;
	}
	$order_amount = NovalnetInterface::getPaymentAmount((array)$orderinfo, $payment_name);
	if (!NovalnetInterface::hidePaymentVisibility($payment_name, $order_amount)) {
	  return false;
	}
	if (!NovalnetInterface::checkValidPaymentZoneID((array)$orderinfo, $payment_name, $payment_enabled)) {
	  return false;
	}

	return true;
  }

  /**
   * Return merchant id
   * @param $send_orig
   *
   * @return mixed
   */
  static public function getVendorID($send_orig = false) {
	if (defined('MODULE_PAYMENT_NOVALNET_VENDOR'))
	  return ((!$send_orig) ? NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_VENDOR) : trim(MODULE_PAYMENT_NOVALNET_VENDOR));
	return false;
  }

  /**
   * Return project id
   * @param $send_orig
   *
   * @return mixed
   */
  static public function getProductID($send_orig = false) {
	if (defined('MODULE_PAYMENT_NOVALNET_PROJECT'))
	  return ((!$send_orig) ? NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_PROJECT) : trim(MODULE_PAYMENT_NOVALNET_PROJECT));

	return false;
  }

  /**
   * Return tariff id
   * @param $send_orig
   *
   * @return mixed
   */
  static public function getTariffID($send_orig = false) {
	if (defined('MODULE_PAYMENT_NOVALNET_TARIFF'))
	  return ((!$send_orig) ? MODULE_PAYMENT_NOVALNET_TARIFF : trim(MODULE_PAYMENT_NOVALNET_TARIFF));

	return false;
  }

  /**
   * Return merchant authorization code
   *
   * @return mixed
   */
  static public function getVendorAuthCode() {
	return ((defined('MODULE_PAYMENT_NOVALNET_AUTH_CODE')) ? trim(MODULE_PAYMENT_NOVALNET_AUTH_CODE) : false);
  }

  /**
   * Return payment access key
   *
   * @return mixed
   */
  static public function getPaymentAccessKey() {
	return ((defined('MODULE_PAYMENT_NOVALNET_ACCESS_KEY')) ? trim(MODULE_PAYMENT_NOVALNET_ACCESS_KEY) : false);
  }

  /**
   * Return manual check limit
   * @param $send_orig
   *
   * @return mixed
   */
  static public function getManualCheckLimit($send_orig = false) {
	if (defined('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT'))
	  return ((!$send_orig) ? NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT) : trim(MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT));

	return false;
  }

  /**
   * Return referrer id
   * @param $send_orig
   *
   * @return mixed
   */
  static public function getReferrerID($send_orig = false) {
	if (defined('MODULE_PAYMENT_NOVALNET_REFERRER_ID'))
	  return ((!$send_orig) ? NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_REFERRER_ID) : trim(MODULE_PAYMENT_NOVALNET_REFERRER_ID));

	return false;
  }

  /**
   * Return CURL gateway time-out value
   * @param $send_orig
   *
   * @return mixed
   */
  static public function getGatewayTimeout($send_orig = false) {
	if (defined('MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT'))
	  return ((!$send_orig) ? NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT) : trim(MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT));

	return false;
  }

  /**
   * Return tariff period
   *
   * @return mixed
   */
  static public function getTariffPeriod() {
	return ((defined('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD')) ? trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD) : false);
  }

  /**
   * Return tariff period2 amount in cents
   *
   * @return mixed
   */
  static public function getTariffPeriod2Amount() {
	return ((defined('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT')) ? trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT) : false);
  }

  /**
   * Return tariff period2
   *
   * @return mixed
   */
  static public function getTariffPeriod2() {
	return ((defined('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2')) ? trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2) : false);
  }

  /**
   * Return callback notification mail TO address
   *
   * @return mixed
   */
  static public function getCallbackNotifyMail() {
    return ((defined('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO')) ? trim(MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO) : false);
  }

  /**
   * Return callback notification mail BCC address
   *
   * @return mixed
   */
  static public function getCallbackNotifyMailBCC() {
	return ((defined('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC')) ? trim(MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC) : false);
  }

  /**
   * Validate email format
   * @param $input
   *
   * @return boolean
   */
  static public function isValidEmailFormat($input) {
	$input = trim($input);
	if (empty($input)) { return true; }
	return (!tep_validate_email($input)) ? false : true;
  }

  /**
   * Return callback status
   * @param $payment
   * @param $callback
   *
   * @return boolean
   */
  static public function validateCallbackStatus($payment = NULL, $callback = FALSE) {
	if ($callback) {
	  if (isset($_SESSION[$payment.'_payment_lock']) && $_SESSION[$payment.'_callback_max_time'] > time()) {
		if (!empty($_SESSION) && $_SESSION['payment'] == $payment) {
		  unset($_SESSION['payment']);
	    }
		return false;
	  }
	  elseif (isset($_SESSION[$payment.'_payment_lock']) && $_SESSION[$payment.'_callback_max_time'] < time()) {
		unset($_SESSION[$payment.'_payment_lock']);
		unset($_SESSION[$payment.'_callback_max_time']);
		unset($_SESSION['novalnet']);
		unset($_SESSION['nn_session']);
	  }
	}
	return true;
  }

  /**
   * Return last successful transaction payment status
   * @param $customer_email_address
   * @param $payment_code
   *
   * @return boolean
   */
  static public function getLastSuccessTransPayment($customer_email_address = '', $payment_code = '') {
	if ($customer_email_address == '' || $payment_code == '' || (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 1)) {
	  return false;
	}
	$sqlQuerySet = tep_db_query("SELECT payment_type FROM novalnet_transaction_detail WHERE customer_id='" . tep_db_input($_SESSION['customer_id']) . "' and active = 1 and status = 100 order by id desc");
	$sqlQuerySet = tep_db_fetch_array($sqlQuerySet);
   	if ($sqlQuerySet['payment_type'] == $payment_code) {
	  return true;
	}
	return false;
  }

  /**
   * Return countries for fraud modules
   *
   * @return array
   */
  static public function getCallbackCountries() {
	return array('DE', 'AT', 'CH');
  }

  /**
   * Return fraud module enable limit
   * @param $payment
   *
   * @return integer
   */
  static public function getFraudModuleEnableLimit($payment = NULL) {
	return NovalnetValidation::getNumeric(constant('MODULE_PAYMENT_'. strtoupper($payment) .'_CALLBACK_LIMIT'));
  }

  /**
   * Return customer firstname & lastname as customer name
   * @param $orderObj
   *
   * @return string / empty
   */
  static public function customerName($orderObj) {
	return (((!empty($orderObj['firstname'])) ? $orderObj['firstname'] : '') . ' ' . ( (!empty($orderObj['lastname'])) ? $orderObj['lastname'] : ''));
  }

  /**
   * Return credit card valid month
   *
   * @return array
   */
  static public function creditCardValidMonth() {
	$cc_month[] = array ('id' => '', 'text' => MODULE_PAYMENT_NOVALNET_MONTH_TEXT_MESSAGE);
	for ($i = 1; $i <= 12; $i++) {
	  $i_val = (($i<=9)?'0'.$i:$i);
	  $cc_month[] = array ('id' => $i_val, 'text' => $i_val);
	}
	return $cc_month;
  }

  /**
   * Return credit card valid year
   *
   * @return array
   */
  static public function creditCardValidYear() {
	$cc_year[] = array ('id' => '', 'text' => MODULE_PAYMENT_NOVALNET_YEAR_TEXT_MESSAGE);
	$today = getdate();

	if (MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT != '' && NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT) != '' && NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT) > 0) {
	  $merchant_year_limit = NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT);
	} else {
	  $merchant_year_limit = 25; // Default valid year limit if merchant not mentioned in configuration form
	}

	for ($i = $today['year']; $i < ($today['year']+$merchant_year_limit); $i++) {
	  $cc_year[] = array ('id' => $i, 'text' => $i);
	}
	return $cc_year;
  }

  /**
   * Return refill hash for credit card
   *
   * @return string / empty
   */
  static public function getCreditCardRefillHash() {
	if (MODULE_PAYMENT_NOVALNET_AUTO_REFILL == 'True') {
	  if (!empty($_SESSION['novalnet']['novalnet_cc']['nn_cc_hash'])) {
		return $_SESSION['novalnet']['novalnet_cc']['nn_cc_hash'];
	  }
	  elseif (!empty($_SESSION['novalnet']['novalnet_cc']['order_obj']['nn_cc_hash'])) {
		return $_SESSION['novalnet']['novalnet_cc']['order_obj']['nn_cc_hash'];
      }
	}
	return '';
  }

  /**
   * Return credit card script file path
   *
   * @return string
   */
  static public function creditCardScriptPath() {
	if (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Iframe')
	  return DIR_WS_CATALOG.'includes/classes/novalnet/js/novalnet_cc_iframe.js';
	  
	return DIR_WS_CATALOG.'includes/classes/novalnet/js/novalnet_cc.js';
  }

  /**
   * Return style file path
   *
   * @return string
   */
  static public function novalnetCssPath() {
	return DIR_WS_CATALOG.'includes/classes/novalnet/css/loader.css';
  }

  /**
   * Return sepa bank country list
   *
   * @return array
   */
  static public function sepaBankCountry() {
	$countries = tep_db_query("select countries_iso_code_2, countries_name from " . TABLE_COUNTRIES . " order by countries_name");
	$countries = tep_get_countries();
	foreach ($countries as $country) {
	  $country_list[] = tep_get_countries($country['countries_id'], true);
	}
	foreach ($country_list as $country) {
	  $countries_array[] = array( 'id' => $country['countries_iso_code_2'],
								  'text' => $country['countries_name']
								);
	}
	return $countries_array;
  }

  /**
   * Return refill hash for sepa
   * @param $customer_email_address
   * @param $payment_code
   *
   * @return string / empty
   */
  static public function getSepaRefillHash($customer_email_address = '', $payment_code = '') {
	if (isset($_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'])) {
	  return (( MODULE_PAYMENT_NOVALNET_AUTO_REFILL == 'True' || MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER == 'True') ? $_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'] : '');
	} elseif (MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER == 'True' && $customer_email_address != '' && $payment_code != '') {
	  return self::getLastSuccessTransProcessKey($customer_email_address, $payment_code);
	}
  }

  /**
   * Return last successful transaction payment status
   * @param $customer_email_address
   * @param $payment_code
   *
   * @return string / empty
   */
  static public function getLastSuccessTransProcessKey($customer_email_address = '', $payment_code = '') {
	if ($customer_email_address == '' || $payment_code == '') {
	  return '';
	}
	$sqlQuerySet = tep_db_query("SELECT payment_type, process_key FROM novalnet_transaction_detail WHERE customer_id='". tep_db_input($_SESSION['customer_id']) ."' and active = 1 and status = 100 order by id desc limit 1");
	$sqlQuerySet = tep_db_fetch_array($sqlQuerySet);
	if ($sqlQuerySet['payment_type'] == $payment_code) {

	  $_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'] = ((isset($sqlQuerySet['process_key']) && $sqlQuerySet['process_key'] != '') ? $sqlQuerySet['process_key'] : '');
	  return isset($_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash']) ? $_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'] : '';
	}
	return '';
  }

  /**
   * Return direct debit sepa script file path
   *
   * @return string
   */
  static public function sepaScriptPath() {
	if (MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE == 'Iframe')
	  return DIR_WS_CATALOG.'includes/classes/novalnet/js/novalnet_sepa_iframe.js';	  
	return DIR_WS_CATALOG.'includes/classes/novalnet/js/novalnet_sepa.js';
  }

  /**
   * Return direct debit sepa payment duration
   * @param $send_orig
   *
   * @return mixed
   */
  static public function getSepaPaymentDuration($send_orig = false) {
	if (defined('MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE'))
	  return ((!$send_orig) ? NovalnetValidation::getNumeric(MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE) : MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE);
	return false;
  }

  /**
   * Perform payment before_process
   * @param $datas
   *
   * @return mixed
   */
  static public function novalnet_before_process($datas) {
	return NovalnetInterface::doPayment($datas);
  }

  /**
   * Perform order status update with custom order status (as per merchant selection)
   * @param $order_id
   * @param $payment_name
   *
   * @return boolean
   */
  static public function updateOrderStatus($order_id = '', $payment_name = '') {
	if ($order_id != '' && $payment_name != '') {
	  NovalnetInterface::updateOrderCustomStatus($order_id, $payment_name);
	}
	return true;
  }

  /**
   * Perform the novalnet second call with novalnet gateway
   * @param $datas
   *
   * @return boolean
   */
  static public function doSecondCallProcess($datas) {
	$vendor    = $_SESSION['novalnet'][$datas['payment']]['vendor'];
	$auth_code = $_SESSION['novalnet'][$datas['payment']]['auth_code'];
	$product   = $_SESSION['novalnet'][$datas['payment']]['product'];
	$tariff    = $_SESSION['novalnet'][$datas['payment']]['tariff'];
	$tid       = $_SESSION['novalnet'][$datas['payment']]['tid'];
	$key	   = NovalnetInterface::getPaymentKey($datas['payment']);
	$data      = array( 'nn_vendor'    => $vendor,
						'nn_auth_code' => $auth_code,
						'nn_product'   => $product,
						'nn_tariff'    => $tariff,
						'order_no'	   => $datas['order_no'],
						'tid' 		   => $tid,
						'payment' 	   => $datas['payment']
						);
	if ($key == 27) {
	  $data['invoice_ref'] = 'BNR-'.$product.'-'.$datas['order_no'];
	}
	$trans_novalnet_info = NovalnetInterface::updateLiveNovalnetTransStatus(array('tid' => $tid, 'vendor' => $vendor, 'product' => $product, 'tariff' => $tariff, 'auth_code' => $auth_code), true);
	NovalnetInterface::logInitialTransaction(array(
				'tid' 			  		=> $tid,
				'vendor'          		=> $vendor,
				'product'         		=> $product,
				'tariff'          		=> $tariff,
				'auth_code'       		=> $auth_code,
				'subs_id' 		  		=> $trans_novalnet_info->subs_id,
				'payment_id' 	  		=> $key,
				'payment_type' 	  		=> $datas['payment'],
				'amount' 		  		=> $trans_novalnet_info->amount,
				'total_amount' 	  		=> $trans_novalnet_info->amount,
				'refund_amount'   		=> 0,
				'currency' 		  		=> $_SESSION['novalnet'][$datas['payment']]['order_currency'],
				'status' 		  		=> $_SESSION['novalnet'][$datas['payment']]['gateway_response']['status'],
				'order_no' 		  		=> $datas['order_no'],
				'callback_status' 		=> 0,
				'test_mode' 	  		=> $_SESSION['novalnet'][$datas['payment']]['test_mode'],
				'additional_note' 		=> $_SESSION['novalnet'][$datas['payment']]['additional_note'],
				'account_holder'  		=> $_SESSION['novalnet'][$datas['payment']]['card_account_holder'],
				'customer_id' 	  		=> $_SESSION['novalnet'][$datas['payment']]['customer_id'],
				'process_key' 	  		=> $_SESSION['novalnet'][$datas['payment']]['process_key'],
				'gateway_status'  		=> ((isset($trans_novalnet_info->status)) ? $trans_novalnet_info->status : 0),
				'masked_acc_details'    => $_SESSION['novalnet'][$datas['payment']]['masked_acc_details'],
				'reference_transaction' => $_SESSION['novalnet'][$datas['payment']]['reference_transaction'],
				'zerotrxnreference'	  	=> $_SESSION['novalnet'][$datas['payment']]['zerotrxnreference'],
				'zerotrxndetails'		=> $_SESSION['novalnet'][$datas['payment']]['zerotrxndetails'],
				'zero_transaction' 	  	=> $_SESSION['novalnet'][$datas['payment']]['zero_transaction']
      ));

	// PayPal payment pending
	if ($trans_novalnet_info->status == 90 && $datas['payment'] == 'novalnet_paypal') {
	  NovalnetInterface::updateOrderCustomStatus($datas['order_no'], $datas['payment'], $trans_novalnet_info->status);
	}
	if ($trans_novalnet_info->status == 100  && $datas['payment'] == 'novalnet_paypal') {
	  NovalnetInterface::logCallbackProcess(array('payment_type' => 'PAYPAL', 'status' => 100, 'callback_tid' => $tid, 'org_tid' => $tid, 'amount' => $trans_novalnet_info->amount,'currency' => $_SESSION['novalnet'][$datas['payment']]['order_currency'], 'product_id' => $product, 'order_no' => $datas['order_no']));
	}
	if (!empty($trans_novalnet_info->subs_id)) {
	  NovalnetInterface::logSubscriptionTransInfo(array( 'subs_id'     => $trans_novalnet_info->subs_id,
														 'tid' 		   => $tid,
														 'signup_date' => date('Y-m-d H:i:s'),
														 'order_no'    => $datas['order_no'],
					                                 ));
	}
	unset($_SESSION['novalnet']);
	if (isset($_SESSION['nn_aff_id'])) {
      tep_db_perform('novalnet_aff_user_detail',array('aff_id' => $_SESSION['nn_aff_id'], 'customer_id' => $_SESSION['customer_id'], 'aff_order_no' => $datas['order_no']),'insert');
      unset($_SESSION['nn_aff_id']);
    }
	return NovalnetInterface::doPaymentSecondCall($data);
  }

  /**
   * Validate redirect payment server response
   * @param $orderinfo
   * @param $payment_name
   * @param $payment_response
   *
   * @return boolean
   */
  static public function validateRedirectResponse($orderinfo, $payment_name, $payment_response) {
	if ($payment_name != 'novalnet_cc' || ($payment_name == 'novalnet_cc' && !isset($payment_response['encoded_amount']))) {
	  $payment_response['vendor']    = isset($payment_response['vendor']) ? $payment_response['vendor'] : $payment_response['vendor_id'];
	  $payment_response['auth_code'] = isset($payment_response['auth_code']) ? $payment_response['auth_code'] : $payment_response['vendor_authcode'];
      $payment_response['tariff']    = isset($payment_response['tariff']) ? $payment_response['tariff'] : $payment_response['tariff_id'];
      $payment_response['product']   = isset($payment_response['product']) ? $payment_response['product'] : $payment_response['product_id'];
	  if (!NovalnetInterface::validateHashResponse($payment_response)) { // Hash validation failed
		$payment_error_return = 'payment_error='. $payment_name .'&error=' .  MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR;
		$payment_error_return = NovalnetInterface::setUTFText($payment_error_return);
		tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	  }
	  $payment_response = NovalnetInterface::decodePaygateResponse($payment_response);
	}
	$paygate_result_qry = http_build_query($payment_response); // Build gateway string from the array
	$paymentresponse = NovalnetInterface::checkPaymentStatus($payment_name, $paygate_result_qry, $orderinfo);
	if ( $paymentresponse['status'] == 100 || ( $payment_name == 'novalnet_paypal' && $paymentresponse['status'] == 90 ) ) { // Payment success	
      if ($payment_name == 'novalnet_cc') {
		$card_details = $_SESSION['novalnet'][$payment_name]['card_details'];
		$xml_request = '<?xml version="1.0" encoding="UTF-8"?>
						  <nnxml>
							<info_request>
							  <vendor_id>'.$payment_response['vendor'].'</vendor_id>
							  <vendor_authcode>'.$payment_response['auth_code'].'</vendor_authcode>
							  <request_type>TRANSACTION_PAYMENT_DETAILS</request_type>
							  <product_id>'.$payment_response['product_id'].'</product_id>
							  <tid>'.$payment_response['tid'].'</tid>
							</info_request>
						  </nnxml>';
		$response = NovalnetInterface::doPaymentCurlCall('https://payport.novalnet.de/nn_infoport.xml',$xml_request,false);
		$xml_response = simplexml_load_string($response);
        $maskedvalues = serialize(array(
                'cc_holder' => html_entity_decode($xml_response->cc_holder, ENT_QUOTES, "UTF-8"),
                'cc_no'     => NovalnetInterface::novalnet_masking($xml_response->cc_no),
                'cc_exp_year' => (isset($card_details['novalnet_cc_exp_year']) && $card_details['novalnet_cc_exp_year'])?$card_details['novalnet_cc_exp_year']:$card_details['nn_ref_cc_exp_year'],
                'cc_exp_month' => (isset($card_details['novalnet_cc_exp_month']) && $card_details['novalnet_cc_exp_month'])?$card_details['novalnet_cc_exp_month']:$card_details['nn_ref_cc_exp_month'],
            ));
	  }
	  if(($payment_name == 'novalnet_cc' && MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ZEROAMOUNT')) {
		unset($_SESSION['novalnet'][$payment_name]['request']['encoded_amount']);
		$zerotrxndetails = serialize($_SESSION['novalnet'][$payment_name]['request']);
	  }
	  $_SESSION['novalnet'][$payment_name] = array(
						'order_amount' 		    => $payment_response['amount'],
						'order_currency' 	    => $paymentresponse['gateway_response']['currency'],
						'tid' 				    => $paymentresponse['tid'],
						'product'          	    => ((isset($payment_response['product']))?$payment_response['product']:$payment_response['product_id']),
						'tariff'          	    => $payment_response['tariff'],
						'vendor'           	    => $payment_response['vendor'],
						'auth_code'        	    => $payment_response['auth_code'],
						'gateway_response' 	    => $paymentresponse,
						'additional_note' 	    => NovalnetInterface::getCustomerCustomComments((array)$_SESSION['novalnet'][$payment_name]['order_obj']),
						'card_account_holder'   => ((isset($_SESSION['novalnet'][$payment_name]['card_account_holder']))?$_SESSION['novalnet'][$payment_name]['card_account_holder']:''),
						'test_mode' 		    => NovalnetInterface::getPaymentTestModeStatus($payment_name),
						'customer_id' 		    => $_SESSION['customer_id'],
						'masked_acc_details'    => isset($maskedvalues) ? $maskedvalues : '',
						'reference_transaction' => isset($_SESSION['novalnet'][$payment_name]['urlparam']['payment_ref'])?'1':'0',
						'zerotrxnreference'	    => $paymentresponse['tid'],
						'zerotrxndetails'		=> $zerotrxndetails,
						'zero_transaction'      => ($urlparam['amount'] == 0) ? '1' : '0'
						);
	  return $paymentresponse; // For after_process functionality
	} else { // Payment failed
	  $payment_error_return = 'payment_error='. $payment_name .'&error=' . NovalnetInterface::setUTFText($paymentresponse['status_desc'], true);
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
	return true;
  }

  
  /**
   * Get novalnet prepayment and invoice account information from novalnet_preinvoice_transaction_detail table
   * @param $tid
   *
   * @return array
   */
  static public function getPreInvoiceAcccountInfo($tid) {
	$sqlQuery = tep_db_query("SELECT test_mode, order_no, account_holder, account_number, bank_code, bank_name, bank_city, amount, currency, bank_iban, bank_bic, due_date FROM novalnet_preinvoice_transaction_detail WHERE tid='". tep_db_input($tid) ."'");
	$accountInfo = tep_db_fetch_array($sqlQuery);
	$accountInfo['tid'] = $tid;
	return $accountInfo;
  }

  /**
   * Get novalnet transaction information from novalnet_transaction_detail table
   * @param $order_no
   *
   * @return array
   */
  static public function getNovalnetTransDetails($order_no) {
	$transInfo = tep_db_fetch_array(tep_db_query("SELECT tid, vendor, product, tariff, auth_code, subs_id, payment_id, payment_type, amount, currency, status, gateway_status, callback_status, date, additional_note, account_holder, test_mode, customer_id, process_key, total_amount, refund_amount FROM novalnet_transaction_detail WHERE order_no='". tep_db_input($order_no) ."' and active = 1"));
	$transInfo['order_no'] = $order_no;
	return $transInfo;
  }

  /**
   * Get novalnet callback amount form novalnet_callback_history table
   * @param $order_no
   *
   * @return array
   */
  static public function getNovalnetCallbackAmount($order_no) {
	$sqlQuery = tep_db_query("SELECT SUM(amount) as total_amount FROM novalnet_callback_history WHERE order_no='". tep_db_input($order_no) ."'");
	$transInfo = tep_db_fetch_array($sqlQuery);
	return $transInfo['total_amount'];
  }

  /**
   * Get novalnet subscription transaction information from novalnet_subscription_detail table
   * @param $order_no
   *
   * @return array
   */
  static public function getNovalnetSubscriptionTransDetails($order_no) {
	$sqlQuery = tep_db_query("SELECT subs_id, tid, signup_date, termination_reason, termination_at FROM novalnet_subscription_detail WHERE order_no='". tep_db_input($order_no) ."'");
	$transInfo = tep_db_fetch_array($sqlQuery);
	return $transInfo;
  }

  /**
   * Return affiliate details
   * @param $urlparam
   *
   * @return empty
   */
  static public function getAffDetails(&$urlparam) {
	$_SESSION['nn_access_key'] = self::getPaymentAccessKey();
	if ( $_SESSION['customer_id'] != '' && (!isset($_SESSION['nn_aff_id']) || $_SESSION['nn_aff_id'] == '' ) ) {
	  $db_value = tep_db_fetch_array(tep_db_query('SELECT aff_id FROM novalnet_aff_user_detail WHERE customer_id = "'. tep_db_input($_SESSION['customer_id']).'" ORDER BY id DESC LIMIT 1'));
	  if ( !empty($db_value['aff_id']) ) {
		$_SESSION['nn_aff_id'] = $db_value['aff_id'];
	  }
	}
	if (isset($_SESSION['nn_aff_id']) && NovalnetValidation::getNumeric($_SESSION['nn_aff_id']) != '') {
	  $db_value = tep_db_fetch_array(tep_db_query('SELECT aff_authcode, aff_accesskey FROM novalnet_aff_account_detail WHERE aff_id = "'.tep_db_input($_SESSION['nn_aff_id']).'" and vendor_id = "'.tep_db_input(self::getVendorID()).'"  ORDER BY id DESC'));
	  if (trim($db_value['aff_accesskey']) != '' && trim($db_value['aff_authcode']) != '' && $_SESSION['nn_aff_id'] != '') {
		$urlparam['vendor']        = $_SESSION['nn_aff_id'];
		$urlparam['auth_code']     = $db_value['aff_authcode'];
		$_SESSION['nn_access_key'] = $db_value['aff_accesskey'];
	  }
	}
  }
  
  /**
   * Perform manual check limit functionality
   *
   * @return none
   */
  static public function validatecallbacksession() {
	if(isset($_SESSION['customer_id']) && isset($_SESSION['novalnet']['login'])) {
	  $sql=tep_db_fetch_array(tep_db_query("select customers_info_number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $_SESSION['customer_id'] . "'"));
	  $_SESSION['novalnet']['new_login_id'] = $sql['customers_info_number_of_logons'];
	  if($_SESSION['novalnet']['login'] != $_SESSION['novalnet']['new_login_id']) {
		unset($_SESSION['novalnet']);
		if( isset($_SESSION['novalnet_cc_payment_lock']) ) { unset($_SESSION['novalnet_cc_payment_lock']); }
		if( isset($_SESSION['novalnet_sepa_payment_lock']) ) { unset($_SESSION['novalnet_sepa_payment_lock']); }
		if( isset($_SESSION['novalnet_invoice_payment_lock']) ) { unset($_SESSION['novalnet_invoice_payment_lock']); }
	  }
	}
	$query= tep_db_fetch_array(tep_db_query("select customers_info_number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $_SESSION['customer_id'] . "'"));
	$_SESSION['novalnet']['login'] = $query['customers_info_number_of_logons'];
  }
  
 /*
  * To get the masked account details
  * @param $customers_id
  * @param $payment
  *
  * @return mixed
  */
  static public function getPaymentRefDetails($customers_id, $payment) {
    if ($customers_id == '' || $payment == '') {
      return false;
    }
	$sqlQuerySet = tep_db_query("SELECT masked_acc_details, tid, process_key FROM novalnet_transaction_detail WHERE customer_id='". tep_db_input($customers_id) ."' and payment_type = '" . $payment . "' AND reference_transaction='0' ORDER BY id DESC LIMIT 1");
    $sqlQuerySet = tep_db_fetch_array($sqlQuerySet);
    if (!empty($sqlQuerySet['masked_acc_details'])) {
      return $sqlQuerySet;
    }
    return false;
  }  
  
  /*
  * Perform DB validation process
  *
  * @return array
  */
  static public function validateMerchantDetails($datas) {
    return (!$datas['vendor'] || !$datas['product'] || !$datas['tariff'] || !$datas['auth_code']) ? MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR : '';
  }
}
?>
