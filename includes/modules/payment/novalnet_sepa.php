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
 * Script : novalnet_sepa.php
 */
require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');

class novalnet_sepa extends NovalnetCore {

  public $code, $title, $description, $enabled;

  public function __construct() {
	parent::loadConstants();
	$this->code = 'novalnet_sepa';
	$this->title = $this->public_title = MODULE_PAYMENT_NOVALNET_SEPA_TEXT_TITLE;
	$this->description = MODULE_PAYMENT_NOVALNET_SEPA_DESC;
	$this->sort_order = defined('MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER') && MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER != '' ? MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER : 0;
	if ( strpos(MODULE_PAYMENT_INSTALLED,$this->code) !== false ) {
	  $this->enabled = (MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_MODULE == 'True');
	  $this->fraud_module = ((MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE == 'False') ? false : MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE);
	}
  }

  function selection() {
	global $order, $payment;
	parent::validatecallbacksession();
	if ( !parent::validateMerchantAPIConf((array)$order, $this->code, $this->enabled) || !parent::validateCallbackStatus($this->code, $this->fraud_module) ) {
      return false;
    }
	if ( empty($payment) && (MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION == 'True' || MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER == 'True') ) {
	  if ( parent::getLastSuccessTransPayment($order->customer['email_address'], $this->code) ) {
		$payment = $this->code;
	  }
	}
	if ( isset($_SESSION['payment']) && $_SESSION['payment'] != $this->code && isset($_SESSION['novalnet'][$this->code]) ) { 
	  unset($_SESSION['novalnet'][$this->code]);
	}

	$this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);

	$endcustomerinfo = trim(strip_tags(MODULE_PAYMENT_NOVALNET_SEPA_ENDCUSTOMER_INFO));
	$test_mode = NovalnetInterface::getPaymentTestModeStatus($this->code);
	$description = '<br>'. $this->description. '<noscript><input type="hidden" name="nn_sepa_js_enabled" value="1"><br /><div style="color:red"><b>'. MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_PROBLEM . '</b></div></noscript>';
	$description .= ($endcustomerinfo != '') ? '<br/>'.$endcustomerinfo : '';
	$description .= ($test_mode == 1) ? '<br>' . utf8_encode(MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG) : '';

	$selection['id'] = $this->code;
	$selection['module'] = MODULE_PAYMENT_NOVALNET_SEPA_PUBLIC_TITLE . $description;
	$company = ((!empty($order->billing['company'])) ? $order->billing['company'] : $order->customer['company']);
	if ( isset($_SESSION['novalnet'][$this->code]['tid']) && $this->fraud_module &&  $this->fraud_module_status ) {
	  $selection['fields'] = NovalnetInterface::buildCallbackFieldsAfterResponse($this->fraud_module,$this->code);
	} else {
	  $data = array();
      $data['vendor'] = parent::getVendorID();
      $data['auth_code'] = parent::getVendorAuthCode();
      NovalnetCore::getAffDetails($data);
	  $pin_by_callback = '';
	  if (in_array($this->fraud_module ,array('EMAIL', 'CALLBACK', 'SMS'))) {
        $fraud_module_value = array('EMAIL' => array('name' =>'_fraud_email', 'value' => 'email_address'), 'CALLBACK' => array('name' =>'_fraud_tel', 'value' => 'telephone'), 'SMS' => array('name' =>'_fraud_mobile', 'value' => 'mobile'));
        $pin_by_callback = constant('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_'. $this->fraud_module.'_INPUT_TITLE').' <span style="color:red"> * </span></td>
								  <td>'. tep_draw_input_field($this->code.$fraud_module_value[$this->fraud_module]['name'], (isset($order->customer[$fraud_module_value[$this->fraud_module]['value']]) ?$order->customer[$fraud_module_value[$this->fraud_module]['value']] : ''), 'id=' . $this->code . '-'. strtolower($this->fraud_module).' autocomplete=off" ');
      }
      if (MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE == 'Iframe') {
		$iframe = '';
	    list($original_sepa_customstyle_css, $original_sepa_customstyle_cssval, $params) = NovalnetInterface::setSepaIframe($order);
	    $iframe = '<input type="hidden" name="original_sepa_vendor_id" id="original_sepa_vendor_id" value="'. $data['vendor'] .'"/>
				    <input type="hidden" name="original_vendor_authcode" id="original_vendor_authcode" value="'.$data['auth_code'].'"/>
					<input type="hidden" id="sepa_field_validator"  name="sepa_field_validator" value=""/>
					<input type="hidden" id="original_sepa_panhash"  name="original_sepa_panhash" value=""/>
					<input type="hidden" id="sepa_panhash"  name="sepa_panhash" value=""/>
					<input type="hidden" id="original_sepa_customstyle_css" value="' . $original_sepa_customstyle_css . '"/>
					<input type="hidden" id="original_sepa_customstyle_cssval" value="' . $original_sepa_customstyle_cssval . '"/>
					<input type="hidden" id="original_iframeparent_submit_btn" value=""/>
					<div id="loading_sepa"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/loader.gif" alt="Novalnet AG" /></div>
					<iframe id="payment_form_novalnetSepa" name="payment_form_novalnetSepa" width="550" height="440" scrolling="no" src="' . tep_href_link('novalnet_form.php', $params, 'SSL', true, false) . '" frameBorder="0"></iframe>
					<input type="hidden" id="novalnet_sepa_account_holder" name="novalnet_sepa_account_holder" value=""/>
					<input type="hidden" id="nn_sepa_hash" name="nn_sepa_hash" value=""/>
					<input type="hidden" id="nn_sepa_uniqueid" name="nn_sepa_uniqueid" value=""/>
					<input type="hidden" id="nn_lang_valid_account_details" value="'.MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR.'"/>
					<script src="' . parent::sepaScriptPath() . '" type="text/javascript"></script>
					<link rel="stylesheet" type="text/css" href="' . parent::novalnetCssPath() . '">';
	  }
	  elseif (MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE == 'Local') {
		$sepa_fields = '<input type="hidden" id="nn_vendor" value="'.$data['vendor'].'"/>
									  <input type="hidden" id="nn_auth_code" value="'.$data['auth_code'].'"/>
									  <input type="hidden" id="nn_company" value="'.$company.'"/>
									  <input type="hidden" id="nn_sepa_hash"  name="nn_sepa_hash" value=""/>
									  <input type="hidden" id="nn_sepa_mandate_ref" value=""/>
									  <input type="hidden" id="nn_sepa_iban" value=""/>
									  <input type="hidden" id="nn_sepa_bic" value=""/>
									  <input type="hidden" id="nn_root_sepa_catalog" value="'.DIR_WS_CATALOG.'"/>
									  <input type="hidden" id="nn_sepa_mandate_date" value=""/>
									  <input type="hidden" id="nn_sepa_uniqueid"  name="nn_sepa_uniqueid" value="'.parent::uniqueRandomString().'"/>
									  <input type="hidden" id="nn_sepa_input_panhash" value="'.parent::getSepaRefillHash($order->customer['email_address'], $this->code).'"/>
									  <input type="hidden" id="nn_lang_mandate_confirm" value="'.MODULE_PAYMENT_NOVALNET_SEPA_MANDATE_CONFIRM_ERROR.'"/>
									  <input type="hidden" id="nn_lang_valid_merchant_credentials" value="'.MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR.'"/>
									  <input type="hidden" id="nn_lang_valid_account_details" value="'.MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR.'"/>
									  <div class="loader" id="nn_loader" style="display:none"></div>
									  <script src="'.parent::sepaScriptPath().'" type="text/javascript"></script>
									  <link rel="stylesheet" type="text/css" href="'.parent::novalnetCssPath().'">';
	  }
	  if (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ONECLICK'  &&
            $sqlQuerySet = parent::getPaymentRefDetails($_SESSION['customer_id'], $this->code)) {
        $form_show = isset($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account']) ? $_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] : 1;
        $masked_acc_details = unserialize($sqlQuerySet['masked_acc_details']);
        $selection['fields'][] = array(
					'title' => '',
					'field' => '<span id ="novalnet_sepa_new_acc" style="color:blue"><u><b>' . MODULE_PAYMENT_NOVALNET_SEPA_NEW_ACCOUNT . '</b></u></span>
								  <div id="nn_sepa_ref_details" style="display:none">
									<table>
									  <tr>
										<td>'.MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER.':</td>
										<td>'. $masked_acc_details['bankaccount_holder'] . '</td>
									  </tr>
									  <tr>
										<td>IBAN:</td>
										<td>'.$masked_acc_details['iban'] .'<input type="hidden" id="nn_payment_ref_tid_sepa" name="nn_payment_ref_tid_sepa" value="'.$sqlQuerySet['tid'].'"/>
										<input type="hidden" name="novalnet_sepachange_account" id="novalnet_sepachange_account" value="'.$form_show .'"/></td>
									  </tr>
									  <tr>
										<td>BIC:</td>
										<td>'.$masked_acc_details['bic'].'</td>
									  </tr>
									  <tr>
										<td> <input type="hidden" id="nn_ref_process_key" name="nn_ref_process_key" value="'.$sqlQuerySet['process_key'].'"/></td>
										<td><div>' . tep_draw_checkbox_field($this->code . '_mandate_confirm_ref', 1, false, 'id="' . $this->code . '_mandate_confirm_ref"') . MODULE_PAYMENT_NOVALNET_SEPA_FORM_MANDATE_CONFIRM_TEXT .'</td>
									  </tr>
									</table>
								  </div>');
        if (MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE == 'Iframe') {
			$selection['fields'][] = array('title' => '',
											 'field' => '<div id="nn_sepa_acc" style="display:none"><table ><tr class ="nn_sepa_acc_tr"><td> </td>' . $iframe. '<input type="hidden" id="nn_lang_new_account" value="'.MODULE_PAYMENT_NOVALNET_SEPA_NEW_ACCOUNT.'"/><input type="hidden" id="nn_lang_given_account" value="'.MODULE_PAYMENT_NOVALNET_SEPA_GIVEN_ACCOUNT.'"/></tr><tr class="nn_sepa_acc_tr"><td>'.$pin_by_callback.'</td></tr></table></div>' );
		} elseif (MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE == 'Local') {
		  $selection['fields'][] = array(
			   'title' => '',
			   'field' => '<div id="nn_sepa_acc" style="display:none">
							 <table>
								<tr><td>'. MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER.'<span style="color:red"> * </span></td>
								   <td>'.tep_draw_input_field($this->code.'_account_holder', parent::customerName($order->customer), 'id="' . $this->code . '_account_holder" AUTOCOMPLETE="off" onkeypress="return ibanbic_validate(event, true);"').'</td></tr>
								<tr><td>'. MODULE_PAYMENT_NOVALNET_BANK_COUNTRY.'<span style="color:red"> * </span></td>
								   <td>'.tep_draw_pull_down_menu($this->code . '_bank_country', parent::sepaBankCountry(), $order->billing['country']['iso_code_2'], 'id="' . $this->code . '_bank_country"').'</td></tr>
								<tr><td><nobr>'.MODULE_PAYMENT_NOVALNET_ACCOUNT_OR_IBAN.'<span style="color:red"> * </span></nobr></td>
								   <td>'.tep_draw_input_field($this->code . '_iban', '', 'id="' . $this->code . '_iban" autocomplete="off" onkeypress="return ibanbic_validate(event, false);"').'<span id="novalnet_sepa_iban_span"></span></td></tr>
								<tr><td>'.MODULE_PAYMENT_NOVALNET_BANKCODE_OR_BIC.'<span style="color:red"> * </span></td>
								   <td>'.tep_draw_input_field($this->code . '_bic', '', 'id="' . $this->code . '_bic" autocomplete="off" onkeypress="return ibanbic_validate(event, false);"').'<span id="novalnet_sepa_bic_span"></span></td></tr>
								<tr><td></td><td><div>'.tep_draw_checkbox_field($this->code . '_mandate_confirm', 1, false, 'id="' . $this->code . '_mandate_confirm"').MODULE_PAYMENT_NOVALNET_SEPA_FORM_MANDATE_CONFIRM_TEXT .
								'<input type="hidden" id="nn_lang_new_account" value="'.MODULE_PAYMENT_NOVALNET_SEPA_NEW_ACCOUNT.'"/><input type="hidden" id="nn_lang_given_account" value="'.MODULE_PAYMENT_NOVALNET_SEPA_GIVEN_ACCOUNT.'"/>'
								  . $sepa_fields . '</td></tr><tr><td>'.$pin_by_callback.'</table></div>'
								);
		}
	  } else {
		if (MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE == 'Iframe') {
		  $selection['fields'][] = array('title' => '',
										 'field' => '<table><tr class ="nn_sepa_acc_tr">
											' . $iframe . '</tr><tr class ="nn_sepa_acc_tr"><td>' . $pin_by_callback . '</td></tr></table>' );
		}
		elseif (MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE == 'Local') {
          $selection['fields'][] = array('title' => MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER."<span style='color:red'> * </span>",
									     'field' => tep_draw_input_field($this->code.'_account_holder', parent::customerName($order->customer), 'id="' . $this->code . '_account_holder" AUTOCOMPLETE="off" onkeypress="return ibanbic_validate(event, true);"')
										);
          $selection['fields'][] = array('title' => MODULE_PAYMENT_NOVALNET_BANK_COUNTRY."<span style='color:red'>* </span>",
									     'field' => tep_draw_pull_down_menu($this->code . '_bank_country', parent::sepaBankCountry(), $order->billing['country']['iso_code_2'], 'id="' . $this->code . '_bank_country"')
										);
		  $selection['fields'][] = array('title' => '<nobr>' . MODULE_PAYMENT_NOVALNET_ACCOUNT_OR_IBAN."<span style='color:red'> * </span></nobr>",
									     'field' => tep_draw_input_field($this->code . '_iban', '', 'id="' . $this->code . '_iban" AUTOCOMPLETE="off" onkeypress="return ibanbic_validate(event, false);"').'<span id="novalnet_sepa_iban_span"></span>'
										);
          $selection['fields'][] = array('title' => MODULE_PAYMENT_NOVALNET_BANKCODE_OR_BIC."<span style='color:red'> * </span>",
									     'field' => tep_draw_input_field($this->code . '_bic', '', 'id="' . $this->code . '_bic" AUTOCOMPLETE="off" onkeypress="return ibanbic_validate(event, false);"').'<span id="novalnet_sepa_bic_span"></span>'
										);
          $selection['fields'][] = array('title' => '',
										 'field' => '<div>'.tep_draw_checkbox_field($this->code . '_mandate_confirm', 1, false, 'id="' . $this->code . '_mandate_confirm"').MODULE_PAYMENT_NOVALNET_SEPA_FORM_MANDATE_CONFIRM_TEXT .$sepa_fields
										);
		  if ( $this->fraud_module && $this->fraud_module_status ) {
		    $selection['fields'][] = NovalnetInterface::buildCallbackInputFields($this->fraud_module, $this->code);
	      }
		}
	  }
	}
	return $selection;
  }

  function pre_confirmation_check() {
	global $order;
	$post = $_REQUEST;
	if ( !empty($_SESSION['novalnet'][$this->code]['secondcall']) ) {
	  NovalnetValidation::validateUserInputsOnCallback($this->fraud_module, $this->code, $post);
	} else {
	  $_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] = isset($post['novalnet_sepachange_account']) ? $post['novalnet_sepachange_account'] : '';
	  if (empty($post['novalnet_sepa_mandate_confirm_ref']) && isset($post['novalnet_sepachange_account']) && $post['novalnet_sepachange_account'] == 1) {
        $payment_error_return = 'error_message=' . MODULE_PAYMENT_NOVALNET_SEPA_MANDATE_CONFIRM_ERROR;
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }
      if (isset($post['nn_payment_ref_tid_sepa']) && $post['novalnet_sepachange_account'] == 1) {
        $_SESSION['novalnet'][$this->code]['nn_payment_ref_enable']= TRUE;
        $_SESSION['novalnet'][$this->code]['nn_payment_ref_tid_sepa'] = $post['nn_payment_ref_tid_sepa'];
        return true;
      }
      $this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);
	  NovalnetValidation::validateUserInputs($this->fraud_module, $this->fraud_module_status, $this->code, $post);
	}
    return false;
  }

  function confirmation() {
	global $order;
	if ( !empty($_SESSION['novalnet'][$this->code]['secondcall']) ) {
	  NovalnetValidation::validateAmountOnCallback($this->code, $this->fraud_module);
	}
	$_SESSION['novalnet'][$this->code]['payment_amount'] = NovalnetInterface::getPaymentAmount((array)$order, $this->code);
	return false;
  }

  function process_button() {
	$post = $_REQUEST;
	if ( !empty($post[$this->code . '_new_pin']) ) {
	  $new_pin_response = NovalnetInterface::doCallbackRequest('TRANSMIT_PIN_AGAIN', $this->code);
	  NovalnetValidation::validateNewPinResponse($new_pin_response, 'TRANSMIT_PIN_AGAIN', $this->code);
	}
	elseif ( isset($_SESSION['novalnet'][$this->code]['payment_amount']) ) {
	  $novalnet_order_details = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
	  $_SESSION['novalnet'][$this->code] = array_merge($novalnet_order_details, $post, array('payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']));
	}
	else {
	  $payment_error_return = 'payment_error=' . $this->code . '&error=' . MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	  tep_redirect(self::setUTFText(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	}
	return NovalnetValidation::confirmButtonDisableActivate();
  }

  function before_process() {
	global $order;
	$this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);
	$this->fraud_module_status = isset($_SESSION['novalnet'][$this->code]['nn_payment_ref_enable']) ? FALSE:$this->fraud_module_status;
	$param_inputs = array_merge((array)$order, $_SESSION['novalnet'][$this->code],array('fraud_module' => $this->fraud_module,'fraud_module_status' => $this->fraud_module_status));
	if ( !empty($param_inputs['secondcall']) ) {
	  NovalnetInterface::doConfirmPayment($this->code, $this->fraud_module);
	} else {
	  parent::novalnet_before_process($param_inputs); // Perform real time payment transaction
	  NovalnetInterface::gotoPaymentOnCallback($this->code, $this->fraud_module, $this->fraud_module_status);
	}

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
