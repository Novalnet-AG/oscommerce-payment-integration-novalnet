<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to Novalnet End User License Agreement
 *
 * DISCLAIMER
 *
 * If you wish to customize Novalnet payment extension for your needs, please contact technic@novalnet.de for more information.
 *
 * @author      Novalnet AG
 * @copyright   Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script : novalnet_invoice.php
 *
 */
include_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.novalnetutil.php';
class novalnet_invoice
{
    var $code, $title, $description, $enabled, $key = 27, $payment_type = 'INVOICE';

    /**
     * Constructor
     *
     */
    function novalnet_invoice()
    {
        global $order;
        $this->code                = 'novalnet_invoice';
        $this->title               = $this->public_title = MODULE_PAYMENT_NOVALNET_INVOICE_TEXT_TITLE;
        $this->description         = MODULE_PAYMENT_NOVALNET_INVOICE_TEXT_DESCRIPTION;
        $this->order_status        = defined('MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS_ID') && ((int) MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS_ID > 0) ? (int) MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS_ID : 0;
        $this->fraud_module        = ((MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE == 'False') ? false : MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE);
        $this->fraud_module_status = ($this->fraud_module) ? true : false;
        
        $this->sort_order = defined('MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER') ? MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER : 0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
            $this->enabled    = ((MODULE_PAYMENT_NOVALNET_INVOICE_STATUS == 'True') ? true : false);
        }
        if ($this->enabled === true) {
            if (isset($order) && is_object($order)) {
                $this->update_status();
            }
        }
        echo'<script type= text/javascript src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/authorization.js"></script>';
    }

    /**
     * Core Function : update_status()
     *
     */
    function update_status()
    {
        global $order;
		
		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE > 0) ) {
			$check_flag = false;
			$check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
			while ($check = tep_db_fetch_array($check_query)) {
			  if ($check['zone_id'] < 1) {
				$check_flag = true;
				break;
			  } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
				$check_flag = true;
				break;
			  }
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
        $guarantee_payment = NovalnetUtil::displayBirthdateField($order, $this->code); // Check condition for displaying birthdate field
        
        if (!NovalnetUtil::checkMerchantConfiguration()  || $this->gurantee_error()  || !$this->validateAdminConfiguration() || !NovalnetUtil::hidePaymentVisibility(NovalnetUtil::getPaymentAmount((array) $order), MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT) || !NovalnetUtil::validateCallbackStatus($this->code, $this->fraud_module)) { // Validate the Novalnet merchant details, prepayment, invoice payment admin details and payment visibility
            return false;
        }

        $this->fraud_module_status = NovalnetUtil::setFraudModuleStatus($this->code, $this->fraud_module); // Validate status of fraud modules
        
        $customer_details = NovalnetUtil::getCustomerDetails($order->customer['email_address']); // Get customer details
        $_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate'] = $customer_details['customers_dob'];
        if(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && $this->guranteePaymentInvoiceVerifcation($order)) {
			$this->fraud_module_status = false;
		}

        if (!empty($_SESSION['payment']) && isset($_SESSION['novalnet'][$this->code]['tid']) && $_SESSION['payment'] != $this->code) {
            unset($_SESSION['novalnet'][$this->code]['tid']);
        }
        $notification = trim(strip_tags(MODULE_PAYMENT_NOVALNET_INVOICE_CUSTOMER_INFO));
		if ($guarantee_payment == 'error') {
            $notification .= '<br>'.MODULE_PAYMENT_NOVALNET_FORCE_GUARANTEE_ERROR_MESSAGE;
        }
        
        $selection['id']     = $this->code;
        $selection['module'] = $this->public_title . MODULE_PAYMENT_NOVALNET_INVOICE_PUBLIC_TITLE;
        $selection['module'] .= '<br />' . MODULE_PAYMENT_NOVALNET_INVOICE_TEXT_DESC . '<br />' . $notification;
        if (MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE == 'True') {
            $selection['module'] .= '<br>' . MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG;
        }
        if (isset($_SESSION['novalnet'][$this->code]['tid']) && $this->fraud_module) {
            $selection['fields'] = NovalnetUtil::buildCallbackFieldsAfterResponse($this->fraud_module, $this->code); // Display pin number field after getting response
        } elseif (!isset($_SESSION['novalnet'][$this->code]['tid']) && in_array($this->fraud_module, array(
            'CALLBACK',
            'SMS'
        )) && $this->fraud_module_status) { // Display fraud module field
            $fraud_module_value    = array(
                'CALLBACK' => array('name' => '_fraud_tel', 'value' => 'telephone'),
                'SMS'      => array('name' => '_fraud_mobile', 'value' => 'mobile')
            );
            $selection['fields'][] = array(
                'title' => constant('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_' . $this->fraud_module . '_INPUT_TITLE') . "<span style='color:red'> * </span>",
                'field' => tep_draw_input_field($this->code . $fraud_module_value[$this->fraud_module]['name'], (isset($order->customer[$fraud_module_value[$this->fraud_module]['value']]) ? $order->customer[$fraud_module_value[$this->fraud_module]['value']] : ''), 'id="' . $this->code . '-' . strtolower($this->fraud_module) . '"')
            );
        }

       
        if (!isset($_SESSION['novalnet'][$this->code]['tid']) && isset($guarantee_payment) && $guarantee_payment == 'guarantee' && $this->guranteePaymentInvoiceVerifcation($order) && $order->billing['company'] == '' ) { // Display guarantee payment date of birth field
            $customer_bday = !empty($customer_details['customers_dob']) ? date('d-m-Y', strtotime($customer_details['customers_dob'])) : '';
			$selection['fields'][] = array(
                'title' => MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_BIRTH_DATE . "<span style='color:red'> * </span>",
                'field' => tep_draw_input_field($this->code . 'birthdate', $customer_bday, 'id="novalnet_invoice_birth_date" readonly') . '&nbsp;<span class="inputRequirement" id="novalnet_invoice"></span>
            <script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/jquery-1.9.1.js' . '" type="text/javascript"></script>
            <script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/jquery-ui.js' . '" type="text/javascript"></script>  
            <link rel="stylesheet" type="text/css" href="'.DIR_WS_CATALOG.'ext/modules/payment/novalnet/css/jquery-ui.css'.'">          
            <script>
            $(document).ready(
              function () {
                $( "#novalnet_invoice_birth_date" ).datepicker({ dateFormat: "dd-mm-yy",
                  changeMonth: true,//this option for allowing user to select month
                  changeYear: true, //this option for allowing user to select from year range
                   yearRange: "-100:+0", // last hundred years
                });
              }
            );
            </script>'
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
        
        $post = $_REQUEST;
         
        $guarantee_payment = NovalnetUtil::displayBirthdateField($order, $this->code); // Check condition for displaying birthdate field

        if ($guarantee_payment == 'error') {
           tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_FORCE_GUARANTEE_ERROR), 'SSL'));
        }
         $_SESSION['novalnet'][$this->code]['invoicebirthdate'] = $_REQUEST['novalnet_invoicebirthdate'];       
        if(isset($post['novalnet_invoicebirthdate']) && $post['novalnet_invoicebirthdate'] != '') {
			$_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate'] = $post['novalnet_invoicebirthdate'];
		}	
        if (isset($_SESSION['novalnet'][$this->code]['secondcall'])) { // Validate fraud module pin number field
            NovalnetUtil::validateUserInputsOnCallback($this->code, $post, $this->fraud_module);
        } elseif (MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT != 'True' && (!NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate']) || !NovalnetUtil::check_data($_REQUEST['novalnet_invoicebirthdate'])) && $order->billing['company'] == '' ){ // Validate age
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_AGE_ERROR), 'SSL'));
        } else {
            // Validate fraud module field
            $this->fraud_module_status = NovalnetUtil::setFraudModuleStatus($this->code, $this->fraud_module);
            
            if((MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && $this->guranteePaymentInvoiceVerifcation($order)) || (MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT == 'True' && !NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate']))) { 
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
            if ($_SESSION['novalnet'][$this->code]['amount'] != NovalnetUtil::getPaymentAmount((array) $order)) {
                unset($_SESSION['novalnet']);
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
        if (isset($post['novalnet_invoice_new_pin']) && $post['novalnet_invoice_new_pin'] == 1) { // Sending new pin number to Novalnet server
            $new_pin_response = NovalnetUtil::doXMLCallbackRequest('TRANSMIT_PIN_AGAIN', $this->code);
            $response         = NovalnetUtil::getStatusFromXmlResponse($new_pin_response); // Converting Xml response from Novalnet server
            if ($response['status'] != 100) { // If the transation is failure
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $response['status_message'], 'SSL'));
            }
        } elseif (isset($_SESSION['novalnet'][$this->code]['order_amount'])) {
            $order_details                     = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
            $_SESSION['novalnet'][$this->code] = array_merge($order_details, $post, array(
                'payment_amount' => $_SESSION['novalnet'][$this->code]['order_amount']
            ));
        } else { // Display error message
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE, 'SSL'));
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
        if((MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && $this->guranteePaymentInvoiceVerifcation($order)) || (MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT == 'True' && !NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate']))) { 
			$this->fraud_module_status = false;
		}		
        if (isset($_SESSION['novalnet'][$this->code]['secondcall']) && $_SESSION['novalnet'][$this->code]['secondcall']) { // Sending pin number to Novalnet server
            $callback_response = ($this->fraud_module && in_array($this->fraud_module, array('SMS', 'CALLBACK'
            ))) ? NovalnetUtil::doXMLCallbackRequest('PIN_STATUS', $this->code) : '';
            $response          = NovalnetUtil::getStatusFromXmlResponse($callback_response); // Converting Xml response from Novalnet server
            if ($response['status'] != 100) { // Novalnet transaction status got failure for displaying error message
                if ($response['status'] == '0529006') {
                    $_SESSION[$this->code . '_nn_payment_lock'] = true;
                }
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . (!empty($response['status_message']) ? NovalnetUtil::setUTFText($response['status_message']) : $response['pin_status']['status_message']), 'SSL'));
            } else { // Novalnet transaction status got success               
                $_SESSION['novalnet'][$this->code]['gateway_status'] = $response['tid_status'];
                $this->updateTransComments($_SESSION['novalnet'][$this->code], '');
            }
        } else {
            $input_params             = array_merge((array) $order, array(
                'payment' => $this->code,
                'payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']
            ));
            $urlparam                 = NovalnetUtil::getRequestParams($input_params);
            $urlparam['key']          = $this->key;
            $urlparam['payment_type'] = $this->payment_type;
            $urlparam['invoice_type'] = $this->payment_type;

            if (MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE != '' && MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE >= 7) { // Get invoice due date
                $urlparam['due_date'] = date('Y-m-d', strtotime('+' . MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE . ' days'));
            } else {
				$urlparam['due_date'] = date('Y-m-d', strtotime('+14 days'));
			}

            // To process on hold product
             $order_amount = $order->info['total']*100;
			if(($order_amount >= trim(MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT) && MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE == 'authorize' ) || (empty (MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT) && MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE == 'authorize' )){
                $urlparam['on_hold'] = 1;
            }
            if (MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True') {	
                if ($this->guranteePaymentInvoiceVerifcation($order)  && NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate'])   && NovalnetUtil::check_data($_SESSION['novalnet'][$this->code]['invoicebirthdate']) ) { // Appending parameters for guarantee payment
                    $urlparam['key']          = '41';
                    $urlparam['payment_type'] = 'GUARANTEED_INVOICE';
                    $urlparam['birth_date']   = $_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate'];
                } else {
                    if (MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT == 'False' && ($order->billing['company'] == '' && $_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate'] == ''  )){
                        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . MODULE_PAYMENT_NOVALNET_AGE_ERROR, 'SSL'));
                    }
                }
            }
	
            
            if ($this->fraud_module && $this->fraud_module_status) { // Appending parameters for Fraud module
                if ($this->fraud_module == 'CALLBACK') {
                    $urlparam['tel']             = trim($_SESSION['novalnet'][$this->code]['novalnet_invoice_fraud_tel']);
                    $urlparam['pin_by_callback'] = '1';
                } else {
                    $urlparam['mobile']     = trim($_SESSION['novalnet'][$this->code]['novalnet_invoice_fraud_mobile']);
                    $urlparam['pin_by_sms'] = '1';
                }
            }
            if(isset($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate'])) {
				$urlparam['birth_date']   = date('Y-m-d', strtotime($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate']));
			}
            $_SESSION['novalnet'][$this->code]['order_amount'] = $urlparam['amount'];	    
            	    
            $response = NovalnetUtil::doPaymentCall("https://payport.novalnet.de/paygate.jsp", $urlparam);
            parse_str($response, $datas);                        	
            if ($datas['status'] == 100) {
                
                $this->updateTransComments($datas, $urlparam); // Update Novalnet transaction comments
                $_SESSION['novalnet_invoice_callback_max_time_nn'] = time() + (30 * 60);
            } else {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($datas), 'SSL'));
            }
            if(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION != 'True' || (MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && !$this->guranteePaymentInvoiceVerifcation($order))) {
				NovalnetUtil::gotoPaymentOnCallback($this->code, $this->fraud_module, $this->fraud_module_status); // Redirect to checkout page for displaying fraud module message
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
        // Form payment reference comments.
		$reference_comments  = NovalnetUtil::novalnetReferenceComments($insert_id, $this->code, ''); 
        $order_status['orders_status'] = $order_status_id['orders_status_id'] = ($_SESSION['novalnet'][$this->code]['payment_id'] == 41 ? ($_SESSION['novalnet'][$this->code]['gateway_status'] == 75 ? MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS : ($_SESSION['novalnet'][$this->code]['gateway_status'] == 91 ?MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE : MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS)) :
        ($_SESSION['novalnet'][$this->code]['gateway_status'] == 91 ? MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE  : MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS));
        $order_status['orders_status'] = ($order_status['orders_status'] > 0) ? $order_status['orders_status'] : DEFAULT_ORDERS_STATUS_ID;
        $order_status_id['orders_status_id'] = ($order_status_id['orders_status_id'] > 0) ? $order_status_id['orders_status_id'] : DEFAULT_ORDERS_STATUS_ID;   		
        tep_db_perform(TABLE_ORDERS, $order_status, "update", "orders_id='$insert_id'");
       
        $order_status_id['comments'] = $_SESSION['novalnet'][$this->code]['comments'] . (($_SESSION['novalnet'][$this->code]['gateway_status'] != 75) ? $reference_comments : '' );
       
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_status_id, "update", "orders_id='$insert_id'");
       
        // Sending post back call to Novalnet server
        NovalnetUtil::doSecondCallProcess(array(
            'payment' => $this->code,
            'order_no' => $insert_id
        ));
    }

    /**
     * Core Function : check()
     *
     */
    function check()
    {
        if (!isset($this->_check)) {
            $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_INVOICE_STATUS'");
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

        include_once DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/payment/novalnet_invoice.php';

        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_group_id, configuration_description, sort_order, set_function, use_function, date_added)
        VALUES
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_STATUS','False', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_STATUS_DESC . "', '1', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_STATUS\'," . MODULE_PAYMENT_NOVALNET_INVOICE_STATUS . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE','False', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE_DESC . "', '2', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE\'," . MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE','capture', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE_DESC . "', '2', 'tep_mod_select_option(array(\'capture\' => MODULE_PAYMENT_NOVALNET_CAPTURE,\'authorize\' => MODULE_PAYMENT_NOVALNET_AUTHORIZE),\'MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE\'," . MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE','False', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE_DESC . "','3','tep_mod_select_option(array(\'False\' => MODULE_PAYMENT_NOVALNET_OPTION_NONE,\'CALLBACK\' => MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK,\'SMS\' => MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS,),\'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE\'," . MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE . ",' , '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT', '', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT_DESC . "', '4','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE', '', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_DESC . "', '5','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT', '', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT_DESC . "', '6','', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_CUSTOMER_INFO_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_CUSTOMER_INFO', '', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_CUSTOMER_INFO_DESC . "', '7','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER', '0', '6', '" . MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER_DESC . "', '8', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS_DESC . "', '9', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS_DESC . "', '10', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE', '0', '6', '" . MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE_DESC . "','11', 'tep_cfg_pull_down_zone_classes(', 'tep_get_zone_class_title',now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION','False', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION_DESC . "', '12', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION\'," . MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT', '', '6', '" . MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT_DESC . "','18', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_INVOICE_TITLE . "','MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT', '', '6', '".MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT_DESC."' , '7', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS', '', '6', '" . MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS_DESC . "','16', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT_TITLE . "','MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT','True', '6','" . MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT_DESC . "','19', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT\'," . MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT . ",' , '', now())
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
        return array('MODULE_PAYMENT_NOVALNET_INVOICE_STATUS', 'MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE','MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE','MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT','MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE', 'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT','MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE', 'MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT','MODULE_PAYMENT_NOVALNET_INVOICE_CUSTOMER_INFO', 'MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER','MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS', 'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS','MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE', 'MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION','MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT','MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS','MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT');
    }

    /**
     * Validate admin configuration
     * @param $admin
     *
     * @return boolean
     */
    function validateAdminConfiguration($admin = false)
    {
        if (MODULE_PAYMENT_NOVALNET_INVOICE_STATUS == 'True') {
            if (( MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT !='' && !ctype_digit(MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_INVOICE_LIMIT)) || (MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT 
            != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT))) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE);
                return false;
            } elseif (trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE) != '' && !ctype_digit(trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE))) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_ERROR);
                return false;
            } elseif(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && (MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '' && (!ctype_digit(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT) || MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT < 999))){
				if(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '' && (!ctype_digit(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT) || MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT < 999)) {
					if ($admin)
						echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_INVOICE_GURANTEE_PAYMENT_MIN_AMOUNT_ERROR_MSG);
					return false;
				}
			}
			
			elseif(MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE != 'False' && (MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT))) {
				if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE);
                return false;
			}
			else {
                return true;
            }
        }
    }
    
    
    function gurantee_error($admin = false)
    {
    global $order;
    $order_amount = $order->info['total']*100 ;
    $minimum_amount_gurantee = trim(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT) != '' ? trim(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT) : '999';
        // Delivery address
		$delivery_address = array(
			'street_address' => $order->delivery['street_address'],
			'city'           => $order->delivery['city'],
			'postcode'       => $order->delivery['postcode'],
			'country'        => $order->delivery['country']['iso_code_2'],
		);
		// Billing address
		$billing_address = array(
			'street_address' => $order->billing['street_address'],
			'city'           => $order->billing['city'],
			'postcode'       => $order->billing['postcode'],
			'country'        => $order->billing['country']['iso_code_2'],
		);
    
		if(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True'){
			if(MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FORCE_GUARANTEE_PAYMENT == 'False'){
				if ($delivery_address !== $billing_address) {
				echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_ADDRESS);
				return false;
				}else if(!in_array(strtoupper($order->billing['country']['iso_code_2']), array('DE', 'AT', 'CH'))){
					echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_COUNTRY);
					return false;
				}else if ($order->info['currency'] != 'EUR'){
					echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_CURRENCY);
					return false;
				}else if($order_amount <= $minimum_amount_gurantee ){
					echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, sprintf(MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_AMOUNT, sprintf("%.2f", ($minimum_amount_gurantee / 100))));
					return false;
				}
			}
		}
	}
      
    /**
     * Update transaction comments
     * @param $payment_response
     * @param $input_params
     *
     * @return none
     */
    function updateTransComments($payment_response, $input_params)
    {
        global $order;
        $trans_comments = PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $payment_response['tid'] . (((isset($payment_response['test_mode']) && $payment_response['test_mode'] == 1) || MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE == 1) ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
        list($invoice_comments, $bank_details) = NovalnetUtil::formInvoicePrepaymentComments($payment_response); // Get Invoice / Prepayment comments in class.novalnetutil.php file
        if (empty($_SESSION['novalnet'][$this->code]['bank_details'])) {
            $_SESSION['novalnet'][$this->code]['bank_details'] = serialize($bank_details);
        }
        $_SESSION['novalnet'][$this->code] = array_merge($_SESSION['novalnet'][$this->code], array(
            'tid'               => $payment_response['tid'],
            'vendor'            => !empty($input_params['vendor']) ? $input_params['vendor'] : $payment_response['vendor'],
            'product'               => !empty($input_params['product']) ? $input_params['product'] : $payment_response['product'],
            'tariff'                => !empty($input_params['tariff']) ? $input_params['tariff'] : $payment_response['tariff'],
            'auth_code'             => !empty($input_params['auth_code']) ? $input_params['auth_code'] : $payment_response['auth_code'],
            'amount'                => $_SESSION['novalnet'][$this->code]['order_amount'],
            'total_amount'          => $_SESSION['novalnet'][$this->code]['order_amount'],
            'order_currency'        => $payment_response['currency'],          
            'payment_id'            => (MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_CONFIGURATION == 'True' && $this->guranteePaymentInvoiceVerifcation($order) && NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate']) && NovalnetUtil::check_data($_SESSION['novalnet'][$this->code]['invoicebirthdate'])) ? 41: 27,
            'test_mode'             => $payment_response['test_mode'],
            'customer_id'           => $payment_response['customer_no'],
            'comments'              => $order->info['comments'] . $trans_comments . $invoice_comments,        
            'payment_details'       => $_SESSION['novalnet'][$this->code]['bank_details'],
            'gateway_status'        => ($payment_response['tid_status']) ? $payment_response['tid_status'] : $_SESSION['novalnet'][$this->code]['gateway_status'],            
            'customer_no'           => $payment_response['customer_no'],
            'due_date'              => $payment_response['due_date'],
            'invoice_iban'          => $payment_response['invoice_iban'],
            'reference_transaction' => '0',
            'invoice_bic'           => $payment_response['invoice_bic'],
            'invoice_bankname'      => $payment_response['invoice_bankname'],
            'invoice_bankplace'     => $payment_response['invoice_bankplace'],
            'currency'              => $payment_response['currency']
        ));
        if($_SESSION['novalnet'][$this->code]['payment_id'] == '41' && in_array($_SESSION['novalnet'][$this->code]['gateway_status'], array('75','91','100'))){
			$_SESSION['novalnet'][$this->code]['comments'] =  MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY . PHP_EOL .$_SESSION['novalnet'][$this->code]['comments'];
		}
        
        if($_SESSION['novalnet'][$this->code]['payment_id'] == '41' && $_SESSION['novalnet'][$this->code]['gateway_status'] == '75'){
			$_SESSION['novalnet'][$this->code]['comments'] .=  PHP_EOL .MODULE_PAYMENT_NOVALNET_MENTION_GUARANTEE_PAYMENT_PENDING_TEXT.PHP_EOL;
		}
        
        $order->info['comments']     .= $_SESSION['novalnet'][$this->code]['comments']; // Update Novalnet order comments & customer notes in Order table
    }

    /**
     * Verifing gurantee payment possibility
     * @param $order
     *
     * @return boolean
     */
    function guranteePaymentInvoiceVerifcation($order)
    {
		
        $minimum_amount = (MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT != '') ? MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_PAYMENT_MINIMUM_AMOUNT : 999;
        return (in_array($order->customer['country']['iso_code_2'], array('AT', 'DE', 'CH'
        )) && $order->info['currency'] == 'EUR' && NovalnetUtil::addressVerification($order) && isset($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate']) && NovalnetUtil::validateAge($_SESSION['novalnet'][$this->code]['novalnet_invoicebirthdate']) && NovalnetUtil::getPaymentAmount((array) $order) >= $minimum_amount  );
    }
}
?>
