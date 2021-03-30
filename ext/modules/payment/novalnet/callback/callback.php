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
 * Script : callback.php
 *
 */
 
chdir('../../../../../');
require('includes/application_top.php');
$currencies        = new currencies();
$request_params    = array_map('trim', $_REQUEST);
$process_testmode  = (MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE == 'True');
$nnvendor_script = new NovalnetVendorScript($request_params); // Novalnet Callback Class Object

$nntrans_history = $nnvendor_script->getOrderReference();

require(DIR_WS_NOVALNET_ADMIN . 'includes/classes/class.novalnet.php');
$nncapture_params = $nnvendor_script->getCaptureParams(); // Collect callback capture parameters
$paymentlevel     = $nnvendor_script->getPaymentTypeLevel();

$status_check = ($nncapture_params['tid_status'] == '100' && $nncapture_params['status'] == '100');

$formatted_amount = $currencies->format($nncapture_params['amount'] / 100, false, $nncapture_params['currency']);

switch ($paymentlevel) {
	case 2: // Type of Creditentry payment and Collections available
		if ($status_check) {
			
			if( in_array($nncapture_params['payment_type'], array('INVOICE_CREDIT', 'CASHPAYMENT_CREDIT'))) {
				
				$total_amount = $nntrans_history['order_paid_amount'] + $nntrans_history['callback_old_amount'] + $nncapture_params['amount'];
				
				if ($total_amount <= $nntrans_history['order_total_amount']) {
					$callback_comments = PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS), $nncapture_params['tid_payment'], $formatted_amount, date('Y-m-d'), date('H:i:s'), $nncapture_params['shop_tid']) . PHP_EOL;
					
					if ($total_amount == $nntrans_history['order_total_amount']) {
						$callback_status_id = (!empty($nntrans_history['callback_script_status']) ? $nntrans_history['callback_script_status'] : 1);
					} else {
						$callback_status_id = (!empty($nntrans_history['order_current_status']) ? $nntrans_history['order_current_status'] : 1);
					}
					
					$nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $callback_status_id, $nntrans_history['order_no'], '', $total_amount);
				} else {
					$nnvendor_script->displayMessage('Callback script executed already. Refer order: ' . $nntrans_history['order_no']);
				}
			} else {
				$callback_comments = PHP_EOL . sprintf(NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS), $nncapture_params['tid_payment'], $formatted_amount, date('Y-m-d'), date('H:i:s'), $nncapture_params['shop_tid']) . PHP_EOL;
				
				$callback_status_id = $nntrans_history['order_current_status'];
				
				$nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $callback_status_id, $nntrans_history['order_no']);
			}
		}
		break;
	case 1: // Chargeback payment type
		if ($status_check) {
			
			$message = (in_array($nncapture_params['payment_type'], array('GUARANTEED_SEPA_BOOKBACK','GUARANTEED_INVOICE_BOOKBACK','CREDITCARD_BOOKBACK', 'PAYPAL_BOOKBACK', 'PRZELEWY24_REFUND','REFUND_BY_BANK_TRANSFER_EU','CASHPAYMENT_REFUND'))) ? NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_BOOKBACK_COMMENTS) : NovalnetUtil::setUTFText(MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS);  
			$callback_comments = PHP_EOL . sprintf($message, $nncapture_params['tid_payment'], $formatted_amount, date('Y-m-d'), date('H:i:s'), $nncapture_params['tid']) . PHP_EOL;
			$nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $nntrans_history['order_current_status'], $nntrans_history['order_no']);                
		}
		break;
	case 0: // Type of payment available
		if(($nncapture_params['payment_type'] =='PAYPAL') &&  in_array( $nntrans_history['gateway_status'] , array(85,90))){
			$order_status = MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS;
			$callback_comments .= PHP_EOL.sprintf(MODULE_PAYMENT_NOVALNET_GUARANTEE_TRANS_CONFIRM_SUCCESSFUL_MESSAGE, date(DATE_FORMAT, strtotime(date('d.m.Y'))),date('H:i:s')).PHP_EOL;
			$nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $order_status, $nntrans_history['order_no']);
				   
		} elseif (in_array($nncapture_params['payment_type'], array('PAYPAL', 'PRZELEWY24')) && $nncapture_params['tid_status'] == 100) {
			if ($nntrans_history['callback_amount'] <= 0 ) {

				$callback_comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS, $nncapture_params['shop_tid'], $formatted_amount, date('Y-m-d'), date('H:i:s')) . PHP_EOL;

				tep_db_query("UPDATE novalnet_transaction_detail SET gateway_status=  " . $nncapture_params['tid_status'] . " where order_no=" . $nntrans_history['order_no']);

				$order_status = (constant('MODULE_PAYMENT_NOVALNET_'.$nncapture_params['payment_type'].'_ORDER_STATUS') != '0' && constant('MODULE_PAYMENT_NOVALNET_'.$nncapture_params['payment_type'].'_ORDER_STATUS') != '') ? constant('MODULE_PAYMENT_NOVALNET_'.$nncapture_params['payment_type'].'_ORDER_STATUS') : DEFAULT_ORDERS_STATUS_ID;

				tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status= " . $order_status . " where orders_id=$nntrans_history[order_no]");
				
				$nnvendor_script->updatefinalcomments($nncapture_params, $callback_comments, $order_status, $nntrans_history['order_no']);
			}
			$nnvendor_script->displayMessage('Callback script executed already. Refer order: ' . $nntrans_history['order_no']);

		} elseif ($nncapture_params['payment_type'] == 'PRZELEWY24' && $nncapture_params['tid_status'] != '86') {
			$message = $nnvendor_script->updatePrzelewyCancelcomments($nntrans_history);
			$nnvendor_script->displayMessage($message);
		
		} else if(in_array($nncapture_params['payment_type'],array('GUARANTEED_INVOICE','GUARANTEED_DIRECT_DEBIT_SEPA','INVOICE_START','DIRECT_DEBIT_SEPA', 'CREDITCARD')) && in_array($nncapture_params['tid_status'], array(91,98,99,100)) && $nncapture_params['status'] == 100 && in_array($nntrans_history['gateway_status'] ,array(75,91,98,99))){
			
			$param = array();
			$callback_comments = $transaction_comments =  $transactionCommentsForm = '';
			$test_mode_text     = ($nncapture_params['test_mode'] == 1) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG : '';
			if(in_array($nncapture_params['payment_type'],array('GUARANTEED_INVOICE','GUARANTEED_DIRECT_DEBIT_SEPA'))){
				if(in_array($nncapture_params['payment_type'],array('GUARANTEED_INVOICE')) && $nncapture_params['tid_status'] == '100'){
					$transaction_comments .= MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID;
					$nn_comments = PHP_EOL . $transaction_comments . $nncapture_params['shop_tid'] . PHP_EOL . $test_mode_text.PHP_EOL;
				}
			}
			if( in_array($nncapture_params['tid_status'],array(99,91)) && $nntrans_history['gateway_status'] == 75){
				$order_status = constant('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE') > 0 ? constant('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE') : DEFAULT_ORDERS_STATUS_ID;
				$callback_comments .= PHP_EOL. sprintf(MODULE_PAYMENT_GUARANTEE_PAYMENT_PENDING_TO_HOLD_MESSAGE, $nncapture_params['shop_tid'],date(DATE_FORMAT, strtotime(date('d.m.Y'))), date('H:i:s')). PHP_EOL;
			} else if($nncapture_params['tid_status'] == 100 && in_array( $nntrans_history['gateway_status'], array(75,98,85,91,99))){
				
				if(in_array ($nncapture_params['payment_type'] , array('INVOICE_CREDIT', 'GUARANTEED_INVOICE')) &&  in_array( $nntrans_history['gateway_status'] , array(75,91))){
					$order_status =  ((constant('MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS') > 0) ? constant('MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS') : DEFAULT_ORDERS_STATUS_ID);
				} elseif($nncapture_params['payment_type'] == 'INVOICE_START' && $nntrans_history['gateway_status'] == 91){
					$order_status =  ((constant('MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS') > 0) ? constant('MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS') : DEFAULT_ORDERS_STATUS_ID);
				}elseif(in_array ($nncapture_params['payment_type'] , array('DIRECT_DEBIT_SEPA' ,'GUARANTEED_DIRECT_DEBIT_SEPA')) && in_array( $nntrans_history['gateway_status'],array(75,99))) {
					$order_status = ((constant('MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS') > 0) ? constant('MODULE_PAYMENT_NOVALNET_SEPA_ORDER_STATUS') : DEFAULT_ORDERS_STATUS_ID);
				} elseif(in_array($nncapture_params['payment_type'], array('PAYPAL', 'CREDITCARD')) &&  in_array( $nntrans_history['gateway_status'] , array(85,98))){
					$order_status = constant('MODULE_PAYMENT_'.strtoupper($nntrans_history['payment_type']).'_ORDER_STATUS') > 0 ? constant('MODULE_PAYMENT_'.strtoupper($nntrans_history['payment_type']).'_ORDER_STATUS') : DEFAULT_ORDERS_STATUS_ID;
				} 
				
				$callback_comments .= PHP_EOL.sprintf(MODULE_PAYMENT_NOVALNET_GUARANTEE_TRANS_CONFIRM_SUCCESSFUL_MESSAGE, date(DATE_FORMAT, strtotime(date('d.m.Y'))),date('H:i:s')).PHP_EOL;
			}
			if(in_array($nncapture_params['tid_status'],array(100,91)) && in_array($nncapture_params['payment_type'],array('GUARANTEED_INVOICE','INVOICE_START')) && in_array($nntrans_history['gateway_status'], array(75, 91))){

					$serialize_data = unserialize($nntrans_history['payment_details']);
					
					list($transactionCommentsForm)  = NovalnetUtil::formInvoicePrepaymentComments(array(
						'invoice_account_holder'   => $serialize_data['account_holder'],
						'invoice_bankname'         => $serialize_data['bank_name'],
						'invoice_bankplace'        => $serialize_data['bank_city'],
						'amount'                   => sprintf("%.2f", ($serialize_data['amount'] / 100)),
						'currency'                 => $serialize_data['currency'],
						'tid'                      => $serialize_data['tid'],
						'invoice_iban'             => $serialize_data['bank_iban'],
						'invoice_bic'              => $serialize_data['bank_bic'],
						'due_date'                 => !empty($nncapture_params['due_date']) ? $nncapture_params['due_date'] : '',
						'payment_id'               => $nntrans_history['payment_id'],
						'tid_status'               => $nncapture_params['tid_status']
						)); 
					$vendor_details = array('product'=>$nncapture_params['product_id'],
											'order_no'=> $nntrans_history['order_no'],
											'tid'=> $nncapture_params['shop_tid'],
					);
					 
					 // Form payment reference comments
					$transactionCommentsForm .= NovalnetUtil::novalnetReferenceComments($nntrans_history['order_no'],$nntrans_history['payment_type'], $vendor_details);
					$param['payment_details'] = serialize($serialize_data);
					
					
					NovalnetUtil::guarantee_mail(array(
						'comments' => '<br>' . PHP_EOL. $callback_comments.PHP_EOL.$nn_comments.PHP_EOL.$transactionCommentsForm,
						'order_no' => $nntrans_history['order_no'],
					));
				}
		   
			$param ['gateway_status'] = $nncapture_params['tid_status'];
			$order_status = (($order_status > 0) ? $order_status : DEFAULT_ORDERS_STATUS_ID);
			tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $nncapture_params['shop_tid'] . "'");
			
			// To update order details in shop
			$nnvendor_script->updateCallbackComments(array(
			'order_no'        => $nntrans_history['order_no'],
			'orders_status_id' => $order_status,
			'comments'         => $callback_comments. $nn_comments. $transactionCommentsForm
			));
			// Send notification mail to Merchant
			$nnvendor_script->sendNotifyMail(array(
				'comments'      => $callback_comments,
				'order_no'      => $nntrans_history['order_no'],
			));
			$nnvendor_script->displayMessage($callback_comments. $nn_comments. $transactionCommentsForm);
		 } else if(in_array($nncapture_params['payment_type'],array('GUARANTEED_INVOICE','GUARANTEED_DIRECT_DEBIT_SEPA')) && $nncapture_params['tid_status'] != 100 && $nncapture_params['status'] != 100 && in_array($nntrans_history['gateway_status'], array(75,91,99))){
			 
			// To form the server status message
			$callback_comments = '';
			$test_mode_text     = ($nncapture_params['test_mode'] == 1) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE : '';
			$callback_comments .= MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS . PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID.$nncapture_params['shop_tid'].PHP_EOL.$test_mode_text.PHP_EOL;
			$callback_comments .= PHP_EOL.sprintf(MODULE_PAYMENT_GUARANTEE_PAYMENT_CANCELLED_MESSAGE,date(DATE_FORMAT, strtotime(date('d.m.Y'))), date('H:i:s')) . PHP_EOL;
			$param ['gateway_status'] = $nncapture_params['tid_status'];
			tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $nncapture_params['shop_tid'] . "'");
			
			// Update callback comments in order status history table
			
			// To update order details in shop
			$nnvendor_script->updateCallbackComments(array(
			'order_no'        => $nntrans_history['order_no'],
			'orders_status_id' => MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED,
			'comments'         => $callback_comments
			));               
			 
			 // Send notification mail to Merchant
			$nnvendor_script->sendNotifyMail(array(
			 'comments'      => $callback_comments,
			 'order_no'      => $nntrans_history['order_no'],
			));
			$nnvendor_script->displayMessage($callback_comments);
		 }
		  else {
			$nnvendor_script->displayMessage('Novalnet Callbackscript received. Payment type ( ' . $nncapture_params['payment_type'] . ' ) is not applicable for this process!');
		 }
		  break;       
	 DEFAULT:

		$nnvendor_script->displayMessage('Novalnet Callbackscript received. Payment type ( ' . $nncapture_params['payment_type'] . ' ) is not applicable for this process!');

		break;
}

class NovalnetVendorScript
{

    /** @Array Type of payment available - Level : 0 */
    protected $payments = array('CREDITCARD', 'INVOICE_START', 'DIRECT_DEBIT_SEPA', 'GUARANTEED_INVOICE', 'PAYPAL', 'ONLINE_TRANSFER', 'IDEAL', 'EPS', 'GIROPAY', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'PRZELEWY24');

    /** @Array Type of Chargebacks available - Level : 1 */
    protected $chargebacks = array('RETURN_DEBIT_SEPA', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'PAYPAL_BOOKBACK', 'REFUND_BY_BANK_TRANSFER_EU', 'PRZELEWY24_REFUND', 'REVERSAL','GUARANTEED_SEPA_BOOKBACK', 'GUARANTEED_INVOICE_BOOKBACK', 'CASHPAYMENT_REFUND');

    /** @Array Type of Creditentry payment and Collections available - Level : 2 */
    protected $debit_collections = array('INVOICE_CREDIT',  'CREDIT_ENTRY_CREDITCARD', 'CREDIT_ENTRY_SEPA', 'DEBT_COLLECTION_SEPA', 'DEBT_COLLECTION_CREDITCARD', 'ONLINE_TRANSFER_CREDIT', 'CREDIT_ENTRY_DE', 'DEBT_COLLECTION_DE', 'CASHPAYMENT_CREDIT');

    protected $paymentgroups = array(
        'novalnet_cc'         => array('CREDITCARD', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'CREDIT_ENTRY_CREDITCARD',  'DEBT_COLLECTION_CREDITCARD'),
        'novalnet_sepa'       => array('DIRECT_DEBIT_SEPA', 'RETURN_DEBIT_SEPA',  'DEBT_COLLECTION_SEPA', 'CREDIT_ENTRY_SEPA', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'REFUND_BY_BANK_TRANSFER_EU','GUARANTEED_SEPA_BOOKBACK'),
        'novalnet_ideal'      => array('IDEAL', 'REFUND_BY_BANK_TRANSFER_EU', 'ONLINE_TRANSFER_CREDIT', 'REVERSAL', 'CREDIT_ENTRY_DE', 'DEBT_COLLECTION_DE'),
        'novalnet_sofortbank' => array('ONLINE_TRANSFER', 'REFUND_BY_BANK_TRANSFER_EU', 'ONLINE_TRANSFER_CREDIT', 'REVERSAL', 'CREDIT_ENTRY_DE', 'DEBT_COLLECTION_DE'),
        'novalnet_paypal'     => array('PAYPAL',  'PAYPAL_BOOKBACK', 'REFUND_BY_BANK_TRANSFER_EU'),
        'novalnet_prepayment' => array('INVOICE_START', 'INVOICE_CREDIT', 'REFUND_BY_BANK_TRANSFER_EU'),
        'novalnet_invoice'    => array('INVOICE_START','REFUND_BY_BANK_TRANSFER_EU', 'INVOICE_CREDIT',  'GUARANTEED_INVOICE', 'GUARANTEED_INVOICE_BOOKBACK', 'CREDIT_ENTRY_DE', 'DEBT_COLLECTION_DE'),
        'novalnet_eps'        => array('EPS', 'REFUND_BY_BANK_TRANSFER_EU', 'CREDIT_ENTRY_DE', 'REVERSAL', 'DEBT_COLLECTION_DE'),
        'novalnet_giropay'    => array('GIROPAY', 'REFUND_BY_BANK_TRANSFER_EU', 'CREDIT_ENTRY_DE', 'REVERSAL', 'DEBT_COLLECTION_DE'),
        'novalnet_przelewy24' => array('PRZELEWY24', 'PRZELEWY24_REFUND'),
        'novalnet_barzahlen'  => array('CASHPAYMENT','CASHPAYMENT_REFUND','CASHPAYMENT_CREDIT'));

    /** @Array Callback Capture parameters */
    protected $request_params = array();
    protected $paramsRequired = array();
    
     /**
     * @var Mail ID to be notify to technic
     */
    protected $technic_notify_mail = 'technic@novalnet.de';
  
    function __construct($arycapture = array())
    {
        $this->validateIpAddress();

        $this->paramsRequired                     = array('vendor_id', 'tid', 'payment_type', 'status', 'tid_status');

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
        
        $real_host_ip = gethostbyname('pay-nn.de');
        if (empty($real_host_ip)) {
           $this->displayMessage('Novalnet HOST IP missing');
        }

        $client_ip = tep_get_ip_address();
        if ($client_ip != $real_host_ip && !$process_testmode) {
            $this->displayMessage("Unauthorised access from the IP " . $client_ip, true);
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
        
		foreach ($this->paramsRequired as $v) {
			if ($arycapture[$v] == '') {
				$this->displayMessage('Required param ( ' . $v . '  ) missing!');
			}
			if (in_array($v, array('tid', 'tid_payment', 'signup_tid')) && !preg_match('/^\d{17}$/', $arycapture[$v])) {
				$this->displayMessage('Invalid TID [' . $arycapture[$v] . '] for Order.');
			}
		}

		if (!in_array($arycapture['payment_type'], array_merge($this->payments, $this->chargebacks, $this->debit_collections)) && $arycapture['payment_type'] != 'TRANSACTION_CANCELLATION') {
			$this->displayMessage('Payment type ( ' . $arycapture['payment_type'] . ' ) is mismatched!');
		}

		if ($arycapture['payment_type'] != '' && (!is_numeric($arycapture['amount']) || $arycapture['amount'] < 0)) {
			$this->displayMessage('The requested amount (' . $arycapture['amount'] . ') is not valid');
		}

		if (in_array($arycapture['payment_type'], $this->chargebacks) || ($arycapture['payment_type'] == 'INVOICE_CREDIT')) {
			$arycapture['shop_tid'] = $arycapture['tid_payment'];
		} else {
			$arycapture['shop_tid'] = $arycapture['tid'];
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
        
        //Update callback order status due to full payment
		tep_db_perform(TABLE_ORDERS, array(
			'orders_status' => $datas['orders_status_id']
		), 'update', 'orders_id="' . $datas['order_no'] . '"');
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
     *
     * @return none
     */
    function displayMessage($error_msg)
    {
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
		
		if (in_array($this->arycaptureparams['payment_type'], $this->payments))
		{
			$tid = tep_db_input($this->arycaptureparams['shop_tid']);
		}elseif (in_array($this->arycaptureparams['payment_type'], $this->chargebacks))
		{
	       $tid = tep_db_input($this->arycaptureparams['shop_tid']);
		}elseif (in_array($this->arycaptureparams['payment_type'], $this->debit_collections)) {
		   $tid = tep_db_input($this->arycaptureparams['tid_payment']);
		} else {
			$tid = tep_db_input($this->arycaptureparams['shop_tid']);
		}
		
        $select_query = tep_db_query("SELECT order_no, amount, payment_id, payment_type,language,callback_amount,gateway_status,payment_details,payment_ref from novalnet_transaction_detail where tid = '" .$tid. "'");
        $dbVal        = tep_db_fetch_array($select_query);
        
        $language = isset($dbVal['language']) ? $dbVal['language'] : 'english';
        
        include_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'languages/' . $language . '/modules/payment/novalnet.php');
       
        if (!empty($dbVal)) {
			$dbVal['tid'] = $this->arycaptureparams['shop_tid'];
			$this->transactionCancellation($dbVal);
            if(is_array($this->paymentgroups[$dbVal['payment_type']])) {
                if (!in_array($this->arycaptureparams['payment_type'], $this->paymentgroups[$dbVal['payment_type']])) {
                    $this->displayMessage('Payment Type [' . $this->arycaptureparams['payment_type'] . '] is not valid.');
                }
            }
            if (!empty($this->arycaptureparams['order_no']) && $this->arycaptureparams['order_no'] != $dbVal['order_no']) {
				list($subject, $message) = $this->sendCriticalMail();
				// Send E-mail, if transaction not found
				tep_mail(STORE_OWNER, $this->technic_notify_mail, $subject, $message, STORE_NAME, EMAIL_FROM);
                $this->displayMessage($message);
            }
            $dbVal['nn_order_lang']        = $dbVal['language'];
            $dbVal['order_current_status'] = $this->getOrderCurrentStatus($dbVal['order_no']);
            if (in_array($dbVal['payment_type'], array('novalnet_invoice', 'novalnet_prepayment','novalnet_barzahlen'))) {
                $tables_sql = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '" AND table_name= "novalnet_callback_history"');
                $result     = tep_db_fetch_array($tables_sql);
                if (empty($dbVal['callback_amount']) && $result['table_name'] == 'novalnet_callback_history') {
                    $order_totalqry        = tep_db_query("select sum(amount) as amount_total from novalnet_callback_history where order_no = '" . tep_db_input($dbVal['order_no']) . "'");
                    $result                = tep_db_fetch_array($order_totalqry);
                    $callback_amount_value = $result['amount_total'];
                }
                $dbVal['callback_script_status'] = constant('MODULE_PAYMENT_' . strtoupper($dbVal['payment_type']) . '_CALLBACK_ORDER_STATUS');
            }
			
            $dbVal['order_total_amount']         = $dbVal['amount'];
            $dbVal['order_paid_amount']          = 0;
            $payment_type_level                  = $this->getPaymentTypeLevel();
            if (in_array($payment_type_level, array(0, 2))) {
                $dbVal['callback_old_amount'] = isset($callback_amount_value) ? $callback_amount_value : 0;
                $dbVal['order_paid_amount']   = isset($dbVal['callback_amount']) ? $dbVal['callback_amount'] : 0;
            }
        } else {
			list($subject, $message) = $this->sendCriticalMail();
			// Send E-mail, if transaction not found
			tep_mail(STORE_OWNER, $this->technic_notify_mail, $subject, $message, STORE_NAME, EMAIL_FROM);
            $this->displayMessage($message);
        }
        return $dbVal;
    }
    
     /**
     * Send critical notification mail
     *
     * @return none
     */
    function sendCriticalMail() {
		$subject  = 'Critical error on shop system '.STORE_NAME.': order not found for TID: ' . $this->arycaptureparams['shop_tid'];
        $message  = "Dear Technic team,".PHP_EOL.PHP_EOL;
        $message .= "Please evaluate this transaction and contact our payment module team at Novalnet.".PHP_EOL.PHP_EOL;
        $message .= 'Merchant ID: ' . $this->arycaptureparams['vendor_id'] . PHP_EOL;
        $message .= 'Project ID: ' . $this->arycaptureparams['product_id'] . PHP_EOL;
        $message .= 'TID: ' . $this->arycaptureparams['shop_tid'] . PHP_EOL;
        $message .= 'TID status: ' . $this->arycaptureparams['tid_status'] . PHP_EOL;
        $message .= 'Order no: ' . $this->arycaptureparams['order_no'] . PHP_EOL;
        $message .= 'Payment type: ' . $this->arycaptureparams['payment_type'] . PHP_EOL;
        $message .= 'E-mail: ' . $this->arycaptureparams['email'] . PHP_EOL.PHP_EOL;

        $message .= 'Regards,'.PHP_EOL.'Novalnet Team';
        
         return array($subject, $message);
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
            $email_subject = 'Novalnet Callback script notification'; // Mail subject
            $email_content = PHP_EOL . $datas['comments'];
           
			tep_mail(STORE_OWNER, $email_to, $email_subject, $email_content, STORE_NAME, EMAIL_FROM);
            return true;
        }
        return false;
    }

    /**
     * Update the comments and displaying message
     * @param $nncapture_params
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
     * Update Przelewy24 cancel status
     *
     * @param $nntrans_history
     * @return string
     */
    function updatePrzelewyCancelcomments($nntrans_history)
    {
        $nncapture_params   = $this->getCaptureParams();
        $callback_status_id = (!empty($nntrans_history['callback_script_status']) ? $nntrans_history['callback_script_status'] : DEFAULT_ORDERS_STATUS_ID);

        // Form failure comments
        $comments          = !empty($nncapture_params['status_text']) ? PHP_EOL . $nncapture_params['status_text'] : (!empty($nncapture_params['status_desc']) ? PHP_EOL . $nncapture_params['status_desc'] : (!empty($nncapture_params['status_message']) ? PHP_EOL . $nncapture_params['status_message'] : ''));
        $callback_comments = 'The transaction has been canceled due to:' . $comments;

        $this->updateCallbackComments(array('order_no' => $nntrans_history['order_no'], 'comments' => $callback_comments,
        'orders_status_id' => $callback_status_id));

        return $callback_comments;
    }
    
    /**
	 * Handle transaction_cancellation process
	 * $param $nntrans_history
	 *
	 * @return void
	 */
    function transactionCancellation($nntrans_history) {
		$nncapture_params = $this->getCaptureParams();
		
		if ( $nntrans_history['gateway_status'] != '103' && $nncapture_params['tid_status'] == '103' && in_array($nncapture_params['payment_type'], array( 'TRANSACTION_CANCELLATION','CREDITCARD','DIRECT_DEBIT_SEPA','GUARANTEED_DIRECT_DEBIT_SEPA','PAYPAL','INVOICE_START','GUARANTEED_INVOICE')) ) {
            
            $canceled_status = ((MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED > 0) ? MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED : DEFAULT_ORDERS_STATUS_ID);
            
            // To form the callback comments
            $callback_comments = PHP_EOL.sprintf(MODULE_PAYMENT_GUARANTEE_PAYMENT_CANCELLED_MESSAGE,date(DATE_FORMAT, strtotime(date('d.m.Y'))), date('H:i:s')) . PHP_EOL;
           $param ['gateway_status'] = $nncapture_params['tid_status'];
           tep_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $nncapture_params['shop_tid'] . "'"); 
           
           // To update order details in shop
           $this->updateCallbackComments(array(
           'order_no'        => $nntrans_history['order_no'],
           'orders_status_id' => $canceled_status,
            'comments'         => $callback_comments
            ));
			// Send notification mail to Merchant
			$this->sendNotifyMail(array(
			 'comments'      => $callback_comments,
			 'order_no'      => $nntrans_history['order_no'],
			));
          $this->displayMessage($callback_comments);
        }
	 }
}
?>
