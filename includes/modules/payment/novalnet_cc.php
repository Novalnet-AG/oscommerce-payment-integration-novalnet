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
 * Script :  novalnet_cc.php
 */
 
require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');
class novalnet_cc extends NovalnetCore {
  public $code, $title, $description, $enabled, $fraud_module, $fraud_module_status;

  public function __construct() {
	parent::loadConstants();
	$this->code = 'novalnet_cc';
	if ( ( defined('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE') && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True' ) || (defined('MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE') && MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect') ) {
	  $this->form_action_url = NovalnetInterface::getPaygateURL($this->code);
	}
	$this->title = $this->public_title = MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE;
	$this->description = MODULE_PAYMENT_NOVALNET_CC_DESC;
	$this->sort_order = defined('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER') && MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER != '' ? MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER : 0;
	if ( strpos(MODULE_PAYMENT_INSTALLED,$this->code) !== false ) {
	  $this->enabled      = (MODULE_PAYMENT_NOVALNET_CC_ENABLE_MODULE == 'True');
	  $this->fraud_module = ((MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE == 'False') ? false : MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE);
	}
  }

  function selection() {
	global $order, $payment;
	parent::validatecallbacksession();
	if ( !parent::validateMerchantAPIConf((array)$order, $this->code, $this->enabled) || !parent::validateCallbackStatus($this->code, $this->fraud_module) ) {
	  return false;
	}
	if ( empty($payment) && MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION == 'True' && parent::getLastSuccessTransPayment($order->customer['email_address'], $this->code) ) {
		$payment = $this->code;
	}
	if(isset($_SESSION['payment']) && $_SESSION['payment'] != $this->code && isset($_SESSION['novalnet'][$this->code])) { unset($_SESSION['novalnet'][$this->code]); 
	}
	$this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);

	$endcustomerinfo = trim(strip_tags(MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO));
	$test_mode = NovalnetInterface::getPaymentTestModeStatus($this->code);
	$description = '<br>'. $this->description . '<noscript><input type="hidden" name="nn_cc_js_enabled" value="1"><br /><div style="color:red"><b>'. MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_PROBLEM . '</b></div></noscript>';
	$description .= ($endcustomerinfo != '') ? '<br/>'.$endcustomerinfo : '';
    $description .= ($test_mode == 1) ? '<br>' . utf8_encode(MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG) : '';

	$selection['id'] 	 = $this->code;
	$selection['module'] = MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE . $description;
	if (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True') {
	  return $selection;
	} elseif ( isset($_SESSION['novalnet'][$this->code]['tid']) && $this->fraud_module &&  $this->fraud_module_status ) {
	  $selection['fields'] = NovalnetInterface::buildCallbackFieldsAfterResponse($this->fraud_module,$this->code);
	} else {
	  $data = array();
	  $pin_by_callback = '';
      $data['vendor'] = parent::getVendorID();
	  $data['auth_code'] = parent::getVendorAuthCode();
	  $data['product'] = parent::getProductID();
      NovalnetCore::getAffDetails($data);
      if (in_array($this->fraud_module, array('EMAIL', 'CALLBACK', 'SMS')) && $this->fraud_module_status && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True') {
        $fraud_module_value = array('EMAIL' => array('name' =>'_fraud_email', 'value' => 'email_address'), 'CALLBACK' => array('name' =>'_fraud_tel', 'value' => 'telephone'), 'SMS' => array('name' =>'_fraud_mobile', 'value' => 'mobile'));
        $pin_by_callback = constant('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_'. $this->fraud_module.'_INPUT_TITLE') . ' <span style="color:red"> * </span></td>
                <td>' . tep_draw_input_field($this->code.$fraud_module_value[$this->fraud_module]['name'], (isset($order->customer[$fraud_module_value[$this->fraud_module]['value']]) ?$order->customer[$fraud_module_value[$this->fraud_module]['value']] : ''), 'id=' . $this->code . '-'. strtolower($this->fraud_module).' autocomplete=off');
      }
      if (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Iframe') {
		list($original_cc_customstyle_css, $original_cc_customstyle_cssval, $params) = NovalnetInterface::setCreditCardIframe();
		$iframe = '<input type="hidden" name="original_vendor_id" id="original_vendor_id" value="'. $data['vendor'] .'"/>
				   <input type="hidden" name="original_vendor_authcode" id="original_vendor_authcode" value="'. $data['auth_code'] .'"/>
		 		   <input type="hidden" id="cc_fldvalidator" name="cc_fldvalidator" value=""/>
				   <input type="hidden" id="original_customstyle_css" value="' . $original_cc_customstyle_css . '"/>
				   <input type="hidden" id="original_customstyle_cssval" value="' . $original_cc_customstyle_cssval . '"/>
				   <div id="loading_cc"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/loader.gif" alt="Novalnet AG" /></div>
				   <iframe id="payment_form_novalnetCc" name="payment_form_novalnetCc" width="550" height="230" scrolling="no" src="' . tep_href_link('novalnet_form.php', $params, 'SSL', true, false) . '" frameBorder="0"></iframe></tr>
				   <input type="hidden" id="novalnet_cc_exp_month" name="novalnet_cc_exp_month" value=""/>
				   <input type="hidden" id="novalnet_cc_exp_year" name="novalnet_cc_exp_year" value=""/>
				   <input type="hidden" id="novalnet_cc_holder" name="novalnet_cc_holder" value=""/>
				   <input type="hidden" id="novalnet_cc_cvc" name="novalnet_cc_cvc" value=""/>
				   <input type="hidden" id="nn_cc_hash" name="nn_cc_hash" value=""/>
				   <input type="hidden" id="nn_cc_uniqueid" name="nn_cc_uniqueid" value=""/>
				   <input type="hidden" id="nn_cc_valid_error_ccmessage" value="'.MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS.'"/>
				   <script src="' . parent::creditCardScriptPath() . '" type="text/javascript"></script>
				   <link rel="stylesheet" type="text/css" href="' . parent::novalnetCssPath() . '">';		   
	  }
	  else {
		$cc_fields = '<input type="hidden" id="nn_vendor" value="'.$data['vendor'].'"/>
					  <input type="hidden" id="nn_auth_code" value="'.$data['auth_code'].'"/>
					  <input type="hidden" id="nn_cc_hash" name="nn_cc_hash" value=""/>
					  <input type="hidden" id="nn_cc_uniqueid" name="nn_cc_uniqueid" value="'.parent::uniqueRandomString().'"/>
					  <input type="hidden" id="nn_cc_input_panhash" value="'.parent::getCreditCardRefillHash().'"/>
					  <input type="hidden" id="nn_cc_valid_error_ccmessage" value="'.MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS.'"/>
					  <input type="hidden" id="nn_merchant_valid_error_ccmessage" value="'.MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR.'"/>
					  <input type="hidden" id="nn_root_cc_catalog" value="'.DIR_WS_CATALOG.'"/>
					  <div class="loader" id="loader" style="display:none"></div>
					  <span id="cvc_info" style ="display:none;"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/creditcard_cvc.png"></span>
					  <script src="'.parent::creditCardScriptPath().'" type="text/javascript"></script>
					  <link rel="stylesheet" type="text/css" href="'.parent::novalnetCssPath().'">';
	  }	
	  if (MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ONECLICK' &&
        $sqlQuerySet = parent::getPaymentRefDetails($_SESSION['customer_id'], $this->code)) {
        $cvc = '';
        $masked_acc_details = unserialize($sqlQuerySet['masked_acc_details']);
        $masked_acc_details['cc_holder'] = html_entity_decode($masked_acc_details['cc_holder'], ENT_QUOTES, 'UTF-8');
        $form_show = isset($_SESSION['novalnet'][$this->code]['novalnet_ccchange_account']) ? $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] : 1;
        if (MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT == 'True') {
          $cvc = '<tr class ="nn_cc_ref_details_tr"><td>' . MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC . ':</td>
                    <td>'.tep_draw_input_field($this->code.'_cvc_ref', '', 'id="' . $this->code . '_cvc_ref" autocomplete=off onkeypress="return isNumberKey(event, false)" style="width:30px;"').
                      '&nbsp;<span id="showcvc_ref"><a onmouseover="show_cvc_info(true, \'cvc_info_ref\', \'showcvc_ref\');" onmouseout="show_cvc_info(false, \'cvc_info_ref\', \'showcvc_ref\');" style="text-decoration: none;"> <img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/cvc_hint.png" border="0" style="margin-top:0px;" alt="CCV/CVC?"></a></span>
                      <input type="hidden" id="nn_cc_hash_ref" name="nn_cc_hash" value="'.$sqlQuerySet['process_key'].'"/>
                      <span id="cvc_info_ref" style ="display:none;"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/creditcard_cvc.png"></span>
                      <link rel="stylesheet" type="text/css" href="'.parent::novalnetCssPath().'"></td>
                    </tr>';
        }
        $selection['fields'][] = array('title' => '',
									   'field' => '<span id ="novalnet_cc_new_acc" style="color:blue"><u><b>' . MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT . '</b></u></span>
                    <div id="nn_cc_ref_details">
                        <table>
                    <tr class ="nn_cc_ref_details_tr"><td>'.MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER.':</td>
                        <td>'. $masked_acc_details['cc_holder'] . '</td>
                    </tr>
                    <tr class ="nn_cc_ref_details_tr"><td>'.MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO.':</td>
                        <td>'.$masked_acc_details['cc_no'] .'<input type="hidden" id="nn_payment_ref_tid_cc" name="nn_payment_ref_tid" value="'.$sqlQuerySet['tid'].'"/><input type="hidden" name="novalnet_ccchange_account" id="novalnet_ccchange_account" value="'.$form_show.'"/></td>
                    </tr>
                    <tr class ="nn_cc_ref_details_tr"><td>'.MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE.':</td>
                        <td>'.$masked_acc_details['cc_exp_month'] . ' / ' . $masked_acc_details['cc_exp_year'] .'
                        <input type="hidden" id="nn_ref_cc_exp_month" name="nn_ref_cc_exp_month" value="'.$masked_acc_details['cc_exp_month'].'"/><input type="hidden" id="nn_ref_cc_exp_year" name="nn_ref_cc_exp_year" value="'.$masked_acc_details['cc_exp_year'].'"/><input type="hidden" id="nn_ref_process_key" name="nn_ref_process_key" value="'.$sqlQuerySet['process_key'].'"/>
                        </td>
                    </tr>'.$cvc
                    .'
            </table></div>');
        if (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Iframe') {
		  $selection['fields'][] = array('title' => '',
									     'field' => '<div id="nn_cc_acc" style="display:none"><table>
										   <tr class ="nn_cc_acc_tr">
										   <input type="hidden" id="nn_lang_cc_new_account" value="' . MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT . '"/><input type="hidden" id="nn_lang_cc_given_account" value="' . MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT . '"/>' . $iframe  . '</tr><tr class ="nn_cc_acc_tr"><td>' . $pin_by_callback . '</td></tr></table></div>' );
		} else {
		  $selection['fields'][] = array('title' => '',
										 'field' => '<div id="nn_cc_acc" style="display:none"><table>
                 <tr class ="nn_cc_acc_tr"><td>'.MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER.'<span style="color:red"> * </span></td>
                    <td>'. tep_draw_input_field($this->code.'_holder', parent::customerName($order->customer), 'id="' . $this->code . '_holder" autocomplete=off onkeypress="return splCharValidate(event)" ').'</td>
                </tr>
                <tr class ="nn_cc_acc_tr"><td>'.MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO.'<span style="color:red"> * </span></td>
                    <td>'.tep_draw_input_field($this->code . '_no', '', 'id="' . $this->code . '_no" autocomplete=off onkeypress="return isNumberKey(event, true);" ').'</td>
                </tr>
                <tr class ="nn_cc_acc_tr"><td>'. MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE.'<span style="color:red"> * </span></td>
                    <td>'.tep_draw_pull_down_menu($this->code.'_exp_month', parent::creditCardValidMonth() , '', 'id="' . $this->code . '_exp_month"').'&nbsp;'. tep_draw_pull_down_menu($this->code . '_exp_year', parent::creditCardValidYear() , '', 'id="' . $this->code . '_exp_year"')
                        .'</td>
                </tr>'.
                '<tr class ="nn_cc_acc_tr"><td>'. MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC .'<span style="color:red"> * </span></td>
                <td>'.tep_draw_input_field($this->code.'_cvc', '', 'id="' . $this->code . '_cvc" autocomplete=off onkeypress="return isNumberKey(event, false)" style="width:30px;"').
                                  '&nbsp;<span id="showcvc"><a onmouseover="show_cvc_info(true, \'cvc_info\', \'showcvc\');" onmouseout="show_cvc_info(false, \'cvc_info\', \'showcvc\');" style="text-decoration: none;"> <img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/cvc_hint.png" border="0" style="margin-top:0px;" alt="CCV/CVC?"></a></span>
                                  <input type="hidden" id="nn_lang_cc_new_account" value="'.MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT.'"/><input type="hidden" id="nn_lang_cc_given_account" value="' . MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT . '"/>' . $cc_fields . '</td></tr><tr class ="nn_cc_acc_tr"><td>'
                                   . $pin_by_callback . '</td></tr></table></div>'
                                );
		}
      } elseif(MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE != 'ONECLICK' || empty($sqlQuerySet) || MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE != 'Redirect' || (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True')) {
		if (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Iframe') {
		  $selection['fields'][] = array('title' => '',
										 'field' => '<table><tr class="nn_cc_acc_tr">' . $iframe . '</tr><tr class ="nn_cc_acc_tr"><td>' . $pin_by_callback . '</td></tr></table>' );
		}
		else {
		  $selection['fields'][] = array('title' => MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER.'<span style="color:red"> * </span>',
									  'field' => tep_draw_input_field($this->code.'_holder', parent::customerName($order->customer), 'id="' . $this->code . '_holder" autocomplete=off onkeypress="return splCharValidate(event)" ')
									);
		  $selection['fields'][] = array('title' => MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO.'<span style="color:red"> * </span>',
									  'field' => tep_draw_input_field($this->code . '_no', '', 'id="' . $this->code . '_no" autocomplete=off onkeypress="return isNumberKey(event, true);" ')
									);
		  $selection['fields'][] = array('title' => MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE.'<span style="color:red"> * </span>',
									  'field' => tep_draw_pull_down_menu($this->code.'_exp_month', parent::creditCardValidMonth(), '', 'id="' . $this->code . '_exp_month"').'&nbsp;'.
									  tep_draw_pull_down_menu($this->code . '_exp_year', parent::creditCardValidYear() , '', 'id="' . $this->code . '_exp_year"')
									);

		  $selection['fields'][] = array('title' => MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC .'<span style="color:red"> * </span>',
									  'field' => tep_draw_input_field($this->code.'_cvc', '', 'id="' . $this->code . '_cvc" autocomplete=off onkeypress="return isNumberKey(event, false)" style="width:30px;"').
									  '&nbsp;<span id="showcvc"><a onmouseover="show_cvc_info(true, \'cvc_info\', \'showcvc\');" onmouseout="show_cvc_info(false, \'cvc_info\', \'showcvc\');" style="text-decoration: none;"> <img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/cvc_hint.png" border="0" style="margin-top:0px;" alt="CCV/CVC?"></a></span><input type="hidden" id="nn_lang_cc_new_account" value="' . MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT . '"/><input type="hidden" id="nn_lang_cc_given_account" value="' . MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT . '"/>'. $cc_fields
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
	$post = $_POST;
	if ( !empty($_SESSION['novalnet'][$this->code]['secondcall']) ) {
	  NovalnetValidation::validateUserInputsOnCallback($this->fraud_module, $this->code, $post);
	} else {
	  $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] = isset($post['novalnet_ccchange_account']) ? $post['novalnet_ccchange_account'] : '';
	  if (isset($post['nn_payment_ref_tid'])  && $post['novalnet_ccchange_account'] == 1) {
	    $_SESSION['novalnet'][$this->code]['cc_fldvalidator'] = isset($post['cc_fldvalidator']) ? $post['cc_fldvalidator'] : '';
	    $_SESSION['novalnet'][$this->code]['nn_cc_hash'] = isset($post['nn_cc_hash']) ? $post['nn_cc_hash'] : '';
        if (MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT == 'True' && trim($post['novalnet_cc_cvc_ref']) == '') {
          $payment_error_return = 'error_message=Your credit card CVC/CVV/CID detail invalid';
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
        $_SESSION['novalnet'][$this->code]['nn_payment_ref_enable'] = TRUE;
        $_SESSION['novalnet'][$this->code]['nn_payment_ref_tid'] = $post['nn_payment_ref_tid'];
        return true;
      }
      if (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True') {
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
	global $order;
	$post = $_REQUEST;
	if(MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True' || (MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'True')) {
	  if ( isset($_SESSION['novalnet'][$this->code]['payment_amount']) ) {
		$_SESSION['novalnet'][$this->code]['order_obj'] = array_merge((array)$order, $post, array('payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']));
		$before_process_response = parent::novalnet_before_process($_SESSION['novalnet'][$this->code]['order_obj']); // Perform real time 3d secure payment transaction
		$before_process_response .= NovalnetValidation::confirmButtonDisableActivate();
		return $before_process_response;
	  } else {
		$payment_error_return = 'payment_error=' . $this->code . '&error=' . MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	    tep_redirect(self::setUTFText(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	  }
	} else {
	  if ( !empty($post[$this->code . '_new_pin']) ) {
		$new_pin_response = NovalnetInterface::doCallbackRequest('TRANSMIT_PIN_AGAIN', $this->code);
		NovalnetValidation::validateNewPinResponse($new_pin_response, 'TRANSMIT_PIN_AGAIN', $this->code);
	  } elseif ( isset($_SESSION['novalnet'][$this->code]['payment_amount']) ) {
		$novalnet_order_details = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
		$_SESSION['novalnet'][$this->code] = array_merge($novalnet_order_details, $post, array('payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']));
	  } else {
		$payment_error_return = 'payment_error=' . $this->code . '&error=' . MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE;
	    tep_redirect(self::setUTFText(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
	  }
	}
	return NovalnetValidation::confirmButtonDisableActivate();
  }

  function before_process() {
	global $order;
	$post = $_REQUEST;
	if (MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'True' || MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE == 'Redirect') {
	  if ( isset($post['tid']) ) { // 3D Secure response validation
	    $before_process_response = parent::validateRedirectResponse((array)$order, $this->code, $post);
	    $order->info['comments'] = $before_process_response['comments'];
	  } else {
        $payment_error_return = 'error_message=' . utf8_decode($post['status_desc']);
        tep_redirect(self::setUTFText(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false)));
      }
    } else{
	  $this->fraud_module_status = NovalnetInterface::setFraudModuleStatus($this->code, (array)$order, $this->fraud_module);
	  $this->fraud_module_status = isset($_SESSION['novalnet'][$this->code]['nn_payment_ref_enable']) ? FALSE:$this->fraud_module_status;
	  $param_inputs = array_merge($_SESSION['novalnet'][$this->code], (array)$order, array('fraud_module' => $this->fraud_module,'fraud_module_status' => $this->fraud_module_status));
	  if (!empty($param_inputs['secondcall']) ) {
		NovalnetInterface::doConfirmPayment($this->code, $this->fraud_module);
	  } else {
		$before_process_response = parent::novalnet_before_process($param_inputs); // Perform real time normal cc payment transaction
		NovalnetInterface::gotoPaymentOnCallback($this->code, $this->fraud_module, $this->fraud_module_status);
	  }
	  $order->info['comments'] = $_SESSION['novalnet'][$this->code]['nntrxncomments'];
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
