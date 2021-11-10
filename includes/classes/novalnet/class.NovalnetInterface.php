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
 * Script : class.NovalnetInterface.php
 */
 
include_once('version.php');
include_once('class.Novalnet.php');
include_once(DIR_WS_CLASSES . 'currencies.php');

class NovalnetInterface extends NovalnetValidation {

  protected $payment_types_info;

  /**
   * Get basic credentials and configuration values
   * @param $payment_name
   *
   * @return array
   */
  static public function getPaymentTypeInfoAry($payment_name = NULL) {	
	if (strpos(MODULE_PAYMENT_INSTALLED,$payment_name) !== false) {
	  $payment = array(
        'novalnet_cc'         => array('payment_type'=>'CREDITCARD',
                                       'key' => 6,
                                       'paygate_url' => array(
												'novalnet_cc' 	  => 'https://payport.novalnet.de/paygate.jsp', 
												'novalnet_cc3d'   => 'https://payport.novalnet.de/global_pci_payport',
												'novalnet_cc_pci' => 'https://payport.novalnet.de/pci_payport')),
        'novalnet_sepa'       => array('payment_type'=>'DIRECT_DEBIT_SEPA',
                                       'key' => 37,
                                       'paygate_url' =>  array('novalnet_sepa' => 'https://payport.novalnet.de/paygate.jsp')),
        'novalnet_sofortbank' => array('payment_type'=>'ONLINE_TRANSFER',
                                       'key' => 33,
                                       'paygate_url' =>  array('novalnet_sofortbank' => 'https://payport.novalnet.de/online_transfer_payport')),
        'novalnet_paypal'     => array('payment_type'=>'PAYPAL',
                                       'key' => 34,
                                       'paygate_url' =>  array('novalnet_paypal' => 'https://payport.novalnet.de/paypal_payport')),
        'novalnet_ideal'      => array('payment_type'=>'IDEAL',
                                       'key' => 49,
                                       'paygate_url' =>  array('novalnet_ideal' => 'https://payport.novalnet.de/online_transfer_payport')),
        'novalnet_invoice'    => array('payment_type'=>'INVOICE',
                                       'key' => 27,
                                       'paygate_url' => array('novalnet_invoice' => 'https://payport.novalnet.de/paygate.jsp')),
        'novalnet_prepayment' => array('payment_type'=>'PREPAYMENT',
                                       'key' => 27,
                                       'paygate_url' => array('novalnet_prepayment' => 'https://payport.novalnet.de/paygate.jsp')),
        'novalnet_eps'        => array('payment_type'=>'EPS',
                                       'key' => 50,
                                       'paygate_url' => array('novalnet_eps' => 'https://payport.novalnet.de/eps_payport')) );
      $arrayValue[$payment_name]                            = $payment[$payment_name];
      $arrayValue[$payment_name]['test_mode_status']        = defined('MODULE_PAYMENT_'.strtoupper($payment_name).'_TEST_MODE') ? constant('MODULE_PAYMENT_'.strtoupper($payment_name).'_TEST_MODE') : '';
      $arrayValue[$payment_name]['success_order_status']    = defined('MODULE_PAYMENT_'.strtoupper($payment_name).'_ORDER_STATUS') ? constant('MODULE_PAYMENT_'.strtoupper($payment_name).'_ORDER_STATUS') : '';
      $arrayValue[$payment_name]['visibility_amount_limit'] = defined('MODULE_PAYMENT_'.strtoupper($payment_name).'_VISIBILITY_BYAMOUNT') ? constant('MODULE_PAYMENT_'.strtoupper($payment_name).'_VISIBILITY_BYAMOUNT') : '';
      $arrayValue[$payment_name]['payment_zone']            = defined('MODULE_PAYMENT_'.strtoupper($payment_name).'_PAYMENT_ZONE') ? constant('MODULE_PAYMENT_'.strtoupper($payment_name).'_PAYMENT_ZONE') : '';
      if($payment_name == 'novalnet_paypal') {
		 $arrayValue[$payment_name]['payment_pending_order_status'] = defined('MODULE_PAYMENT_NOVALNET_PAYPAL_PAYPENDING_ORDER_STATUS') ? MODULE_PAYMENT_NOVALNET_PAYPAL_PAYPENDING_ORDER_STATUS : '';
      }
      return $arrayValue;
	}
	return array();
  }

  /**
   * Get payment method's custom parameters
   * @param $payment_type
   *
   * @return array
   */
  static public function getCustomParametersInfoAry($payment_type) {
	$payment = array( 'CC' => 'CREDITCARD','SEPA' => 'DIRECT_DEBIT_SEPA', 'SOFORTBANK' => 'ONLINE_TRANSFER', 'PAYPAL' => 'PAYPAL', 'IDEAL' => 'IDEAL', 'INVOICE' => 'INVOICE', 'PREPAYMENT' => 'PREPAYMENT', 'EPS' => 'EPS' );
    foreach($payment as $k => $v) {
      $value[$v]['inputval1'] = defined('MODULE_PAYMENT_NOVALNET_'.$k.'_TRANS_REFERENCE1') ? constant('MODULE_PAYMENT_NOVALNET_'.$k.'_TRANS_REFERENCE1') : '';
      $value[$v]['inputval2'] = defined('MODULE_PAYMENT_NOVALNET_'.$k.'_TRANS_REFERENCE2') ? constant('MODULE_PAYMENT_NOVALNET_'.$k.'_TRANS_REFERENCE2') : '';
    }
	return $value[$payment_type];
  }

  /**
   * Perform novalnet payment call
   * @param $datas
   *
   * @return mixed
   */
  static public function doPayment($datas) {
	$given_payment_name = $datas['payment'];
	$language = MODULE_PAYMENT_NOVALNET_LANGUAGE_TEXT;
	list($firstName, $lastName, $streetAddress, $city, $postCode, $telephone, $countryCode) = self::getPaymentCustomerAddressInfo($datas);
	$payment_type_val = self::getPaymentType($given_payment_name);
	$order_amount = $datas['payment_amount'];
	if ($order_amount == '') {
	  $payment_error_return = 'payment_error=' . $given_payment_name . '&error=' .  MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	  $payment_error_return = self::setUTFText($payment_error_return);
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
    }
    $tariff_type = '';
    $tariff_val = NovalnetCore::getTariffID();
    $tariff_details = explode('-', $tariff_val);
    $tariff_type = $tariff_details[0];
    $tariff = $tariff_details[1];
	$tid_onhold = self::checkManualCheckLimit($given_payment_name, $order_amount);
	list($customers_id, $customer_gender, $customer_dob, $customer_fax) = self::collectCustomerDobGenderFax($datas['customer']['email_address']);
	$is_redirect_method = 0;
	$card_account_holder = "";
	$urlparam = array(
				  'vendor'           => NovalnetCore::getVendorID(),
				  'product'          => NovalnetCore::getProductID(),
				  'key'              => self::getPaymentKey($given_payment_name),
				  'payment_type'     => $payment_type_val,
				  'tariff'           => $tariff,
				  'auth_code'        => NovalnetCore::getVendorAuthCode(),
				  'currency'         => $datas['info']['currency'],
				  'first_name'       => $firstName,
				  'last_name'        => $lastName,
				  'gender'           => $customer_gender,
				  'email'            => $datas['customer']['email_address'],
				  'birth_date'       => $customer_dob,
				  'street'           => $streetAddress,
				  'search_in_street' => 1,
				  'city'             => $city,
				  'zip'              => $postCode,
				  'lang'             => $language,
				  'language'         => $language,
				  'country'          => $countryCode,
				  'country_code'     => $countryCode,
				  'tel'              => $telephone,
				  'fax'              => $customer_fax,
				  'remote_ip'        => self::getRemoteAddr(),
				  'test_mode'        => self::getPaymentTestModeStatus($given_payment_name),
				  'customer_no'      => (($customers_id != '')?$customers_id:'Guest'),
				  'amount'           => $order_amount,
				  'system_name'      => 'oscommerce',
				  'system_version'   => (function_exists('tep_get_version') ? 'osCommerce'.tep_get_version() : PROJECT_VERSION) . '-NN' . getPaymentModuleVersion(),
				  'system_url'       => NovalnetValidation::getSiteDomain(),
				  'system_ip'        => self::getServerAddr(),
				  'notify_url'		 => MODULE_PAYMENT_NOVALNET_CALLBACK_URL
				);
	$current_process_key = '';
	if (NovalnetCore::getReferrerID() != '') {
	  $urlparam['referrer_id'] = NovalnetCore::getReferrerID();
	}
	if (NovalnetCore::getTariffPeriod() != '') {
	  $urlparam['tariff_period'] = NovalnetCore::getTariffPeriod();
	}
	if (NovalnetCore::getTariffPeriod2() != '') {
	  $urlparam['tariff_period2'] = NovalnetCore::getTariffPeriod2();
	}
	if (NovalnetCore::getTariffPeriod2Amount() != '') {
	  $urlparam['tariff_period2_amount'] = NovalnetCore::getTariffPeriod2Amount();
	}
	
	if ( !in_array($tariff_type, array(1, 3, 4)) && ( ( $payment_type_val == 'CREDITCARD' && MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ZEROAMOUNT' ) || ( $payment_type_val == 'DIRECT_DEBIT_SEPA' && MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ZEROAMOUNT' ) ) ) {
	  $urlparam['amount'] = 0;
	}	

	// TID onhold process
	if ($tid_onhold == 1 && in_array($payment_type_val, array('CREDITCARD','INVOICE','DIRECT_DEBIT_SEPA'))) {
	  $urlparam['on_hold'] = 1;
	}
	// Appending Credit Card / 3D Secure payment parameters
	if ($payment_type_val == 'CREDITCARD' && (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE != 'Redirect' || MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True')) {
	  if (!empty($_SESSION['novalnet'][$given_payment_name]['nn_payment_ref_tid']) && $datas['novalnet_ccchange_account'] == 1) {
		if (MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT == 'True') {
          $urlparam['cc_cvc2']   = $datas['novalnet_cc_cvc_ref'];
          $urlparam['pan_hash']  = $current_process_key = $datas['nn_ref_process_key'];
          $urlparam['unique_id'] = NovalnetCore::uniqueRandomString();
        } else {
		  $current_process_key = $datas['nn_ref_process_key'];
	    }
        $urlparam['payment_ref'] = $_SESSION['novalnet'][$given_payment_name]['nn_payment_ref_tid'];
        unset($_SESSION['novalnet'][$given_payment_name]['nn_payment_ref_tid']);
	  } else {
		$urlparam['cc_cvc2']   = $datas['novalnet_cc_cvc'];
		$urlparam['pan_hash']  = $current_process_key = $datas['nn_cc_hash'];
		$urlparam['unique_id'] = $datas['nn_cc_uniqueid'];
	  }
      if (MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True') {
	    $_SESSION['novalnet'][$given_payment_name]['card_account_holder'] = $datas['novalnet_cc_holder'];
	    $is_redirect_method = 1;
	  } else {
	    $card_account_holder = isset($datas['novalnet_cc_holder']) ? $datas['novalnet_cc_holder'] : '';
	  }
	}
	// Appending Direct Debit SEPA payment parameters
	elseif ($payment_type_val == 'DIRECT_DEBIT_SEPA') {
      if (!empty($_SESSION['novalnet'][$given_payment_name]['nn_payment_ref_tid_sepa']) && $datas['novalnet_sepachange_account'] == 1) {
        $urlparam['payment_ref']  = $_SESSION['novalnet'][$given_payment_name]['nn_payment_ref_tid_sepa'];
        $current_process_key = $datas['nn_ref_process_key'];
        unset($_SESSION['novalnet'][$given_payment_name]['nn_payment_ref_tid_sepa']);
      } else {	  
	    $urlparam['iban_bic_confirmed'] = 1;
	    $urlparam['bank_account_holder'] = $card_account_holder = parent::getValidHolderName($datas['novalnet_sepa_account_holder']);
	    $urlparam['sepa_hash'] = $current_process_key = $datas['nn_sepa_hash'];
	    $urlparam['sepa_unique_id'] = $datas['nn_sepa_uniqueid'];
      }
	  $sepa_due_date_limit_tmp = NovalnetCore::getSepaPaymentDuration();
	  $sepa_due_date_limit = (($sepa_due_date_limit_tmp != '' && $sepa_due_date_limit_tmp >= 7) ? $sepa_due_date_limit_tmp : 7);
	  unset($sepa_due_date_limit_tmp);
	  $urlparam['sepa_due_date'] = (date('Y-m-d', strtotime('+'.$sepa_due_date_limit.' days')));
	}
	// Append values for Invoice and Prepayment payment methods
	elseif (in_array($payment_type_val, array('INVOICE','PREPAYMENT'))) {
	  if ($payment_type_val == 'INVOICE') {
		$invoice_due_date = self::getInvoiceDueDate();
		if ($invoice_due_date != '') {
		  $urlparam['due_date'] = $invoice_due_date;
		}
	  }
	  $urlparam['invoice_type'] = $payment_type_val;
	}

	// Affiliate process
	NovalnetCore::getAffDetails($urlparam);
	// Append values for redirection methods
	if (in_array($payment_type_val, array('PAYPAL', 'IDEAL', 'ONLINE_TRANSFER', 'EPS')) || ($payment_type_val == 'CREDITCARD' && (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True'))) {
	  $encoded_values = self::generateHashValue(array(
							  'auth_code' => $urlparam['auth_code'],
							  'product'   => $urlparam['product'],
							  'tariff'    => $urlparam['tariff'],
							  'amount'    => $order_amount,
							  'test_mode' => $urlparam['test_mode'],
							  'uniqid'    => time())
							);
	  foreach($encoded_values as $k => $v) {
        $urlparam[$k] = $v;
      }
	  $urlparam['user_variable_0'] = str_replace(array('http://','https://'), '', NovalnetValidation::getSiteDomain());
	  $urlparam['implementation'] = ($payment_type_val == 'CREDITCARD' && MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect') ? 'PHP_PCI' : 'PHP'; //Encoding type
	  $is_redirect_method = 1;
	}
	if ($is_redirect_method == 1) {
	  if ($payment_type_val == 'CREDITCARD' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True') {
		$urlparam['encoded_amount'] =  self::generateEncode($urlparam['amount']);
	  }
	  $urlparam['return_method'] = $urlparam['error_return_method'] = 'POST';
	  $urlparam['return_url'] = $urlparam['error_return_url'] = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
    }

	$urlparam = self::appendCustomParameters($urlparam); // input1, input2 parameters
	if (in_array($payment_type_val, array ('CREDITCARD', 'INVOICE', 'DIRECT_DEBIT_SEPA')) && !isset($_SESSION['novalnet'][$given_payment_name]['nn_payment_ref_enable'])) {
	  if ( !empty($datas['fraud_module']) && $datas['fraud_module_status'] ) {
		switch ($datas['fraud_module']) {
		  case 'CALLBACK':
						$urlparam['tel'] = trim($datas[$given_payment_name.'_fraud_tel']);
						$urlparam['pin_by_callback'] = '1';
						break;
		  case 'EMAIL':
						$urlparam['email'] = trim($datas[$given_payment_name.'_fraud_email']);
						$urlparam['reply_email_check'] = '1';
						break;
		  case 'SMS':
						$urlparam['mobile'] = trim($datas[$given_payment_name.'_fraud_mobile']);
						$urlparam['pin_by_sms'] = '1';
						break;
		}
	  }
	}
	$paygate_url = self::getPaygateURL($given_payment_name);
	$urlparam = array_map('html_entity_decode', $urlparam);
	if ($is_redirect_method == 0) {
	  $payment_response = self::doPaymentCurlCall($paygate_url, $urlparam); // Sending parameters to novalnet gateway
	  $paymentresponse = self::checkPaymentStatus($given_payment_name, $payment_response, $datas); // Validate novalnet paygate response
	  $maskedvalues = '';
      if ($payment_type_val == 'CREDITCARD') {
        $maskedvalues = serialize(array(
                'cc_holder'    => html_entity_decode($paymentresponse['gateway_response']['cc_holder'], ENT_QUOTES, "UTF-8"),
                'cc_no'        => self::novalnet_masking($paymentresponse['gateway_response']['cc_no']),
                'cc_exp_year'  => (isset($datas['novalnet_cc_exp_year']) && $datas['novalnet_cc_exp_year'])?$datas['novalnet_cc_exp_year']:$datas['nn_ref_cc_exp_year'],
                'cc_exp_month' => (isset($datas['novalnet_cc_exp_month']) && $datas['novalnet_cc_exp_month'])?$datas['novalnet_cc_exp_month']:$datas['nn_ref_cc_exp_month'],
            ));
      } elseif ($payment_type_val == 'DIRECT_DEBIT_SEPA') {
        $maskedvalues = serialize(array(
                'bankaccount_holder' => html_entity_decode($paymentresponse['gateway_response']['bankaccount_holder'], ENT_QUOTES, "UTF-8"),
                'iban'     			 => self::novalnet_masking($paymentresponse['gateway_response']['iban']),
                'bic' 	   			 => self::novalnet_masking($paymentresponse['gateway_response']['bic'])
            ));
      }
      $zero_transaction = 0;
      if(!in_array($tariff_type, array(1, 3, 4)) && (($payment_type_val == 'CREDITCARD' && MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ZEROAMOUNT') || ($payment_type_val == 'DIRECT_DEBIT_SEPA' && MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ZEROAMOUNT'))) {
        $zero_transaction = 1;
		if($payment_type_val == 'DIRECT_DEBIT_SEPA' && isset($sepa_due_date_limit)) {
		  unset($urlparam['sepa_due_date']);
		  $urlparam['sepa_due_date_limit'] = $sepa_due_date_limit;
	    }
	    unset($urlparam['pin_by_callback']);
	    unset($urlparam['reply_email_check']);
	    unset($urlparam['pin_by_sms']);
		$zerotrxndetails = serialize($urlparam);
	  }
	  if ($paymentresponse['status'] == 100) { // Payment success
		$_SESSION[$given_payment_name.'_callback_max_time'] = time() + (30 * 60);
		$_SESSION['novalnet'][$given_payment_name] = array(
								  'order_amount'          => $order_amount,
								  'order_currency'        => $paymentresponse['gateway_response']['currency'],
								  'test_mode'             => $paymentresponse['gateway_response']['test_mode'],
								  'tid'                   => $paymentresponse['tid'],
								  'vendor'                => $urlparam['vendor'],
								  'product'               => $urlparam['product'],
								  'tariff'                => $urlparam['tariff'],
								  'auth_code'             => $urlparam['auth_code'],
								  'gateway_response'      => $paymentresponse['gateway_response'],
								  'additional_note'       => $paymentresponse['additional_note'],
								  'card_account_holder'   => $card_account_holder,
								  'customer_id'           => (($customers_id != '') ? $customers_id : ''),
								  'process_key'           => (($current_process_key != '') ? $current_process_key:''),
								  'nntrxncomments'        => $paymentresponse['comments'],
								  'masked_acc_details'    => isset($maskedvalues) ? $maskedvalues : '',
								  'reference_transaction' => isset($urlparam['payment_ref'])?'1':'0',
								  'zerotrxnreference'	  => ($zero_transaction == 1) ? $paymentresponse['tid'] : '',
								  'zerotrxndetails'		  => isset($zerotrxndetails) ? $zerotrxndetails : '',
								  'zero_transaction'      => $zero_transaction
								);

		return $paymentresponse; // For after_process functionality
	  } else { // Payment failed
		$payment_error_return = 'payment_error=' . $given_payment_name . '&error=' . self::setUTFText($paymentresponse['status_desc'], true);
		tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	  }
	} elseif ($is_redirect_method == 1) {
	  $frmData = '';
	  if ($payment_type_val == 'CREDITCARD') {
		$_SESSION['novalnet'][$given_payment_name] = array_merge($_SESSION['novalnet'][$given_payment_name], array('request'      => $urlparam, 'card_details' => $datas));
		if (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True') {
		  $urlparam['vendor_id'] = $urlparam['vendor'];
		  $urlparam['vendor_authcode'] = $urlparam['auth_code'];
		  $urlparam['product_id'] = $urlparam['product'];
		  $urlparam['tariff_id'] = $urlparam['tariff'];
		  unset($urlparam['vendor']);
		  unset($urlparam['auth_code']);
		  unset($urlparam['product']);
		  unset($urlparam['tariff']);
		}
	  }
	  foreach ($urlparam as $k => $v) {
		$frmData .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
	  }
	  return $frmData;
	}
	tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
  }
  
  /*
   * Perform masking of the account details
   * @param $number
   * 
   * @return string
   */
  static public function novalnet_masking($number) {
	return str_repeat("X", strlen($number) - 4) . substr($number, -4);
  }

  /**
   * Perform manual check limit functionality
   * @param $payment_name
   * @param $order_amount
   *
   * @return integer
   */
  static public function checkManualCheckLimit($payment_name = '', $order_amount = '') {
	$tid_onhold = 0;
	if ( $payment_name != '' && in_array($payment_name, array('novalnet_cc', 'novalnet_sepa', 'novalnet_invoice')) && $order_amount != '' ) {
	  $manual_check_limit_value = self::getPaymentManualCheckLimit();
	  if ( $manual_check_limit_value != '' && $order_amount >= $manual_check_limit_value ) {
		$tid_onhold = 1;
	  }
	}
	return $tid_onhold;
  }

  /**
   * Perform paygate second call for updating order_no and confirm the transaction
   * @param $datas
   *
   * @return boolean
   */
  static public function doPaymentSecondCall($datas) {
	if ( $datas['nn_vendor'] != '' && $datas['nn_auth_code'] != '' && $datas['nn_product'] != '' && $datas['nn_tariff'] != '' && $datas['tid'] != '' && $datas['payment'] != '' ) {
	  $payment_key = self::getPaymentKey($datas['payment']);
	  if($payment_key == 6 && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True' || MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect') {		
        $urlparam = array('vendor_id'       => $datas['nn_vendor'],
						  'product_id'      => $datas['nn_product'],        
						  'tariff_id'       => $datas['nn_tariff'],
						  'vendor_authcode' => $datas['nn_auth_code']);
	  } else {
		$urlparam = array('vendor'    => $datas['nn_vendor'],
						  'product'   => $datas['nn_product'],  
						  'tariff'    => $datas['nn_tariff'],
						  'auth_code' => $datas['nn_auth_code']);
	  }
      $trans_details = array(
        'key'      => $payment_key,
        'status'   => 100,
        'tid'      => $datas['tid'],
        'order_no' => $datas['order_no'],
      );
      $urlparam = array_merge($urlparam, $trans_details);
	  if ($payment_key == 27) {
		if ( !empty($datas['invoice_ref']) ) {
		  $urlparam['invoice_ref'] = $datas['invoice_ref'];
		}
		// Update order number for invoice and prepayment transaction table
		self::updatePrepaymentInvoiceTransOrderRef(array('order_id' => $datas['order_no'],
														 'tid' => $datas['tid'],
														 'additional_note' => $datas['tid']
														));
	  }
	  if(isset($_SESSION['nn_aff_id'])) { unset($_SESSION['nn_aff_id']); }
	  $payment_response = self::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparam);
	  return true;
	}
  }

  /**
   * Get payment amount of given order
   * @param $datas
   *
   * @return integer
   */
  static public function getPaymentAmount($datas, $code) {
	$total = $datas['info']['total'] * $datas['info']['currency_value'];
	$total = sprintf('%0.2f', $total);
	$total = (string)($total * 100); // Convert into Cents
	if ( preg_match('/[^\d\.]/', $total) || !$total ) {
	  $payment_error_return = 'payment_error='. $code .'&error=' .  MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	  $payment_error_return = self::setUTFText($payment_error_return);
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
	return $total;
  }

  /**
   * Get reference parameters
   * @param $urlparam
   *
   * @return array
   */
  static public function appendCustomParameters($urlparam) {
	$custom_params = self::getCustomParametersInfoAry($urlparam['payment_type']);
	foreach ($custom_params as $ukey => $uval) {
	  $uval = trim(strip_tags($uval));
	  $customparam_id = explode('inputval',$ukey);
	  if ( $customparam_id[1] > 0 && $customparam_id[1] <= 7 && $uval != '' ) {
		$urlparam['input'.$customparam_id[1]] = 'Reference '.$customparam_id[1];
		$urlparam[$ukey] = str_replace(array("+","(",")","$","~","%",".",":","*","?","<",">","{","}"), '', $uval);
	  }
	}
	return $urlparam;
  }

  /**
   * Validate gateway response status
   * @param $payment_name
   * @param $response
   * @param $order_obj
   *
   * @return array
   */
  static public function checkPaymentStatus($payment_name = '', $response = '', $order_obj = array()) {
	$trans_comments = $trans_status = $trans_status_desc = '';
	parse_str($response, $data); // Parse paygate response into array
	if ( $data['status'] == 100 || ($payment_name == 'novalnet_paypal' && $data['status'] == 90) ) { // Payment success
	  $trans_status = $data['status'];
	  $payment_test_mode_in_shop = self::getPaymentTestModeStatus($payment_name);
	  $test_mode_msg = (((!empty($data['test_mode']) == 1) ||
	  $payment_test_mode_in_shop == 1) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE : '');
	  $data['test_mode'] = (($data['test_mode']==1)?$data['test_mode']:$payment_test_mode_in_shop);
	  if (in_array($payment_name, array('novalnet_invoice','novalnet_prepayment'))) {
		list($trans_comments, $invoice_due_date) = self::formInvoicePrepaymentComments($data, $test_mode_msg);
		// Append end customer comments
		$trans_user_comments = self::getCustomerCustomComments($order_obj);
		$trans_comments = $trans_user_comments.$trans_comments;
		self::logPrepaymentInvoiceTransAccountInfo(array(
				'order_no' 		  => '',
				'tid' 			  => $data['tid'],
				'test_mode' 	  => (($data['test_mode']==1) ? $data['test_mode'] : $payment_test_mode_in_shop),
				'account_holder'  => 'NOVALNET AG',
				'account_number'  => $data['invoice_account'],
				'bank_code' 	  => $data['invoice_bankcode'],
				'bank_name' 	  => $data['invoice_bankname'],
				'bank_city' 	  => $data['invoice_bankplace'],
				'amount' 		  => $data['amount'],
				'currency' 		  => $data['currency'],
				'bank_iban' 	  => $data['invoice_iban'],
				'bank_bic' 		  => $data['invoice_bic'],
				'due_date' 		  => $invoice_due_date,
				'additional_note' => self::getCustomerCustomComments($order_obj)
			  ));
	  } else {
		$trans_comments = MODULE_PAYMENT_NOVALNET_TRANSACTION_ID.$data['tid'].PHP_EOL.$test_mode_msg;
	  }
	} else { // Payment failed
	  $trans_status_desc = ( (!empty($data['status_text'])) ? $data['status_text'] : ((isset($data['status_desc']) &&
	  $data['status_desc'] != '' ) ? $data['status_desc'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR) );
	}
	$result = array( 'status'      	    => $trans_status,
			         'status_desc' 	    => $trans_status_desc,
			         'tid'          	=> ((isset($data['tid'])) ? $data['tid'] : ''),
			         'comments'     	=> $trans_comments,
					 'gateway_response' => $data,
					 'additional_note'  => self::getCustomerCustomComments($order_obj)
			       );
	return $result;
  }

  /**
   * Get Invoice due date
   * @param $format
   *
   * @return mixed
   */
  static public function getInvoiceDueDate($format = 'Y-m-d') {
	$invoice_due_date = trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE);
	if ($invoice_due_date != '' && ctype_digit($invoice_due_date)) {
	  return (date($format, strtotime('+'.$invoice_due_date.' days')));
	}
	return '';
  }

  /**
   * Get Invoice / Prepayment comments
   * @param $data
   *
   * @return array
   */
  static public function formInvoicePrepaymentComments($data , $testmode = '') {
	$currencies = new currencies();
	$trans_comments = MODULE_PAYMENT_NOVALNET_TRANSACTION_ID.$data['tid'].PHP_EOL.$testmode;
	$trans_comments .= PHP_EOL.MODULE_PAYMENT_NOVALNET_INVOICE_COMMENTS_PARAGRAPH.PHP_EOL;
	$invoice_due_date = $data['due_date'];
	if ($invoice_due_date != '') {
	  $trans_comments .= MODULE_PAYMENT_NOVALNET_DUE_DATE.' : '.tep_date_short($invoice_due_date).PHP_EOL;
	}
	$trans_comments .= MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER.' : NOVALNET AG'.PHP_EOL;
	$trans_comments .= MODULE_PAYMENT_NOVALNET_IBAN.' : '.$data['invoice_iban'].PHP_EOL;
	$trans_comments .= MODULE_PAYMENT_NOVALNET_SWIFT_BIC.' : '.$data['invoice_bic'].PHP_EOL;
	$trans_comments .= MODULE_PAYMENT_NOVALNET_BANK.' : '.$data['invoice_bankname'].' '.$data['invoice_bankplace'].PHP_EOL;
	$trans_comments .= MODULE_PAYMENT_NOVALNET_AMOUNT.' : '.$currencies->format($data['amount'], false, $data['currency']);
	return array($trans_comments, $invoice_due_date);
  }

  /**
   * Get the payment key of the given payment name
   * @param $payment_name
   *
   * @return integer
   */
  static public function getPaymentKey($payment_name = '') {
	if ($payment_name != '') {
	  $payment_types_info = self::getPaymentTypeInfoAry($payment_name);
	  if (isset($payment_types_info[$payment_name])) {
		$payment_type = $payment_types_info[$payment_name];
		$current_payment_key = $payment_type['key'];
		return $current_payment_key;
	  }
	}
  }

  /**
   * Get payment visibility amount limit of the given payment name
   * @param $payment_name
   *
   * @return mixed
   */
  static public function getPaymentVisibilityAmount($payment_name) {
	$payment_types_info = self::getPaymentTypeInfoAry($payment_name);
	if (isset($payment_types_info[$payment_name])) {
	  $payment_type = $payment_types_info[$payment_name];
	  return ((!empty($payment_type['visibility_amount_limit'])) ? trim($payment_type['visibility_amount_limit']) : '');
	}
  }

  /**
   * Get manual check limit value
   *
   * @return mixed
   */
  static public function getPaymentManualCheckLimit()	{
	$tmp_manual_limit = NovalnetCore::getManualCheckLimit();
	return (($tmp_manual_limit != '') ? $tmp_manual_limit : '');
  }

  /**
   * Get payment_zone of the given payment name
   * @param $payment_name
   *
   * @return string
   */
  static public function getPaymentZoneID($payment_name) {
	$payment_types_info = self::getPaymentTypeInfoAry($payment_name);
	if (isset($payment_types_info[$payment_name])) {
	  return $payment_types_info[$payment_name]['payment_zone'];
    }
  }

  /**
   * Validate payment zone for module visibility
   * @param $datas
   * @param $payment_name
   * @param $enabled_status
   *
   * @return boolean
   */
  static public function checkValidPaymentZoneID($datas, $payment_name = '', $enabled_status = false) {
	if ( $payment_name != '' && $enabled_status ) {
	  $payment_zone_id = self::getPaymentZoneID($payment_name);
	  $check_flag = false;
	  if ( $payment_zone_id != '' && $payment_zone_id != 0 ) {
		$sqlQuery = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '".tep_db_input($payment_zone_id)."' and zone_country_id = '".tep_db_input($datas['billing']['country']['id'])."' order by zone_id");
	   	while ($check = tep_db_fetch_array($sqlQuery)) {
		  if ($check['zone_id'] < 1) {
			$check_flag = true;
			break;
		  }
		  elseif ($check['zone_id'] == $datas['billing']['zone_id']) {
			$check_flag = true;
			break;
		  }
		}
	  } else {
		$check_flag = true;
	  }
	  return $check_flag;
	}
  }

  /**
   * Collect customer DOB, FAX, Gender information from the database
   * @param $customer_email
   *
   * @return array
   */
  static public function collectCustomerDobGenderFax($customer_email = '') {
	$result = array('', 'u', '', '');
	if ($customer_email != '') {
	  $querySearch = (isset($_SESSION['customer_id'])) ? 'customers_id= "'. tep_db_input($_SESSION['customer_id']).'"' : 'customers_email_address= "'. tep_db_input($customer_email).'"';
	  $sqlQuery = tep_db_query("SELECT customers_id, customers_gender, customers_dob, customers_fax FROM ". TABLE_CUSTOMERS . " WHERE ".$querySearch." ORDER BY customers_id DESC");
	  $customer_dbvalue = tep_db_fetch_array($sqlQuery);
	  if (!empty($customer_dbvalue)) {
		  $customer_dbvalue['customers_dob'] = ($customer_dbvalue['customers_dob'] != '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($customer_dbvalue['customers_dob'])) : '';
		return array($customer_dbvalue['customers_id'], $customer_dbvalue['customers_gender'], $customer_dbvalue['customers_dob'], $customer_dbvalue['customers_fax']);
	  }
	}
	return $result;
  }

  /**
   * Get payment type of given payment name
   * @param $payment_name
   *
   * @return string
   */
  static public function getPaymentType($payment_name) {
	$payment_types_info = self::getPaymentTypeInfoAry($payment_name);
	if (isset($payment_types_info[$payment_name])) {
	  return $payment_types_info[$payment_name]['payment_type'];
    }
  }

  /**
   * Get paygate URL of given payment
   * @param $payment_name
   *
   * @return string
   */
  static public function getPaygateURL($payment_name) {
	$payment_types_info = self::getPaymentTypeInfoAry($payment_name);
	if (isset($payment_types_info[$payment_name])) {
	  $payment_type = $payment_types_info[$payment_name];
	  return ($payment_name == 'novalnet_cc' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True') ? $payment_type['paygate_url']['novalnet_cc3d'] : (($payment_name == 'novalnet_cc' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True' && MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect') ? $payment_type['paygate_url']['novalnet_cc_pci'] : $payment_type['paygate_url'][$payment_name]);
    }
    return '';
  }

  /**
   * Get order status of given payment
   * @param $payment_name
   * @param $payment_status
   *
   * @return integer
   */
  static public function getPaymentOrderStatus($payment_name = '', $payment_status = '') {
	$payment_types_info = self::getPaymentTypeInfoAry($payment_name);
	if (isset($payment_types_info[$payment_name])) {
	  if ($payment_status == '90') {
		return ($payment_types_info[$payment_name]['payment_pending_order_status'] > 0 && !empty($payment_types_info[$payment_name]['payment_pending_order_status'])) ? $payment_types_info[$payment_name]['payment_pending_order_status'] : DEFAULT_ORDERS_STATUS_ID;
	  }
  	  return ($payment_types_info[$payment_name]['success_order_status'] > 0 && !empty($payment_types_info[$payment_name]['success_order_status'])) ? $payment_types_info[$payment_name]['success_order_status'] : DEFAULT_ORDERS_STATUS_ID;
    }
  }

  /**
   * Get address information of given order
   * @param $datas
   *
   * @return array
   */
  static public function getPaymentCustomerAddressInfo($datas) {
	$arrayValue = array('firstname', 'lastname',  'street_address', 'city', 'postcode', 'telephone');
	foreach($arrayValue as $v) {
	  $value[] = ((!empty($datas['billing'][$v])) ? $datas['billing'][$v] : $datas['customer'][$v]);
	}
	$value[] = ((!empty($datas['billing']['country']['iso_code_2'])) ? $datas['billing']['country']['iso_code_2'] : $datas['customer']['country']['iso_code_2']);
	return $value;
  }

  /**
   * Process payment visibility status
   * @param $payment_name
   * @param $order_amount
   *
   * @return boolean
   */
  static public function hidePaymentVisibility($payment_name, $order_amount = '') {
	if ( $payment_name == '' || $order_amount == '' ) {
	  return false;
	}
	$payment_visible_amount = self::getPaymentVisibilityAmount($payment_name);
	if ($payment_visible_amount == '') return true;
	if ( $payment_visible_amount != '' && (int)$payment_visible_amount <= (int)$order_amount ) {
      return true;
	}
	return false;
  }

  /**
   * Get remote ip address
   *
   * @return string
   */
  static public function getRemoteAddr() {
	$remoteip_address = tep_get_ip_address();
	return (($remoteip_address == '::1') ? '127.0.0.1' : $_SERVER['REMOTE_ADDR']);
  }

  /**
   * Get server ip address
   *
   * @return string
   */
  static public function getServerAddr() {
 	return (($_SERVER['SERVER_ADDR'] == '::1') ? '127.0.0.1' : $_SERVER['SERVER_ADDR']);
  }

  /**
   * Get customer custom comments
   * @param $order_obj
   *
   * @return string
   */
  static public function getCustomerCustomComments($order_obj) {
	return ((!empty($order_obj['comments'])) ? PHP_EOL.$order_obj['comments'] : ((!empty($order_obj['info']['comments'])) ? PHP_EOL.$order_obj['info']['comments'] : ''));
  }

  /**
   * Get test mode status
   * @param $payment_name
   *
   * @return boolean;
   */
  static public function getPaymentTestModeStatus($payment_name) {
    $payment_types_info = self::getPaymentTypeInfoAry($payment_name);
	if (isset($payment_types_info[$payment_name])) {
	  $payment_type = $payment_types_info[$payment_name];
	  return ((isset($payment_type['test_mode_status']) && $payment_type['test_mode_status'] == 'True') ? 1 : 0);
	}
  }

  /**
   * Function to communicate transaction parameters with novalnet paygate
   * @param $paygate_url
   * @param $datas
   * @param $build_query
   *
   * @return array
   */
  static public function doPaymentCurlCall($paygate_url, $datas = array(), $build_query = true) {
	$paygate_query = ($build_query) ? http_build_query($datas) : $datas; // Build gateway string from the array
	$curl_timeout = NovalnetCore::getGatewayTimeout();
	$ch = curl_init($paygate_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $paygate_query);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_TIMEOUT, (($curl_timeout != '' && $curl_timeout > 240) ? $curl_timeout : 240)); // Custom curl time-out
	if (trim(MODULE_PAYMENT_NOVALNET_PROXY) != '') { // Custom proxy option
	  curl_setopt($ch, CURLOPT_PROXY, trim(MODULE_PAYMENT_NOVALNET_PROXY));
	}
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
  }

  /**
   * Function to update order status as per merchant selection
   * @param $order_id
   * @param $payment_name
   * @param $payment_status
   *
   * @return boolean
   */
  static public function updateOrderCustomStatus($order_id = '', $payment_name = '', $payment_status = '') {
	if ( $order_id != '' && $payment_name != '' ) {
	  $payment_order_status = self::getPaymentOrderStatus($payment_name, $payment_status);
	  $payment_order_status = ($payment_order_status > 0) ? $payment_order_status : DEFAULT_ORDERS_STATUS_ID;
		//Update the Merchant selected order status
	  $dbVal1['orders_status'] = $dbVal2['orders_status_id'] = $payment_order_status;
	  if (in_array($payment_name, array('novalnet_invoice', 'novalnet_prepayment'))) {
		$invpre_comments = self::novalnetReferenceComments($order_id, $payment_name);
	    $dbVal2['comments'] = $_SESSION['novalnet'][$payment_name]['nntrxncomments'].$invpre_comments;
	  }
	  tep_db_perform(TABLE_ORDERS, $dbVal1, "update", "orders_id='$order_id'");
	  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $dbVal2, "update", "orders_id='$order_id'");
	}
	return true;
  }

  /**
   * Function to update order status in orders table
   * @param $order_id
   * @param $orders_status_id
   * @param $message
   * @param $insertstatushistory
   * @param $customer_notified
   *
   * @return boolean
   */
  static public function updateOrderStatus($order_id = '', $orders_status_id = '', $message = '', $insertstatushistory = true, $customer_notified = 0) {
	$orders_status_id = ($orders_status_id > 0) ? $orders_status_id : DEFAULT_ORDERS_STATUS_ID;
	$param['orders_status'] = $orders_status_id;
	$param['last_modified'] = 'now()';
	//Update order orders_status_id
	tep_db_perform(TABLE_ORDERS, $param, "update", "orders_id='$order_id'");
	if ($insertstatushistory) {
	  self::insertOrderStatusHistory($order_id, $message, $orders_status_id, $customer_notified);
	}
	return true;
  }

  /**
   * Function to update comments in orders_status_history
   * @param $order_id
   * @param $message
   * @param $orders_status_id
   * @param $customer_notified
   *
   * @return none
   */
  static public function insertOrderStatusHistory($order_id = '', $message = '', $orders_status_id = DEFAULT_ORDERS_STATUS_ID, $customer_notified = 0) {
	if ( $order_id != '' && $message != '' ) {
	  $message = tep_db_input($message);
	  $orders_status_id = ($orders_status_id > 0) ? $orders_status_id : DEFAULT_ORDERS_STATUS_ID;
	  tep_db_query("insert into ".TABLE_ORDERS_STATUS_HISTORY."(orders_id, date_added, customer_notified, comments, orders_status_id) values ( " . $order_id . ", NOW(), " . $customer_notified . ", '" . $message . "', " . $orders_status_id . ")");
	}
  }

  /**
   * Perform the encoding process for redirection payment methods
   * @param $data
   *
   * @return string
   */
  static public function generateEncode($data = '') {
	try {
	  $crc = sprintf('%u', crc32($data));
	  $data = $crc . "|" . $data;
	  $data = bin2hex($data . $_SESSION['nn_access_key']);
	  $data = strrev(base64_encode($data));
	} catch (Exception $e) {
	  echo('Error: ' . $e);
	}
	return $data;
  }

  /**
   * Perform the decoding process for redirection payment methods
   * @param $data
   *
   * @return string
   */
  static public function generateDecode($data = '') {
	try {
	  $data = base64_decode(strrev($data));
	  $data = pack("H" . strlen($data), $data);
	  $data = substr($data, 0, stripos($data, $_SESSION['nn_access_key']));
	  $pos = strpos($data, "|");
	  if ($pos === false) {
		return("Error: CKSum not found!");
	  }
	  $crc = substr($data, 0, $pos);
	  $value = trim(substr($data, $pos + 1));
	  if ($crc != sprintf('%u', crc32($value))) {
		return("Error; CKSum invalid!");
	  }
	  return $value;
	} catch (Exception $e) {
	  echo('Error: ' . $e);
	}
	return $data;
  }

  /**
   * Perform the decoding paygate response process for redirection payment methods
   * @param $datas
   *
   * @return array
   */
  static public function decodePaygateResponse($datas) {
 	$result = array();
    $datas['vendor'] = isset($datas['vendor']) ? $datas['vendor'] : $datas['vendor_id'];
    $datas['auth_code'] = isset($datas['auth_code']) ? $datas['auth_code'] : $datas['vendor_authcode'];
    $datas['tariff'] = isset($datas['tariff']) ? $datas['tariff'] : $datas['tariff_id'];
    $datas['product'] = isset($datas['product']) ? $datas['product'] : $datas['product_id'];
	foreach ($datas as $key => $value) {
      $result[$key] = (in_array($key, array('tariff','auth_code','amount','product','uniqid','test_mode'))) ? self::generateDecode($value) : $value;
	}
	return $result;
  }

  /**
   * Perform hash generation process for redirection payment methods
   * @param $datas
   *
   * @return array
   */
  static public function generateHashValue($datas) {
	foreach($datas as $k => $v) {
	  $result[$k] = self::generateEncode($v);
    }
	$result['hash'] = self::generatemd5Value($result);
	return $result;
  }

  /**
   * Get hash value
   * @param $datas
   *
   * @return string
   */
  static public function generatemd5Value($datas) {
	return md5($datas['auth_code'].$datas['product'].$datas['tariff'].$datas['amount'].$datas['test_mode'].$datas['uniqid']. strrev($_SESSION['nn_access_key']));
  }

  /**
   * Perform hash validation with paygate response
   * @param $datas
   *
   * @return boolean
   */
  static public function validateHashResponse($datas) {
	return (isset($datas['hash2']) && $datas['hash2'] != self::generatemd5Value($datas)) ? false : true;
  }

  /**
   * Function to log all novalnet transaction in novalnet_transaction_detail table
   * @param $datas
   *
   * @return none
   */
  static public function logInitialTransaction($datas) {
	$table_values = array(
			    'tid' 					=> $datas['tid'],
				'vendor'  				=> $datas['vendor'],
				'product' 				=> $datas['product'],
				'tariff'  				=> $datas['tariff'],
				'auth_code' 			=> $datas['auth_code'],
				'subs_id' 				=> $datas['subs_id'],
				'payment_id' 			=> $datas['payment_id'],
				'payment_type' 			=> $datas['payment_type'],
				'amount' 				=> ((!empty($datas['amount'])) ? $datas['amount'] : 0),
				'total_amount' 			=> ((!empty($datas['total_amount'])) ? $datas['total_amount'] : 0),
				'currency' 				=> $datas['currency'],
				'status' 				=> $datas['status'],
				'gateway_status' 		=> $datas['gateway_status'],
				'order_no' 				=> $datas['order_no'],
				'callback_status' 		=> $datas['callback_status'],
				'date' 					=> date('Y-m-d H:i:s'),
				'test_mode' 			=> ((!empty($datas['test_mode'])) ? 1 : 0),
				'additional_note' 		=> $datas['additional_note'],
				'customer_id' 			=> $datas['customer_id'],
				'language'				=> $_SESSION['language'],
				'masked_acc_details' 	=> $datas['masked_acc_details'],
				'reference_transaction' => $datas['reference_transaction'],
				'zerotrxnreference'	  	=> $datas['zerotrxnreference'],
				'zerotrxndetails'		=> $datas['zerotrxndetails'],
				'zero_transaction' 		=> $datas['zero_transaction']
				);
	if (isset($datas['refund_amount'])) {
	  $table_values['refund_amount'] = $datas['refund_amount'];
	}
	if (!empty($datas['account_holder'])) {
	  $table_values['account_holder'] = $datas['account_holder'];
	}
	if ( !empty($datas['process_key']) ) { // Update hash value for endcustomer next order auto refill feature
	  $table_values['process_key'] = $datas['process_key'];
	}
	tep_db_perform('novalnet_transaction_detail', $table_values, "insert");
  }

  /**
   * Function to log novalnet prepayment and invoice transaction's account details in novalnet_preinvoice_transaction_detail table
   * @param $datas
   *
   * @return none
   */
  static public function logPrepaymentInvoiceTransAccountInfo($datas) {
	$due_date = '';
	if ($datas['due_date'] != '') {
	  $due_date = date('Y-m-d', strtotime(str_replace('/','.',$datas['due_date'])));
	}
	tep_db_perform('novalnet_preinvoice_transaction_detail', array(
				'order_no' 		 => $datas['order_no'],
				'tid' 			 => $datas['tid'],
				'account_holder' => $datas['account_holder'],
				'account_number' => $datas['account_number'],
				'bank_code' 	 => $datas['bank_code'],
				'bank_name' 	 => $datas['bank_name'],
				'bank_city' 	 => $datas['bank_city'],
				'amount' 		 => ($datas['amount']*100), // Convert into cents
				'currency' 		 => $datas['currency'],
				'bank_iban' 	 => $datas['bank_iban'],
				'bank_bic' 		 => $datas['bank_bic'],
				'due_date' 		 => $due_date,
				'date' 			 => date('Y-m-d H:i:s'),
				'test_mode' 	 => ( (!empty($datas['test_mode'])) ? 1 : 0 )
			), "insert");
  }

  /**
   * Function to log novalnet subscription transaction's details in novalnet_subscription_detail table
   * @param $datas
   *
   * @return none
   */
  static public function logSubscriptionTransInfo($datas) {
	tep_db_perform('novalnet_subscription_detail', array(
					 'order_no' => $datas['order_no'],
					 'subs_id' => $datas['subs_id'],
					 'tid' => $datas['tid'],
					 'parent_tid' => $datas['tid'],
					 'signup_date' => $datas['signup_date'],
					 'termination_reason' => ((!empty($datas['termination_reason'])) ? $datas['termination_reason'] : ''),
					 'termination_at' => ((!empty($datas['termination_at'])) ? $datas['termination_at'] : '')
					), "insert");
  }

  /**
   * Function to log novalnet transaction's details in novalnet_callback_history table
   * @param $datas
   *
   * @return boolean
   */
  static public function logCallbackProcess($datas)	{
	$datas['date'] = date('Y-m-d H:i:s');
	tep_db_perform('novalnet_callback_history', $datas, 'insert');
  }

  /**
   * Function to update subscription termination details in novalnet_subscription_detail table
   * @param $datas
   *
   * @return none
   */
  static public function updateSubscriptionTransInfo($datas) {
	if ( !empty($datas['termination_reason']) ) {
	  $param['termination_reason'] = $datas['termination_reason'];
	}
	if ( !empty($datas['termination_at']) ) {
	  $param['termination_at'] = $datas['termination_at'];
	}
	tep_db_perform('novalnet_subscription_detail', $param, "update", "parent_tid='" . $datas['parent_tid'] . "'");
  }

  /**
   * Function to update novalnet prepayment and invoice transaction order reference in novalnet_preinvoice_transaction_detail table
   * @param $datas
   *
   * @return none
   */
  static public function updatePrepaymentInvoiceTransOrderRef($datas) {
	if ( $datas['order_id'] != '' && $datas['tid'] != '' ) {
	  $param['order_no'] = $datas['order_id'];
	  if ($datas['amount'] != '') {
		$param['amount'] = $datas['amount'];
	  }
	  if ($datas['due_date'] != '') {
		$param['due_date'] = $datas['due_date'];
	  }
	  tep_db_perform('novalnet_preinvoice_transaction_detail', $param, "update", "tid='" . $datas['tid'] . "'");
	}
  }

  /**
   * Function to update real time novalnet transactions in novalnet_transaction_detail table
   * @param $datas
   * @param $return_trans_info
   * @param $transdetails_active
   * @param $preinvoice_table_update
   * @param $refund_amount
   * @param $update_amount
   *
   * @return mixed
   */
  static public function updateLiveNovalnetTransStatus($datas, $return_trans_info = false, $transdetails_active = true, $preinvoice_table_update = false, $refund_amount = '', $update_amount = false) {
	$cancel_order_status = MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED > 0 ? MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED : DEFAULT_ORDERS_STATUS_ID;
	$tid = $datas['tid'];
	if ($tid != '') {
	  $trans_novalnet_info = self::getTransDetails(array( 'nn_vendor'    => $datas['vendor'],
						                                  'nn_auth_code' => $datas['auth_code'],
						                                  'nn_product'   => $datas['product'],
						                                  'tid' 		 => $tid
														));
	  if ($return_trans_info) {
		return $trans_novalnet_info;
	  }
	  if (!$transdetails_active) {
		$param['active'] = 0;
	  }
	  $param['amount'] = $trans_novalnet_info->amount;
	  if ( !empty($refund_amount) ) {
		$param['refund_amount'] = $refund_amount;
		if ( isset($trans_novalnet_info->status) && $trans_novalnet_info->status == '103' ) {
		  $order_id = $datas['order_id'];
		  tep_db_perform(TABLE_ORDERS, array('orders_status' => $cancel_order_status, 'last_modified' => 'now()'), "update", "orders_id='$order_id'");
		  tep_db_query("UPDATE ". TABLE_ORDERS_STATUS_HISTORY." SET orders_status_id = ".$cancel_order_status." WHERE orders_id = ".tep_db_input($order_id)." ORDER BY orders_status_history_id DESC LIMIT 1");
		}
	  } elseif ($update_amount) {
		$param['total_amount'] = $trans_novalnet_info->amount;
	  }
	  $param['gateway_status'] = ((isset($trans_novalnet_info->status)) ? $trans_novalnet_info->status : 0);

	  tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='$tid'");

	  if ($preinvoice_table_update) {
		tep_db_perform('novalnet_preinvoice_transaction_detail', array( 'amount' => $trans_novalnet_info->amount), "update", "tid='$tid'");
	  }
	}
	return true;
  }

  /**
   * Send transaction status request to novalnet gateway
   * @param $datas
   *
   * @return mixed
   */
  static public function getTransDetails($datas) {
	if ( $datas['nn_vendor'] != '' && $datas['nn_auth_code'] != '' && $datas['nn_product'] != '' && $datas['tid'] != '' ) {
	  $xml_request = "<?xml version='1.0' encoding='UTF-8'?>
						<nnxml>
						  <info_request>
							<vendor_id>".$datas['nn_vendor']."</vendor_id>
							<vendor_authcode>".$datas['nn_auth_code']."</vendor_authcode>
							<request_type>TRANSACTION_STATUS</request_type>
							<product_id>".$datas['nn_product']."</product_id>
							<tid>".$datas['tid']."</tid>
						  </info_request>
						</nnxml>";
	  $response = self::doPaymentCurlCall('https://payport.novalnet.de/nn_infoport.xml',$xml_request,false);
	  $xml_response = simplexml_load_string($response);
	  return $xml_response;
	}
	return '';
  }

  /**
   * Perform onhold transaction debit/cancel process
   * @param $datas
   *
   * @return string
   */
  static public function onholdTransConfirm($datas) {
	$transAPIValidate = NovalnetCore::validateMerchantDetails($datas);
	if($transAPIValidate != '')
      return $transAPIValidate;
	$response = self::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp',array(
						'vendor'      => $datas['vendor'],
						'product'     => $datas['product'],
						'key'         => $datas['payment_id'],
						'tariff'      => $datas['tariff'],
						'auth_code'	  => $datas['auth_code'],
						'edit_status' => '1',
						'tid' 		  => $datas['tid'],
						'status' 	  => $datas['status'] // 100 / 103
					));
	parse_str($response, $reponse_data); // Parse Paygate response into array

	if ($reponse_data['status'] == 100) {
	  if ($datas['status'] == 100) {
		self::updateOrderStatus($datas['order_id'], MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE,sprintf(MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE, tep_date_short(date('Y/m/d')), date('H:i:s')), true, 1);
	  } else {
		self::updateOrderStatus($datas['order_id'], MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED, sprintf(MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE, tep_date_short(date('Y/m/d')), date('H:i:s')), true, 1);
	  }
	  self::updateLiveNovalnetTransStatus($datas);
	} else {
	  $message = MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_FAILED_MESSAGE.((!empty($reponse_data['status_desc'])) ? $reponse_data['status_desc'] : ((!empty($reponse_data['status_text'])) ? $reponse_data['status_text'] : '')).'( Status : '.$reponse_data['status'].')';
	  self::updateOrderStatus($datas['order_id'], self::getOrderStatus($datas['order_id']), $message, true, 1);
	  return $message;
	}
	return '';
  }

  /**
   * Get order status of given order id
   * @param $order_id
   *
   * @return integer
   */
  static public function getOrderStatus($order_id) {
    $orderInfo = tep_db_fetch_array(tep_db_query("select orders_status from ".TABLE_ORDERS." where orders_id = ".tep_db_input($order_id)));
	return $orderInfo['orders_status'];
  }

  /**
   * Perform subscription stop process
   * @param $datas
   *
   * @return mixed
   */
  static public function subscriptionTransStop($datas = array()) {	  
	$transAPIValidate = NovalnetCore::validateMerchantDetails($datas);
	if($transAPIValidate != '')
      return $transAPIValidate;
	  
	$sql_val = tep_db_fetch_array(tep_db_query('SELECT parent_tid FROM `novalnet_subscription_detail` where tid='.$datas['tid']));
	$parent_tid = $sql_val['parent_tid'];
	$response = self::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp',array(
						'vendor'        => $datas['vendor'],
						'product'       => $datas['product'],
						'key'           => $datas['payment_id'],
						'tariff'        => $datas['tariff'],
						'auth_code'     => $datas['auth_code'],
						'cancel_sub' 	=> '1',
						'tid' 			=> $parent_tid,
						'cancel_reason' => $datas['termination_reason']
						));
	parse_str($response, $data); // Parse Paygate response into array
	if ($data['status'] == 100) {
	  $message = MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_MESSAGE.$datas['termination_reason'];
	  $order_status = MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL > 0 ? MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL : DEFAULT_ORDERS_STATUS_ID;
	  $db_query = tep_db_query('SELECT order_no from novalnet_subscription_detail WHERE parent_tid = "'.$parent_tid.'"');
	  while($row = tep_db_fetch_array($db_query) ) {
		self::updateOrderStatus($row['order_no'], $order_status);
	  }
	  $parent_order_no = tep_db_fetch_array(tep_db_query('SELECT order_no from novalnet_transaction_detail WHERE tid = "'.$parent_tid.'"'));

	  self::insertOrderStatusHistory($parent_order_no['order_no'], $message, $order_status, 1);
      self::updateSubscriptionTransInfo(array(
          'termination_reason' => $datas['termination_reason'],
          'termination_at' => date('Y-m-d H:i:s'),
          'parent_tid' => $parent_tid
      ));
	} else {
	  return ( (!empty($data['status_desc']) ) ? $data['status_desc'] : ((!empty($data['status_text'])) ? $data['status_text'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR) );
	}
  }

  /**
   * Perform transaction refund process
   * @param $datas
   *
   * @return string
   */
  static public function refundTransAmount($datas = array()) {	  
	$transAPIValidate = NovalnetCore::validateMerchantDetails($datas);
	if($transAPIValidate != '')
      return $transAPIValidate;
	if ($datas['payment_id'] == 27)
	  $paid_amount = NovalnetCore::getNovalnetCallbackAmount($datas['order_id']);
	if ( !isset($datas['refund_trans_amount']) || ( trim($datas['refund_trans_amount']) <= 0 || !is_numeric($datas['refund_trans_amount'])) || ($datas['payment_id'] == 27 &&  $datas['refund_trans_amount'] > ($paid_amount - $datas['refund_amount'])))
	  return MODULE_PAYMENT_NOVALNET_REFUND_ZERO_AMOUNT_ERROR_MESSAGE;
	  
	$currencies = new currencies();
	$refund_param = $datas['refund_trans_amount'];
	$refund_params = array( 'vendor'         => $datas['vendor'],
							'product'        => $datas['product'],
							'key'            => $datas['payment_id'],
							'tariff'         => $datas['tariff'],
							'auth_code'      => $datas['auth_code'],
							'refund_request' => '1',
							'tid' 		   => $datas['tid'],
							'refund_param'   => $refund_param
						  );
	if ($datas['refund_ref'] != '')
	  $refund_params['refund_ref'] = $datas['refund_ref'];

	if ($datas['refund_paymenttype'] == 'sepa') {
	  $refund_params['iban'] 			 = $datas['iban'];
	  $refund_params['bic'] 			 = $datas['bic'];
	  $refund_params['account_holder'] = $datas['account_holder'];
	}
	$amount_formated = $currencies->format($refund_param/100, false, $datas['refund_trans_amount_currency']);
	$response = self::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $refund_params);
	parse_str($response, $data); // Parse Paygate response into array
	$order_status = '';
	if ($data['status'] == 100) {
	  $nn_tid = (!empty($data['tid']) ? $data['tid'] : $datas['tid']);
	  $trans_novalnet_info = self::getTransDetails(array( 'nn_vendor'    => $datas['vendor'],
														  'nn_auth_code' => $datas['auth_code'],
														  'nn_product'   => $datas['product'],
														  'tid' 		   => $nn_tid
														));
	  $message = '';
	  $order_server_response = utf8_decode((!empty($data['status_desc'])) ? $data['status_desc'] : ((!empty($data['status_message'])) ? ($data['status_message']) : ((!empty($data['status_text'])) ? $data['status_text'] : MODULE_PAYMENT_NOVALNET_STATUS_SUCCESSFULL_TEXT)));
	  $message .= sprintf(MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG, $datas['tid'], $amount_formated);
	  if (!empty($data['tid'])) {
		$message .= sprintf(MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG, $data['tid']);
	  }
	  if ( $datas['payment_id'] == 34 && !empty($data['paypal_refund_tid']) ) {
		$message .= MODULE_PAYMENT_NOVALNET_PARTIAL_REFUND_MESSAGE."[ ".$amount_formated." ] : ".$order_server_response.".";
		$message .= ' - PayPal Ref: '.$data['paypal_refund_tid'];
  	  }
	  $refund_amount = ($datas['refund_amount'] + $refund_param);
	  if ($refund_amount >= $datas['total_amount']) {
	    $order_status = ( ($datas['payment_id'] == 27 && isset($trans_novalnet_info->status) && $trans_novalnet_info->status == 100) ? self::getOrderStatus($datas['order_id']) : MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED );
	  } else {
	    $order_status = self::getOrderStatus($datas['order_id']);
	  }
	  self::updateOrderStatus($datas['order_id'], $order_status, $message, true, 1);
 	  if ( !empty($data['tid']) && $datas['payment_id'] != 6 ) {
		$nn_existing_trans_data = NovalnetCore::getNovalnetTransDetails($datas['order_id']);
		self::updateLiveNovalnetTransStatus($datas, false, false); // Update status along with active = 0
		self::logInitialTransaction(array(
					'tid' 			  => $data['tid'],
					'vendor'          => $datas['vendor'],
					'product'         => $datas['product'],
					'tariff'          => $datas['tariff'],
					'auth_code'       => $datas['auth_code'],
					'subs_id' 		  => $datas['subs_id'],
					'payment_id' 	  => $datas['payment_id'],
					'payment_type' 	  => $datas['payment_type'],
					'amount' 		  => $trans_novalnet_info->amount,
					'refund_amount'   => $refund_amount,
					'total_amount' 	  => $trans_novalnet_info->amount,
					'currency' 		  => $trans_novalnet_info->currency,
					'status' 		  => 100,
					'order_no' 		  => $datas['order_id'],
					'callback_status' => 0,
					'test_mode' 	  => $datas['test_mode'],
					'additional_note' => $datas['additional_note'],
					'account_holder'  => ((!empty($datas['account_holder'])) ? $datas['account_holder'] : ((!empty($nn_existing_trans_data['account_holder']) ) ? $nn_existing_trans_data['account_holder'] : '')),
					'gateway_status'  => ((isset($trans_novalnet_info->status)) ? $trans_novalnet_info->status : 0),
					'process_key' 	  => $nn_existing_trans_data['process_key'],
					'customer_id' 	  => $nn_existing_trans_data['customer_id']
					));
	  } else {
		($datas['payment_id'] == 27) ? self::updateLiveNovalnetTransStatus($datas, false, true, true, $refund_amount, false) : self::updateLiveNovalnetTransStatus($datas, false, true, false, $refund_amount, false);
	  }
	  return '';
	} else {
	  return (!empty($data['status_desc'])) ? $data['status_desc'] : ((!empty($data['status_message'])) ? ($data['status_message']) : ((!empty($data['status_text'])) ? $data['status_text'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR));
	}
  }

  /**
   * Perform transaction amount / due_date update process
   * @param $datas
   *
   * @return string
   */
  static public function updateTransAmount($datas = array()) {
	if (!is_numeric($datas['amount']) || $datas['amount'] <= 0)
	  return MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	$transAPIValidate = NovalnetCore::validateMerchantDetails($datas);
	if($transAPIValidate != '')
      return $transAPIValidate;
	
	$currencies = new currencies();	
	$amountChange_request = array( 'vendor'            => $datas['vendor'],
								   'product'           => $datas['product'],
								   'key'               => $datas['payment_id'],
								   'tariff'            => $datas['tariff'],
								   'auth_code'         => $datas['auth_code'],
								   'edit_status' 	   => '1',
								   'tid' 			   => $datas['tid'],
								   'status' 		   => 100,
								   'update_inv_amount' => '1',
								   'amount' 		   => $datas['amount']
								 );
	if ( $datas['due_date'] != '0000-00-00' && $datas['due_date'] != '' ) {
	  $amountChange_request['due_date'] = $datas['due_date'];
	}
	$response = self::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $amountChange_request);
	parse_str($response, $data); // Parse Paygate response into array
	if ($data['status'] == 100) {
	  $amount_formated = $currencies->format($datas['amount']/100, false, $datas['amount_currency']);
	  $message = sprintf(MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE, $amount_formated, tep_date_short(date('Y/m/d')), date(' H:i:s'));
	  self::insertOrderStatusHistory($datas['order_id'], $message, self::getOrderStatus($datas['order_id']), 1);
		($datas['payment_id'] == 27) ? self::updateLiveNovalnetTransStatus($datas, false, true, true, '', true) : self::updateLiveNovalnetTransStatus($datas, false, true, false, '', true);
	  if ($datas['payment_id'] == 27) {
		// Update new comments in orders table for invoice and prepayment payment methods
		self::updatePrepaymentInvoiceTransOrderRef(array(
				'order_id' => $datas['order_id'],
				'tid' => $datas['tid'],
				'amount' => $datas['amount'],
				'due_date' => $datas['due_date']
			   ));
		$tidAccountInfo = NovalnetCore::getPreInvoiceAcccountInfo($datas['tid']);
		$test_mode_msg = (($tidAccountInfo['test_mode'] == 1) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE : '');
		$trans_comments = '';
		list($transDetails, $due_date) = self::formInvoicePrepaymentComments(array(
					  'invoice_account'   => $tidAccountInfo['account_number'],
					  'invoice_bankname'  => $tidAccountInfo['bank_name'],
					  'invoice_bankplace' => $tidAccountInfo['bank_city'],
					  'amount'            => sprintf("%.2f",($tidAccountInfo['amount']/100)),
					  'currency'          => $tidAccountInfo['currency'],
					  'tid'               => $datas['tid'],
					  'invoice_iban'      => $tidAccountInfo['bank_iban'],
					  'invoice_bic'       => $tidAccountInfo['bank_bic'],
					  'due_date'          => $datas['due_date']
					), $test_mode_msg);
		$transDetails .= self::novalnetReferenceComments($datas['order_id'], $datas['payment_type'], $datas['tid'], 1);
	  }
	  $trans_comments .= $transDetails;
	  self::updateOrderStatus($datas['order_id'], self::getOrderStatus($datas['order_id']), $trans_comments, true, 1);
	} else {
	  return (!empty($data['status_desc'])) ? $data['status_desc'] : ((!empty($data['status_text'])) ? $data['status_text'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR);
    }
    return '';
  }

  /**
   * Build reference comments for invoice
   * @param $insert_id
   * @param $payment_type
   * @param $tid
   * @param $amount_update
   *
   * @return string
   */
  static public function novalnetReferenceComments($insert_id, $payment_type, $tid='', $amount_update = '') {
	$invoice_comments = '';
	if($amount_update == 1) {
	  $payment_ref_value = tep_db_fetch_array(tep_db_query("SELECT payment_ref FROM novalnet_preinvoice_transaction_detail WHERE tid='". tep_db_input($tid) ."' "));
	  $reference = unserialize($payment_ref_value['payment_ref']);
	  $references[1] = $reference['payment_ref1'];
      $references[2] = $reference['payment_ref2'];
      $references[3] = $reference['payment_ref3'];	  
	}
	else {
	  $references[1] = $data['payment_ref1'] = constant('MODULE_PAYMENT_' . strtoupper($payment_type) . '_PAYMENT_REFERENCE1') == 'True' ? 1 : 0;
      $references[2] = $data['payment_ref2'] = constant('MODULE_PAYMENT_' . strtoupper($payment_type) . '_PAYMENT_REFERENCE2') == 'True' ? 1 : 0;
      $references[3] = $data['payment_ref3'] = constant('MODULE_PAYMENT_' . strtoupper($payment_type) . '_PAYMENT_REFERENCE3') == 'True' ? 1 : 0;
	}
	$i = 1;
    $ac_reference = array_count_values($references);
    $tid = (empty($tid)) ? $_SESSION['novalnet'][$payment_type]['tid'] : $tid;
    $invoice_comments .= PHP_EOL . (($ac_reference['1'] > 1) ? MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT : MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT) . PHP_EOL;
    foreach ($references as $k => $v) {
      if ($references[$k] == '1') {
        $invoice_comments .= ($ac_reference['1'] == 1) ? MODULE_PAYMENT_NOVALNET_INVPRE_REF : str_replace('@i',$i++, MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF);
        $invoice_comments .= (($k == 1) ? 'BNR-' . NovalnetCore::getProductID() . '-' . $insert_id : ( $k == 2 ? 'TID '. $tid  : MODULE_PAYMENT_NOVALNET_ORDER_NUMBER . ' ' . $insert_id)). PHP_EOL;
      }
    }
	if($amount_update == '') {
 	  $payment_ref = serialize($data);
	  tep_db_perform('novalnet_preinvoice_transaction_detail', array('payment_ref' => $payment_ref), "update", "tid='".$_SESSION['novalnet'][$payment_type]['tid'] . "'");
	}
	return $invoice_comments;
  }

  /**
   * Build fraudmodules input fields
   * @param $fraud_module
   * @param $code
   *
   * @return mixed
   */
  static public function buildCallbackInputFields($fraud_module, $code) {
	global $order;
	if(in_array($fraud_module,array('EMAIL', 'CALLBACK', 'SMS'))) {
      $fraud_module_value = array('EMAIL' => array('name' =>'_fraud_email', 'value' => 'email_address'), 'CALLBACK' => array('name' =>'_fraud_tel', 'value' => 'telephone'), 'SMS' => array('name' =>'_fraud_mobile', 'value' => 'mobile'));
      return array('title' => constant('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_'. $fraud_module . '_INPUT_TITLE') ."<span style='color:red'> * </span>", 'field' => tep_draw_input_field($code . $fraud_module_value[$fraud_module]['name'], (isset($order->customer[$fraud_module_value[$fraud_module]['value']]) ? $order->customer[$fraud_module_value[$fraud_module]['value']] : ''), 'id="' . $code . '-' . strtolower($fraud_module) .'"' ));
    }
    return array();
  }

  /**
   * build input fields to get PIN
   * @param $fraud_module
   * @param $code
   *
   * @return array
   */
  static public function buildCallbackFieldsAfterResponse($fraud_module, $code) {
	if (in_array($fraud_module, array('CALLBACK', 'SMS'))) {
	  $pin_field[] = array('title' => MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC."<span style='color:red'> * </span>",
                           'field' => tep_draw_input_field($code . '_fraud_pin', '', 'id="' . $code . '-callbackpin" autocomplete=off' ));
	  $pin_field[] = array('title' => '',
						   'field' => tep_draw_checkbox_field($code . '_new_pin', '1', false, 'id="' . $code . '-new_pin"') . MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN);
	}
	else {
	  $pin_field[] = array('title' => '',
                           'field' => MODULE_PAYMENT_NOVALNET_FRAUDMODULE_MAIL_INFO);
	}
    return $pin_field;
  }

  /**
   * Redirect to checkout on success using fraud module
   * @param $code
   * @param $fraud_module
   * @param $fraud_module_status
   *
   * @return none
   */
  static public function gotoPaymentOnCallback($code, $fraud_module = NULL, $fraud_module_status = NULL) {
	if ($fraud_module && $fraud_module_status) {
	  $_SESSION['novalnet'][$code]['secondcall'] = TRUE;
	  $error_message = ( ($fraud_module == 'EMAIL') ? MODULE_PAYMENT_NOVALNET_FRAUDMODULE_MAIL_INFO : ( ($fraud_module == 'SMS') ? MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO : MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TEL_PIN_INFO) );
	  
	  $payment_error_return = 'payment_error='. $code .'&error=' . html_entity_decode($error_message, ENT_QUOTES, "UTF-8");
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
  }

  /**
   * Perform server xml request
   * @param $requesttype
   * @param $payment_type
   *
   * @return string
   */
  static public function doCallbackRequest($requesttype, $payment_type = '') {
	$xml = '<?xml version="1.0" encoding="UTF-8"?>
			  <nnxml>
                <info_request>
                  <vendor_id>' . $_SESSION['novalnet'][$payment_type]['vendor'] . '</vendor_id>
				  <vendor_authcode>' . $_SESSION['novalnet'][$payment_type]['auth_code'] . '</vendor_authcode>
				  <request_type>' . $requesttype . '</request_type>
				  <tid>' . $_SESSION['novalnet'][$payment_type]['tid'] . '</tid>';
	if ($requesttype == 'PIN_STATUS')
	  $xml .= '<pin>' .trim($_SESSION['novalnet'][$payment_type][$payment_type.'_fraud_pin']) . '</pin>';
	$xml .= '</info_request></nnxml>';
	$xml_response = self::doPaymentCurlCall('https://payport.novalnet.de/nn_infoport.xml',$xml,false);
	return $xml_response;
  }

  /**
   * Confirm payment after sucessfull fraud check
   * @param $payment_type
   * @param $fraud_module
   *
   * @return mixed
   */
  static public function doConfirmPayment($payment_type, $fraud_module = NULL) {
	$callback_response = ( $fraud_module && in_array($fraud_module,array('SMS', 'CALLBACK')) ) ? self::doCallbackRequest('PIN_STATUS', $payment_type) : self::doCallbackRequest('REPLY_EMAIL_STATUS', $payment_type);
	list($status, $statusMessage) = self::getStatusFromXmlResponse($callback_response);
	if ($status != 100) {
	  if ($status == '0529006') {
		$_SESSION[$payment_type.'_payment_lock'] = TRUE;
	  }
	  $payment_error_return = 'payment_error='. $payment_type .'&error=' . self::setUTFText($statusMessage, true);
	  tep_redirect(html_entity_decode(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	} else {
	  return $callback_response;
	}
  }

  /**
   * Validate status of fraud module
   * @param $payment
   * @param $order
   * @param $fraud_module
   *
   * @return boolean
   */
  static public function setFraudModuleStatus($payment = NULL, $order = array(), $fraud_module = '') {
	if ( !$fraud_module || !NovalnetValidation::validateCallbackCountry() || parent::getFraudModuleEnableLimit($payment) > self::getPaymentAmount($order, $payment) ) {
	  return false;
	}
	if ( $payment == 'novalnet_cc' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True' ) {
	  return false;
	}
	return true;
  }

  /**
   * Get status and message from server response
   * @param $response
   *
   * @return array
   */
  static public function getStatusFromXmlResponse($response) {
	preg_match('/status>?([^<]+)/i', $response, $status);
	preg_match('/status_message>?([^<]+)/i', $response, $statusMessage);
	$status = isset($status['1']) ? $status['1'] : '';
	$statusMessage = isset($statusMessage['1']) ? $statusMessage['1'] : '()';
	return array($status, $statusMessage);
  }

  /**
   * To control the UTF-8 characters
   * @param $data
   * @param $server_error
   *
   * @return string
   */
  static public function setUTFText($string, $server_error = false) {
	if ($server_error) {
	  if (strtoupper(mb_detect_encoding($string)) == 'UTF-8'){
		return (strtoupper(CHARSET) == 'UTF-8') ? html_entity_decode($string, ENT_QUOTES, "UTF-8") : utf8_decode($string);
	  } else{
		return utf8_decode($string);
	  }
	} else{
	  return (strtoupper(CHARSET) == 'UTF-8') ? utf8_encode(html_entity_decode($string, ENT_QUOTES, "UTF-8")) : html_entity_decode($string);
	}
  }
  
  /*
   * Log the novalnet process
   * @param $trans_details
   * @param $response
   *
   */
  static public function apiAmountCalculation(&$trans_details, $response) {
	$payment_type = ($response['payment_id'] == 6) ? 'CREDITCARD_BOOKBACK' : 'REFUND_BY_BANK_TRANSFER_EU';
	if(in_array($response['payment_id'], array(6, 37)) && !empty($response['child_tid_info'])) {
	  $child_tid_info = json_decode(json_encode($response['child_tid_info']), TRUE);
	  if (isset($child_tid_info['follow_up']['payment_type']) && $child_tid_info['follow_up']['payment_type'] == $payment_type)
		$refund_amount += $child_tid_info['follow_up']['amount'];
	  else {
	    foreach( $child_tid_info['follow_up'] as $value ) {
		  if(!empty( $value['payment_type'] ) && $value['payment_type'] == $payment_type)
		    $refund_amount += $value['amount'];
	    }
	  }
	} else {
	  $refund_amount = $trans_details['total_amount'] - $response['amount'];
    }    
	$trans_details['amount'] = $response['amount'];
	$trans_details['status'] = $trans_details['gateway_status'] = $response['status'];
    $trans_details['refund_amount'] = $refund_amount;
  }
  
  /*
   * To get the order amount for booking
   * @param $datas
   *
   */
  static public function getOrderAmount($order_no) {
	$order_total = tep_db_fetch_array(tep_db_query("SELECT value FROM " . TABLE_ORDERS_TOTAL . " where class = 'ot_total' AND orders_id = " . tep_db_input($order_no)));
	return $order_total['value']*100;
  }  
  
  /*
  * Perform to book the amount
  * @param $datas
  *
  * @return string
  */
  static public function bookTransAmount($datas) {    
    $transAPIValidate = NovalnetCore::validateMerchantDetails($datas);
	if($transAPIValidate != '')
      return $transAPIValidate;
	  
	$sqlQuery = tep_db_query("SELECT zerotrxndetails FROM novalnet_transaction_detail WHERE order_no='". tep_db_input($datas['order_id']) ."'");
    $transInfo = tep_db_fetch_array($sqlQuery);
    $urlparam  = unserialize($transInfo['zerotrxndetails']);
	if(isset($urlparam['sepa_due_date_limit'])) {
	  $urlparam['sepa_due_date'] = date('Y-m-d', strtotime('+'.$urlparam['sepa_due_date_limit'].' days'));
	}
    $urlparam['amount']      = $datas['book_amount'];
    $urlparam['order_no']    = $datas['order_id'];
    $urlparam['payment_ref'] = $datas['tid'];
    $response = self::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparam);
    parse_str($response, $data); //Parse Paygate response into array
    if($data['status'] == 100) {
	  $currencies = new currencies();
	  $amount_formated = $currencies->format($datas['book_amount']/100, false, $datas['amount_currency']);
	  self::updateOrderStatus($datas['order_id'], self::getOrderStatus($datas['order_id']), PHP_EOL.sprintf(MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE, $amount_formated, $data['tid']).PHP_EOL, true, true, true);
	  $test_mode_msg = (isset($urlparam['test_mode']) && $urlparam['test_mode'] == 1) ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE : '';
	  $message = MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $data['tid'] . $test_mode_msg;
	  $trans_novalnet_info = self::getTransDetails(array(
        'nn_vendor'    => $urlparam['vendor'],
        'nn_auth_code' => $urlparam['auth_code'],
        'nn_product'   => $urlparam['product'],
        'tid'          => $data['tid']
      ));
	  $param['tid'] = $data['tid'];
	  tep_db_perform('novalnet_subscription_detail', $param, "update", "order_no='". tep_db_input($datas['order_id']) ."'");
	  $param['amount'] 		   = $param['total_amount'] = $urlparam['amount'];
	  $param['gateway_status'] = $trans_novalnet_info->status;
	  tep_db_perform('novalnet_transaction_detail', $param, "update", "order_no='". tep_db_input($datas['order_id']) ."'");
 	  self::updateOrderStatus($datas['order_id'], self::getOrderStatus($datas['order_id']), $message, true, true, true, true);
    } else {
      return utf8_decode((!empty($data['status_desc'])) ? $data['status_desc']:((!empty($data['status_text']))?$data['status_text']:MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR));
    }
    return '';
  }
    
  /*
  * Sets Iframe param for creditcard
  *
  * @return array
  */
  static public function setCreditCardIframe () {
	require_once DIR_FS_CATALOG.'/novalnet_css_link.php';
	if (NOVALNET_CC_CUSTOM_CSS)
	  $css = NOVALNET_CC_CUSTOM_CSS;
	else 
	  $css = 'body~~~input, select';
	if (NOVALNET_CC_CUSTOM_CSS_STYLE)
	  $cssval = NOVALNET_CC_CUSTOM_CSS_STYLE;
	else
	  $cssval = 'color: #555555; font-family: Arial,Sans-serif !important; font-size: 12px !important; line-height: 18px;~~~border: 1px solid #666666; padding: 2px; max-width: 202px; min-width: 39px;';
	
	$params['nn_language'] = MODULE_PAYMENT_NOVALNET_LANGUAGE_TEXT;
	$params['iframe']  	   = 'creditcard';
	$params['hash'] 	   = NovalnetCore::getCreditCardRefillHash();
	$params['fldvdr']      = isset($_SESSION['novalnet']['novalnet_cc']['cc_fldvalidator']) ? $_SESSION['novalnet']['novalnet_cc']['cc_fldvalidator'] : '';
	$params 			   = http_build_query($params);
	return array($css, $cssval, $params);
  }
  
  /*
  * Sets Iframe param for sepa
  * @param $order
  *
  * @return array
  */
  static public function setSepaIframe($order) {
	require_once DIR_FS_CATALOG.'novalnet_css_link.php';
	if (NOVALNET_SEPA_CUSTOM_CSS)
	  $css = NOVALNET_SEPA_CUSTOM_CSS;
	else 
	  $css = 'body~~~input, select';
	if (NOVALNET_SEPA_CUSTOM_CSS_STYLE)
	  $cssval = NOVALNET_SEPA_CUSTOM_CSS_STYLE;
	else
	  $cssval = 'color: #555555; font-family: Arial,Sans-serif !important; font-size: 12px !important; line-height: 18px;~~~border: 1px solid #666666; padding: 2px; max-width: 202px; min-width: 39px;';
    $params['nn_language']  = MODULE_PAYMENT_NOVALNET_LANGUAGE_TEXT;
	$params['iframe']  		= 'sepa';
	$firstname 				= $order->billing['firstname'] ? trim($order->billing['firstname']) : trim($order->customer['firstname']);
	$lastname 				= $order->billing['lastname'] ? trim($order->billing['lastname']) : trim($order->customer['lastname']);
	$params['hash'] 	    = parent::getSepaRefillHash($order->customer['email_address'], 'novalnet_sepa');
	$params['fldvdr']       = isset($_SESSION['novalnet']['novalnet_sepa']['sepa_field_validator']) ? $_SESSION['novalnet']['novalnet_sepa']['sepa_field_validator'] : '';
	$params['country_code'] = $order->billing['country']['iso_code_2'] ? trim($order->billing['country']['iso_code_2']) : trim($order->customer['country']['iso_code_2']);
	$params['name'] 	    = $firstname . ' ' . $lastname;
	$params['comp'] 	    = $order->billing['company'] ? trim($order->billing['company']) : $order->customer['company'];
	$params['address']      = $order->billing['street_address'] ? trim($order->billing['street_address']) : $order->customer['street_address'];
	$params['zip'] 	   		= $order->billing['postcode'] ? trim($order->billing['postcode']) : $order->customer['postcode'];
	$params['city'] 	    = $order->billing['city'] ? trim($order->billing['city']) : $order->customer['city'];
	$params['email'] 	    = $order->customer['email_address'] ? trim($order->customer['email_address']) : '';
	$params 			    = http_build_query($params);
	return array($css, $cssval, $params);
  }  
  
  /*
  * Check the novalnet version
  *
  * @return mixed
  */
  static public function checkNovalnetVersion() {
    $sql = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '"');
    while($result = tep_db_fetch_array($sql)) {
	  if($result['table_name'] == 'novalnet_version_detail') {
		$db_version = tep_db_query('SELECT version from novalnet_version_detail');
		$novalnet_version = tep_db_fetch_array($db_version);
		return $novalnet_version['version'];
	  }
	}
	return false;
  }
}
?>
