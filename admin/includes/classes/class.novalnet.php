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
 * Script : class.novalnet.php
 *
 */
include_once(DIR_WS_CLASSES . 'currencies.php');
include_once(DIR_FS_CATALOG . 'includes/classes/class.novalnetutil.php');

class NovalnetAdmin
{

    /**
     * Perform on hold transaction debit/cancel process
     * @param $request
     *
     * @return string
     */
    static public function onholdTransConfirm($request)
    {
        $remote_ip  = (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $_SERVER['REMOTE_ADDR'] == '::1' || empty($_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $onhold_params = array(
            'vendor'      => $request['vendor'],
            'product'     => $request['product'],
            'key'         => $request['payment_id'],
            'tariff'      => $request['tariff'],
            'auth_code'   => $request['auth_code'],
            'edit_status' => '1',
            'tid'         => $request['tid'],
            'status'      => $request['status'], //100 or 103
            'remote_ip'   => $remote_ip
        );      
        $response = NovalnetUtil::doPaymentCall('https://payport.novalnet.de/paygate.jsp', $onhold_params);
        parse_str($response, $data);	
        if ($data['status'] == 100) {
            $param['gateway_status'] = $request['status'];
            $order_status            = ($request['status'] == 100) ? MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE : MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED;
            if (isset($data['paypal_transaction_id']) && $data['paypal_transaction_id'] != '') {
                tep_db_perform('novalnet_transaction_detail', array(
                    'payment_ref' => serialize(array(
                        'paypal_transaction_id' => $data['paypal_transaction_id']
                    ))
                ), 'update', 'tid="' . $request['tid'] . '"');
            }
            $order_status = ($order_status > 0) ? $order_status : DEFAULT_ORDERS_STATUS_ID;
            tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $request['tid'] . "'");
            ($request['status'] == 100) ? self::updateOrderStatus($request['order_id'], $order_status, PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE), date(DATE_FORMAT, strtotime(date('d.m.Y'))), date('H:i:s')) . PHP_EOL, true, true) : self::updateOrderStatus($request['order_id'], $order_status, PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE, date(DATE_FORMAT, strtotime(date('d.m.Y'))), date('H:i:s')) . PHP_EOL, true, true, true);
        } else {
            $message = !empty($data['status_text']) ? $data['status_text'] : (!empty($data['status_desc']) ? $data['status_desc'] : (!empty($data['status_message']) ? $data['status_message'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR));
            return $message;
        }
        return '';
    }

    /**
     * Perform transaction refund process
     * @param $request
     *
     * @return string
     */
    static public function refundTransAmount($request)
    {
        $remote_ip  = (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $_SERVER['REMOTE_ADDR'] == '::1' || empty($_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $refund_params = array(
            'vendor'         => $request['vendor'],
            'product'        => $request['product'],
            'key'            => $request['payment_id'],
            'tariff'         => $request['tariff'],
            'auth_code'      => $request['auth_code'],
            'refund_request' => '1',
            'tid'            => $request['tid'],
            'refund_param'   => $request['refund_trans_amount'],
            'remote_ip'      => $remote_ip
        );

        if ($request['refund_ref'] != '') {
            $refund_params['refund_ref'] = $request['refund_ref'];
        }        
        $response = NovalnetUtil::doPaymentCall('https://payport.novalnet.de/paygate.jsp', $refund_params);
        parse_str($response, $data);
        $order_status            = '';
        $currencies              = new currencies();
        $param['gateway_status'] = $data['tid_status'];	
        if ($data['status'] == 100) {
            $message = '';

            $amount_formatted = $currencies->format($request['refund_trans_amount'] / 100, false, $request['refund_trans_amount_currency']);

            $message .= PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG), $request['tid'], $amount_formatted);
            if (!empty($data['tid'])) {
                $message .= sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG), $data['tid']);
            }
            $message .= PHP_EOL;
            $select_query       = tep_db_query("SELECT orders_status from " . TABLE_ORDERS . " where orders_id = " . tep_db_input($request['order_id']));
            $order_status       = tep_db_fetch_array($select_query);
            $order_status_value = $order_status['orders_status'];
            tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $request['tid'] . "'");

            if (!empty($data['tid']) && $request['payment_id'] != 6) {
                $nn_existing_trans_data = self::getNovalnetTransDetails($request['order_id'], true);
                NovalnetUtil::logInitialTransaction(array(
                    'tid'             => $data['tid'],
                    'vendor'          => $request['vendor'],
                    'product'         => $request['product'],
                    'tariff_id'       => $request['tariff'],
                    'auth_code'       => $request['auth_code'],
                    'subs_id'         => $request['subs_id'],
                    'payment_id'      => $request['payment_id'],
                    'payment_type'    => $request['payment_type'],
                    'amount'          => $data['amount'],
                    'currency'        => $data['currency'],
                    'order_no'        => $request['order_id'],
                    'test_mode'       => $request['test_mode'],
                    'additional_note' => $request['additional_note'],
                    'gateway_status'  => $data['tid_status'],
                    'customer_id'     => $nn_existing_trans_data['customer_id']
                ));
            }
            if ($param['gateway_status'] != 100) {
                $order_status_value = (MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED > 0) ? MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED : DEFAULT_ORDERS_STATUS_ID;
                tep_db_perform(TABLE_ORDERS, array(
                    'orders_status' => $order_status_value
                ), 'update', 'orders_id="' . $request['order_id'] . '"');
            }
            self::updateOrderStatus($request['order_id'], $order_status_value, $message, true, true);
            return '';
        } else {
            return NovalnetUtil::getTransactionMessage($data);
        }
    }

    /**
     * Perform transaction booking
     * @param $request
     *
     * @return string
     */
    static public function bookTransAmount($request)
    {
        $remote_ip  = (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $_SERVER['REMOTE_ADDR'] == '::1' || empty($_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $select_query = tep_db_query("SELECT zerotrxndetails FROM novalnet_transaction_detail WHERE order_no='" . tep_db_input($request['order_id']) . "'");
        $transInfo    = tep_db_fetch_array($select_query);

        $urlparam = unserialize($transInfo['zerotrxndetails']);

        $urlparam['amount']      = $request['book_amount'];
        $urlparam['order_no']    = $request['order_id'];
        $urlparam['payment_ref'] = $request['tid'];
        $urlparam['remote_ip']   = $remote_ip;


        if (isset($urlparam['pin_by_callback']) || isset($urlparam['pin_by_sms'])) {
            unset($urlparam['pin_by_callback'], $urlparam['pin_by_sms']);
        }
        if ($urlparam['payment_type'] == 'DIRECT_DEBIT_SEPA') {
            $urlparam['sepa_due_date'] = date('Y-m-d', strtotime('+' . $urlparam['sepa_due_date_val'] . ' days'));
            unset($urlparam['sepa_due_date_val']);
        }	
        $response = NovalnetUtil::doPaymentCall('https://payport.novalnet.de/paygate.jsp', $urlparam);
        parse_str($response, $data);	
        if ($data['status'] == 100) {
            $select_query    = tep_db_query("SELECT orders_status from " . TABLE_ORDERS . " where orders_id = " . tep_db_input($request['order_id']));
            $orderInfo       = tep_db_fetch_array($select_query);
            $currencies      = new currencies();
            $amount_formatted = $currencies->format($request['book_amount'] / 100, false, $request['amount_currency']);
            self::updateOrderStatus($request['order_id'], $orderInfo['orders_status'], PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE), $amount_formatted, $data['tid']) . PHP_EOL, true, true);
            $test_mode_msg = (isset($data['test_mode']) && $data['test_mode'] == 1) ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '';
            $message       = PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $data['tid'] . $test_mode_msg;
            $param['tid']  = $data['tid'];
            tep_db_perform('novalnet_subscription_detail', $param, "update", "order_no='" . tep_db_input($request['order_id']) . "'");
            $param['amount']          = $urlparam['amount'];
            $param['callback_amount'] = $urlparam['amount'];
            $param['gateway_status']  = $data['tid_status'];
            $order_status             = ($orderInfo['orders_status'] > 0) ? $orderInfo['orders_status'] : DEFAULT_ORDERS_STATUS_ID;
            tep_db_perform('novalnet_transaction_detail', $param, "update", "order_no='" . tep_db_input($request['order_id']) . "'");
            self::updateOrderStatus($request['order_id'], $order_status, $message, true, true);
        } else {
            return (!empty($data['status_text']) ? $data['status_text'] : (!empty($data['status_desc']) ? $data['status_desc'] : (!empty($data['status_message']) ? $data['status_message'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR)));
        }
        return '';
    }

    /**
     * Perform transaction amount / due_date update process
     * @param $request
     *
     * @return string
     */
    static public function updateTransAmount($request)
    {
        $remote_ip  = (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $_SERVER['REMOTE_ADDR'] == '::1' || empty($_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $amount_change_request = array(
            'vendor'            => $request['vendor'],
            'product'           => $request['product'],
            'key'               => $request['payment_id'],
            'tariff'            => $request['tariff'],
            'auth_code'         => $request['auth_code'],
            'edit_status'       => '1',
            'tid'               => $request['tid'],
            'status'            => 100,
            'update_inv_amount' => '1',
            'amount'            => $request['amount'],
            'remote_ip'         => $remote_ip
        );
        if ($request['due_date'] != '0000-00-00' && $request['due_date'] != '') {
            $amount_change_request['due_date'] = date('Y-m-d', strtotime($request['due_date']));
        }
        $response = NovalnetUtil::doPaymentCall('https://payport.novalnet.de/paygate.jsp', $amount_change_request);
        parse_str($response, $data);      
	if ($data['status'] == 100) {
            $trans_comments          = '';
            $currencies              = new currencies();
            $amount_formatted         = $currencies->format($request['amount'] / 100, false, $request['amount_currency']);
            $message                 = PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE), $amount_formatted, date(DATE_FORMAT, strtotime(date('Y-m-d'))), date('H:i:s')) . PHP_EOL;
            $select_orderstatus      = tep_db_query("SELECT orders_status from " . TABLE_ORDERS . " where orders_id = " . tep_db_input($request['order_id']));
            $orderInfo               = tep_db_fetch_array($select_orderstatus);
            $param['gateway_status'] = $data['tid_status'];
            $param['amount']         = $request['amount'];
            if ($request['payment_id'] == 37)
                $param['callback_amount'] = $request['amount'];
            tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $request['tid'] . "'");

            if ($request['payment_id'] == 27) {
                $transaction_info = self::getNovalnetTransDetails($request['order_id']);
                $tables_sql       = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '"');
                while ($result = tep_db_fetch_array($tables_sql)) {
                    if ($result['table_name'] == 'novalnet_preinvoice_transaction_detail' && $transaction_info['payment_details'] == '') {
                        $accountInfo = self::getInvPrePaymentDetails($transaction_info['tid']);
                    }
                }
                $param['amount'] = $request['amount'];
                $trans_comments  = '';
                $trans_comments  = PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $request['tid'] . PHP_EOL;
                $trans_comments .= (($transaction_info['test_mode'] == 1) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL : '');

                $payment_ref     = $transaction_info['payment_ref'];
                list($transDetails, $bank_details) = NovalnetUtil::formInvoicePrepaymentComments(array(
                    'invoice_accountholder' => $accountInfo['account_holder'] ? $accountInfo['account_holder'] : $transaction_info['payment_details']['account_holder'],
                    'invoice_bankname'      => $accountInfo['bank_name'] ? $accountInfo['bank_name'] : $transaction_info['payment_details']['bank_name'],
                    'invoice_bankplace'     => $accountInfo['bank_city'] ? $accountInfo['bank_city'] : $transaction_info['payment_details']['bank_city'],
                    'amount'                => sprintf("%.2f", ($amount_change_request['amount'] / 100)),
                    'currency'              => $accountInfo['currency'] ? $accountInfo['currency'] : $transaction_info['payment_details']['currency'],
                    'tid'                   => $accountInfo['tid'] ? $accountInfo['tid'] : $transaction_info['payment_details']['tid'],
                    'invoice_iban'          => $accountInfo['bank_iban'] ? $accountInfo['bank_iban'] : $transaction_info['payment_details']['bank_iban'],
                    'invoice_bic'           => $accountInfo['bank_bic'] ? $accountInfo['bank_bic'] : $transaction_info['payment_details']['bank_bic'],
                    'due_date'              => $amount_change_request['due_date']
                ));
                $trans_comments .= $transDetails;
                if(empty($payment_ref)) {
                    $payment_ref = serialize(array('payment_reference1' => '1', 'payment_reference2' => '1', 'payment_reference3' => '1',));
                }
                $trans_comments .= NovalnetUtil::novalnetReferenceComments($payment_ref, $request['order_id'], $request['payment_name'], $request);
                $param['payment_details'] = serialize($bank_details);
            }
            $order_status = ($orderInfo['orders_status'] > 0) ? $orderInfo['orders_status'] : DEFAULT_ORDERS_STATUS_ID;
            tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $request['tid'] . "'");
            self::updateOrderStatus($request['order_id'], $order_status, $message . $trans_comments, true, true);
        } else {
            return (!empty($data['status_text']) ? $data['status_text'] : (!empty($data['status_desc']) ? $data['status_desc'] : (!empty($data['status_message']) ? $data['status_message'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR)));
        }
        return '';
    }

    /**
     * Perform transaction subscription stop process
     * @param $request
     *
     * @return string
     */
    static public function subscriptionTransStop($request)
    {
        $remote_ip  = (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $_SERVER['REMOTE_ADDR'] == '::1' || empty($_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $sql_val             = tep_db_fetch_array(tep_db_query('SELECT parent_tid FROM novalnet_subscription_detail where tid=' . $request['tid']));
        $parent_tid          = !empty($sql_val['parent_tid']) ? $sql_val['parent_tid'] : $request['tid'];
        $subscription_params = array(
            'vendor'        => $request['vendor'],
            'product'       => $request['product'],
            'key'           => $request['payment_id'],
            'tariff'        => $request['tariff_id'],
            'auth_code'     => $request['auth_code'],
            'cancel_sub'    => '1',
            'tid'           => $parent_tid,
            'cancel_reason' => $request['termination_reason'],
            'remote_ip'     => $remote_ip
        );
	
        $response = NovalnetUtil::doPaymentCall('https://payport.novalnet.de/paygate.jsp', $subscription_params);
        parse_str($response, $data);	
        $params['gateway_status'] = $data['tid_status'];	
        if ($data['status'] == 100) {
            $order_status_value = (MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS > 0) ? MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS : DEFAULT_ORDERS_STATUS_ID;
            $message            = PHP_EOL . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_MESSAGE) . $request['termination_reason'] . PHP_EOL;
            $db_query           = tep_db_query('SELECT order_no from novalnet_subscription_detail WHERE parent_tid = "' . $parent_tid . '"');
            while ($row = tep_db_fetch_array($db_query)) {
                self::updateOrderStatus($row['order_no'], $order_status_value, $message, true, true);
            }

            tep_db_perform('novalnet_transaction_detail', $params, "update", "tid='" . $parent_tid . "'");
            $param = array(
                'termination_reason' => $request['termination_reason'],
                'termination_at'     => date('Y-m-d h:i:s')
            );

            $subs_details = tep_db_fetch_array(tep_db_query('SELECT subs_id from novalnet_subscription_detail WHERE tid = "' . $parent_tid . '"'));
            tep_db_perform('novalnet_subscription_detail', $param, 'update', 'subs_id = "' . $subs_details['subs_id'] . '"');
            tep_db_perform(TABLE_ORDERS, array(
                'orders_status' => $order_status_value
            ), 'update', 'orders_id="' . $request['order_id'] . '"');
        } else {
            return (!empty($data['status_text']) ? $data['status_text'] : (!empty($data['status_desc']) ? $data['status_desc'] : (!empty($data['status_message']) ? $data['status_message'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR)));
        }
    }

    /**
     * Function to update order status as per Merchant selection
     * @param $order_id
     * @param $orders_status_id
     * @param $message
     * @param $insertstatushistory
     * @param $customer_notified
     *
     * @return boolean
     */
    static public function updateOrderStatus($order_id = '', $orders_status_id = '', $message = '', $insertstatushistory = true, $customer_notified = false)
    {
        $orders_status_id = ($orders_status_id > 0) ? $orders_status_id : DEFAULT_ORDERS_STATUS_ID;
        tep_db_perform(TABLE_ORDERS, array(
            'orders_status' => $orders_status_id
        ), 'update', 'orders_id="' . $order_id . '"');
        if ($insertstatushistory) {
            $customer_notified_status = (($customer_notified) ? 1 : 0);
            tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . "(orders_id, date_added, customer_notified, comments, orders_status_id) values ( " . $order_id . ", NOW(), " . $customer_notified_status . ", '" . $message . "', " . $orders_status_id . ")");
        }
        return true;
    }

    /**
     * Get Novalnet transaction information from novalnet_transaction_detail table
     * @param $order_no
     * @param $customer_id
     *
     * @return array
     */
    static public function getNovalnetTransDetails($order_no, $customer_id = false)
    {
        $select_query                 = ($customer_id == true) ? tep_db_query("SELECT customer_id FROM novalnet_transaction_detail WHERE order_no='" . tep_db_input($order_no) . "'") : tep_db_query("SELECT tid, vendor, product, tariff, auth_code, subs_id, payment_id, payment_type, amount, total_amount, gateway_status, date, test_mode, customer_id, zero_transaction, payment_details,payment_ref,callback_amount FROM novalnet_transaction_detail WHERE order_no='" . tep_db_input($order_no) . "'");
        $transInfo                    = tep_db_fetch_array($select_query);
        $transInfo['payment_details'] = unserialize($transInfo['payment_details']);

        return $transInfo;
    }

    /**
     * Get Novalnet Invoice/Prepayment transaction information from novalnet_preinvoice_transaction_detail table
     * @param $tid
     *
     * @return array
     */
    static public function getInvPrePaymentDetails($tid)
    {
        $table_sql = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '"
            AND table_name= "novalnet_preinvoice_transaction_detail"');
        $result    = tep_db_fetch_array($table_sql);
        if (empty($result))
            return false;

        $sql_query = tep_db_query("SELECT tid, order_no, test_mode, account_holder, account_number, bank_code, bank_name, bank_city, amount, currency, bank_iban, bank_bic, due_date FROM novalnet_preinvoice_transaction_detail WHERE tid='" . tep_db_input($tid) . "'");
        return tep_db_fetch_array($sql_query);
    }

}
?>
