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
 * Script : class.NovalnetPaymentKeys.php
 */
class NovalnetPaymentKeys {

  /**
   * Get the module keys based on input module name
   * @param $moduleName
   *
   * @return array
   */
  static public function getKeyValues($moduleName = '') {
	switch ($moduleName) {
	  case 'novalnet_config':
				return self::getBasicConfigKeys();
				break;
	  case 'novalnet_invoice':
				return self::getInvoiceKeys();
				break;
	  case 'novalnet_prepayment':
				return self::getPrepaymentKeys();
				break;
	  case 'novalnet_cc':
				return self::getCreditCardKeys();
				break;
	  case 'novalnet_sepa':
				return self::getDirectDebitSEPAKeys();
				break;
	  case 'novalnet_sofortbank':
			    return self::getSofortBankKeys();
			    break;
	  case 'novalnet_ideal':
				return self::getIdealKeys();
				break;
	  case 'novalnet_paypal':
				return self::getPayPalKeys();
				break;
	  case 'novalnet_eps':
			    return self::getEPSKeys();
				break;
	  default:
				return array();
	}
  }

  /**
   * Get novalnet basic API module configuration keys
   *
   * @return array
   */
  static public function getBasicConfigKeys() {
	global $request_type;
	return array(
		'MODULE_PAYMENT_NOVALNET_PUBLIC_KEY' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PUBLIC_KEY_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PUBLIC_KEY_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_VENDOR' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_VENDOR_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_VENDOR_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_AUTH_CODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_AUTH_CODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_AUTH_CODE_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PROJECT' => array(
				'desc'		   => MODULE_PAYMENT_NOVALNET_PROJECT_DESC,
				'title' 	   => MODULE_PAYMENT_NOVALNET_PROJECT_TITLE,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_TARIFF' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_TARIFF_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_TARIFF_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_ACCESS_KEY' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_ACCESS_KEY_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_ACCESS_KEY_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_AUTO_REFILL' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_AUTO_REFILL_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_AUTO_REFILL_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_AUTO_REFILL', MODULE_PAYMENT_NOVALNET_AUTO_REFILL,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_TITLE,
				'desc'	  	   => MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION',  MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PROXY' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PROXY_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PROXY_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT_DESC,
				'value' 	   => '240',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_REFERRER_ID' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_REFERRER_ID_TITLE,
				'desc'	 	   => MODULE_PAYMENT_NOVALNET_REFERRER_ID_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY_DESC,
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY', MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY,",
				'use_function' => '',
				),
		'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_DESC,
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY',MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY,",
				'use_function' => '',
				),				
		'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_DESC,
				'value'  	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_DEBUG_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_DEBUG_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_DEBUG_MODE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_DEBUG_MODE', MODULE_PAYMENT_NOVALNET_DEBUG_MODE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_TEST_MODE_CALLBACK' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE,
				'desc'	  	   => MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_TEST_MODE_CALLBACK', MODULE_PAYMENT_NOVALNET_TEST_MODE_CALLBACK,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_DESC,
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND', MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_DESC,
				'value' 	   => STORE_OWNER_EMAIL_ADDRESS,
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CALLBACK_URL' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CALLBACK_URL_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CALLBACK_URL_DESC,
				'value' 	   => ((($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG) . 'callback_novalnet2oscommerce.php',
				'set_function' => '',
				'use_function' => '',
				),
	);
  }

  /**
   * Get novalnet invoice module configuration keys
   *
   * @return array
   */
  static public function getInvoiceKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE_TITLE,
				'desc'	 	   => MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_MODULE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE', MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('False' => '" . MODULE_PAYMENT_NOVALNET_OPTION_NONE . "','CALLBACK' => '" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK . "','SMS' =>'" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS . "','EMAIL' =>'" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONEMAIL . "'), 'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE', MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT_DESC,
				'value'    	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BYAMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BYAMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACKSCRIPT_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACKSCRIPT_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACKSCRIPT_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_TRANS_REFERENCE1_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_INVOICE_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_INVOICE_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_1,
				'desc'		   => '',
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE1', MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE1,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_2,
				'desc'		   => '',
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE2', MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE2,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE3' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_3,
				'desc'		   => '',
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE3', MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_REFERENCE3,",
				'use_function' => ''
				)
	);
  }

  /**
   * Get novalnet prepayment module configuration keys
   *
   * @return array
   */
  static public function getPrepaymentKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENABLE_MODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENABLE_MODULE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_TEST_MODE_DESC,
				'value'    	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PREPAYMENT_TEST_MODE', MODULE_PAYMENT_NOVALNET_PREPAYMENT_TEST_MODE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_VISIBILITY_BYAMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_VISIBILITY_BYAMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENDCUSTOMER_INFO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_ORDER_STATUS_DESC,
				'value'        => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_CALLBACKSCRIPT_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_CALLBACKSCRIPT_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_CALLBACKSCRIPT_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_TRANS_REFERENCE1_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PREPAYMENT_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_1,
				'desc'		   => '',
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE1', MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE1,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_2,
				'desc'		   => '',
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE2', MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE2,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE3' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_3,
				'desc'		   => '',
				'value' 	   => 'True',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE3', MODULE_PAYMENT_NOVALNET_PREPAYMENT_PAYMENT_REFERENCE3,",
				'use_function' => ''
				)
		);
  }

  /**
   * Get novalnet credit card module configuration keys
   *
   * @return array
   */
  static public function getCreditCardKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_CC_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_ENABLE_MODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CC_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_CC_ENABLE_MODULE,",
				'use_function' => ''
				),
        'MODULE_PAYMENT_NOVALNET_CC_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CC_TEST_MODE', MODULE_PAYMENT_NOVALNET_CC_TEST_MODE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('False' => '" . MODULE_PAYMENT_NOVALNET_OPTION_NONE . "','CALLBACK' => '" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK . "','SMS' =>'" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS . "','EMAIL' =>'" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONEMAIL . "'), 'MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE', MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_CALLBACK_LIMIT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_CALLBACK_LIMIT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_CALLBACK_LIMIT_DESC,
				'value'  	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE', MODULE_PAYMENT_NOVALNET_CC_3D_SECURE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT', MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT,",
				'use_function' => ''
				),				
		'MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT', MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT,",
				'use_function' => '',
				),
        'MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_DESC,
				'value' 	   => 'False',
				'set_function' =>"tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT', MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT,",
				'use_function' => '',
				),
        'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC,
				'value'		   => 'False',
			    'set_function' => "tep_mod_select_option(array('False' => '" . MODULE_PAYMENT_NOVALNET_OPTION_NONE . "','ONECLICK' => '" . MODULE_PAYMENT_NOVALNET_ONE_CLICK . "','ZEROAMOUNT' => '" .MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT. "'), 'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE', MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE,",
				'use_function' => '',
				),
        'MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT', MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT,",
				'use_function' => '',
				),
		'MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE_DESC,
				'value' 	   => 'Local',
				'set_function' => "tep_mod_select_option(array('Local' => '" . MODULE_PAYMENT_NOVALNET_FORM_LOCAL . "', 'Iframe' => '" . MODULE_PAYMENT_NOVALNET_FORM_IFRAME . "', 'Redirect' => '" . MODULE_PAYMENT_NOVALNET_FORM_REDIRECT ."'), 'MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE', MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE,",
				'use_function' => '',
		),     
		'MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT_DESC,
				'value' 	   => '25',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BYAMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BYAMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => '',
				),
		'MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
				),
		'MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE1_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				)
	  );
  }

  /**
   * Get novalnet sepa module configuration keys
   *
   * @return array
   */
  static public function getDirectDebitSEPAKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_MODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_MODULE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE', MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('False' => '" . MODULE_PAYMENT_NOVALNET_OPTION_NONE . "','CALLBACK' => '" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK . "','SMS' =>'" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS . "','EMAIL' =>'" . MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONEMAIL . "'), 'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE', MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE,",
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER', MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER,",
				'use_function' => ''
				),				
		'MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('False' => '" . MODULE_PAYMENT_NOVALNET_OPTION_NONE . "','ONECLICK' => '" . MODULE_PAYMENT_NOVALNET_ONE_CLICK . "','ZEROAMOUNT' => '" .MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT. "'), 'MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE', MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE,",
				'use_function' => '',
				),     
		'MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE_DESC,
				'value' 	   => 'Local',
				'set_function' => "tep_mod_select_option(array('Local' => '" . MODULE_PAYMENT_NOVALNET_FORM_LOCAL . "', 'Iframe' => '" . MODULE_PAYMENT_NOVALNET_FORM_IFRAME . "'), 'MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE', MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE,",
				'use_function' => '',
				),			
		'MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BYAMOUNT_TITLE,
				'desc'	  	   => MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BYAMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_ENDCUSTOMER_INFO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
				),
		'MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE1_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				),
		 'MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
				)
		);
  }

  /**
   * Get novalnet sofort bank module configuration keys
   *
   * @return array
   */
  static public function getSofortBankKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENABLE_MODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENABLE_MODULE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' =>  "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE', MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BYAMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BYAMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENDCUSTOMER_INFO' => array(
				'title'  	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_TRANS_REFERENCE1_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_SOFORTBANK_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_SOFORTBANK_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  )
	  );
  }

  /**
   * Get novalnet iDEAL module configuration keys
   *
   * @return array
   */
  static public function getIdealKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_IDEAL_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_ENABLE_MODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_IDEAL_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_IDEAL_ENABLE_MODULE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_IDEAL_TEST_MODE', MODULE_PAYMENT_NOVALNET_IDEAL_TEST_MODE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_VISIBILITY_BYAMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_VISIBILITY_BYAMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_ENDCUSTOMER_INFO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_TRANS_REFERENCE1_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_IDEAL_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_IDEAL_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_IDEAL_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  )
	  );
  }

  /**
   * Get novalnet paypal module configuration keys
   *
   * @return array
   */
  static public function getPayPalKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_PAYPAL_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_ENABLE_MODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' =>  "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PAYPAL_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_PAYPAL_ENABLE_MODULE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' =>  "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE', MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BYAMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BYAMOUNT_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_ENDCUSTOMER_INFO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_PAYPENDING_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_PAYPENDING_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_PAYPENDING_ORDER_STATUS_DESC,
				'value'  	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE1_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  )
	  );
  }

  /**
   * Get novalnet eps module configuration keys
   *
   * @return array
   */
  static public function getEPSKeys() {
	return array(
		'MODULE_PAYMENT_NOVALNET_EPS_ENABLE_MODULE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_ENABLE_MODULE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_ENABLE_MODULE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_EPS_ENABLE_MODULE', MODULE_PAYMENT_NOVALNET_EPS_ENABLE_MODULE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_TEST_MODE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_TEST_MODE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_TEST_MODE_DESC,
				'value' 	   => 'False',
				'set_function' => "tep_mod_select_option(array('True' => '" . MODULE_PAYMENT_NOVALNET_TRUE . "','False' => '" . MODULE_PAYMENT_NOVALNET_FALSE . "'), 'MODULE_PAYMENT_NOVALNET_EPS_TEST_MODE', MODULE_PAYMENT_NOVALNET_EPS_TEST_MODE,",
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_VISIBILITY_BYAMOUNT' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_VISIBILITY_BYAMOUNT_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_VISIBILITY_BYAMOUNT_DESC,
				'value'	       => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_ENDCUSTOMER_INFO' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_ENDCUSTOMER_INFO_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_ENDCUSTOMER_INFO_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER_DESC,
				'value' 	   => '0',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_ORDER_STATUS' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_ORDER_STATUS_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_ORDER_STATUS_DESC,
				'value'  	   => '',
				'set_function' => 'tep_cfg_pull_down_order_statuses(',
				'use_function' => 'tep_get_order_status_name'
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_PAYMENT_ZONE' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_PAYMENT_ZONE_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_PAYMENT_ZONE_DESC,
				'value' 	   => '',
				'set_function' => 'tep_cfg_pull_down_zone_classes(',
				'use_function' => 'tep_get_zone_class_title'
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_TRANS_REFERENCE1' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_TRANS_REFERENCE1_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_TRANS_REFERENCE1_DESC,
				'value'	   	   => '',
				'set_function' => '',
				'use_function' => ''
			  ),
		'MODULE_PAYMENT_NOVALNET_EPS_TRANS_REFERENCE2' => array(
				'title' 	   => MODULE_PAYMENT_NOVALNET_EPS_TRANS_REFERENCE2_TITLE,
				'desc'		   => MODULE_PAYMENT_NOVALNET_EPS_TRANS_REFERENCE2_DESC,
				'value' 	   => '',
				'set_function' => '',
				'use_function' => ''
			  )
	);
  }
}
?>
