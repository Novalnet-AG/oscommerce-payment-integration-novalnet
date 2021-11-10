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
 * Script : novalnet_paypal.php
 *
 */
include_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.novalnetutil.php';
class novalnet_paypal
{
    var $code, $title, $description, $enabled, $key = 34, $payment_type = 'PAYPAL';

    /**
     * Constructor
     *
     */
    function novalnet_paypal()
    {
        global $order;
        $this->code        = 'novalnet_paypal';
        $this->title       = $this->public_title = MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_TITLE;

        $sql_query       = NovalnetUtil::getPaymentDetails($_SESSION['customer_id'], 'novalnet_paypal');
		$payment_details = unserialize($sql_query['payment_details']);
        if(in_array(MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE, array('False', 'ZEROAMOUNT')) || (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ONECLICK' && (empty($payment_details) || $_REQUEST['nn_paypal_transaction_type'] == '1'))) {
			$this->form_action_url = 'https://payport.novalnet.de/paypal_payport';
		}
        $this->description = (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ONECLICK') ? MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK_SHOPPING_DESCRIPTION : MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_DESCRIPTION;
        $this->sort_order = 0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
            $this->sort_order = MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER;
            $this->enabled    = ((MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS == 'True') ? true : false);
        }
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

        if (($this->enabled == true) && ((int) MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE > 0)) {
            $check_flag  = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE . "' and (zone_id < 1 or zone_id = " . $order->billing['zone_id'] . " ) and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

        if (!NovalnetUtil::checkMerchantConfiguration() || !$this->validateAdminConfiguration() || !NovalnetUtil::hidePaymentVisibility(NovalnetUtil::getPaymentAmount((array) $order), MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT)) { // Validate the Novalnet merchant details, paypal admin configuration details and payment visibility
            return false;
        }

        NovalnetUtil::getLastSuccessPayment($this->code); // To get the payment name of last successful order
        $selection['id']     = $this->code;
        $selection['module'] = $this->public_title . MODULE_PAYMENT_NOVALNET_PAYPAL_PUBLIC_TITLE;
        $selection['module'] .= '<br /><input type="hidden" id="nn_root_paypal_catalog" value="' . DIR_WS_CATALOG . '"/><span id="nn_paypal_desc">' .  $this->description . '</span>' . MODULE_PAYMENT_NOVALNET_REDIRECT_NOTICE_MSG . '<br />' . trim(strip_tags(MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO)) . '<script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/jquery.js' . '" type="text/javascript"></script><script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/novalnet_paypal.js' . '" type="text/javascript"></script><input type="hidden" id="nn_paypal_transaction_type" name="nn_paypal_transaction_type" value="0"><span  id="nn_paypal_normal_desc" style="display:none;">'.MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_DESCRIPTION.'</span><input type="hidden" id="nn_paypal_one_click_desc" value="'.MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK_SHOPPING_DESCRIPTION.'">';
        if (MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE == 'True') {
            $selection['module'] .= '<br>' . MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG;
        }

        $one_click_shop = '';
        if (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ONECLICK') {
            $sqlQuerySet      = NovalnetUtil::paypalTransReference($_SESSION['customer_id']);
            $paypal_trans_ref = unserialize($sqlQuerySet['payment_details']);
            if (!empty($sqlQuerySet)) {
                $one_click_shop = '<span id ="novalnet_paypal_new_acc" style="color:blue;cursor: pointer;"><u><b>' . MODULE_PAYMENT_NOVALNET_PAYPAL_NEW_ACCOUNT . '</b></u></span>
                <div id="nn_paypal_ref_details">
                    <table>
                        <tr>
                            <td>'.MODULE_PAYMENT_NOVALNET_PAYPAL_TRANSACTION_ID.'</td>
                            <td>' . $paypal_trans_ref['paypal_transaction_id'] . '</td>
                        </tr>
                        <tr>
                            <td>'.MODULE_PAYMENT_NOVALNET_TRANSACTION_ID.'</td>
                            <td>' . $sqlQuerySet['tid'] . '</td>
                        </tr>
                    </table>
                </div>
                <div id="nn_paypal_ref_account" style="display:none;">
                    <span id ="novalnet_paypal_new_acc" style="color:blue;cursor: pointer;"><u><b>' . MODULE_PAYMENT_NOVALNET_PAYPAL_GIVEN_ACCOUNT . '</b></u></span>
                </div>';
            }
        }
        $selection['fields'][] = array(
            'title' => '',
            'field' => '<table><tr>
                                   <td class="main">' . $one_click_shop . '</td></tr></table>'
        );
        return $selection;
    }

    /**
     * Core Function : pre_confirmation_check()
     *
     */
    function pre_confirmation_check()
    {
        $_SESSION['novalnet']['paypal_transaction_type'] = $_REQUEST['nn_paypal_transaction_type'];
        return false;
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

        if (isset($_SESSION['novalnet'][$this->code]['order_amount'])) {
            $_SESSION['novalnet'][$this->code] = array(
                'payment_amount' => $_SESSION['novalnet'][$this->code]['order_amount']
            );
        }

        $input_params             = array_merge((array) $order, array(
            'payment' => $this->code,
            'payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']
        ));
        $urlparam                 = NovalnetUtil::getRequestParams($input_params);
        $urlparam['key']          = $this->key;
        $urlparam['payment_type'] = $this->payment_type;

        $_SESSION['novalnet']['paypal_param'] = $urlparam;


        if (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ZEROAMOUNT') {
            $_SESSION['novalnet']['paypal_order_amount'] = $urlparam['amount'];
            $urlparam = NovalnetUtil::novalnetZeroAmountProcess('novalnet_paypal', $urlparam);
            if($urlparam['amount'] == '0') {
				$urlparam['create_payment_ref'] = '1';
			}
        }
        if (MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT != '' && $_SESSION['novalnet'][$this->code]['payment_amount'] >= MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT && (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE != 'ZEROAMOUNT'  || $urlparam['amount'] != '0')) { // Assigning on hold parameter
            $urlparam['on_hold'] = '1';
        }        
        NovalnetUtil::getRedirectParams($urlparam);
        $process_button_string = '';

        if (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ONECLICK' && $_SESSION['novalnet']['paypal_transaction_type'] == '0') {
            $sql_query       = NovalnetUtil::getPaymentDetails($_SESSION['customer_id'], $this->code);
            $payment_details = unserialize($sql_query['payment_details']);
            if (empty($payment_details)) {
                $urlparam['create_payment_ref'] = '1';		
                foreach ($urlparam as $key => $value) {
                    $process_button_string .= tep_draw_hidden_field($key, $value);
                }
                $process_button_string .= NovalnetUtil::confirmButtonDisableActivate();
                return $process_button_string;
            }
            return false;

        } else {

            if(MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ONECLICK') {
				$urlparam['create_payment_ref'] = '1';
			}	
	    foreach ($urlparam as $key => $value) {
                $process_button_string .= tep_draw_hidden_field($key, $value);
            }
            $process_button_string .= NovalnetUtil::confirmButtonDisableActivate();
            return $process_button_string;
        }

    }

    /**
     * Core Function : before_process()
     *
     */
    function before_process()
    {
        global $order;
        $post = $_REQUEST;        
        if (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ONECLICK') {
            if (isset($post['tid']) && $post['tid'] != '') {
                if (in_array($post['status'], array(100, 90, 85))) {
                    $payment_response = NovalnetUtil::decodePaygateResponse($post); // Decoding Novalnet server response
                    if (NovalnetUtil::validateHashResponse($post)) { //Hash Validation failed
                        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . utf8_decode(MODULE_PAYMENT_NOVALNET_PAYPAL_REDIRECTION_ERROR_MESSAGE), 'SSL'));
                    }
                    $_SESSION['novalnet'][$this->code] = array(
                        'tid'                   => $payment_response['tid'],
                        'vendor'                => $payment_response['vendor'],
                        'product'               => $payment_response['product'],
                        'tariff'                => $payment_response['tariff'],
                        'auth_code'             => $payment_response['auth_code'],
                        'amount'                => $payment_response['amount'],
                        'total_amount'          => $payment_response['amount'],
                        'currency'              => $payment_response['currency'],
                        'gateway_status'        => $payment_response['tid_status'],
                        'test_mode'             => $payment_response['test_mode'],
                        'customer_id'           => $payment_response['customer_no'],
                        'payment_id'            => $this->key,
                        'subs_id'               => $payment_response['subs_id'],
                        'reference_transaction' => 0,
                        'payment_details'       => serialize(array('paypal_transaction_id' => $post['paypal_transaction_id']))
                    );


                    $trans_comments          = MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $payment_response['tid'] . (((isset($payment_response['test_mode']) && $payment_response['test_mode'] == 1) || MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE == 'True') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
                    $order->info['comments'] .= PHP_EOL.$trans_comments;
                } else {
                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($post), 'SSL'));
                }
            } else {
                $urlparam                = $_SESSION['novalnet']['paypal_param'];
                $paypal_ref              = NovalnetUtil::paypalTransReference($_SESSION['customer_id']);
                $urlparam['payment_ref'] = $paypal_ref['tid'];
                if (MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT != '' && $_SESSION['novalnet'][$this->code]['payment_amount'] >= MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT && MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE != 'ZEROAMOUNT') { // Assigning on hold parameter
                    $urlparam['on_hold'] = '1';
                }	
                $response = NovalnetUtil::doPaymentCall("https://payport.novalnet.de/paygate.jsp", $urlparam);
                parse_str($response, $datas);     
		if ($datas['status'] == 100) {
                    $_SESSION['novalnet'][$this->code] = array(
                        'tid'                   => $datas['tid'],
                        'vendor'                => $urlparam['vendor'],
                        'product'               => $urlparam['product'],
                        'tariff'                => $urlparam['tariff'],
                        'auth_code'             => $urlparam['auth_code'],
                        'amount'                => $datas['amount'],
                        'total_amount'          => $datas['amount'],
                        'currency'              => $urlparam['currency'],
                        'gateway_status'        => $datas['tid_status'],
                        'test_mode'             => $urlparam['test_mode'],
                        'customer_id'           => $urlparam['customer_no'],
                        'payment_id'            => $this->key,
                        'subs_id'               => $datas['subs_id'],
                        'reference_transaction' => 1,
                    );
                    $trans_comments                    = MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $payment_response['tid'] . (((isset($payment_response['test_mode']) && $payment_response['test_mode'] == 1) || MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE == 'True') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
                    $order->info['comments']           .= PHP_EOL.$trans_comments;
                } else {
                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($datas), 'SSL'));
                }
            }
        } else {	
            if (isset($post['tid']) && in_array($post['status'], array(100, 90, 85))) {
				
                $payment_response = NovalnetUtil::decodePaygateResponse($post); // Decoding Novalnet server response
                if (NovalnetUtil::validateHashResponse($post)) { //Hash Validation failed
                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . utf8_decode(MODULE_PAYMENT_NOVALNET_PAYPAL_REDIRECTION_ERROR_MESSAGE), 'SSL'));
                }
                $_SESSION['novalnet'][$this->code] = array(
                    'tid'                   => $payment_response['tid'],
                    'vendor'                => $payment_response['vendor'],
                    'product'               => $payment_response['product'],
                    'tariff'                => $payment_response['tariff'],
                    'auth_code'             => $payment_response['auth_code'],
                    'amount'                => $payment_response['amount'],
                    'total_amount'          => $payment_response['amount'],
                    'currency'              => $payment_response['currency'],
                    'gateway_status'        => $payment_response['tid_status'],
                    'test_mode'             => $payment_response['test_mode'],
                    'customer_id'           => $payment_response['customer_no'],
                    'payment_id'            => $this->key,
                    'subs_id'               => $payment_response['subs_id'],
                    'reference_transaction' => 0,
                );
                if (MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE == 'ZEROAMOUNT') {
                    $tariff_type = explode('-', MODULE_PAYMENT_NOVALNET_TARIFF_ID);
					if ($tariff_type['0'] == 2) {
						$urlparam                                                   = $_SESSION['novalnet']['paypal_param'];
						$_SESSION['novalnet'][$this->code]['zero_transaction']      = '1';
						$_SESSION['novalnet'][$this->code]['zerotrxndetails']       = serialize($urlparam);
						$_SESSION['novalnet'][$this->code]['zerotrxnreference']     = $payment_response['tid'];
						$_SESSION['novalnet'][$this->code]['reference_transaction'] = '0';
						$_SESSION['novalnet'][$this->code]['total_amount']          = $_SESSION['novalnet']['paypal_order_amount'];
					}
                }
                $trans_comments          = MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $payment_response['tid'] . (((isset($payment_response['test_mode']) && $payment_response['test_mode'] == 1) || MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE == 'True') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
                $order->info['comments'] .= PHP_EOL.$trans_comments;
                                
            } else { // Display error message for failure transaction
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($post), 'SSL'));
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
        if (MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION == 'True' && MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE != 'True') {
			NovalnetUtil::sendTestTransactionNotification($_SESSION['novalnet'][$this->code], $insert_id);
		}
        $payment_order_status          = (in_array($_SESSION['novalnet'][$this->code]['gateway_status'], array(90, 85))) ? MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS : MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS;
        
        $order_status['orders_status'] = $order_status_id['orders_status_id'] = ($payment_order_status > 0) ? $payment_order_status : DEFAULT_ORDERS_STATUS_ID;

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
            $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS'");
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

        include_once DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/payment/novalnet_paypal.php';
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_group_id, configuration_description, sort_order, set_function, use_function, date_added)
        VALUES
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS','False', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_DESC . "', '1', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS\'," . MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE','False', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_DESC . "', '2', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE\'," . MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE . ",', '', now()),

        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE','False', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_DESC . "','8', 'tep_mod_select_option(array(\'False\' => MODULE_PAYMENT_NOVALNET_PAYPAL_NONE,\'ONECLICK\' => MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK,\'ZEROAMOUNT\' => MODULE_PAYMENT_NOVALNET_PAYPAL_ZERO_AMOUNT,),\'MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE\'," . MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE . ",' ,'',now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT', '', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_DESC . "', '3','', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO', '', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_DESC . "', '4','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER', '0', '6', '" . MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_DESC . "', '5', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_DESC . "', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_DESC . "', '7', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE', '0', '6', '" . MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_DESC . "','8', 'tep_cfg_pull_down_zone_classes(', 'tep_get_zone_class_title',now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE1_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE1', '', '6','" . MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE1_DESC . "', '9', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE2_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE2', '', '6', '" . MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE2_DESC . "','10', '', '', now())");
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
		echo '<input type="hidden" id="nn_root_catalog" value="'. DIR_WS_CATALOG .'" /><input type="hidden" id="nn_pp_message" value="'.MODULE_PAYMENT_NOVALNET_PAYPAL_SHOW_MESSAGE.'"/><script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/novalnet_paypal.js"></script>';
        $this->validateAdminConfiguration(true); // Validate admin configuration
        return array('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS', 'MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE','MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE', 'MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT','MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO', 'MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER','MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS', 'MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS','MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE', 'MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE1','MODULE_PAYMENT_NOVALNET_PAYPAL_TRANS_REFERENCE2');
    }

    /**
     * Validate admin configuration
     * @param $admin
     *
     * @return boolean
     */
    function validateAdminConfiguration($admin = false)
    {
        if (MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS == 'True') {
            if (MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT)) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_PAYPAL_BLOCK_TITLE);
                return false;
            }
        }
        return true;
    }
}
?>
