<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnet_sepa.php
 *
 */
include_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.novalnetutil.php';
class novalnet_sepa
{
    var $code, $title, $description, $enabled, $key = 37, $payment_type = 'DIRECT_DEBIT_SEPA';

    /**
     * Constructor
     *
     */
    function novalnet_sepa()
    {
        global $order;
        $this->code        = 'novalnet_sepa';
        $this->title       = $this->public_title = MODULE_PAYMENT_NOVALNET_SEPA_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_NOVALNET_SEPA_TEXT_DESCRIPTION;

        $this->sort_order = 0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
            $this->sort_order = MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER;
            $this->enabled    = ((MODULE_PAYMENT_NOVALNET_SEPA_STATUS == 'True') ? true : false);
        }
        $this->fraud_module        = ((MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE == 'False') ? false : MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE);
        $this->fraud_module_status = ($this->fraud_module) ? true : false;
        if ($this->enabled === true) {
            if (isset($order) && is_object($order)) {
                $this->update_status();
            }
        }
    }

    /**
     * Core Function : update_status()
     *
     */
    function update_status()
    {
        global $order;
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE > 0)) {
            $check_flag  = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE . "' and (zone_id < 1 or zone_id = " . $order->billing['zone_id'] . " ) and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
            $check       = tep_db_fetch_array($check_query);
            if ($check['zone_id']) {
                $check_flag = true;
            }
            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
        // disable the module if the order only contains virtual products
        if ($this->enabled == true) {
            if ($order->content_type == 'virtual') {
                $this->enabled = false;
            }
        }
    }

    /**
     * Core Function : javascript_validation()
     *
     */
    function javascript_validation()
    {
        return false;
    }

    /**
     * Core Function : selection()
     *
     */
    function selection()
    {
        global $order;
        NovalnetUtil::validatecallbacksession();
        $guarantee_payment = NovalnetUtil::displayBirthdateField($this->code); // Check condition for displaying birthdate field
		
        if (!NovalnetUtil::checkMerchantConfiguration() || !$this->validateAdminConfiguration() || !NovalnetUtil::hidePaymentVisibility(NovalnetUtil::getPaymentAmount((array) $order), MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BY_AMOUNT) || !NovalnetUtil::validateCallbackStatus($this->code, $this->fraud_module) || $guarantee_payment == '') { // Validate the Novalnet merchant details, SEPA admin details and payment visibility
            return false;
        }

        NovalnetUtil::getLastSuccessPayment($this->code); // To get the payment name of last successful order

        if (!empty($_SESSION['payment']) && isset($_SESSION['novalnet'][$this->code]['tid']) && $_SESSION['payment'] != $this->code) {
            unset($_SESSION['novalnet'][$this->code]['tid'], $_SESSION['novalnet'][$this->code]['secondcall']);
        }
        $this->fraud_module_status = NovalnetUtil::setFraudModuleStatus($this->code, $this->fraud_module); // Validate status of fraud modules
        
        $customer_details = NovalnetUtil::getCustomerDetails($order->customer['email_address']); // Get customer details
        $_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate'] = $customer_details['customers_dob'];            
        if(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && $this->guranteePaymentVerifcation($order)) {					$this->fraud_module_status = false;
		}		
        $selection['id']           = $this->code;
        $selection['module']       = $this->public_title . MODULE_PAYMENT_NOVALNET_SEPA_PUBLIC_TITLE;
        $selection['module']      .= $this->description . '<input type="hidden" id="nn_root_sepa_catalog" value="' . DIR_WS_CATALOG . '"/> <script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/novalnet_sepa.js' . '" type="text/javascript"></script><link rel="stylesheet" type="text/css" href="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/css/novalnet.css' . '"><noscript><input type="hidden" name="nn_sepa_js_enabled" value="1"><br /><div style="color:red"><b>' . MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_ERROR . '</b></div></noscript>' . '<br/>' . trim(strip_tags(MODULE_PAYMENT_NOVALNET_SEPA_CUSTOMER_INFO));
        if (MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE == 'True') {
            $selection['module'] .= '<br>' . MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG;
        }
        if (isset($_SESSION['novalnet'][$this->code]['tid']) && $this->fraud_module && $this->fraud_module_status) {			
            $selection['fields'] = NovalnetUtil::buildCallbackFieldsAfterResponse($this->fraud_module, $this->code); // Display pin number field after getting response
        } else {
            $customer_name     = ((!empty($order->customer['firstname']) ? $order->customer['firstname'] : '') . ' ' . (!empty($order->customer['lastname']) ? $order->customer['lastname'] : ''));
            $data              = array();
            $data['vendor']    = MODULE_PAYMENT_NOVALNET_VENDOR_ID;
            $data['auth_code'] = MODULE_PAYMENT_NOVALNET_AUTH_CODE;
            NovalnetUtil::getAffDetails($data); // Affiliate process
            $pin_by_callback = '';
            if (in_array($this->fraud_module, array(
                'CALLBACK',
                'SMS'
            )) && $this->fraud_module_status) { // Loading Fraud module field
				if(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION != 'True' || (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && !$this->guranteePaymentVerifcation($order))) {
					$fraud_module_value = array(
						'CALLBACK' => array(
							'name' => '_fraud_tel',
							'value' => 'telephone'
						),
						'SMS' => array(
							'name' => '_fraud_mobile',
							'value' => 'mobile'
						)
					);
					$pin_by_callback    = '<tr><td class="main">' . constant('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_' . $this->fraud_module . '_INPUT_TITLE') . ' <span style="color:red"> * </span></td>
                                  <td class="main">' . tep_draw_input_field($this->code . $fraud_module_value[$this->fraud_module]['name'], (isset($order->customer[$fraud_module_value[$this->fraud_module]['value']]) ? $order->customer[$fraud_module_value[$this->fraud_module]['value']] : ''), 'id=' . $this->code . '-' . strtolower($this->fraud_module) . ' AUTOCOMPLETE=off') . '</td></tr>';
                }
            }
            
            $birthdate_field  = '';           
            
            if (isset($guarantee_payment) && $guarantee_payment == 'guarantee' && $this->guranteePaymentVerifcation($order)) { // Display guarantee payment date of birth field								
                $birth_field_label = MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_BIRTH_DATE . ' <span style="color:red"> * </span>';
                $birthdate_field   = true;
            }
            $sqlQuerySet     = NovalnetUtil::getPaymentDetails($_SESSION['customer_id'], $this->code);
            $payment_details = unserialize($sqlQuerySet['payment_details']);
            $form_show       = '1';
           
           
            if (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ONECLICK' && !empty($payment_details)) {
                $form_show      = !empty($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account']) ? '1' : '0';
                $one_click_shop = '<span id ="novalnet_sepa_new_acc" style="color:blue;cursor: pointer;"><u><b>' . MODULE_PAYMENT_NOVALNET_SEPA_NEW_ACCOUNT . '</b></u></span>
                                  <div id="nn_sepa_ref_details" style="display:none">
                                    <table>
                                        <tr>
                                            <td class="main">' . MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER . ':</td>
                                            <td class="main">' . NovalnetUtil::setUTFText($payment_details['bankaccount_holder']) . '</td>
                                        </tr>
                                        <tr>
                                            <td class="main">IBAN:</td>
                                            <td class="main">' . $payment_details['iban'] . '</td>
                                        </tr>
                                        <tr>
                                            <td class="main">BIC:</td>
                                            <td class="main">' . $payment_details['bic'] . '</td>
                                        </tr>';
                if (!empty($birthdate_field)) {
                    $one_click_shop .= '<tr>
                                        <td class="main">' . $birth_field_label . '</td>
                                        <td class="main">' . $this->get_guarantee_field('novalnet_sepa_birth_date_one_click', $customer_details) . '</td>
                                    </tr>';

                }

                $one_click_shop .= '<input type="hidden" id="nn_payment_ref_tid_sepa" name="nn_payment_ref_tid_sepa" value="' . $payment_details['tid'] . '"/>
                                </table></div>';
            } else {
                $one_click_shop = '<input type="hidden" id="payment_ref_details" value=""/>';
            }
            
            $guarantee_field = '';
            if (!empty($birthdate_field) && $this->guranteePaymentVerifcation($order)) { 
                $guarantee_field = '<tr><td class="main">' . $birth_field_label . '</td>
                                   <td class="main">' . $this->get_guarantee_field('novalnet_sepa_birth_date_normal', $customer_details) . '</td></tr>';
            }


            $sepa_fields           = '<input type="hidden" id="nn_vendor" value="' . $data['vendor'] . '"/>
                                      <input type="hidden" id="nn_auth_code" value="' . $data['auth_code'] . '"/>
                                      <input type="hidden" id="nn_sepa_hash"  name="nn_sepa_hash" value=""/>
                                      <input type="hidden" id="nn_sepa_iban" value=""/>
                                      <input type="hidden" id="nn_sepa_bic" value=""/>

                                      <input type="hidden" id="nn_sepa_country" value="' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SEPA_SELECT_COUNTRY) . '"/>
                                      <input type="hidden" name="novalnet_sepachange_account" id="novalnet_sepachange_account" value="' . $form_show . '"/></td></tr>
                                      <input type="hidden" id="nn_sepa_uniqueid"  name="nn_sepa_uniqueid" value="' . NovalnetUtil::randomString() . '"/>
                                      <input type="hidden" id="nn_sepa_input_panhash" value="' . $this->getRefillHash($order->customer['email_address']) . '"/>
                                      <input type="hidden" id="nn_lang_mandate_confirm" value="' . MODULE_PAYMENT_NOVALNET_SEPA_MANDATE_CONFIRM_ERROR . '"/>
                                      <input type="hidden" id="nn_lang_valid_merchant_credentials" value="' . MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR . '"/>
                                      <input type="hidden" id="nn_lang_valid_account_details" value="' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR) . '"/>
                                      <input type="hidden" id="nn_sepa_shopping_type" value="' . MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE . '"/>
                                      <input type="hidden" id="nn_lang_choose_payment_method" value="' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SELECT_PAYMENT_METHOD) . '">
                                      <div class="loader" id="nn_loader" style="display:none"></div>';
            $selection['fields'][] = array(
                'title' => '',
                'field' => $one_click_shop . '<div id="nn_sepa_acc" style="display:block">
                             <table><tr><td class="main">' . MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER . '<span style="color:red"> * </span></td>
                                   <td class="main">' . tep_draw_input_field($this->code . '_account_holder', $customer_name, 'id="' . $this->code . '_account_holder" autocomplete="off" onkeypress="return ibanbic_validate(event, true);"') . '</td></tr>
                                <tr><td class="main">' . MODULE_PAYMENT_NOVALNET_BANK_COUNTRY . '<span style="color:red"> * </span></td>
                                   <td class="main">' . tep_draw_pull_down_menu($this->code . '_bank_country', $this->sepaBankCountry(), $order->billing['country']['iso_code_2'], 'id="' . $this->code . '_bank_country"') . '</td></tr>
                                <tr><td class="main">' . MODULE_PAYMENT_NOVALNET_ACCOUNT_OR_IBAN . '<span style="color:red"> * </span></td>
                                   <td class="main">' . tep_draw_input_field($this->code . '_iban', '', 'id="' . $this->code . '_iban" AUTOCOMPLETE="off" onkeypress="return ibanbic_validate(event, false);"') . '<span id="novalnet_sepa_iban_span"></span></td></tr>
                                <tr><td class="main">' . MODULE_PAYMENT_NOVALNET_BANKCODE_OR_BIC . '<span style="color:red"> * </span></td>
                                   <td class="main">' . tep_draw_input_field($this->code . '_bic', '', 'id="' . $this->code . '_bic" AUTOCOMPLETE="off" onkeypress="return ibanbic_validate(event, false);"') . '<span id="novalnet_sepa_bic_span"></span></td></tr>
                                   ' . $guarantee_field . '
                                
                                <input type="hidden" id="nn_lang_new_account" value="' . MODULE_PAYMENT_NOVALNET_SEPA_NEW_ACCOUNT . '"/><input type="hidden" id="nn_lang_given_account" value="' . MODULE_PAYMENT_NOVALNET_SEPA_GIVEN_ACCOUNT . '"/>' . $sepa_fields . $pin_by_callback . '<tr>
                                   <td class="main" colspan="2"><span>' . tep_draw_checkbox_field($this->code . '_mandate_confirm', 1, false, 'id="' . $this->code . '_mandate_confirm"') . '</span><span>'.MODULE_PAYMENT_NOVALNET_SEPA_FORM_MANDATE_CONFIRM_TEXT . '</span></td></tr>   </table></div>'
            );
           
        }
        return $selection;
    }

    /**
     * Core Function : pre_confirmation_check()
     *
     */
    function pre_confirmation_check()
    {
		global $order;	
        $post                                              = $_REQUEST;               
        $_SESSION['novalnet'][$this->code]['nn_sepa_hash'] = $post['nn_sepa_hash'];
        if (!empty($post['nn_sepa_js_enabled'])) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_ERROR, 'SSL'));
        }
        $gurantee_date_normal    = '';
        $gurantee_date_one_click = '';
        if (isset($post['novalnet_sepa_birth_date_normal'])) {
            $gurantee_date_normal = $post['novalnet_sepa_birth_date_normal'];
        }
        if (isset($post['novalnet_sepa_birth_date_one_click'])) {
            $gurantee_date_one_click = $post['novalnet_sepa_birth_date_one_click'];
        }
        $_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] = !empty($post['novalnet_sepachange_account']) ? $post['novalnet_sepachange_account'] : '0';        
     

        $_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate']     = ($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] == '1') ? $gurantee_date_normal : $gurantee_date_one_click;



        if (isset($_SESSION['novalnet'][$this->code]['secondcall'])) { // Validate fraud module pin number field
            NovalnetUtil::validateUserInputsOnCallback($this->code, $post, $this->fraud_module);
        } elseif (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT != 'True' && !NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate'])) { // Validate age
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_AGE_ERROR), 'SSL'));
        }  else { // Validate SEPA form mandate confirm

            if (empty($post['novalnet_sepa_mandate_confirm']) && (isset($post['novalnet_sepachange_account']) &&  $post['novalnet_sepachange_account'] == '1' )) { 
                $payment_error_return = 'error_message=' . MODULE_PAYMENT_NOVALNET_SEPA_MANDATE_CONFIRM_ERROR;
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL'));
            }
			if(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT != 'True' && !$this->guranteePaymentVerifcation($order)) {
				tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SEPA_GURANTEE_PAYMENT_NOT_MATCH_ERROR_MSG), 'SSL'));
			}

            if (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE != 'ONECLICK' || (isset($post['novalnet_sepachange_account']) && $post['novalnet_sepachange_account'] == 1)) {
                if (((!isset($post['novalnet_sepa_account_holder']) || trim($post['novalnet_sepa_account_holder']) == '') || (!isset($post['nn_sepa_hash']) || $post['nn_sepa_hash'] == '') || (!isset($post['nn_sepa_uniqueid']) || $post['nn_sepa_uniqueid'] == ''))) {
                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR), 'SSL'));
                }
            }
            if (isset($post['nn_payment_ref_tid_sepa']) && $post['novalnet_sepachange_account'] == 0) {
                $_SESSION['novalnet'][$this->code]['nn_payment_ref_enable']   = true;
                $_SESSION['novalnet'][$this->code]['nn_payment_ref_tid_sepa'] = $post['nn_payment_ref_tid_sepa'];
                return true;
            }
            // Validate fraud module field
            $this->fraud_module_status = NovalnetUtil::setFraudModuleStatus($this->code, $this->fraud_module);
            if(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && $this->guranteePaymentVerifcation($order) || (MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT == 'True' && !NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate']))) {		
				$this->fraud_module_status = false;				
			}			
            NovalnetUtil::validateCallbackFields($post, $this->fraud_module, $this->fraud_module_status, $this->code);
        }
    }

    /**
     * Core Function : confirmation()
     *
     */
    function confirmation()
    {
        global $order;
        // Check amount validation for Fraud module after genarating the pin number
        if (isset($_SESSION['novalnet'][$this->code]['secondcall'])) {
            if ($_SESSION['novalnet'][$this->code]['order_amount'] != NovalnetUtil::getPaymentAmount((array) $order)) {
                if (isset($_SESSION['novalnet'])) {
                    unset($_SESSION['novalnet']);
                }
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_FRAUDMODULE_AMOUNT_CHANGE_ERROR), 'SSL'));
            }
        }
        $_SESSION['novalnet'][$this->code]['order_amount'] = NovalnetUtil::getPaymentAmount((array) $order); // Payment amount
        return false;
    }

    /**
     * Core Function : process_button()
     *
     */
    function process_button()
    {
        $post = $_REQUEST;
        if (isset($post[$this->code . '_new_pin']) && $post[$this->code . '_new_pin'] == 1) { // Sending new pin number to Novalnet server
            $new_pin_response = NovalnetUtil::doXMLCallbackRequest('TRANSMIT_PIN_AGAIN', $this->code);
            $response         = NovalnetUtil::getStatusFromXmlResponse($new_pin_response); // Converting Xml response from Novalnet server
            if ($response['status'] != 100) { // If the transation is failure
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $response['status_message'], 'SSL'));
            }
        } elseif (isset($_SESSION['novalnet'][$this->code]['order_amount'])) {
            $novalnet_order_details            = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
            $_SESSION['novalnet'][$this->code] = array_merge($novalnet_order_details, array(
                'payment_amount' => $_SESSION['novalnet'][$this->code]['order_amount']
            ), $post);
        } else { // Display error message
            $payment_error_return = 'error_message=' . MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE;
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL'));
        }

        return NovalnetUtil::confirmButtonDisableActivate(); // Hiding Buy button in confirmation page
    }

    /**
     * Core Function : before_process()
     *
     */
    function before_process()
    {
        global $order;
        $this->fraud_module_status = NovalnetUtil::setFraudModuleStatus($this->code, $this->fraud_module);
      
		if(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && $this->guranteePaymentVerifcation($order) || (MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT == 'True' && !NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate']))) {
			$this->fraud_module_status = false;				
		}
        $param_inputs              = array_merge((array) $order, $_SESSION['novalnet'][$this->code], array(
            'payment' => $this->code,
            'fraud_module' => $this->fraud_module,
            'fraud_module_status' => $this->fraud_module_status
        ));
        $this->fraud_module_status = isset($_SESSION['novalnet'][$this->code]['nn_payment_ref_enable']) ? FALSE : $this->fraud_module_status;

        if (isset($param_inputs['secondcall']) && $param_inputs['secondcall']) { // Sending pin number to Novalnet server
            $callback_response = ($this->fraud_module && in_array($this->fraud_module, array('SMS', 'CALLBACK'))) ? NovalnetUtil::doXMLCallbackRequest('PIN_STATUS', $this->code) : '';
            $response          = NovalnetUtil::getStatusFromXmlResponse($callback_response); // Converting Xml response from Novalnet server
            $_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] = 1;
            if ($response['status'] != 100) { // Novalnet transaction status got failure for displaying error message
                if ($response['status'] == '0529006') {
                    $_SESSION[$this->code . '_nn_payment_lock'] = true;
                }
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . (!empty($response['status_message']) ? NovalnetUtil::setUTFText($response['status_message']) : $response['pin_status']['status_message']), 'SSL'));
            } else { // Novalnet transaction status got success
                $novalnet_order_details                              = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
                $serialize_data                                      = array(
                    'bankaccount_holder' => $_SESSION['novalnet'][$this->code]['bankaccount_holder'],
                    'iban'               => $_SESSION['novalnet'][$this->code]['iban'],
                    'bic'                => $_SESSION['novalnet'][$this->code]['bic'],
                    'tid'                => $_SESSION['novalnet'][$this->code]['tid']
                );
                $_SESSION['novalnet'][$this->code]['gateway_status'] = $response['tid_status'];
                $this->updateTransComments($serialize_data, $_SESSION['novalnet'][$this->code], '');
            }
        } else {

            $urlparam                 = NovalnetUtil::getRequestParams($param_inputs); // Get common request parameters
            $urlparam['key']          = $this->key;
            $urlparam['payment_type'] = $this->payment_type;

            if (MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT != '' && $_SESSION['novalnet'][$this->code]['payment_amount'] >= MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT &&  MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE != 'ZEROAMOUNT') {
                // Assigning on hold parameter
                $urlparam['on_hold'] = '1';
            }

            if (!empty($_SESSION['novalnet'][$this->code]['nn_payment_ref_tid_sepa']) && $_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] == '0') {
                $urlparam['payment_ref'] = $_SESSION['novalnet'][$this->code]['nn_payment_ref_tid_sepa'];
                unset($_SESSION['novalnet'][$this->code]['nn_payment_ref_tid_sepa']);
            } else {
                $urlparam['iban_bic_confirmed']  = 1;
                $urlparam['bank_account_holder'] = $_SESSION['novalnet'][$this->code]['novalnet_sepa_account_holder'];
                $urlparam['sepa_hash']           = $_SESSION['novalnet'][$this->code]['nn_sepa_hash'];
                $_SESSION['novalnet'][$this->code]['nn_sepa_hash_details']  = $_SESSION['novalnet'][$this->code]['nn_sepa_hash'];
                $urlparam['sepa_unique_id']      = $_SESSION['novalnet'][$this->code]['nn_sepa_uniqueid'];
            }

            $urlparam['sepa_due_date'] = NovalnetUtil::sepaDuedate();           
            if (isset($this->fraud_module) && $this->fraud_module_status && ((MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ONECLICK' && !empty($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'])) || (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE != 'ONECLICK' && !empty($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'])))) { // Appending parameters for Fraud module
					if($this->fraud_module_status) {	
						if ($this->fraud_module == 'CALLBACK') {
							$urlparam['tel']             = trim($_SESSION['novalnet'][$this->code]['novalnet_sepa_fraud_tel']);
							$urlparam['pin_by_callback'] = '1';
						} else {
							$urlparam['mobile']     = trim($_SESSION['novalnet'][$this->code]['novalnet_sepa_fraud_mobile']);
							$urlparam['pin_by_sms'] = '1';
						}
				}
            }           

            $_SESSION['novalnet'][$this->code]['order_amount'] = $urlparam['amount'];
            if (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ZEROAMOUNT' && MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION != 'True') {
                $urlparam = NovalnetUtil::novalnetZeroAmountProcess('novalnet_sepa', $urlparam);
            }

            if (in_array(MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE, array('ZEROAMOUNT', 'ONECLICK'))) {                
                $urlparam['create_payment_ref'] = '1';
                if(isset($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account']) && $_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] == '0') {
					$_SESSION['novalnet'][$this->code]['nn_sepa_hash_details']  = $_SESSION['novalnet'][$this->code]['nn_sepa_hash']; 
					unset($urlparam['create_payment_ref']);
				}
            }
            
            if(MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ZEROAMOUNT' && ($urlparam['tariff_period'] != '' && $urlparam['tariff_period2'] != '' && $urlparam['tariff_period2_amount'] != '')) {				
				unset($urlparam['create_payment_ref']);				
			}
            
            if (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True') {
                if ($this->guranteePaymentVerifcation($order)) {
                    // Appending parameters for guarantee payment
                    $urlparam['key']          = '40';
                    $urlparam['payment_type'] = 'GUARANTEED_DIRECT_DEBIT_SEPA';
                    $urlparam['birth_date']   = $_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate'];
					if(isset($urlparam['create_payment_ref'])) {
						unset($urlparam['create_payment_ref']);
					}
                } else {
                    if (MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT == 'False') {
                        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . MODULE_PAYMENT_NOVALNET_AGE_ERROR, 'SSL'));
                    }
                }
            }
            
            if(isset($_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate'])) {
				$urlparam['birth_date']   = $_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate'];
			}									
			
            $response = NovalnetUtil::doPaymentCall('https://payport.novalnet.de/paygate.jsp', $urlparam); //Send
            parse_str($response, $payment_response);           
            if ($payment_response['status'] == '100') { // Novalnet transaction status got success                
                $novalnet_order_details = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
                $serialize_data         = array(
                    'bankaccount_holder' => $payment_response['bankaccount_holder'],
                    'iban' => $payment_response['iban'],
                    'bic'  => $payment_response['bic'],
                    'tid'  => $payment_response['tid']
                );
                $this->updateTransComments($serialize_data, $payment_response, $urlparam); // Update Novalnet transaction comments
            } else { // Novalnet transaction status got failure for displaying error message
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($payment_response), 'SSL'));
            }			
            if ((MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ONECLICK' && !empty($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'])) || (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE != 'ONECLICK')) {
				if(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION != 'True' || (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && !$this->guranteePaymentVerifcation($order))) {	
					NovalnetUtil::gotoPaymentOnCallback($this->code, $this->fraud_module, $this->fraud_module_status); // Redirect to checkout page for displaying fraud module message
                }
            }
        }
    }

    /**
     * Core Function : after_process()
     *
     */
    function after_process()
    {
        global $insert_id;
        if (MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION == 'True' && MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE != 'True') {
			NovalnetUtil::sendTestTransactionNotification($_SESSION['novalnet'][$this->code], $insert_id);
		}
        $order_status['orders_status'] = $order_status_id['orders_status_id'] = (MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS > 0) ? MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS : DEFAULT_ORDERS_STATUS_ID;
        tep_db_perform(TABLE_ORDERS, $order_status, "update", "orders_id='$insert_id'"); // Update order status in order status table
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_status_id, "update", "orders_id='$insert_id'"); // Update order status id in order status history table

        // Sending post back call to Novalnet server
        NovalnetUtil::doSecondCallProcess(array('payment' => $this->code, 'order_no' => $insert_id));
    }

    /**
     * Core Function : check()
     *
     */
    function check()
    {
        if (!isset($this->_check)) {
            $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_SEPA_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /**
     * Core Function : install()
     *
     */
    function install()
    {
        global $language;

        include_once DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/payment/novalnet_sepa.php';
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_group_id, configuration_description, sort_order, set_function, use_function, date_added)
        VALUES
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_STATUS','False', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_STATUS_DESC . "', '1', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_SEPA_STATUS\'," . MODULE_PAYMENT_NOVALNET_SEPA_STATUS . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE','False', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE_DESC . "', '2', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE\'," . MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE','False', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE_DESC . "','3','tep_mod_select_option(array(\'False\' => MODULE_PAYMENT_NOVALNET_OPTION_NONE,\'CALLBACK\' => MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK,\'SMS\' => MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS,),\'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE\'," . MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE . ",' , '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT', '', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT_DESC . "', '4','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE', '', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE_DESC . "', '5','', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_AUTO_FILL_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_AUTO_FILL','False', '6', '" . MODULE_PAYMENT_NOVALNET_SEPA_AUTO_FILL_DESC . "','6', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_SEPA_AUTO_FILL\'," . MODULE_PAYMENT_NOVALNET_SEPA_AUTO_FILL . ",' ,'',now()),
        ('" . MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER_TITLE . "','MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER','False', '6','" . MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER_DESC . "', '7', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER\'," . MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER . ",' ,'',now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE','False', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE_DESC . "','8', 'tep_mod_select_option(array(\'False\' => MODULE_PAYMENT_NOVALNET_OPTION_NONE,\'ONECLICK\' => MODULE_PAYMENT_NOVALNET_SEPA_ONE_CLICK,\'ZEROAMOUNT\' => MODULE_PAYMENT_NOVALNET_SEPA_ZERO_AMOUNT,),\'MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE\'," . MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE . ",' ,'',now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BY_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BY_AMOUNT', '', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BY_AMOUNT_DESC . "', '9','', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_CUSTOMER_INFO_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_CUSTOMER_INFO', '', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_CUSTOMER_INFO_DESC . "', '10','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER', '0', '6', '" . MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER_DESC . "', '11', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS_DESC . "', '12', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE', '0', '6', '" . MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE_DESC . "','13', 'tep_cfg_pull_down_zone_classes(', 'tep_get_zone_class_title',now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE1_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE1', '', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE1_DESC . "', '16', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE2_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE2', '', '6', '" . MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE2_DESC . "','17', '', '', now()),

        ('" . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION','False', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION_DESC . "', '12', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION\'," . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT', '', '6', '" . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT_DESC . "','18', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT', '', '6', '" . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT_DESC . "','18', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT_TITLE . "','MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT','True', '6','" . MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT_DESC . "','16', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT\'," . MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT . ",' , '', now())
        ");
    }

    /**
     * Core Function : remove()
     *
     */
    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    /**
     * Core Function : keys()
     *
     */
    function keys()
    {
        $this->validateAdminConfiguration(true); // Validate admin configuration
        return array('MODULE_PAYMENT_NOVALNET_SEPA_STATUS', 'MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE','MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE', 'MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT','MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE', 'MODULE_PAYMENT_NOVALNET_SEPA_AUTO_FILL','MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER', 'MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE','MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BY_AMOUNT', 'MODULE_PAYMENT_NOVALNET_SEPA_CUSTOMER_INFO','MODULE_PAYMENT_NOVALNET_SEPA_SORT_ORDER', 'MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS', 'MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_ZONE','MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE1', 'MODULE_PAYMENT_NOVALNET_SEPA_TRANS_REFERENCE2','MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION', 'MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT','MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT', 'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FORCE_GUARANTEE_PAYMENT');
    }

    /**
     * Validate admin configuration
     * @param $admin
     *
     * @return boolean
     */
    function validateAdminConfiguration($admin = false)
    {
        if (MODULE_PAYMENT_NOVALNET_SEPA_STATUS == 'True') {
            if (MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE != '' && (!is_numeric(trim(MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE)) || MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE < 7)) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_ERROR);
                return false;
            } elseif (MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BY_AMOUNT != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_SEPA_VISIBILITY_BY_AMOUNT)) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE);
                return false;
            } elseif(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && ((MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '' && (!ctype_digit(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT) || MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT < 2000)) || (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT != '' && (!ctype_digit(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT) || MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT > 500000)) || (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '' && MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT != '' && (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT <= MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT)))){ 
				if(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '' && (!ctype_digit(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT) || MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT < 2000)) {
					if ($admin)
						echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_SEPA_GURANTEE_PAYMENT_MIN_AMOUNT_ERROR_MSG);
					return false;
				}
				elseif(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT != '' && (!ctype_digit(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT) || MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT > 500000)) {
					if ($admin)
						echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_SEPA_GURANTEE_PAYMENT_MAX_AMOUNT_ERROR_MSG);
					return false;
				}
				elseif(MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '' && MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT != '' && (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT <= MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT)) {
					if ($admin)
						echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_SEPA_GURANTEE_PAYMENT_MAX_AMOUNT_ERROR_MSG);
					return false;
				}
			} elseif(MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE != 'False' && (MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT))){
				if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE);
                return false;
			}
        }
        return true;
    }


    /**
     * Return refill hash
     * @param $email_address
     *
     * @return string
     */
    function getRefillHash($email_address = '')
    {

        if (isset($_SESSION['payment']) && isset($_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'])) { 			
            return ((MODULE_PAYMENT_NOVALNET_SEPA_AUTO_FILL == 'True' || MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER == 'True') ? $_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'] : '');
        } elseif (MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER == 'True' && $email_address != '') {  
            $customer_details  = NovalnetUtil::getCustomerDetails($email_address);
            $processkey_query  = tep_db_query("SELECT process_key, payment_type FROM novalnet_transaction_detail WHERE customer_id='" . tep_db_input($customer_details['customers_id']) . "' ORDER BY id DESC LIMIT 1");
            $getResult         = tep_db_fetch_array($processkey_query);
            $_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'] = ((!empty($getResult['process_key']) && $getResult['payment_type'] == 'novalnet_sepa') ? $getResult['process_key'] : '');
            return isset($_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash']) ? $_SESSION['novalnet']['novalnet_sepa']['nn_sepa_hash'] : '';
        }
    }

    /**
     * Return countries list
     * @param none
     *
     * @return array
     */
    function sepaBankCountry()
    {
        $countries = tep_db_query("select countries_iso_code_2, countries_name from " . TABLE_COUNTRIES . " order by countries_name");
        $countries = tep_get_countries();

        foreach ($countries as $country) {
            $country_list[] = tep_get_countries($country['countries_id'], true);
        }

        $countries_array[] = array('id' => '', 'text' => PLEASE_SELECT_SEPA_COUNTRY);

        foreach ($country_list as $country) {
            $countries_array[] = array('id' => $country['countries_iso_code_2'], 'text' => $country['countries_name']);
        }
        return $countries_array;
    }

    /**
     * Update transaction comments
     * @param $serialize_data
     * @param $payment_response
     * @param $input_params
     *
     * @return none
     */
    function updateTransComments($serialize_data, $payment_response, $input_params)
    {	
        global $order;
        if (isset($_SESSION['novalnet'][$this->code]['zero_transaction']) && $_SESSION['novalnet'][$this->code]['zero_transaction'] == '1') {
            $_SESSION['novalnet'][$this->code] = array(
                'zerotrxnreference' => $payment_response['tid'],
                'zerotrxndetails'   => isset($_SESSION['novalnet'][$this->code]['zerotrxndetails']) ? $_SESSION['novalnet'][$this->code]['zerotrxndetails'] : '',
                'zero_transaction'  => isset($_SESSION['novalnet'][$this->code]['zero_transaction']) ? $_SESSION['novalnet'][$this->code]['zero_transaction'] : '0',
                'total_amount'      => $_SESSION['novalnet'][$this->code]['order_amount'],
                'payment_id'        => $_SESSION['novalnet'][$this->code]['payment_id'],
                'gateway_status'    => $_SESSION['novalnet'][$this->code]['gateway_status']
            );
        }
        $_SESSION['novalnet'][$this->code]              = array_merge($_SESSION['novalnet'][$this->code], array(
            'tid'                   => $payment_response['tid'],
            'vendor'                => !empty($input_params['vendor']) ? $input_params['vendor'] : $payment_response['vendor'],
            'product'               => !empty($input_params['product']) ? $input_params['product'] : $payment_response['product'],
            'tariff'                => !empty($input_params['tariff']) ? $input_params['tariff'] : $payment_response['tariff'],
            'auth_code'             => !empty($input_params['auth_code']) ? $input_params['auth_code'] : $payment_response['auth_code'],
            'payment_id'            => !empty($input_params['key']) ? $input_params['key'] : $_SESSION['novalnet'][$this->code]['payment_id'],
            'test_mode'             => $payment_response['test_mode'],
            'reference_transaction' => isset($input_params['payment_ref']) ? '1' : '0',
            'amount'                => ($_SESSION['novalnet'][$this->code]['zero_transaction'] == '1') ? '0' : $_SESSION['novalnet'][$this->code]['order_amount'],
            'total_amount'          => !empty($_SESSION['novalnet'][$this->code]['total_amount']) ? $_SESSION['novalnet'][$this->code]['total_amount'] : $_SESSION['novalnet'][$this->code]['order_amount'],
            'currency'              => $payment_response['currency'],
            'gateway_status'        => ($payment_response['tid_status']) ? $payment_response['tid_status'] : $_SESSION['novalnet'][$this->code]['gateway_status'],                      
            'process_key'           => (isset($input_params['sepa_hash']) && $input_params['sepa_hash'] != '') ? $input_params['sepa_hash'] : $_SESSION['novalnet'][$this->code]['nn_sepa_hash_details'],
            'subs_id'               => !empty($input_params['subs_id']) ? $input_params['subs_id'] : $payment_response['subs_id'],
            'customer_id'           => (isset($payment_response['customer_no']) && $payment_response['customer_no'] != '') ? $payment_response['customer_no'] : $payment_response['customer_id'],
            'bankaccount_holder'    => $payment_response['bankaccount_holder'],
            'iban'                  => $payment_response['iban'],
            'bic'                   => $payment_response['bic'],
            'order_amount'          => !empty($_SESSION['novalnet'][$this->code]['total_amount']) ? $_SESSION['novalnet'][$this->code]['total_amount'] : $_SESSION['novalnet'][$this->code]['order_amount']

        ));
        if(isset($_SESSION['novalnet'][$this->code]['novalnet_sepachange_account']) && $_SESSION['novalnet'][$this->code]['novalnet_sepachange_account'] == '0') {
			$_SESSION['novalnet'][$this->code]['process_key'] = '';
		}
        if (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE ==  'ONECLICK' && !isset($input_params['payment_ref'])) {                
			$_SESSION['novalnet'][$this->code]['payment_details'] = serialize($serialize_data);
		}
        $_SESSION['novalnet_sepa_callback_max_time_nn'] = time() + (30 * 60);
        $test_mode                                      = $payment_response['test_mode'];
        $trans_comments                                 = MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $payment_response['tid'] . ((($test_mode == 1) || MODULE_PAYMENT_NOVALNET_SEPA_TEST_MODE == 'True') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
        $order->info['comments']                        = $order->info['comments'] .PHP_EOL .$trans_comments;
    }

    /**
     * Doing verification for gurantee payment.
     * @param $order
     *
     * @return boolean
     */
    function guranteePaymentVerifcation($order)
    {
        $minimum_amount = (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '') ? MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MINIMUM_AMOUNT : 2000;
        $maximum_amount = (MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT != '') ? MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_MAXIMUM_AMOUNT : 500000;			
		
        return (in_array($order->customer['country']['iso_code_2'], array('AT', 'DE', 'CH')) && $order->info['currency'] == 'EUR' && NovalnetUtil::addressVerification($order) && isset($_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate']) && NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_sepa_birthdate']) && (NovalnetUtil::getPaymentAmount((array) $order) >= $minimum_amount && NovalnetUtil::getPaymentAmount((array) $order) <= $maximum_amount));
    }

    function get_guarantee_field($name, $customer_details)
    {
        return tep_draw_input_field($name, isset($customer_details['customers_dob']) ? $customer_details['customers_dob'] : '', 'id="' . $name . '" readonly') . '&nbsp;<span id="' . $name . '"></span>

            <script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/jquery-1.9.1.js' . '" type="text/javascript"></script>
            <script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/jquery-ui.js' . '" type="text/javascript"></script>
            <link rel="stylesheet" type="text/css" href="'.DIR_WS_CATALOG.'ext/modules/payment/novalnet/css/jquery-ui.css'.'">           
            <script>
            jQuery(document).ready(function(){

                var field_value = "' . $name . '";

                jQuery("#novalnet_sepachange_account")
                .change(function(){
                    if(jQuery("#novalnet_sepachange_account").val() == "0") {
                        jQuery("#' . $name . '").attr("name", "novalnet_sepa_birth_date_one_click");
                        jQuery("#' . $name . '").attr("id", "novalnet_sepa_birth_date_one_click");
                        var field_value = "novalnet_sepa_birth_date_one_click";
                    }else{
                        jQuery("#' . $name . '").attr("name", "novalnet_sepa_birth_date_normal");
                        jQuery("#' . $name . '").attr("id", "novalnet_sepa_birth_date_normal");
                        var field_value = "novalnet_sepa_birth_date_normal";
                    }

                    jQuery( "#"+field_value ).datepicker({ dateFormat: "yy-mm-dd",
                      changeMonth: true,//this option for allowing user to select month
                      changeYear: true, //this option for allowing user to select from year range
                      yearRange: "-100:+0", // last hundred years
                    });
                });

                jQuery( "#"+field_value ).datepicker({ dateFormat: "yy-mm-dd",
                  changeMonth: true,//this option for allowing user to select month
                  changeYear: true, //this option for allowing user to select from year range
                  yearRange: "-100:+0", // last hundred years
                });
            });
            </script>';
    }
}
?>
