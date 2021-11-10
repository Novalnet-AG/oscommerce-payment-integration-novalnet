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
 * Script : class.NovalnetValidation.php
 */
require_once('class.Novalnet.php');
require_once(DIR_WS_FUNCTIONS.'validations.php');

class NovalnetValidation extends NovalnetCore {

  /**
   * Get numeric values from the input value
   * @param $input
   *
   * @return mixed
   */
  static public function getNumeric($input = '') {
	$input = trim($input);
	if ($input == '') { return ''; }
	preg_match_all('/\d+/', $input, $output);
	unset($input);
	return ( (!empty($output[0][0])) ? $output[0][0] : '' );
  }

  /**
   * Generate unique string
   *
   * @return string
   */
  static public function randomString() {
	$randomwordarray=explode(",", "a,b,c,d,e,f,g,h,i,j,k,l,m,1,2,3,4,5,6,7,8,9,0");
	shuffle($randomwordarray);
	return substr(implode($randomwordarray,""), 0, 30);
  }

  /**
   * Get protocol type of the site
   *
   * @return string
   */
  static public function getSiteDomain() {
	return ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER);
  }

  /**
   * Get valid holder name
   * @param $org_holder_name
   *
   * @return string
   */
  static public function getValidHolderName($org_holder_name = '') {
	return str_replace('&', '', trim($org_holder_name));
  }

  /*
  * Validate the payment configuration and display the error/warning message
  * @param $module
  *
  * @return mixed
  */
  static public function checkMerchantConfiguration($module = '') {
    parent::loadConstants();
    if(in_array($_GET['module'], array('novalnet_cc', 'novalnet_sepa', 'novalnet_invoice', 'novalnet_prepayment', 'novalnet_eps', 'novalnet_paypal', 'novalnet_ideal', 'novalnet_sofortbank', 'novalnet_config')) && !isset($_GET['action']) && $_GET['action'] != 'edit') {
	  $novalnet_tables = false;
      $tables_sql = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '"');
      while($result = tep_db_fetch_array($tables_sql)) {
	    if($result['table_name'] == 'novalnet_transaction_detail')
		  $novalnet_tables = true;
	  }
	  $novalnet_version = NovalnetInterface::checkNovalnetVersion();
	  if (!empty($novalnet_tables) && (empty($novalnet_version) || getPaymentModuleVersion() != $novalnet_version)) {
	    echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_VERSION_ERROR);
	  }
    }
    $merchant_api_error = self::merchantValidate();
    if($merchant_api_error && empty($_GET['action'])) {
      if(strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false && $module == '') {
        echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE);
      }
    }
    
    $payment_file = array('NOVALNET_CC', 'NOVALNET_SEPA', 'NOVALNET_PAYPAL', 'NOVALNET_EPS', 'NOVALNET_INVOICE', 'NOVALNET_PREPAYMENT', 'NOVALNET_SOFORTBANK', 'NOVALNET_IDEAL');    
    foreach($payment_file as $k) {
      if(strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false && strpos(MODULE_PAYMENT_INSTALLED, strtolower($k)) !== false && defined('MODULE_PAYMENT_'.$k.'_ENABLE_MODULE') && constant('MODULE_PAYMENT_'.$k.'_ENABLE_MODULE') == 'True'    && constant('MODULE_PAYMENT_'.$k.'_VISIBILITY_BYAMOUNT') != ''
        && !ctype_digit(constant('MODULE_PAYMENT_'.$k.'_VISIBILITY_BYAMOUNT'))) {
        if(!isset($_GET['action']) && $_GET['action'] != 'edit'){
          echo self::novalnetBackEndShowError(constant('MODULE_PAYMENT_'. $k .'_BLOCK_TITLE'));
        }
      }
    }
    if(strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false && strpos(MODULE_PAYMENT_INSTALLED,'novalnet_sepa') !== false && MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_MODULE == 'True'
        && ((parent::getSepaPaymentDuration(true) != '' && (!ctype_digit(parent::getSepaPaymentDuration(true))
            || parent::getSepaPaymentDuration() < 7)))) {
      if(!isset($_GET['action']) && $_GET['action'] != 'edit') {
        echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_ERROR);
      }
    }
    if(strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false && strpos(MODULE_PAYMENT_INSTALLED,'novalnet_cc') !== false && MODULE_PAYMENT_NOVALNET_CC_ENABLE_MODULE == 'True'
        && ((trim(MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT) != '' && !ctype_digit(trim(MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT))) )) {
      if(!isset($_GET['action']) && $_GET['action'] != 'edit') {
        echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CC_BLOCK_TITLE);
      }
    }
    if(strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false && strpos(MODULE_PAYMENT_INSTALLED,'novalnet_invoice') !== false
        && MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE == 'True' && ((trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE) != '' && !ctype_digit(trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE))))) {
      if(!isset($_GET['action']) && $_GET['action'] != 'edit') {
        echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_ERROR);
      }
    }
    if(strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false && strpos(MODULE_PAYMENT_INSTALLED,'novalnet_invoice') !== false
        && MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE == 'True' && MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE1 == 'False' && MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE2 == 'False' && MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE3 == 'False') {
      if(!isset($_GET['action']) && $_GET['action'] != 'edit') {
        echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_REFERENCE_ERROR);
      }
    }
    if(strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false && strpos(MODULE_PAYMENT_INSTALLED,'novalnet_prepayment') !== false
        && MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENABLE_MODULE == 'True' && MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE1 == 'False' && MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE2 == 'False' && MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE3 == 'False') {
      if(!isset($_GET['action']) && $_GET['action'] != 'edit') {
        echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_PREPAYMENT_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_REFERENCE_ERROR);
      }
    }

    if(!function_exists('base64_encode') || !function_exists('pack') || !function_exists('crc32') || !function_exists('md5') || !function_exists('curl_init')) {
       echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_FUNC_ERROR);
    }
  }

  /*
  * Show validation error in backend
  * @param $error_payment
  * @param $other_error
  *
  * @return string
  */
  static public function novalnetBackEndShowError($error_payment, $other_error = '') {
    return '<div style="border: 1px solid #0080c9; background-color: #FCA6A6; padding: 10px;    font-family: Arial, Verdana; font-size: 11px; margin:0px 5px 5px 0;"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png" alt="Novalnet"/><b>'.$error_payment.'</b><br/><br/>'.($other_error != '' ? $other_error : MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR).'</div>';
  }

  /*
  * Validate the Merchant API configuration and display the error/warning message
  *
  * return boolean
  */
  static public function merchantValidate() {
    $merchant_api_error = false;
    $pattern = "/^\d+\|\d+\|[\w-]+\|\w+\|\w+\|(|\d+)\|(|\d+)\|(|\d+)\|(|\d+)\|(|\w+)\|(|\w+)$/";
    $value = parent::getVendorID(true) . '|' . parent::getProductID(true) . '|' . parent::getTariffID(true) . '|' . parent::getVendorAuthCode() . '|' . parent::getPaymentAccessKey() . '|' . parent::getManualCheckLimit(true) . '|' . parent::getReferrerID(true) . '|' . parent::getGatewayTimeout(true) . '|' . parent::getTariffPeriod2Amount() . '|' . parent::getTariffPeriod2() . '|' . parent::getTariffPeriod();
    preg_match($pattern, $value, $match);
    if(empty($match[0])) {
      $merchant_api_error = true;
    } elseif ( parent::getTariffPeriod2Amount() != '' && !is_numeric(parent::getTariffPeriod2Amount()) ) {
	  $merchant_api_error = true;
	} elseif ( parent::getTariffPeriod2Amount() != '' && is_numeric(parent::getTariffPeriod2Amount()) && parent::getTariffPeriod2() == '' ) {
	  $merchant_api_error = true;
	} elseif ( ( parent::getTariffPeriod2Amount() == '' || !is_numeric(parent::getTariffPeriod2Amount()) ) && parent::getTariffPeriod2() != '' ) {
	  $merchant_api_error = true;
	} elseif (self::getCallbackNotifyMail(true) != '' || self::getCallbackNotifyMailBCC(true) != '') {
      $email = explode(',', self::getCallbackNotifyMail(true) . ',' . self::getCallbackNotifyMailBCC(true));
      foreach($email as $value) {
        if (trim($value) != '' && !parent::isValidEmailFormat($value)) {
          $merchant_api_error = true;
        }
      }
    }
    return $merchant_api_error;
  }

  /**
   * Validate input form parameters based on payment types
   * @param $fraud_module
   * @param $fraud_module_status
   * @param $payment_module
   * @param $datas
   *
   * @return none
   */
  static public function validateUserInputs($fraud_module, $fraud_module_status, $payment_module = '', $datas = array()) {
	$datas = array_map('trim', $datas);
	if ( !empty($payment_module) && !empty($datas) ) {
	  $error_found = 0;
	  $error_message = '';
	  switch ($payment_module) {
		case 'novalnet_cc':
						if ( !empty($datas['nn_cc_js_enabled']) ) {
						  $error_found   = 1;
						  $error_message = MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_PROBLEM;
						}
						if ( !$error_found && ( empty($datas['novalnet_cc_cvc']) || empty($datas['nn_cc_hash']) || empty($datas['nn_cc_uniqueid']) ) ) {
						  $error_found   = 1;
						  $error_message = MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS;
						}
						if ( !$error_found && $fraud_module && $fraud_module_status ) {
						  list ($error_found, $error_message) = self::validateCallbackFields($datas, $fraud_module, $payment_module);
						}
						break;
		case 'novalnet_sepa':
						if ( !empty($datas['nn_sepa_js_enabled']) ) {
						  $error_found   = 1;
						  $error_message = MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_PROBLEM;
						}
						if ( !$error_found && ( empty($datas['novalnet_sepa_account_holder']) || empty($datas['nn_sepa_hash']) || empty($datas['nn_sepa_uniqueid']) ) ) {
						  $error_found   = 1;
						  $error_message = MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_DETAILS;
						}
						if ( !$error_found && $fraud_module && $fraud_module_status ) {
						  list ($error_found, $error_message) = self::validateCallbackFields($datas, $fraud_module, $payment_module);
						}
						break;
		case 'novalnet_invoice':
						if ( !$error_found && $fraud_module && $fraud_module_status ) {
						  list ($error_found, $error_message) = self::validateCallbackFields($datas, $fraud_module, $payment_module);
						}
						break;
	  }
	  if(isset($_SESSION['novalnet'])) { unset($_SESSION['novalnet']); }// Unset existing novalnet values
	  if (MODULE_PAYMENT_NOVALNET_AUTO_REFILL == 'True') { // For refilling purpose
		$_SESSION['novalnet'][$payment_module] = $datas;
	  }
	  if ($error_found == 1) {
		if ($error_message == '') {
		  $error_message = MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR;
	    }
	    $payment_error_return = 'payment_error='. $payment_module .'&error=' . NovalnetInterface::setUTFText($error_message);
		tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	  }
	}
  }

  /**
   * Validate input form callback parameters
   * @param $datas
   * @param $fraud_module
   * @param $code
   *
   * @return array
   */
  static public function validateCallbackFields ($datas, $fraud_module, $code) {
	if ( $fraud_module == 'CALLBACK' && ( empty($datas[$code . '_fraud_tel']) || !is_numeric($datas[$code . '_fraud_tel']) ) ) {
	  return array ('1', MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR);
	} elseif ( $fraud_module == 'EMAIL' && ( empty($datas[$code . '_fraud_email']) || empty($datas[$code . '_fraud_email']) || !parent::isValidEmailFormat($datas[$code . '_fraud_email']) ) ) {
	  return array ('1', MODULE_PAYMENT_NOVALNET_FRAUDMODULE_EMAIL_ERROR);
	} elseif ( $fraud_module == 'SMS' && (empty($datas[$code . '_fraud_mobile']) || !is_numeric($datas[$code . '_fraud_mobile'])|| strlen($datas[$code . '_fraud_mobile']) < 8) ) {
	  return array ('1', MODULE_PAYMENT_NOVALNET_FRAUDMODULE_MOBILE_ERROR);
	}
    return array ('0', '');
  }

  /**
   * Validate callback country and customer's country
   *
   * @return boolean
   */
  static public function validateCallbackCountry() {
	global $order;
	$customer_iso_code = strtoupper($order->customer['country']['iso_code_2']);
    return ($customer_iso_code && in_array($customer_iso_code, parent::getCallbackCountries()));
  }

  /**
   * Validate users input for callback
   * @param $fraud_module
   * @param $payment_module
   * @param $datas
   *
   * @return none
   */
  static public function validateUserInputsOnCallback($fraud_module, $payment_module = '', $datas = array()) {
	$error_message = '';
	$datas 	       = array_map('trim', $datas);
	if ( ($payment_module == 'novalnet_cc' && !empty($datas['nn_cc_js_enabled'])) || ($payment_module == 'novalnet_sepa' && !empty($datas['nn_sepa_js_enabled'])) ) {
	  $error_message = MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_PROBLEM;
	}
	elseif ( in_array($fraud_module, array('CALLBACK', 'SMS')) ) {
	  if ( !isset($datas[$payment_module . '_new_pin']) && isset($datas[$payment_module . '_fraud_pin']) && ( empty($datas[$payment_module . '_fraud_pin']) || !preg_match('/^[a-zA-Z0-9]+$/', $datas[$payment_module . '_fraud_pin']) ) ) {
		$error_message = MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID;
	  } else {
		$_SESSION['novalnet'][$payment_module][$payment_module .'_new_pin'] = !isset($datas[$payment_module . '_new_pin']) ? 0 : '';
	  }
	}
	if (!empty($error_message)) {
	  $payment_error_return = 'payment_error='. $payment_module .'&error=' . NovalnetInterface::setUTFText($error_message);
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
  }

  /**
   * Validate amount for callback
   * @param $payment_module
   * @param $fraud_module
   *
   * @return none
   */
  static public function validateAmountOnCallback($payment_module = '',$fraud_module='') {
	global $order;
	$error_message = '';
	if ($_SESSION['novalnet'][$payment_module]['order_amount'] != NovalnetInterface::getPaymentAmount((array)$order, $payment_module)) {
	  unset($_SESSION['novalnet'][$payment_module]);
	  $error_message = MODULE_PAYMENT_NOVALNET_FRAUDMODULE_AMOUNT_CHANGE_ERROR;
	}
	if (!empty($error_message)) {
	  $payment_error_return = 'payment_error='. $payment_module .'&error=' . NovalnetInterface::setUTFText($error_message);
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
  }

  /**
   * Validate response of callback
   * @param $response
   * @param $request_type
   *
   * @return none
   */
  static public function validateNewPinResponse($response, $request_type, $code) {
	list($status, $statusMessage) = NovalnetInterface::getStatusFromXmlResponse($response);
	if ( $request_type == 'TRANSMIT_PIN_AGAIN' && $status != 100 ) {
	  $payment_error_return = 'payment_error='. $code .'&error=' .  $statusMessage;
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
  }

  /**
   * Return JS script to disable confirm button
   *
   * @return string
   */
  static public function confirmButtonDisableActivate() {
    return '<script type="text/javascript">
		if(typeof(jQuery) != "undefined" ) {
          $(document).ready(function(){
            $("form[name=checkout_confirmation]").submit(function(){
			  $(this).find("button[type=submit]").attr("disabled", "disabled");
              $(this).find("button[type=submit]").css("opacity", "0.3");
            });
          });
	    }
        </script>';
  }
}
