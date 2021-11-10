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
 * Script : novalnet_sofortbank.php
 *
 */
include_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.novalnetutil.php';
class novalnet_sofortbank
{
    var $code, $title, $description, $enabled, $key = 33, $payment_type = 'ONLINE_TRANSFER';

    /**
     * Constructor
     *
     */
    function novalnet_sofortbank()
    {
        global $order;
        $this->code            = 'novalnet_sofortbank';
        $this->title           = $this->public_title = MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEXT_TITLE;
        $this->description     = MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEXT_DESCRIPTION;
        $this->form_action_url = 'https://payport.novalnet.de/online_transfer_payport';

        $this->sort_order = defined('MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER') ? MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER : 0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
            $this->enabled    = ((MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS == 'True') ? true : false);
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
        
        if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE > 0) ) {
			$check_flag = false;
			$check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
       
        if (!NovalnetUtil::checkMerchantConfiguration() || !$this->validateAdminConfiguration() || !NovalnetUtil::hidePaymentVisibility(NovalnetUtil::getPaymentAmount((array) $order), MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BY_AMOUNT)) { // Validate the Novalnet merchant details, sofortbank payment admin configuration details and payment visibility
            return false;
        }
        $selection['id']     = $this->code;
        $selection['module'] = $this->public_title . MODULE_PAYMENT_NOVALNET_SOFORTBANK_PUBLIC_TITLE;
        $selection['module'] .= '<br />' . MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEXT_DESC . '<br />' . trim(strip_tags(MODULE_PAYMENT_NOVALNET_SOFORTBANK_CUSTOMER_INFO));
        if (MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE == 'True') {
            $selection['module'] .= '<br>' . MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG;
        }
        return $selection;
    }

    /**
     * Core Function : pre_confirmation_check()
     *
     */
    function pre_confirmation_check()
    {
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
        NovalnetUtil::getRedirectParams($urlparam);       
        $process_button_string = '';              	
        foreach ($urlparam as $key => $value) {
            $process_button_string .= tep_draw_hidden_field($key, $value);
        }
        $process_button_string .= tep_draw_hidden_field(tep_session_name(), tep_session_id());
        $process_button_string .= NovalnetUtil::confirmButtonDisableActivate(); // Hiding Buy button in confirmation page
        return $process_button_string;

    }

    /**
     * Core Function : before_process()
     *
     */
    function before_process()
    {
        global $order;
        $post = $_REQUEST;        
        if (isset($post['tid']) && $post['status'] == 100) { // Novalnet transaction status got success
            $payment_response = NovalnetUtil::decodePaygateResponse($post); // Decoding Novalnet server response            
            if (NovalnetUtil::validateHashResponse($post)) { //Hash Validation failed
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . utf8_decode(MODULE_PAYMENT_NOVALNET_SOFORT_REDIRECTION_ERROR_MESSAGE), 'SSL'));
            }
            $_SESSION['novalnet'][$this->code] = array(
                'tid'              => $payment_response['tid'],
                'vendor'           => $payment_response['vendor'],
                'product'          => $payment_response['product'],
                'tariff'           => $payment_response['tariff'],
                'auth_code'        => $payment_response['auth_code'],
                'amount'           => $payment_response['amount'],
                'total_amount'     => $payment_response['amount'],
                'currency'         => $payment_response['currency'],
                'gateway_status'   => $payment_response['tid_status'],
                'test_mode'        => $payment_response['test_mode'],
                'customer_id'      => $payment_response['customer_no'],
                'payment_id'       => $this->key
            );
            $trans_comments                    = MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $payment_response['tid'] . (((isset($payment_response['test_mode']) && $payment_response['test_mode'] == 1) || MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE == 'True') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
            $order->info['comments']          .= PHP_EOL.$trans_comments;
        } else { // Novalnet transaction status got failure for displaying error message
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($post), 'SSL'));
        }
    }

    /**
     * Core Function : after_process()
     *
     */
    function after_process()
    {
        global $insert_id;
        $order_status['orders_status'] = $order_status_id['orders_status_id'] = (MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS > 0) ? MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS : DEFAULT_ORDERS_STATUS_ID;
        tep_db_perform(TABLE_ORDERS, $order_status, "update", "orders_id='" . $insert_id . "'");
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_status_id, "update", "orders_id='$insert_id'"); // Update order status id in order status history table
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
            $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS'");
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

        include_once DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/payment/novalnet_sofortbank.php';
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_group_id, configuration_description, sort_order, set_function, use_function, date_added)
        VALUES
        ('" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS','False', '6','" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS_DESC . "', '1', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS\'," . MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE_TITLE . "','MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE','False', '6','" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE_DESC . "', '2', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE\'," . MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BY_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BY_AMOUNT', '', '6','" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BY_AMOUNT_DESC . "', '3','', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_CUSTOMER_INFO_TITLE . "','MODULE_PAYMENT_NOVALNET_SOFORTBANK_CUSTOMER_INFO', '', '6','" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_CUSTOMER_INFO_DESC . "', '4','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER_TITLE . "','MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER', '0', '6', '" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER_DESC . "', '5', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS_DESC . "', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE_TITLE . "','MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE', '0', '6', '" . MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE_DESC . "','7', 'tep_cfg_pull_down_zone_classes(', 'tep_get_zone_class_title',now())");
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
            'MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS',
            'MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEST_MODE',
            'MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BY_AMOUNT',
            'MODULE_PAYMENT_NOVALNET_SOFORTBANK_CUSTOMER_INFO',
            'MODULE_PAYMENT_NOVALNET_SOFORTBANK_SORT_ORDER',
            'MODULE_PAYMENT_NOVALNET_SOFORTBANK_ORDER_STATUS',
            'MODULE_PAYMENT_NOVALNET_SOFORTBANK_PAYMENT_ZONE',
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
        if (MODULE_PAYMENT_NOVALNET_SOFORTBANK_STATUS == 'True') {
            if (MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BY_AMOUNT != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_SOFORTBANK_VISIBILITY_BY_AMOUNT)) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_SOFORTBANK_BLOCK_TITLE);
                return false;
            }
        }
        return true;
    }
}
?>
