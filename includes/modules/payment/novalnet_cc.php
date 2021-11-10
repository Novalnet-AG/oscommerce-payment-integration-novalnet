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
 * Script : novalnet_cc.php
 *
 */
include_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.novalnetutil.php';
class novalnet_cc
{
    var $code, $title, $description, $enabled, $key = 6, $payment_type = 'CREDITCARD';

    /**
     * Constructor
     *
     */
    function novalnet_cc()
    {
        global $order;
        $this->code        = 'novalnet_cc';
        $this->title       = $this->public_title = MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION;         
            
		$this->desc = MODULE_PAYMENT_NOVALNET_CC_TEXT_DESC;
		
        $this->sort_order = defined('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER') ? MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER : 0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
            $this->enabled    = ((MODULE_PAYMENT_NOVALNET_CC_STATUS == 'True') ? true : false);
        }

        if ($this->enabled === true) {
            if (isset($order) && is_object($order)) {
                $this->update_status();
            }
        }
        echo '<script type= text/javascript src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/authorization.js"></script>';
    }

    /**
     * Core Function : update_status()
     *
     */
    function update_status()
    {
        global $order;
        
        if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE > 0) ) {
			$check_flag = false;
			$check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
        
        if (!NovalnetUtil::checkMerchantConfiguration() || !$this->validateAdminConfiguration() || !NovalnetUtil::hidePaymentVisibility(NovalnetUtil::getPaymentAmount((array) $order), MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT) ) { // Validate the Novalnet merchant details and payment visibility
            return false;
        }
        $selection['id']     = $this->code;
        $selection['module'] = $this->public_title . MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE . '<input type="hidden" id="nn_root_cc_catalog" value="' . DIR_WS_CATALOG . '"/>';

        $payment_details = array();
        if (MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ONECLICK') {
            $sql_query       = NovalnetUtil::getPaymentDetails($_SESSION['customer_id'], $this->code);
            $payment_details = unserialize($sql_query['payment_details']);
        }
		
        if ( !empty($payment_details) && (isset($payment_details['cc_one_click']) && $payment_details['cc_one_click'] == 'yes')) {
            $selection['module'] .= '<div id="nn_cc_payment_description">' . '<b>' . $this->desc . '</b></div><input type="hidden" id="nn_lang_cc_new_account" value="' . MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT . '"/><input type="hidden" id="nn_lang_cc_given_account" value="' . MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT . '"/>';
        }

        $end_customer_info = trim(strip_tags(MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO));

        $test_mode_msg = '';
        if (MODULE_PAYMENT_NOVALNET_CC_TEST_MODE == 'True') {
            $test_mode_msg = MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG;
        }

        // To process the normal iframe in checkout page
		if ( MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE != 'ONECLICK' || (MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ONECLICK' && empty($payment_details))) { // Displaying iframe form type
            $selection['module'] .= '<br />' . $this->desc;
            $selection['module'] .= '<br />' . $end_customer_info;
			$selection['module'] .=  ($end_customer_info != '') ? '<br/>'. $test_mode_msg : $test_mode_msg;
            $selection['fields'][] = array(
                'title' => '',
                'field' => $this->renderIframe()
            );
            return $selection;
            
        } elseif (!empty($payment_details)) {
            $form_show = isset($_SESSION['novalnet'][$this->code]['novalnet_ccchange_account']) ? $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] : 1;

            $selection['module'] .= $end_customer_info;
			$selection['module'] .=  ($end_customer_info != '') ? '<br/>'. $test_mode_msg : $test_mode_msg;
            $selection['fields'][] = array(
                'title' => '',
                'field' => '<div id="nn_cc_ref_details">
                    <span id ="novalnet_cc_new_acc" style="color:blue;cursor: pointer;"><u><b>' . MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT . '</b></u></span>
                        <table>
                            <tr class ="nn_cc_ref_details_tr"><td class="main">' . MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE . ':</td>
                                <td class="main">' . $payment_details['cc_card_type'] . '</td>
                            </tr>
                            <tr class ="nn_cc_ref_details_tr"><td class="main">' . MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER . ':</td>
                                <td class="main">' . NovalnetUtil::setUTFText($payment_details['cc_holder']) . '</td>
                            </tr>
                            <tr class ="nn_cc_ref_details_tr"><td class="main">' . MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO . ':</td>
                                <td class="main">' . $payment_details['cc_no'] . '<input type="hidden" id="nn_payment_ref_tid_cc" name="nn_payment_ref_tid" value="' . $payment_details['tid'] . '"/><input type="hidden" name="novalnet_ccchange_account" id="novalnet_ccchange_account" value="' . $form_show . '"/></td>
                            </tr>
                            <tr class ="nn_cc_ref_details_tr"><td class="main">' . MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE . ':</td>
                                <td class="main">' . $payment_details['cc_exp_month'] . ' / ' . $payment_details['cc_exp_year'] . '
                                </td>
                            </tr>
                        </table>
                    </div>'
            );
            $selection['fields'][] = array(
                'title' => '',
                'field' => '<div id="nn_cc_acc" style="display:none"> <span id ="novalnet_cc_given_acc" style="color:blue; cursor: pointer;"><u><b>' . MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT . '</b></u></span><br/>' . $this->renderIframe() . '</div>'
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
        $post                                                           = $_REQUEST;
        $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] = (isset($post['novalnet_ccchange_account']) ) ? $post['novalnet_ccchange_account'] : '0';
		
        $_SESSION['novalnet'][$this->code]['nn_cc_hash']   = $post['nn_cc_hash'];
        $_SESSION['novalnet'][$this->code]['nn_cc_uniqid'] = $post['nn_cc_uniqid'];
        $_SESSION['novalnet'][$this->code]['nn_cc_do_redirect'] = $post['nn_cc_do_redirect'];

        if ((MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE != 'ONECLICK' && (empty($post['nn_cc_hash']) || empty($post['nn_cc_uniqid']))) || (MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ONECLICK' && $post['novalnet_ccchange_account'] == '0' && (empty($post['nn_cc_hash']) || empty($post['nn_cc_uniqid'])))) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS), 'SSL'));
        }

        if (isset($post['nn_payment_ref_tid'])) {
            $_SESSION['novalnet'][$this->code]['nn_payment_ref_tid'] = $post['nn_payment_ref_tid'];
            // if cc_3d enable unset the reference transaction details
			if ( $post['novalnet_ccchange_account'] == 0) {
				unset($_SESSION['novalnet'][$this->code]['nn_payment_ref_tid']);
			}
		}
    }

    /**
     * Core Function : confirmation()
     *
     */
    function confirmation()
    {
        global $order;
        $_SESSION['novalnet'][$this->code]['order_amount'] = NovalnetUtil::getPaymentAmount((array) $order);
        return false;
    }

    /**
     * Core Function : process_button()
     *
     */
    function process_button()
    {
        global $order;
        $post = $_REQUEST;

        $params = array_merge((array) $order, $post, array(
            'payment' => $this->code,
            'payment_amount' => $_SESSION['novalnet'][$this->code]['order_amount']
        ));

        $urlparam                 = NovalnetUtil::getRequestParams($params);
        $urlparam['key']          = $this->key;
        $urlparam['payment_type'] = $this->payment_type;
        $urlparam['nn_it']        = 'iframe';
        if (MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ZEROAMOUNT') { 
            $urlparam = NovalnetUtil::novalnetZeroAmountProcess('novalnet_cc', $urlparam); // Appending zero amount details            
        }
        
        // To process on hold product
		$order_amount = $order->info['total']*100;
		if(($order_amount >= trim(MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT) && MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'authorize' ) || (empty (MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT) && MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'authorize' )){
			$urlparam['on_hold'] = 1;
		}
            
        if (isset($_SESSION['novalnet'][$this->code]['nn_cc_do_redirect'])  && $_SESSION['novalnet'][$this->code]['nn_cc_do_redirect'] == 1 ) {
            NovalnetUtil::getRedirectParams($urlparam);
            $urlparam['unique_id'] = $_SESSION['novalnet'][$this->code]['nn_cc_uniqid'];
            $urlparam['pan_hash']  = $_SESSION['novalnet'][$this->code]['nn_cc_hash'];
            if(MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D == 'True') {
				$urlparam['enforce_3d'] = 1;
			}
               
            foreach ($urlparam as $key => $value) {
                $process_button_string .= tep_draw_hidden_field($key, $value);
            }
            $form_action_url = 'https://payport.novalnet.de/pci_payport';
            $process_button_string .= tep_draw_hidden_field(tep_session_name(), tep_session_id());
            $process_button_string .= NovalnetUtil::confirmButtonDisableActivate( $form_action_url ); // Hiding Buy button in confirmation page
            return $process_button_string;
        }

        $_SESSION['novalnet'][$this->code]['input_params'] = $urlparam;

        if (isset($_SESSION['novalnet'][$this->code]['order_amount'])) {
            $novalnet_order_details            = isset($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
            $_SESSION['novalnet'][$this->code] = array_merge($novalnet_order_details, $post, array(
                'payment_amount' => $_SESSION['novalnet'][$this->code]['order_amount']
            ));
        }
        return false;
    }

    /**
     * Core Function : before_process()
     *
     */
    function before_process()
    {
        $post = $_REQUEST;        
        if ( !empty($post['tid'])) {	 
            if ($post['status'] == '100') {			
                $payment_response = NovalnetUtil::decodePaygateResponse($post);  
                        
                if (NovalnetUtil::validateHashResponse($post)) { // Hash Validation failed
                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_ERROR_MESSAGE), 'SSL'));
                }                
                $this->creditCardSessionDetails($payment_response, $post);
                $this->updateTransComments($payment_response); // Update Novalnet transaction comments

            } else {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($post), 'SSL'));
            }
        } else {
            $input_params = $_SESSION['novalnet'][$this->code]['input_params'];
            if (!isset($post['tid']) && $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] != 1) {
                $input_params['unique_id'] = $_SESSION['novalnet'][$this->code]['nn_cc_uniqid'];
                $input_params['pan_hash']  = $_SESSION['novalnet'][$this->code]['nn_cc_hash'];

                if (in_array(MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE, array('ONECLICK', 'ZEROAMOUNT'))) {
					$tariff_type = explode('-', MODULE_PAYMENT_NOVALNET_TARIFF_ID);
					$input_params['create_payment_ref'] = '1';
					if(MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ZEROAMOUNT' && $tariff_type['0'] == 4) {
						unset($input_params['create_payment_ref']);
					}                   	
                }
            }

            if (!empty($_SESSION['novalnet'][$this->code]['nn_payment_ref_tid']) && $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] == 1) {
                $input_params['payment_ref'] = $_SESSION['novalnet'][$this->code]['nn_payment_ref_tid'];
            }  
        
            $response = NovalnetUtil::doPaymentCall('https://payport.novalnet.de/paygate.jsp', $input_params);
            parse_str($response, $payment_response);                    	
            if ($payment_response['status'] == '100') { // Novalnet transaction status got success               
                $this->creditCardSessionDetails($payment_response, $input_params);
                $this->updateTransComments($payment_response); // Update Novalnet transaction comments
            } else { // Novalnet transaction is failure for displaying error message
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($payment_response), 'SSL'));
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
		$payment_order_status          = $_SESSION['novalnet'][$this->code]['gateway_status'] =='98' ?MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE : MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS; 
        $order_status['orders_status'] = $order_status_id['orders_status_id'] = ($payment_order_status > 0) ? $payment_order_status : DEFAULT_ORDERS_STATUS_ID;
		
        // Update order status in order status table
        tep_db_perform(TABLE_ORDERS, $order_status, "update", "orders_id='$insert_id'");
        // Update order status id in order status history table
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
            $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_CC_STATUS'");
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

        include_once DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/payment/novalnet_cc.php';
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_group_id, configuration_description, sort_order, set_function, use_function, date_added)
        VALUES
        ('" . MODULE_PAYMENT_NOVALNET_CC_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_STATUS','False', '6','" . MODULE_PAYMENT_NOVALNET_CC_STATUS_DESC . "', '1', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_CC_STATUS\'," . MODULE_PAYMENT_NOVALNET_CC_STATUS . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_TEST_MODE','False', '6','" . MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_DESC . "', '2', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_CC_TEST_MODE\'," . MODULE_PAYMENT_NOVALNET_CC_TEST_MODE . ",', '', now()),
         ('" . MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE','capture', '6','" . MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_DESC . "', '2', 'tep_mod_select_option(array(\'capture\' => MODULE_PAYMENT_NOVALNET_CAPTURE,\'authorize\' => MODULE_PAYMENT_NOVALNET_AUTHORIZE),\'MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE\'," . MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D','False', '6', '" . MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D_DESC . "', '3', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D\'," . MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D . ",' ,'',now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE','False', '6','" . MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC . "','7', 'tep_mod_select_option(array(\'False\' => MODULE_PAYMENT_NOVALNET_OPTION_NONE,\'ONECLICK\' => MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK,\'ZEROAMOUNT\' => MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT,),\'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE\'," . MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE . ",' ,'',now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT', '', '6','" . MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_DESC . "', '9','', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO', '', '6','" . MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_DESC . "', '10','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER', '0', '6', '" . MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_DESC . "', '11', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_DESC . "', '12', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE', '0', '6', '" . MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_DESC . "','13', 'tep_cfg_pull_down_zone_classes(', 'tep_get_zone_class_title',now()),
        ('" . MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_CC_TITLE . "','MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT', '', '6', '".MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT_DESC."' , '7', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION', '', '6', '', '15', '', '', now()), ('" . MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT', '', '6', '', '15', '', '', now()), ('" . MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS_TITLE . "','MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS', 'body{font-family: Verdana,Arial,sans-serif;font-size:11px;line-height: 1.5;}.input-group{width:50%;font-family: Verdana,Arial,sans-serif;}.label-group{padding:5px 0;width:30%}.row {padding:0}', '6', '', '15', '', '', now())");
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
        return array(
            'MODULE_PAYMENT_NOVALNET_CC_STATUS', 'MODULE_PAYMENT_NOVALNET_CC_TEST_MODE', 'MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE','MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT', 'MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D', 'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE', 'MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT', 'MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO', 'MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER', 'MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS', 'MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE',
            'MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION', 'MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT',
            'MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS'
        );
    }

    /**
     * Validate admin configuration
     * @param $admin
     *
     * @return boolean
     */
    function validateAdminConfiguration($admin = false)
    {

        if (MODULE_PAYMENT_NOVALNET_CC_STATUS == 'True') {
			if ( ( MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT !='' && !ctype_digit(MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT) ) || (MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT) )) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CC_BLOCK_TITLE);
                return false;
            }
        }

        return true;
    }

    /**
     * Appending payment details in Novalnet session
     * @param $response
     * @param $input_params
     *
     * @return none
     */
    function creditCardSessionDetails($response, $input_params)
    {     
        if (isset($_SESSION['novalnet'][$this->code]['zero_transaction']) && $_SESSION['novalnet'][$this->code]['zero_transaction'] == '1') {
            $_SESSION['novalnet'][$this->code] = array(
                'zerotrxnreference'  => $response['tid'],
                'zerotrxndetails'    => isset($_SESSION['novalnet'][$this->code]['zerotrxndetails']) ? $_SESSION['novalnet'][$this->code]['zerotrxndetails'] : '',
                'zero_transaction'   => isset($_SESSION['novalnet'][$this->code]['zero_transaction']) ? $_SESSION['novalnet'][$this->code]['zero_transaction'] : '0',
                'total_amount'       => $_SESSION['novalnet'][$this->code]['order_amount']
            );
        }
        $_SESSION['novalnet'][$this->code] = array_merge($_SESSION['novalnet'][$this->code], array(
            'tid'                   => $response['tid'],
            'vendor'                => !empty($response['vendor']) ? $response['vendor'] : $input_params['vendor'],
            'product'               => !empty($response['product']) ? $response['product'] : $input_params['product'],
            'tariff'                => !empty($response['tariff']) ? $response['tariff'] : $input_params['tariff'],
            'auth_code'             => !empty($response['auth_code']) ? $response['auth_code'] : $input_params['auth_code'],
            'amount'                => str_replace('.', '', $response['amount']),
            'total_amount'          => !empty($_SESSION['novalnet'][$this->code]['total_amount']) ? $_SESSION['novalnet'][$this->code]['total_amount'] : $_SESSION['novalnet'][$this->code]['order_amount'],
            'currency'              => $response['currency'],
            'gateway_status'        => $response['tid_status'],           
            'payment_id'            => $this->key,
            'test_mode'             => !empty($response['test_mode']) ? $response['test_mode'] : $input_params['test_mode'],
            'customer_id'           => $response['customer_no'],         
            'reference_transaction' => isset($input_params['payment_ref']) ? '1' : '0'
        ));
        
        if(MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ONECLICK') {			
			if(!isset($input_params['payment_ref'])) {
				$serialize_data = array(
					'cc_holder'    => $response['cc_holder'],
					'cc_no'        => $response['cc_no'],
					'cc_exp_year'  => $response['cc_exp_year'],
					'cc_exp_month' => $response['cc_exp_month'],
					'tid_status'   => $response['tid_status'],
					'cc_card_type' => $response['cc_card_type'],
					'amount'       => $response['amount'],
					'currency'     => $response['currency'],
					'tid'          => $response['tid'],
					'cc_one_click' => 'yes'
				);
				$_SESSION['novalnet'][$this->code]['payment_details'] = serialize($serialize_data);
			}
		}
        
    }

    /**
     * Update Novalnet transaction comments in Order table
     * @param $postData
     *
     * @return none
     */
    function updateTransComments($postData)
    {
        global $order;
        $trans_comments          = '';
        $trans_comments          = MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $postData['tid'] . (((isset($postData['test_mode']) && $postData['test_mode'] == 1) || MODULE_PAYMENT_NOVALNET_CC_TEST_MODE == 'True') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
        $order->info['comments'] = $order->info['comments']. PHP_EOL . $trans_comments;
    }

    /**
     * Display Iframe form
     *
     * @return none
     */
    function renderIframe()
    {
		global $languages_id, $order;             
        
        // Get language
        $language_code_query = "select code from " . TABLE_LANGUAGES . " where languages_id = ".$languages_id.""; 
		$language_code = tep_db_fetch_array(tep_db_query($language_code_query));
		$lang = $language_code['code'];	 
		
        $ccIframeData = [
			'client_key' => MODULE_PAYMENT_NOVALNET_CLIENT_KEY,
            'test_mode' => (MODULE_PAYMENT_NOVALNET_CC_TEST_MODE == 'True') ? '1' : '0',
            'first_name' => $order->billing['firstname'],
            'last_name' => $order->billing['lastname'],
            'email' => $order->customer['email_address'],
            'street' => $order->billing['street_address'],
            'city' => $order->billing['city'],
            'zip' => $order->customer['postcode'],
            'country_code' => $order->billing['country']['iso_code_2'],
            'amount' => (MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE == 'ZEROAMOUNT') ? '0' : NovalnetUtil::getPaymentAmount((array)$order),
            'currency' => $order->info['currency'],
            'lang' => ($lang) ? strtoupper($lang) : 'DE',
            'enforce_3d' => (MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D == 'True') ? 1 : 0
        ];
        
        $ccIframeData = json_encode($ccIframeData);
        
        $cc_hidden_field .= '<input type="hidden" id="nn_cc_standard_style_label" value="' . MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION . '"><input type="hidden" id="nn_cc_standard_style_input" value="' . MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT . '"><input type="hidden" id="nn_cc_standard_style_css" value="' . MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS . '"><input type="hidden" id="nn_cc_error_msg" value="' . MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS . '">';
        
        return "<link rel='stylesheet' type='text/css' href=".DIR_WS_CATALOG."ext/modules/payment/novalnet/css/novalnet.css><script src='https://cdn.novalnet.de/js/v2/NovalnetUtility.js'></script>
        <iframe id='nnIframe' frameborder='0' scrolling='no' width='400px'></iframe>
        <script src='" . DIR_WS_CATALOG . "ext/modules/payment/novalnet/js/novalnet_cc.js' type='text/javascript'></script>
        <input type='hidden' id='nn_cc_iframe_data' name='nn_cc_iframe_data' value='".$ccIframeData."' />
        <input type='hidden' id='nn_cc_hash' name='nn_cc_hash'/>" . $cc_hidden_field . "<input type='hidden' id='nn_cc_uniqid' name='nn_cc_uniqid'/><input type='hidden' id='nn_cc_do_redirect' name='nn_cc_do_redirect'/>";
    }
}
?>
