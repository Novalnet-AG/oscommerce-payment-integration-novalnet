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
 * Script : novalnet_callback.php
 *
 */
chdir('../../../../../');
require('includes/application_top.php');
require(DIR_FS_CATALOG . 'admin/includes/classes/class.novalnet.php');
$currencies        = new currencies();
$request_params    = array_map('trim', $_REQUEST);
$process_testmode  = (MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE == 'True');
$process_debugmode = (MODULE_PAYMENT_NOVALNET_CALLBACK_DEBUG_MODE == 'True');

$nnvendor_script = new NovalnetVendorScript($request_params); // Novalnet Callback Class Object
if (!empty($request_params['vendor_activation'])) {
    tep_db_perform('novalnet_aff_account_detail', array(
        'vendor_id'       => $request_params['vendor_id'],
        'vendor_authcode' => $request_params['vendor_authcode'],
        'product_id'      => $request_params['product_id'],
        'product_url'     => $request_params['product_url'],
        'aff_accesskey'   => $request_params['aff_accesskey'],
        'activation_date' => (($request_params['activation_date'] != '') ? date('Y-m-d H:i:s', strtotime($request_params['activation_date'])) : ''),
        'aff_id'          => $request_params['aff_id'],
        'aff_authcode'    => $request_params['aff_authcode']
    ), 'insert');
    //Send notification mail to Merchant
    $nnvendor_script->sendNotifyMail(array(
        'comments' => 'Novalnet callback script executed successfully with Novalnet account activation information.',
        'order_no' => ''
    ), false);
    $nnvendor_script->displayMessage('Novalnet callback script executed successfully with Novalnet account activation information.' . PHP_EOL);
} else {
    $nntrans_history = $nnvendor_script->getOrderReference();
    include_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'languages/' . $nntrans_history['nn_order_lang'] . '/modules/payment/novalnet.php');
    $nncapture_params = $nnvendor_script->getCaptureParams(); // Collect callback capture parameters
    $paymentlevel     = $nnvendor_script->getPaymentTypeLevel();

    //Cancellation of a Subscription
    if (($nncapture_params['payment_type'] == 'SUBSCRIPTION_STOP' && $nncapture_params['status'] == 100) || ($nncapture_params['status'] != '100' && !empty($nncapture_params['subs_billing']) && $paymentlevel == 0)) {
        $nnvendor_script->subscriptionCancel($nntrans_history);
    }
    $status_check = ($nncapture_params['tid_status'] == '100' && $nncapture_params['status'] == '100');

    $formatted_amount = $currencies->format($nncapture_params['amount'] / 100, false, $nncapture_params['currency']);

    switch ($paymentlevel) {
        case 2: // Type of Creditentry payment and Collections available
            if ($status_check) {

                if (($nntrans_history['order_paid_amount'] + $nntrans_history['callback_old_amount']) < $nntrans_history['order_total_amount']) {

                    $callback_comments = PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS), $nncapture_params['shop_tid'], $formatted_amount, date('Y-m-d'), date('H:i:s'), $nncapture_params['tid']) . PHP_EOL;

                    $total_amount = $nntrans_history['order_paid_amount'] + $nntrans_history['callback_old_amount'] + $nncapture_params['amount'];

                    if ($nntrans_history['order_total_amount'] <= $total_amount) {

                        $callback_status_id = (!empty($nntrans_history['callback_script_status']) ? $nntrans_history['callback_script_status'] : 1);

                        $callback_greater_amount = ($total_amount > $nntrans_history['order_total_amount']) ? ' Paid amount is greater than Order amount.' : '';

                        //Update callback order status due to full payment
                        tep_db_perform(TABLE_ORDERS, array(
                            'orders_status' => $callback_status_id
                        ), 'update', 'orders_id="' . $nntrans_history['order_no'] . '"');

                    } else {

                        //Partial Payment paid
                        $callback_status_id = $nntrans_history['order_current_status'];
                    }

                    $nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $callback_status_id, $nntrans_history['order_no'], $callback_greater_amount, $total_amount);
                }

                $nnvendor_script->displayMessage('Novalnet callback received. Callback Script executed already. Refer Order :' . $nntrans_history['order_no']);
            }
            break;
        case 1: // Chargeback payment type
            if ($status_check) {

                $message = (in_array($nncapture_params['payment_type'], array('CREDITCARD_BOOKBACK', 'PAYPAL_BOOKBACK', 'PRZELEWY24_REFUND'))) ? NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_BOOKBACK_COMMENTS) : NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS);

                $callback_comments = PHP_EOL . sprintf($message, $nncapture_params['tid_payment'], $formatted_amount, date('Y-m-d'), date('H:i:s'), $nncapture_params['tid']) . PHP_EOL;

                $nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $nntrans_history['order_current_status'], $nntrans_history['order_no']);
            }
            break;
        case 0: // Type of payment available
            if ($nncapture_params['subs_billing'] == 1 && (in_array($nncapture_params['tid_status'], array('100', '90', '91','98', '99', '86', '85')))) {
                $order_comments = MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . ' ' . $nncapture_params['tid'] . PHP_EOL;
                $order_comments .= !empty($nncapture_params['test_mode']) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG . PHP_EOL.PHP_EOL : '';

                if (in_array($nncapture_params['payment_type'], array('INVOICE_START', 'PAYPAL')))
                    $nncapture_params['amount'] = 0;

                $callback_comments = PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_RECURRING_COMMENTS), $nncapture_params['shop_tid'], $formatted_amount, date('Y-m-d'), date('H:i:s'), $nncapture_params['tid']);

                $next_subs_cycle = !empty($nncapture_params['next_subs_cycle']) ? $nncapture_params['next_subs_cycle'] : (!empty($nncapture_params['paid_until']) ? $nncapture_params['paid_until'] : '');

                $callback_comments .= PHP_EOL . NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS) . $next_subs_cycle . PHP_EOL;

                $nnvendor_script->createOrder($nntrans_history['order_no'], $nntrans_history['language'], $order_comments);

                $order_status = constant(MODULE_PAYMENT_.strtoupper($nntrans_history['payment_type']). _ORDER_STATUS);
                if(in_array($nncapture_params['tid_status'], array('90', '85'))) {
                        if($nntrans_history['payment_type'] == 'novalnet_paypal') {
                            $order_status = MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS;
                        }
                        elseif($nntrans_history['payment_type'] == 'novalnet_invoice'){
                            $order_status = MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS;
                        }
                }

                $callback_comments .=  MODULE_PAYMENT_NOVALNET_REFERENCE_ORDER_TEXT . $nntrans_history['order_no'] . PHP_EOL;
				
                $nnvendor_script->sendNotifyMail(array('comments' => $callback_comments, 'order_no' => $nntrans_history['order_no']));

                $nnvendor_script->displayMessage($callback_comments);

            } elseif (in_array($nncapture_params['payment_type'], array('PAYPAL', 'PRZELEWY24')) && $nncapture_params['tid_status'] == 100) {
                if ($nntrans_history['callback_amount'] <= 0 ) {

                    $callback_comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS, $nncapture_params['shop_tid'], $formatted_amount, date('Y-m-d'), date('H:i:s')) . PHP_EOL;

                    tep_db_query("UPDATE novalnet_transaction_detail SET gateway_status=  " . $nncapture_params['tid_status'] . " where order_no=" . $nntrans_history['order_no']);

                    $order_status = (constant('MODULE_PAYMENT_NOVALNET_'.$nncapture_params['payment_type'].'_ORDER_STATUS') != '0' && constant('MODULE_PAYMENT_NOVALNET_'.$nncapture_params['payment_type'].'_ORDER_STATUS') != '') ? constant('MODULE_PAYMENT_NOVALNET_'.$nncapture_params['payment_type'].'_ORDER_STATUS') : DEFAULT_ORDERS_STATUS_ID;

					tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status= " . $order_status . " where orders_id=$nntrans_history[order_no]");
					
                    $nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $order_status, $nntrans_history['order_no']);
                }
                $nnvendor_script->displayMessage('Novalnet Callbackscript received. Order already Paid');

            } elseif ($nncapture_params['payment_type'] == 'PRZELEWY24' && $nncapture_params['tid_status'] != '86') {
                $message = $nnvendor_script->updatePrzelewyCancelcomments($nntrans_history);
                $nnvendor_script->displayMessage($message);
            } else {
                $nnvendor_script->displayMessage('Novalnet Callbackscript received. Payment type ( ' . $nncapture_params['payment_type'] . ' ) is not applicable for this process!');
            }
            break;
        DEFAULT:

            $nnvendor_script->displayMessage('Novalnet Callbackscript received. Payment type ( ' . $nncapture_params['payment_type'] . ' ) is not applicable for this process!');

            break;
    }
    $nnvendor_script->displayMessage(($nncapture_params['tid_status'] != '100' || $nncapture_params['status'] != '100') ? 'Novalnet callback received. Status is not valid.' : 'Novalnet callback received. Callback Script executed already.');

}
class NovalnetVendorScript
{

    /** @Array Type of payment available - Level : 0 */
    protected $payments = array('CREDITCARD', 'INVOICE_START', 'DIRECT_DEBIT_SEPA', 'GUARANTEED_INVOICE', 'PAYPAL', 'ONLINE_TRANSFER', 'IDEAL', 'EPS', 'GIROPAY', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'PRZELEWY24');

    /** @Array Type of Chargebacks available - Level : 1 */
    protected $chargebacks = array('RETURN_DEBIT_SEPA', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'PAYPAL_BOOKBACK', 'REFUND_BY_BANK_TRANSFER_EU', 'PRZELEWY24_REFUND', 'REVERSAL');

    /** @Array Type of Creditentry payment and Collections available - Level : 2 */
    protected $debit_collections = array('INVOICE_CREDIT',  'CREDIT_ENTRY_CREDITCARD', 'CREDIT_ENTRY_SEPA', 'DEBT_COLLECTION_SEPA', 'DEBT_COLLECTION_CREDITCARD', 'ONLINE_TRANSFER_CREDIT');

    protected $subscription = array('SUBSCRIPTION_STOP');

    protected $paymentgroups = array(
        'novalnet_cc'         => array('CREDITCARD', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'CREDIT_ENTRY_CREDITCARD', 'SUBSCRIPTION_STOP', 'DEBT_COLLECTION_CREDITCARD'),
        'novalnet_sepa'       => array('DIRECT_DEBIT_SEPA', 'RETURN_DEBIT_SEPA', 'SUBSCRIPTION_STOP', 'DEBT_COLLECTION_SEPA', 'CREDIT_ENTRY_SEPA', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'REFUND_BY_BANK_TRANSFER_EU'),
        'novalnet_ideal'      => array('IDEAL', 'REFUND_BY_BANK_TRANSFER_EU', 'ONLINE_TRANSFER_CREDIT', 'REVERSAL'),
        'novalnet_sofortbank' => array('ONLINE_TRANSFER', 'REFUND_BY_BANK_TRANSFER_EU', 'ONLINE_TRANSFER_CREDIT', 'REVERSAL'),
        'novalnet_paypal'     => array('PAYPAL', 'SUBSCRIPTION_STOP', 'PAYPAL_BOOKBACK', 'REFUND_BY_BANK_TRANSFER_EU'),
        'novalnet_prepayment' => array('INVOICE_START', 'INVOICE_CREDIT', 'SUBSCRIPTION_STOP'),
        'novalnet_invoice'    => array('INVOICE_START', 'INVOICE_CREDIT', 'SUBSCRIPTION_STOP', 'GUARANTEED_INVOICE'),
        'novalnet_eps'        => array('EPS', 'REFUND_BY_BANK_TRANSFER_EU'),
        'novalnet_giropay'    => array('GIROPAY', 'REFUND_BY_BANK_TRANSFER_EU'),
        'novalnet_przelewy24' => array('PRZELEWY24', 'PRZELEWY24_REFUND'));

    /** @Array Callback Capture parameters */
    protected $request_params = array();
    protected $paramsRequired = array();
    protected $affAccountActivationparams = array();
    /** @IP-address Novalnet IP, is a fixed value, Do not change !!!!! */
    protected $ipAllowed = array('195.143.189.210', '195.143.189.214');

    function __construct($arycapture = array())
    {
        $this->validateIpAddress();

        if (empty($arycapture)) {
            $this->displayMessage('Novalnet callback received. No params passed over!');
        }

        $this->paramsRequired                     = array('vendor_id', 'tid', 'payment_type', 'status', 'tid_status');
        $this->affAccountActivationparams = array('vendor_id', 'vendor_authcode', 'product_id', 'aff_id', 'aff_accesskey','aff_authcode');

        if (isset($arycapture['subs_billing']) && $arycapture['subs_billing'] == 1) {
            array_push($this->paramsRequired, 'signup_tid');

        }
        elseif (isset($arycapture['payment_type']) && in_array($arycapture['payment_type'], array_merge($this->chargebacks, $this->debit_collections))) {
            array_push($this->paramsRequired, 'tid_payment');
        }

        $this->arycaptureparams = $this->validateCaptureParams($arycapture);
    }

    /**
     * Return capture parameters
     *
     * @return array
     */
    function getCaptureParams()
    {
        return $this->arycaptureparams;
    }

    /**
     * Validate IP address
     *
     * @return none
     */
    function validateIpAddress()
    {
        global $process_testmode;
        $client_ip = tep_get_ip_address();
        if (!in_array($client_ip, $this->ipAllowed) && !$process_testmode) {

            $this->displayMessage("Novalnet callback received. Unauthorised access from the IP " . $client_ip, true);
        }
    }

    /**
     * Perform parameter validation process
     * @param $arycapture
     *
     * @return array
     */
    function validateCaptureParams($arycapture)
    {
        if (!isset($arycapture['vendor_activation'])) {
            foreach ($this->paramsRequired as $v) {
                if ($arycapture[$v] == '') {
                    $this->displayMessage('Required param ( ' . $v . '  ) missing!');
                }
                if (in_array($v, array('tid', 'tid_payment', 'signup_tid')) && !preg_match('/^\d{17}$/', $arycapture[$v])) {
                    $this->displayMessage('Novalnet callback received. Invalid TID [' . $arycapture[$v] . '] for Order.');
                }
            }

            if (!in_array($arycapture['payment_type'], array_merge($this->payments, $this->chargebacks, $this->debit_collections, $this->subscription))) {
                $this->displayMessage('Novalnet callback received. Payment type ( ' . $arycapture['payment_type'] . ' ) is mismatched!');
            }

            if ($arycapture['payment_type'] != 'SUBSCRIPTION_STOP' && (!is_numeric($arycapture['amount']) || $arycapture['amount'] < 0)) {
                $this->displayMessage('Novalnet callback received. The requested amount (' . $arycapture['amount'] . ') is not valid');
            }

            if ($arycapture['payment_type'] == 'SUBSCRIPTION_STOP' && ($arycapture['status'] != 100 || $arycapture['tid_status'] != 100)) {
                $status = ($arycapture['status'] != 100) ? $arycapture['status'] : $arycapture['tid_status'];
                $this->displayMessage('Novalnet callback received. The status (' . $status . ') is not valid');
            }

            if (!empty($arycapture['signup_tid'])) { // Subscription
                $arycapture['shop_tid'] = $arycapture['signup_tid'];
            }
            elseif (in_array($arycapture['payment_type'], $this->chargebacks) || ($arycapture['payment_type'] == 'INVOICE_CREDIT')) {
                $arycapture['shop_tid'] = $arycapture['tid_payment'];
            } else {
                $arycapture['shop_tid'] = $arycapture['tid'];
            }
        } else {
            foreach ($this->affAccountActivationparams as $v) {
                if (empty($arycapture[$v])) {
                    $this->displayMessage('Required param ( ' . $v . '  ) missing!');
                }
            }
        }
        return $arycapture;
    }

    /**
     * Update Callback comments in orders_status_history table
     * @param $datas
     *
     * @return none
     */
    function updateCallbackComments($datas)
    {
        $comments = ((!empty($datas['comments'])) ? $datas['comments'] : '');
        tep_db_query("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) VALUES ('" . $datas['order_no'] . "', '" . $datas['orders_status_id'] . "', NOW(), '1','$comments')");
    }

    /**
     * Log callback process in novalnet_callback_history table
     * @param $datas
     * @param $order_no
     * @param $total_amount
     *
     * @return none
     */
    function logCallbackProcess($datas, $order_no, $total_amount)
    {
        if (!empty($datas['amount'])) {
            if(in_array($datas['payment_type'], array('CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'RETURN_DEBIT_SEPA'))) {
                $trans_details   = $this->getOrderReference();
                $datas['amount'] = $trans_details['order_total_amount'];
            }
            else {
                $datas['amount'] = !empty($total_amount) ? $total_amount : $datas['amount'];
            }
            tep_db_query("UPDATE novalnet_transaction_detail SET callback_amount= " . $datas['amount'] . " where order_no=$order_no");
        }
    }

    /**
     * Display message
     * @param $error_msg
     * @param $force_display
     *
     * @return none
     */
    function displayMessage($error_msg, $force_display = false)
    {
        global $process_debugmode;
        if ($process_debugmode || $force_display)
            echo htmlentities($error_msg);
        exit;
    }

    /**
     * Get given payment_type level for process
     *
     * @return integer
     */
    function getPaymentTypeLevel()
    {
        if (in_array($this->arycaptureparams['payment_type'], $this->payments))
            return 0;
        if (in_array($this->arycaptureparams['payment_type'], $this->chargebacks))
            return 1;
        if (in_array($this->arycaptureparams['payment_type'], $this->debit_collections))
            return 2;
    }

    /**
     * Get order reference from the novalnet_transaction_detail table on shop database
     *
     * @return array
     */
    function getOrderReference()
    {
        $select_query = tep_db_query("SELECT order_no, amount, payment_id, payment_type,language,callback_amount from novalnet_transaction_detail where tid = '" . tep_db_input($this->arycaptureparams['shop_tid']) . "'");
        $dbVal        = tep_db_fetch_array($select_query);
        $dbVal['tid'] = $this->arycaptureparams['shop_tid'];
        if (!empty($dbVal)) {
            if(is_array($this->paymentgroups[$dbVal['payment_type']])) {
                if (!in_array($this->arycaptureparams['payment_type'], $this->paymentgroups[$dbVal['payment_type']])) {
                    $this->displayMessage('Novalnet callback received. Payment Type [' . $this->arycaptureparams['payment_type'] . '] is not valid.');
                }
            }
            if (!empty($this->arycaptureparams['order_no']) && $this->arycaptureparams['order_no'] != $dbVal['order_no']) {
                $this->displayMessage('Novalnet callback received. Order Number is not valid.');
            }
            $dbVal['nn_order_lang']        = $dbVal['language'];
            $dbVal['order_current_status'] = $this->getOrderCurrentStatus($dbVal['order_no']);
            if (in_array($dbVal['payment_type'], array('novalnet_invoice', 'novalnet_prepayment'))) {
                $tables_sql = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '" AND table_name= "novalnet_callback_history"');
                $result     = tep_db_fetch_array($tables_sql);
                if (empty($dbVal['callback_amount']) && $result['table_name'] == 'novalnet_callback_history') {
                    $order_totalqry        = tep_db_query("select sum(amount) as amount_total from novalnet_callback_history where order_no = '" . tep_db_input($dbVal['order_no']) . "'");
                    $result                = tep_db_fetch_array($order_totalqry);
                    $callback_amount_value = $result['amount_total'];
                }
                $dbVal['callback_script_status'] = constant('MODULE_PAYMENT_' . strtoupper($dbVal['payment_type']) . '_CALLBACK_ORDER_STATUS');
            }
            $dbVal['subscription_cancel_status'] = MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS > 0 ? MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS : DEFAULT_ORDERS_STATUS_ID;
            $dbVal['order_total_amount']         = $dbVal['amount'];
            $dbVal['order_paid_amount']          = 0;
            $payment_type_level                  = $this->getPaymentTypeLevel();
            if (in_array($payment_type_level, array(0, 2))) {
                $dbVal['callback_old_amount'] = isset($callback_amount_value) ? $callback_amount_value : 0;
                $dbVal['order_paid_amount']   = isset($dbVal['callback_amount']) ? $dbVal['callback_amount'] : 0;
            }
        } else {
            $this->displayMessage('Transaction mapping failed');
        }
        return $dbVal;
    }

    /**
     * Get orders_status from the orders table on shop database
     * @param $order_id
     *
     * @return array
     */
    function getOrderCurrentStatus($order_id = '')
    {
        $select_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . $order_id . "'");
        $dbVal        = tep_db_fetch_array($select_query);
        return ((!empty($dbVal['orders_status'])) ? $dbVal['orders_status'] : DEFAULT_ORDERS_STATUS_ID);
    }

    /**
     * Send notification mail to Merchant
     * @param $datas
     * @param $order_detail
     *
     * @return boolean
     */
    function sendNotifyMail($datas = array(), $order_detail = true)
    {
        if (MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND == 'True') {
            $email_to      = ((MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO != '') ? MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO : STORE_OWNER_EMAIL_ADDRESS);
            $email_bcc     = ((MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC != '') ? MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC : '');
            $email_subject = 'Novalnet Callback script notification'; // Mail subject
            $email_content = PHP_EOL . $datas['comments'];
            if (!empty($email_bcc)) {
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type:text/html;charset=' . CHARSET . "\r\n";
                $headers .= 'From: <' . EMAIL_FROM . '>' . "\r\n" . 'Bcc: ' . $email_bcc . "\r\n";
                mail($email_to, $email_subject, $email_content, $headers);
            } else {
                tep_mail(STORE_OWNER, $email_to, $email_subject, $email_content, STORE_NAME, EMAIL_FROM);
            }
            return true;
        }
        return false;
    }

    /**
     * Update the comments and displaying message
     * @param $nncapture_params
     * @param $tid
     * @param $comments
     * @param $callback_status_id
     * @param $order_id
     * @param $callback_greater_amount
     * @param $total_amount
     *
     * @return none
     */
    function updatefinalcomments($nncapture_params, $comments, $callback_status_id, $order_id, $callback_greater_amount = '', $total_amount = '')
    {
        $this->updateCallbackComments(array('order_no' => $order_id, 'comments' => $comments, 'orders_status_id' => $callback_status_id));
        $this->sendNotifyMail(array('comments' => $comments, 'order_no' => $order_id));
        $this->logCallbackProcess($nncapture_params, $order_id, $total_amount);
        $this->displayMessage($comments . $callback_greater_amount);
    }

    /**
     * Subscription cancel process
     * @param $nntrans_history
     *
     * @return none
     */
    function subscriptionCancel($nntrans_history)
    {
        $message = !empty($this->arycaptureparams['status_text']) ? $this->arycaptureparams['status_text'] : (!empty($this->arycaptureparams['status_desc']) ? $this->arycaptureparams['status_desc'] : (!empty($this->arycaptureparams['status_message']) ? $this->arycaptureparams['status_message'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR));

        $nn_comment = !empty($this->arycaptureparams['termination_reason']) ? $this->arycaptureparams['termination_reason'] : $message;

        $callback_comments = PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_STOP_COMMENTS), $this->arycaptureparams['shop_tid'], date('Y-m-d'), date('H:i:s'));
        $callback_comments .= PHP_EOL . MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_REASON_TEXT . $nn_comment . PHP_EOL;
        $order_status = !empty($nntrans_history['subscription_cancel_status']) ? $nntrans_history['subscription_cancel_status'] : DEFAULT_ORDERS_STATUS_ID;


        $param        = array('termination_reason' => $nn_comment, 'termination_at' => date('Y-m-d H:i:s'));
        $subs_details = tep_db_fetch_array(tep_db_query('SELECT subs_id from novalnet_subscription_detail WHERE tid = "' . $this->arycaptureparams['signup_tid'] . '"'));
        tep_db_perform('novalnet_subscription_detail', $param, 'update', 'subs_id = "' . $subs_details['subs_id'] . '"');

        $subs_orders = tep_db_query('SELECT order_no from novalnet_subscription_detail WHERE subs_id = "' . $subs_details['subs_id'] . '"');

        while ($subs_order = tep_db_fetch_array($subs_orders)) {
            $orders_details = array('orders_id' => $subs_order['order_no'], 'orders_status_id' => $order_status, 'date_added' => 'now()','customer_notified' => 1, 'comments' => $callback_comments);
            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $orders_details);

            tep_db_query('UPDATE ' . TABLE_ORDERS . ' SET orders_status = "' . $order_status . '" WHERE orders_id = "' . $orders_details['orders_id'] . '"');
        }

        $this->displayMessage($callback_comments);
    }

    /**
     * Create the new order for the subscription recurring
     * @param $order_id
     * @param $nn_language
     * @param $order_comments
     *
     * @return none
     */
    function createOrder($order_id,  $nn_language, $order_comments = '')
    {
        $select_query = tep_db_query("SELECT * FROM " . TABLE_ORDERS . " where orders_id = " . tep_db_input($order_id));
        $orderArray   = tep_db_fetch_array($select_query);
		
		
        unset($orderArray['orders_id']);
        $orderArray['date_purchased'] = $orderArray['last_modified'] = date("Y-m-d H:i:s");
        tep_db_perform(TABLE_ORDERS, $orderArray, 'insert');
        $orderid = tep_db_insert_id();

        $orderTotalQry = tep_db_query("SELECT title, text, value, class, sort_order FROM " . TABLE_ORDERS_TOTAL . " where orders_id = " . tep_db_input($order_id));
        while ($orderTotalArray = tep_db_fetch_array($orderTotalQry)) {
            $orderTotalArray['orders_id'] = $orderid;
            tep_db_perform(TABLE_ORDERS_TOTAL, $orderTotalArray);
        }
        $orderProductsQry = tep_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " where orders_id = " . tep_db_input($order_id));
        while ($orderProductsArray = tep_db_fetch_array($orderProductsQry)) {
            unset($orderProductsArray['orders_id']);
            $orderProductsId = $orderProductsArray['orders_products_id'];
            unset($orderProductsArray['orders_products_id']);
            $orderProductsArray['orders_id'] = $orderid;
            tep_db_perform(TABLE_ORDERS_PRODUCTS, $orderProductsArray);

            $orders_products_id = tep_db_insert_id();
            $productsQry        = tep_db_query("select products_quantity, products_ordered from " . TABLE_PRODUCTS . " where products_id = " . tep_db_input($orderProductsArray['products_id']));
            $productsArray      = tep_db_fetch_array($productsQry);
            $productsQuantity   = $productsArray['products_quantity'] - $orderProductsArray['products_quantity'];
            $productsOrdered    = $productsArray['products_ordered'] + $orderProductsArray['products_quantity'];
            ($productsQuantity < 1) ? tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $productsQuantity . "', products_ordered = '" . $productsOrdered . "', products_status = '0' where products_id = '" . $orderProductsArray['products_id'] . "'") : tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $productsQuantity . "', products_ordered = '" . $productsOrdered . "' where products_id = '" . $orderProductsArray['products_id'] . "'");
            $orderProductsAttrQry = tep_db_query("SELECT products_options, products_options_values, options_values_price, price_prefix FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = " . tep_db_input($order_id) . " AND orders_products_id=" . tep_db_input($orderProductsId));
            while ($orderProductsAttrArray = tep_db_fetch_array($orderProductsAttrQry)) {
                $orderProductsAttrArray['orders_id']          = $orderid;
                $orderProductsAttrArray['orders_products_id'] = $orderProductsId;
                tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $orderProductsAttrArray);
            }
            if (tep_db_num_rows(tep_db_query('SHOW TABLES LIKE "' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . '"'))) {
                $orderProductsDownQry = tep_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " WHERE orders_id = " . tep_db_input($order_id) . " AND orders_products_id=" . tep_db_input($orderProductsId));
                while ($orderProductsDownArray = tep_db_fetch_array($orderProductsDownQry)) {
                    $orderProductsDownArray['orders_id']          = $orderid;
                    $orderProductsDownArray['orders_products_id'] = $orders_products_id;
                    tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $orderProductsDownArray);
                }
            }
        }
        $nnTransDetails  = $this->getFullOrderDetails($order_id);
		
        $payment_name    = strtoupper($nnTransDetails['payment_type']);
        $newOrderStatus  = constant('MODULE_PAYMENT_' . $payment_name . '_ORDER_STATUS');
        $newOrderStatus  = !empty($newOrderStatus) ? $newOrderStatus : DEFAULT_ORDERS_STATUS_ID;
        if(in_array($this->arycaptureparams['tid_status'],  array('90', '85'))) {
            if($nnTransDetails['payment_type'] == 'novalnet_paypal') {
                $newOrderStatus = (MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS != '' && MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS != '0') ? MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS : DEFAULT_ORDERS_STATUS_ID;
            }
            elseif($nnTransDetails['payment_type'] == 'novalnet_invoice') {
                $newOrderStatus = (MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS != '' && MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS != '0') ? MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS : DEFAULT_ORDERS_STATUS_ID;
            }
        }
		if($nnTransDetails['payment_id'] == 41) {
			$newOrderStatus = MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS;
		}
        $next_subs_cycle = !empty($this->arycaptureparams['next_subs_cycle']) ? $this->arycaptureparams['next_subs_cycle'] : (!empty($this->arycaptureparams['paid_until']) ? $this->arycaptureparams['paid_until'] : '');
        tep_db_perform(TABLE_ORDERS, array('orders_status' => $newOrderStatus), 'update', 'orders_id="' . $orderid . '"');
        $order_comments .= (in_array($nnTransDetails['payment_type'], array(
            'novalnet_invoice',
            'novalnet_prepayment'
        ))) ? $this->getBankdetails() : '';
        $order_comments .= PHP_EOL. MODULE_PAYMENT_NOVALNET_REFERENCE_ORDER_TEXT . $order_id . PHP_EOL;
        $order_comments .= NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS) . $next_subs_cycle . PHP_EOL;
        $this->insertUpdateShopDetails(array(
            'order_status' => $newOrderStatus,
            'tid'          => $this->arycaptureparams['tid'],
            'total'        => $this->arycaptureparams['amount'],
            'total_amount' => $this->arycaptureparams['amount'],
            'order_no'     => $orderid
        ), $nnTransDetails, $nn_language);
        if (!empty($order_comments)) {
            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, array(
                'orders_id'         => $orderid,
                'orders_status_id'  => $newOrderStatus,
                'date_added'        => date("Y-m-d H:i:s"),
                'customer_notified' => 1,
                'comments'          => PHP_EOL.$order_comments
            ));
            tep_db_perform(TABLE_ORDERS, array('orders_status' => $newOrderStatus), 'update', 'orders_id="' . $order_id . '"');
        }
        tep_db_perform('novalnet_subscription_detail', array(
            'order_no'           => $orderid,
            'subs_id'            => $nnTransDetails['subs_id'],
            'tid'                => $this->arycaptureparams['tid'],
            'parent_tid'         => $this->arycaptureparams['shop_tid'],
            'signup_date'        => date('Y-m-d H:i:s'),
            'termination_reason' => $this->arycaptureparams['termination_reason'],
            'termination_at'     => ''
        ));
    }

    /**
     * Get all transaction details
     * @param $order_no
     *
     * @return array
     */
    function getFullOrderDetails($order_no)
    {
        $select_query = tep_db_query("SELECT vendor, auth_code, product, tariff, subs_id, payment_id, payment_type, gateway_status, customer_id, process_key FROM novalnet_transaction_detail where order_no = " . tep_db_input($order_no));
        return tep_db_fetch_array($select_query);
    }

    /**
     * Get Invoice / Prepayment bank details
     *
     * @return array
     */
    function getBankdetails()
    {
        $currencies        = new currencies();
        $novalnet_comments = NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_INVOICE_COMMETNS_PARAGRAPH) . PHP_EOL;
        $novalnet_comments .= NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_DUE_DATE) . ': ' . date(DATE_FORMAT, strtotime(!empty($this->arycaptureparams['due_date']) ? $this->arycaptureparams['due_date'] : '')) . PHP_EOL;
        $amount = (($currencies->format($this->arycaptureparams['amount'] / 100, false, (!empty($this->arycaptureparams['currency']) ? $this->arycaptureparams['currency'] : ''))));
        $novalnet_comments .= MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER . ': NOVALNET AG' . PHP_EOL;
        $novalnet_comments .= 'IBAN: ' . (!empty($this->arycaptureparams['invoice_iban']) ? $this->arycaptureparams['invoice_iban'] : '') . PHP_EOL;
        $novalnet_comments .= 'BIC: ' . (!empty($this->arycaptureparams['invoice_bic']) ? $this->arycaptureparams['invoice_bic'] : '') . PHP_EOL;
        $novalnet_comments .= 'Bank: ' . (!empty($this->arycaptureparams['invoice_bankname']) ? trim($this->arycaptureparams['invoice_bankname']) : '') . ' ' . (!empty($this->arycaptureparams['invoice_bankplace']) ? trim($this->arycaptureparams['invoice_bankplace']) : '') . PHP_EOL;
        $novalnet_comments .= MODULE_PAYMENT_NOVALNET_AMOUNT . ': ' . $amount;

        return $novalnet_comments;
    }

    /**
     * Insert the transaction details into the shop system.
     * @param $shopInfo
     * @param $nnTransDetails
     * @param $nn_language
     *
     * @return none
     */
    function insertUpdateShopDetails($shopInfo, $nnTransDetails, $nn_language)
    {
        $nnTransDetails['tid']             = $shopInfo['tid'];
        $nnTransDetails['gateway_status']  = $this->arycaptureparams['tid_status'];
        $nnTransDetails['amount']          = $shopInfo['total'];
        $nnTransDetails['total_amount']    = $shopInfo['total'];
        $nnTransDetails['callback_amount'] = 0;
        $nnTransDetails['date']            = date('Y-m-d H:i:s');
        $nnTransDetails['language']        = $nn_language;
        $nnTransDetails['currency']        = isset($this->arycaptureparams['currency']) ? $this->arycaptureparams['currency'] : '';
        $nnTransDetails['test_mode']       = isset($this->arycaptureparams['test_mode']) ? $this->arycaptureparams['test_mode'] : 0;
        $nnTransDetails['order_no']        = $shopInfo['order_no'];
        $nnTransDetails['callback_amount'] = (in_array($nnTransDetails['payment_type'], array('novalnet_invoice', 'novalnet_prepayment')) || ($nnTransDetails['payment_type'] == 'novalnet_paypal' && $this->arycaptureparams['tid_status'] == 90)) ?   0: $this->arycaptureparams['amount'];

        tep_db_perform('novalnet_transaction_detail', $nnTransDetails);
    }

    /**
     * Update Przelewy24 cancel status
     *
     * @param $nntrans_history
     * @return string
     */
    function updatePrzelewyCancelcomments($nntrans_history)
    {
        $nncapture_params   = $this->getCaptureParams();
        $callback_status_id = (!empty($nntrans_history['callback_script_status']) ? $nntrans_history['callback_script_status'] : DEFAULT_ORDERS_STATUS_ID);

        // Assign przelewy24 payment status
        tep_db_perform(TABLE_ORDERS, array(
            'orders_status' => $callback_status_id
        ), 'update', 'orders_id="' . $nntrans_history['order_no'] . '"');

        // Form failure comments
        $comments          = !empty($nncapture_params['status_text']) ? PHP_EOL . $nncapture_params['status_text'] : (!empty($nncapture_params['status_desc']) ? PHP_EOL . $nncapture_params['status_desc'] : (!empty($nncapture_params['status_message']) ? PHP_EOL . $nncapture_params['status_message'] : ''));
        $callback_comments = 'The transaction has been canceled due to:' . $comments;

        $this->updateCallbackComments(array('order_no' => $nntrans_history['order_no'], 'comments' => $callback_comments,
        'orders_status_id' => $callback_status_id));

        return $callback_comments;
    }
}
?>
