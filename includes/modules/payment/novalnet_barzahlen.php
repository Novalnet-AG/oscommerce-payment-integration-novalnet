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
 * Script : novalnet_barzahlen.php
 *
 */
include_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.novalnetutil.php';
class novalnet_barzahlen
{
    var $code, $title, $description, $enabled, $key = 59, $payment_type = 'CASHPAYMENT';

    /**
     * Constructor
     *
     */
    function novalnet_barzahlen()
    {
        global $order;
        $this->code        = 'novalnet_barzahlen';
        $this->title       = $this->public_title = MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEXT_DESCRIPTION;

        $this->sort_order = defined('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER') ? MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER : 0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
            $this->enabled    = ((MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS == 'True') ? true : false);
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
        
        if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE > 0) ) {
			$check_flag = false;
			$check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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


        if (!NovalnetUtil::checkMerchantConfiguration() || !$this->validateAdminConfiguration() || !NovalnetUtil::hidePaymentVisibility(NovalnetUtil::getPaymentAmount((array) $order), MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT)) { // Validate the Novalnet merchant details, prepayment admin details and payment visibility
            return false;
        }

        $selection['id']     = $this->code;
        $selection['module'] = $this->public_title . MODULE_PAYMENT_NOVALNET_BARZAHLEN_PUBLIC_TITLE;
        $selection['module'] .= '<br />' . MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEXT_DESC . '<br />' . trim(strip_tags(MODULE_PAYMENT_NOVALNET_BARZAHLEN_CUSTOMER_INFO)) . '<br />';
        if (MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE == 'True') {
            $selection['module'] .= MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG;
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
        if (isset($_SESSION['novalnet'][$this->code]['order_amount'])) {
            $_SESSION['novalnet'][$this->code] = array(
                'payment_amount' => $_SESSION['novalnet'][$this->code]['order_amount']
            );
        }
        return false;
    }

    /**
     * Core Function : before_process()
     *
     */
    function before_process()
    {
        global $order;
        $input_params             = array_merge((array) $order, array(
            'payment' => $this->code,
            'payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']
        ));
        $urlparam                 = NovalnetUtil::getRequestParams($input_params);
        $urlparam['key']          = $this->key;
        $urlparam['payment_type'] = $this->payment_type;
        if ( !empty(MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE) ) { // Get invoice due date
			$urlparam['cp_due_date'] = date('Y-m-d', strtotime('+' . MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE . ' days'));
		} 
        $response                 = NovalnetUtil::doPaymentCall("https://payport.novalnet.de/paygate.jsp", $urlparam);	
        parse_str($response, $datas);	
        if ($datas['status'] == 100) {            
            $transaction_comments = $order->info['comments'] . PHP_EOL;
            $transaction_comments .= MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid'] . PHP_EOL . ((((isset($datas['test_mode']) && $datas['test_mode']) == 1) || MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE == 1) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');
            list($barzahlen_comments, $nearest_store) = NovalnetUtil::formBarzahlenComments($datas);
            $_SESSION['novalnet_cp_token'] = $datas['cp_checkout_token'].'|'. $datas['test_mode'];
            $_SESSION['novalnet'][$this->code] = array(
                'tid'              => $datas['tid'],
                'vendor'           => $urlparam['vendor'],
                'product'          => $urlparam['product'],
                'auth_code'        => $urlparam['auth_code'],
                'tariff'           => $urlparam['tariff'],
                'test_mode'        => $datas['test_mode'],
                'amount'           => $urlparam['amount'],
                'total_amount'     => $urlparam['amount'],
                'currency'         => $datas['currency'],
                'gateway_status'   => $datas['tid_status'],               
                'payment_id'       => $this->key,
                'customer_id'      => $datas['customer_no'],
                'payment_details'  => serialize($nearest_store),
                'comments'         => $transaction_comments . $barzahlen_comments
            );
            $order->info['comments'] = $transaction_comments . $barzahlen_comments;
        } else {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . NovalnetUtil::getTransactionMessage($datas), 'SSL'));
        }
    }

    /**
     * Core Function : after_process()
     *
     */
    function after_process()
    {
        global $insert_id;
        
        $order_status['orders_status']                    = $order_status_id['orders_status_id'] = (MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS > 0) ? MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS : DEFAULT_ORDERS_STATUS_ID;
        tep_db_perform(TABLE_ORDERS, $order_status, "update", "orders_id='$insert_id'");
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
            $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS'");
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

        include_once DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/payment/novalnet_barzahlen.php';
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_group_id, configuration_description, sort_order, set_function, use_function, date_added)
        VALUES
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS','False', '6','" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS_DESC . "', '1', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS\'," . MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE','False', '6','" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE_DESC . "', '2', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE\'," . MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE', '', '6','" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE_DESC . "', '5','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT', '', '6','" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT_DESC . "', '3','', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_CUSTOMER_INFO_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_CUSTOMER_INFO', '', '6','" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_CUSTOMER_INFO_DESC . "', '4','',  '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER', '0', '6', '" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER_DESC . "', '5', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_CALLBACK_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_CALLBACK_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_CALLBACK_ORDER_STATUS_DESC . "', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
         ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS_DESC . "', '7', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE_TITLE . "','MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE', '0', '6', '" . MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE_DESC . "','8', 'tep_cfg_pull_down_zone_classes(', 'tep_get_zone_class_title',now())");
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
        return array('MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS', 'MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE', 'MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE', 'MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT', 'MODULE_PAYMENT_NOVALNET_BARZAHLEN_CUSTOMER_INFO','MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER', 'MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS','MODULE_PAYMENT_NOVALNET_BARZAHLEN_CALLBACK_ORDER_STATUS', 'MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE');
    }

    /**
     * Validate admin configuration
     * @param $admin
     *
     * @return boolean
     */
    function validateAdminConfiguration($admin = false)
    {
        if (MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS == 'True') {
            if (MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT != '' && !ctype_digit(MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT)) {
                if ($admin)
                    echo NovalnetUtil::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_BARZAHLEN_BLOCK_TITLE);
                return false;
            } else {
                return true;
            }
        }
    }
}
?>
