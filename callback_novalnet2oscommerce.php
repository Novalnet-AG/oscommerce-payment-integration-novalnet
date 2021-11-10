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
 * Script: callback_novalnet2oscommerce.php
 *
 */

// Include shop core libraries
include('includes/application_top.php');
include(DIR_WS_INCLUDES.'classes/novalnet/class.Novalnet.php');
$aryCaptureParams = array_map('trim', $_REQUEST);
$currencies = new currencies();

$processDebugMode = (MODULE_PAYMENT_NOVALNET_DEBUG_MODE == 'True');
$processTestMode  = (MODULE_PAYMENT_NOVALNET_TEST_MODE_CALLBACK == 'True');

$nnVendorScript = new NovalnetVendorScript($aryCaptureParams); // Novalnet Callback Class Object
if (!empty($aryCaptureParams['vendor_activation'])) {
  $nnVendorScript->updateAffAccountActivationDetail($aryCaptureParams);
  $callback_comments = 'Novalnet callback script executed successfully with Novalnet account activation information.';
  // Send notification mail
  $nnVendorScript->sendNotifyMail(array( 'comments' => $callback_comments, 'order_no' => '') );
  $nnVendorScript->debugError($callback_comments);
} else {
  $nntransHistory  = $nnVendorScript->getOrderReference(); // Order reference from novalnet_transaction_detail table
  $nnCaptureParams = $nnVendorScript->getCaptureParams(); // Collect callback capture parameters
  if (!empty($nntransHistory)) {
    $lang     = NovalnetTranslator::loadLocaleContents($nntransHistory['language']);
    foreach ($lang as $key => $val) {
      define($key, utf8_decode($val));
    }
    $paid_amount_with_currency = $currencies->format($nnCaptureParams['amount']/100, false, $nnCaptureParams['currency']);
    $order_id = $nntransHistory['order_no']; // Given shop order ID
    tep_db_perform('novalnet_transaction_detail', array('callback_status' => 1), 'update', "order_no = '$order_id' and active = 1");
    if ($nnVendorScript->getPaymentTypeLevel() == 2) {
      // CreditEntry payment and Collections available
      if ($nnCaptureParams['payment_type'] == 'INVOICE_CREDIT') {// Credit entry of INVOICE or PREPAYMENT
        if ($nnCaptureParams['subs_billing'] == 1) {// Subscription renewal of INVOICE_CREDIT
          $nnTrxnDetails = $nnVendorScript->getSubscriptionTransDetails($nntransHistory['tid'], $order_id);
          // Update callback order status due to full payment
          tep_db_perform(TABLE_ORDERS, array('orders_status' => $nntransHistory['order_current_status']), 'update', 'orders_id="'.$order_id.'"');
          $callback_comments = 'Novalnet Callback Script executed successfully for the Subscription TID: ' .$nnCaptureParams['shop_tid'] . ' with amount:' . $currencies->format($nnCaptureParams['amount']/100, false, $nnCaptureParams['currency']) . ' on ' . date('Y-m-d H:i:s') . '. Please refer PAID transaction in our Novalnet Merchant Administration with the TID:' . $nnCaptureParams['tid'].'.';
          $callback_comments .= (!empty($nnTrxnDetails->next_subs_cycle)) ? PHP_EOL . 'Next Payment Date is: '. $nnTrxnDetails->next_subs_cycle : '';
          // Update callback comments in order status history table
          $nnVendorScript->updateCallbackComments(array( 'order_no' => $order_id, 'comments' => $callback_comments, 'orders_status_id' => $nntransHistory['order_current_status'] ));
          // Send notification mail
          $nnVendorScript->sendNotifyMail( array( 'comments' => $callback_comments, 'order_no' => $order_id ) );
          $nnVendorScript->debugError($callback_comments);
        } else {
          if ($nntransHistory['order_paid_amount'] < $nntransHistory['order_total_amount']) {
            $callback_comments = sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS), $nnCaptureParams['shop_tid'], $paid_amount_with_currency,  date(DATE_FORMAT,strtotime(date('Y-m-d'))), date('H:i:s'), $nnCaptureParams['tid']);
            $callback_paid_amount_greater = '';
            if ($nntransHistory['order_total_amount'] <= ($nntransHistory['order_paid_amount'] + $nnCaptureParams['amount'])){
              // Full Payment paid
              $callback_status_id = (($nntransHistory['callback_script_status'] > 0) ? $nntransHistory['callback_script_status'] : DEFAULT_ORDERS_STATUS_ID);
              $callback_paid_amount_greater = (($nntransHistory['order_paid_amount'] + $nnCaptureParams['amount']) > $nntransHistory['order_total_amount']) ? ' Customer has paid more than the Order amount.' : '';
              // Update callback order status due to full payment
              tep_db_perform(TABLE_ORDERS, array('orders_status' => $callback_status_id), 'update', 'orders_id="'.$order_id.'"');
            } else { // Partial Payment paid
              $callback_status_id = $nntransHistory['order_current_status'];
            }
            // Update callback comments in order status history table
            $nnVendorScript->updateCallbackComments( array( 'order_no' => $order_id, 'comments' => $callback_comments, 'orders_status_id' => $callback_status_id ) );
            // Send notification mail
            $nnVendorScript->sendNotifyMail( array( 'comments' => $callback_comments.$callback_paid_amount_greater, 'order_no' => $order_id ) );
            // Log callback process (for all types of payments default)
            $nnVendorScript->logCallbackProcess($aryCaptureParams, $nntransHistory['tid'], $order_id);
            $nnVendorScript->debugError($callback_comments.$callback_paid_amount_greater);
          }
          $nnVendorScript->debugError('Novalnet callback received. Callback Script executed already. Refer Order :'.$order_id);
        }
      }
      $error = 'Payment type ( '.$nnCaptureParams['payment_type'].' ) is not applicable for this process!';
      $nnVendorScript->debugError($error);
    } elseif($nnVendorScript->getPaymentTypeLevel() == 1) { // level 1 payments - Type of Chargebacks
      // DO THE STEPS TO UPDATE THE STATUS OF THE ORDER OR THE USER AND NOTE THAT THE PAYMENT WAS RECLAIMED FROM USER
      $callback_comments = sprintf(html_entity_decode(MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS), $nnCaptureParams['tid_payment'], $paid_amount_with_currency, date('Y-m-d'), date('H:i:s'), $nnCaptureParams['tid']);
      $callback_status_id = $nntransHistory['order_current_status'];
      // Update callback comments in order status history table
      $nnVendorScript->updateCallbackComments(array( 'order_no' => $order_id, 'comments' => $callback_comments, 'orders_status_id' => $callback_status_id ));
      // Send notification mail
      $nnVendorScript->sendNotifyMail(array( 'comments' => $callback_comments, 'order_no' => $order_id ));
      $nnVendorScript->debugError($callback_comments);
    } elseif($nnVendorScript->getPaymentTypeLevel() === 0) {
      $order_comments = MODULE_PAYMENT_NOVALNET_TRANSACTION_ID. ' ' .$aryCaptureParams['tid'].PHP_EOL;
      $order_comments .= !empty($nnCaptureParams['test_mode']) ? MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE.PHP_EOL : '';
      if ( $aryCaptureParams['payment_type'] == 'INVOICE_START' && $aryCaptureParams['subs_billing'] == 1 ) {
		$nnTrxnDetails      = $nnVendorScript->getSubscriptionTransDetails($nnCaptureParams['shop_tid'], $order_id);
        $callback_comments  = sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_CALLBACK_RECURRING_COMMENTS), $order_id);
        $callback_comments .= (!empty($nnTrxnDetails->next_subs_cycle)) ? PHP_EOL.utf8_encode(MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS). $nnTrxnDetails->next_subs_cycle : '';
        $callback_status_id = $nntransHistory['order_current_status'];
        //Send notification mail to merchant
        $nnVendorScript->sendNotifyMail(array( 'comments' => $callback_comments, 'order_no' => $order_id ));
        $nnVendorScript->createOrder($order_id,$callback_comments, (array) $nnTrxnDetails, $order_comments, $nntransHistory['language']);
        $nnVendorScript->debugError($callback_comments);
	  } elseif ($aryCaptureParams['subs_billing'] == 1) { //IF PAYMENT MADE ON SUBSCRIPTION RENEWAL
        // Update callback comments in order status history table
        $nnTrxnDetails      = $nnVendorScript->getSubscriptionTransDetails($nnCaptureParams['shop_tid'], $order_id);
        $callback_comments  =  sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_CALLBACK_RECURRING_COMMENTS), $order_id);
        $callback_comments .= (!empty($nnTrxnDetails->next_subs_cycle)) ? PHP_EOL.utf8_encode(MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS). $nnTrxnDetails->next_subs_cycle : '';
        $callback_status_id = $nntransHistory['order_current_status'];
        $nnVendorScript->createOrder($order_id,$callback_comments, (array) $nnTrxnDetails, $order_comments, $nntransHistory['language']);
        // Send notification mail to merchant
        $nnVendorScript->sendNotifyMail(array( 'comments' => $callback_comments, 'order_no' => $order_id ));
        $nnVendorScript->debugError($callback_comments);
      } elseif($nnCaptureParams['payment_type'] == 'PAYPAL') {
        if ($nntransHistory['order_paid_amount'] < $nntransHistory['order_total_amount']) {
          $callback_comments = sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS), $nnCaptureParams['shop_tid'], $paid_amount_with_currency,date(DATE_FORMAT,strtotime(date('Y-m-d'))), date('H:i:s')).PHP_EOL;
          $paypal_order_status = MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS != '' ? MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS : DEFAULT_ORDERS_STATUS_ID;
          $callback_status_id = ($nnCaptureParams['status'] == 100) ? $paypal_order_status : $nntransHistory['order_current_status'];
          // Update callback order status due to full payment
          if ($nnCaptureParams['status'] == 100)
            tep_db_perform(TABLE_ORDERS, array('orders_status' => $callback_status_id), 'update', 'orders_id="'.$order_id.'"');
		  // Update callback comments in order status history table
          $nnVendorScript->updateCallbackComments(array( 'order_no' => $order_id, 'comments' => $callback_comments, 'orders_status_id' => $callback_status_id ));
          // Send notification mail
          $nnVendorScript->sendNotifyMail(array( 'comments' => $callback_comments, 'order_no' => $order_id ));
          // Log callback process (for all types of payments default)
          $nnVendorScript->logCallbackProcess($aryCaptureParams, $nnCaptureParams['shop_tid'], $order_id);
          $nnVendorScript->debugError($callback_comments);
        }
        $nnVendorScript->debugError('Novalnet Callbackscript received. Order already Paid');
      } else {
        $nnVendorScript->debugError('Novalnet Callbackscript received. Payment type ( '.$nnCaptureParams['payment_type'].' ) is not applicable for this process!');
      }
    }
    // Cancellation of a Subscription
    if($nnCaptureParams['payment_type'] == 'SUBSCRIPTION_STOP') {
	  $callback_comments = PHP_EOL.sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_STOP_COMMENTS),$nnCaptureParams['shop_tid'], date(DATE_FORMAT,strtotime(date('Y-m-d'))), date('H:i:s') );
      $callback_comments .= MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_REASON_TEXT. $nnCaptureParams['termination_reason'];
	  $sql_val = tep_db_fetch_array(tep_db_query('SELECT parent_tid from novalnet_subscription_detail WHERE tid = "'.$nnCaptureParams["shop_tid"].'"'));
	  $parent_tid = $sql_val['parent_tid'];
	  $db_query = tep_db_query('SELECT order_no from novalnet_subscription_detail WHERE parent_tid = "'.$parent_tid.'"');
	  while($row = tep_db_fetch_array($db_query) ) {
		tep_db_perform(TABLE_ORDERS, array('orders_status' => $nntransHistory['subscription_cancel_status']), 'update', 'orders_id="'.$row['order_no'].'"');
	  }
	  $parent_order_no = tep_db_fetch_array(tep_db_query('SELECT order_no from novalnet_transaction_detail WHERE tid = "'.$parent_tid.'"'));
      
  	  $nnVendorScript->updateSubscriptionReason(array(
          'termination_reason' => $nnCaptureParams['termination_reason'],
          'termination_at' => date('Y-m-d H:i:s'),
          'tid' => $parent_tid
		));
      $nnVendorScript->updateCallbackComments(array(
                'order_no' => $parent_order_no['order_no'],
                'comments' => $callback_comments,
                'orders_status_id' => $nntransHistory['subscription_cancel_status']
        ));
      // Send notification mail
      $nnVendorScript->sendNotifyMail(array(
          'comments' => $callback_comments,
          'order_no' => $order_id,
        ));
      $nnVendorScript->debugError($callback_comments);
    }
  }
  else
  {
    $nnVendorScript->debugError('Order Reference not exist!');
  }
}
class NovalnetVendorScript
{
  /** @Array Type of payment available - Level : 0 */
  protected $aryPayments = array('CREDITCARD','INVOICE_START','DIRECT_DEBIT_SEPA','GUARANTEED_INVOICE_START','PAYPAL','ONLINE_TRANSFER','IDEAL','EPS','PAYSAFECARD');

  /** @Array Type of Chargebacks available - Level : 1 */
  protected $aryChargebacks = array('RETURN_DEBIT_SEPA','CREDITCARD_BOOKBACK','CREDITCARD_CHARGEBACK','REFUND_BY_BANK_TRANSFER_EU');

  /** @Array Type of CreditEntry payment and Collections available - Level : 2 */
  protected $aryCollection = array('INVOICE_CREDIT','GUARANTEED_INVOICE_CREDIT','CREDIT_ENTRY_CREDITCARD','CREDIT_ENTRY_SEPA','DEBT_COLLECTION_SEPA','DEBT_COLLECTION_CREDITCARD');

  /** @Array Type of Subscription methods */
  protected $arySubscription = array('SUBSCRIPTION_STOP');

  /** @Array Type of Novalnet payment methods using payment_types */
  protected $aryPaymentGroups = array(
    'novalnet_cc' => array('CREDITCARD', 'CREDITCARD_BOOKBACK', 'CREDITCARD_CHARGEBACK', 'CREDIT_ENTRY_CREDITCARD','SUBSCRIPTION_STOP','DEBT_COLLECTION_CREDITCARD'),
    'novalnet_sepa' => array('DIRECT_DEBIT_SEPA', 'RETURN_DEBIT_SEPA','SUBSCRIPTION_STOP','DEBT_COLLECTION_SEPA','CREDIT_ENTRY_SEPA'),
    'novalnet_ideal' => array('IDEAL'),
    'novalnet_sofortbank' => array('ONLINE_TRANSFER'),
    'novalnet_paypal' => array('PAYPAL', 'SUBSCRIPTION_STOP'),
    'novalnet_prepayment' => array('INVOICE_START','INVOICE_CREDIT', 'SUBSCRIPTION_STOP'),
    'novalnet_invoice' => array('INVOICE_START',  'INVOICE_CREDIT',  'SUBSCRIPTION_STOP'),
    'novalnet_eps' => array('EPS')
   );
  /** @Array Callback Capture parameters and Required parameters (Normal params and Affiliate activation params) */
  protected $arycaptureparams = array();
  protected $paramsRequired = array();
  protected $affAccountActivationparamsRequired = array();

  /** @IP-ADDRESS Novalnet IP, is a fixed value, DO NOT CHANGE!!!!! */
  protected $ipAllowed = array('195.143.189.210', '195.143.189.214');

  function __construct($aryCapture) {
    global $processTestMode;

    // Validate Authenticated IP
    $ipAddress = tep_get_ip_address();
    if (!in_array($ipAddress, $this->ipAllowed) && !$processTestMode) {
      self::debugError('Novalnet callback received. Unauthorised access from the IP [' . (!empty($ipAddress) ? $ipAddress : '127.0.0.1' ). ']');
    }
    if (empty($aryCapture)) {
      self::debugError('Novalnet callback received. No params passed over!');
    }
    $this->paramsRequired = array('vendor_id', 'tid', 'payment_type', 'status', 'amount');
    $this->affAccountActivationparamsRequired = array('vendor_id', 'vendor_authcode', 'product_id', 'aff_id', 'aff_authcode', 'aff_accesskey');
	if($aryCapture['payment_type'] == 'SUBSCRIPTION_STOP') {
      unset($this->paramsRequired[4]);
    }
    if(isset($aryCapture['subs_billing']) && $aryCapture['subs_billing'] ==1) {
      array_push($this->paramsRequired, 'signup_tid');
    }
    elseif (isset($aryCapture['payment_type']) && in_array($aryCapture['payment_type'], array_merge($this->aryChargebacks, array('INVOICE_CREDIT')))) {
      array_push($this->paramsRequired, 'tid_payment');
    }

    $this->arycaptureparams = self::validateCaptureParams($aryCapture);
  }

  /**
   * Return Capture parameters
   *
   * @return Array
   */
  function getCaptureParams() {
    // DO THE STEPS FOR PARAMETER VALIDATION / PARAMETERS MAPPING WITH SHOP BASED PROCESS IF REQUIRED
    return $this->arycaptureparams;
  }

  /**
   * Perform parameter validation process
   * Set Empty value if not exist in aryCapture
   *
   * @param $aryCapture is having $_REQUEST values
   *
   * @return Array
   */
  function validateCaptureParams($aryCapture) {
	$arySetNullvalueIfnotExist = array('reference', 'vendor_id', 'tid', 'status', 'status_messge', 'payment_type', 'signup_tid');
	foreach($arySetNullvalueIfnotExist as $value) {
	  if(!isset($aryCapture[$value])) {
		$aryCapture[$value] = '';
	  }
	}
	if (!isset($aryCapture['vendor_activation'])) {
	  foreach ($this->paramsRequired as $v) {
		if ($aryCapture[$v] == '') {
		  self::debugError('Required param ( ' . $v . '  ) missing!');
		}
		if (in_array($v, array('tid', 'tid_payment', 'signup_tid')) && !preg_match('/^\d{17}$/', $aryCapture[$v])) {
		  self::debugError('Novalnet callback received. Invalid TID [' . $v . '] for Order.');
		}
	  }
	  if (!in_array($aryCapture['payment_type'], array_merge($this->aryPayments, $this->aryChargebacks, $this->aryCollection,$this->arySubscription))) {
		self::debugError('Novalnet callback received. Payment type ( ' . $aryCapture['payment_type'] . ' ) is mismatched!');
	  }
	  if (isset($aryCapture['status']) && $aryCapture['status'] !=100)  {
		self::debugError('Novalnet callback received. Status (' . $aryCapture['status'] . ') is not valid: Only 100 is allowed');
	  }
	  if (in_array('amount', $this->paramsRequired) && (!is_numeric($aryCapture['amount']) || $aryCapture['amount'] < 0)) {
		self::debugError('Novalnet callback received. The requested amount (' . $aryCapture['amount'] . ') is not valid');
	  }
	  if (isset($aryCapture['signup_tid']) && $aryCapture['signup_tid'] != '') { // Subscription
		$aryCapture['shop_tid'] = $aryCapture['signup_tid'];
	  }
	  elseif(in_array($aryCapture['payment_type'], array_merge($this->aryChargebacks, array('INVOICE_CREDIT')))) {
		$aryCapture['shop_tid'] = $aryCapture['tid_payment'];
	  }
	  else {
		$aryCapture['shop_tid'] = $aryCapture['tid'];
	  }
	} else {
	  foreach ($this->affAccountActivationparamsRequired as $v) {
		if (empty($aryCapture[$v])) {
		  self::debugError('Required param ( ' . $v . '  ) missing!');
		}
	  }
	}
    return $aryCapture;
  }

  /**
   * Get given payment_type level for process
   *
   * @return boolean
   */
  function getPaymentTypeLevel() {
    if(in_array($this->arycaptureparams['payment_type'], $this->aryPayments)) {
      return 0;
    }
    elseif(in_array($this->arycaptureparams['payment_type'], $this->aryChargebacks)) {
      return 1;
    }
    elseif(in_array($this->arycaptureparams['payment_type'], $this->aryCollection)) {
      return 2;
    }
  }

  /**
   * Get order reference from the novalnet_transaction_detail table on shop database
   *
   * @return Array
   */
  function getOrderReference()
  {
    $orderRefQry = tep_db_query("select order_no, total_amount, payment_id, payment_type, language from novalnet_transaction_detail where tid = " . $this->arycaptureparams['shop_tid'] . " limit 1");
    $dbVal 	  = tep_db_fetch_array($orderRefQry);
    $order_no = $dbVal['order_no'];
    $dbVal['tid'] = $this->arycaptureparams['shop_tid'];
    if ($order_no) {
      $dbVal['order_current_status'] = self::getOrderCurrentStatus($order_no);
      $dbVal['callback_amount'] = $this->arycaptureparams['amount'];
      if (in_array($dbVal['payment_type'], array('novalnet_invoice', 'novalnet_prepayment'))) {
        $dbVal['callback_script_status'] = constant('MODULE_PAYMENT_'.strtoupper($dbVal['payment_type']).'_CALLBACKSCRIPT_ORDER_STATUS');
        $dbVal['callback_script_status'] = ($dbVal['callback_script_status'] > 0) ? $dbVal['callback_script_status'] : DEFAULT_ORDERS_STATUS_ID;
	  }
      $dbVal['subscription_cancel_status'] = MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL > 0 ? MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL : DEFAULT_ORDERS_STATUS_ID;
      $dbVal['order_total_amount'] = $dbVal['total_amount'];

      // Collect paid amount information from the novalnet_callback_history
      $dbVal['order_paid_amount'] = 0;
      $payment_type_level = self::getPaymentTypeLevel();
      if (in_array($payment_type_level, array(0, 2))) {
        $orderTotalQry = tep_db_query("select sum(amount) as amount_total from novalnet_callback_history where order_no = ".tep_db_input($order_no));
        $dbCallbackTotalVal = tep_db_fetch_array($orderTotalQry);
        $dbVal['order_paid_amount'] = ((isset($dbCallbackTotalVal['amount_total'])) ? $dbCallbackTotalVal['amount_total'] : 0);
      }
      if (!in_array($this->arycaptureparams['payment_type'], $this->aryPaymentGroups[$dbVal['payment_type']])) {
        self::debugError('Novalnet callback received. Payment Type [' . $this->arycaptureparams['payment_type'] . '] is not valid.');
      }
      if (!empty($this->arycaptureparams['order_no']) && $this->arycaptureparams['order_no'] != $order_no) {
        self::debugError('Novalnet callback received. Order Number is not valid.');
      }
    }
    else
    {
      self::debugError('Transaction mapping failed');
    }
    return $dbVal;
  }

  /**
   * Get orders_status from the orders table on shop database
   *
   * @param : $order_id is given order id or based on given Tid to fetch the order id value from database
   *
   * @return mixed
   */
  function getOrderCurrentStatus($order_id)
  {
    $orderRefQry = tep_db_query("select orders_status from ".TABLE_ORDERS." where orders_id = ".tep_db_input($order_id));
    $dbVal = tep_db_fetch_array($orderRefQry);
    return ((!empty($dbVal['orders_status'])) ? $dbVal['orders_status'] : DEFAULT_ORDERS_STATUS_ID);
  }

  /**
   * Update Callback comments in shop order tables
   * Table : orders_status_history
   *
   * @param : $datas is the array of order_no and comments
   *
   * @return boolean
   */
  function updateCallbackComments($datas) {
    $comments = ((isset($datas['comments']) && $datas['comments'] != '') ? $datas['comments'] : '');
    $orders_id = $datas['order_no'];
    $get_orders_status = $datas['orders_status_id'];
    tep_db_query("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) VALUES ('$orders_id', '$get_orders_status', NOW(), '1','$comments')");
    return true;
  }

  /**
   * Display the error message
   * @display ERROR MESSAGE
   *
   * @param : $errorMsg get from value or set to default string in "Authentication Failed!"
   * @param : $ipBlock get the access for authenticate the file
   *
   * @return none
   */
  function debugError($errorMsg) {
    global $processDebugMode;
    if ($processDebugMode) 
      echo htmlentities($errorMsg);
    exit;
  }

  /**
   * Log callback process in novalnet_callback_history table
   *
   * @param : $datas is the array value of selected order id
   * @param : $org_tid is the Tid of the current Transaction
   * @param : $order_no is the selected order number
   *
   * @return boolean
   */
  function logCallbackProcess($datas, $org_tid, $order_no) {
    $param['payment_type'] = $datas['payment_type'];
    $param['status'] = $datas['status'];
    $param['callback_tid'] = $datas['tid'];
    $param['org_tid'] = $org_tid;
    $param['amount'] = $datas['amount'];
    $param['currency'] = $datas['currency'];
    $param['product_id'] = $datas['product_id'];
    $param['order_no'] = $order_no;
    $param['date'] = date('Y-m-d H:i:s');
    tep_db_perform('novalnet_callback_history', $param, 'insert');
    return true;
  }

  /**
   * Send notification mail to Merchant
   *
   * @param : $datas is the array of Order number and Comments
   * @return boolean
   */
  function sendNotifyMail($datas = array()) {
    if (MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND == 'True') {
      $email_from      = EMAIL_FROM; // From Shop Configuration
      $email_from_name = STORE_NAME; // From Shop Configuration
      $email_to        = ((MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO != '') ? MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO : STORE_OWNER_EMAIL_ADDRESS);
      $email_bcc       = ((MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC != '') ? MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC : '');
      $email_to_name   = STORE_OWNER; // From Shop Configuration
      $email_subject   = 'Novalnet Callback script notification';
      $email_content   = (!empty($datas['order_no'])) ? 'Order :'.$datas['order_no'].' <br/>' : ' <br/>';
      $email_content  .= ' Message : '.$datas['comments'];
      $email_content = str_replace('€', (isset($_REQUEST['currency']) ? $_REQUEST['currency'] : ''), $email_content);
      $email_to_name = (strpos($email_to,',') === false) ? $email_to_name : '';
      if (!empty($email_bcc)) {
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type:text/html;charset='.CHARSET. "\r\n";
        $headers .= 'From: ' .$email_from_name. ' <'. $email_from . '>'."\r\n". 'Bcc: ' . $email_bcc . "\r\n";
        mail($email_to, $email_subject, self::utf_text($email_content), $headers);
      } else {
        tep_mail($email_to_name, $email_to, $email_subject, self::utf_text($email_content), $email_from_name, $email_from);
      }
      return true;
    }
    return false;
  }

  /**
   * update affliate details in novalnet_aff_account_detail table
   *
   * @param : $datas is the array of Order number and Comments
   * @return boolean
   */
  function updateAffAccountActivationDetail($datas = array()) {
    $param['vendor_id'] = $datas['vendor_id'];
    $param['vendor_authcode'] = $datas['vendor_authcode'];
    $param['product_id'] = $datas['product_id'];
    $param['product_url'] = $datas['product_url'];
    $param['activation_date'] = (!empty($datas['activation_date'])) ? date('Y-m-d H:i:s', strtotime($datas['activation_date'])) : '';
    $param['aff_id'] = $datas['aff_id'];
    $param['aff_authcode'] = $datas['aff_authcode'];
    $param['aff_accesskey'] = $datas['aff_accesskey'];
    tep_db_perform('novalnet_aff_account_detail', $param, 'insert');
    return true;
  }

  /**
   * update subscription details in novalnet_subscription_detail table
   *
   * @param : $datas is the array of Order number and Comments
   *
   * @return boolean
   */
  function updateSubscriptionReason($datas) {
    $param = array(
        'termination_reason'    => $datas['termination_reason'],
        'termination_at'        => $datas['termination_at'],
      );
    tep_db_perform('novalnet_subscription_detail', $param, 'update', "parent_tid = '" . $datas['tid'] . "'");
  }

  /**
   * Perform server call to get the Subscription transaction status
   *
   * @param : $tid is current order transaction id
   *
   * @return boolean
   */
  function getSubscriptionTransDetails($tid, $order_id) {
    $db_value = self::getOrderDetails($order_id);
    if(!empty($db_value)) {
      $xml_request = "<?xml version='1.0' encoding='UTF-8'?>
					  <nnxml>
						<info_request>
						  <vendor_id>" . $db_value['vendor'] . "</vendor_id>
						  <vendor_authcode>" . $db_value['auth_code'] . "</vendor_authcode>
						  <product_id>" . $db_value['product'] . "</product_id>
						  <request_type>TRANSACTION_STATUS</request_type>
						  <tid>" . $tid . "</tid>
						</info_request>
					  </nnxml>";
      $paygate_url = 'https://payport.novalnet.de/nn_infoport.xml';

      $ch = curl_init($paygate_url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

      $response = curl_exec($ch);
      curl_close($ch);
      return simplexml_load_string($response);
    }
  }

  /*
   * Create the new order for the subscription recurring
   * @param $orderId
   * @param $callbackComments
   * @param $subs_response
   * @param $order_comments
   *
   * @return none
   */
  function createOrder($orderId, $callbackComments, $subs_response, $order_comments = '', $nn_language) {
    $orderArray   = tep_db_fetch_array(tep_db_query("SELECT * FROM " . TABLE_ORDERS . " where orders_id = " . tep_db_input($orderId)));

    unset($orderArray['orders_id']);
    $orderArray['date_purchased'] = $orderArray['last_modified'] = date("Y-m-d H:i:s");
    tep_db_perform(TABLE_ORDERS, $orderArray, 'insert');
    $order_id = tep_db_insert_id();
    $orderTotalQry = tep_db_query("SELECT title, text, value, class, sort_order FROM " . TABLE_ORDERS_TOTAL . " where orders_id = " . tep_db_input($orderId));
    while ($orderTotalArray = tep_db_fetch_array($orderTotalQry)) {
      $orderTotalArray['orders_id'] = $order_id;
      tep_db_perform(TABLE_ORDERS_TOTAL, $orderTotalArray);
    }
    $orderProductsQry = tep_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " where orders_id = " . tep_db_input($orderId));
    while ($orderProductsArray = tep_db_fetch_array($orderProductsQry)) {
      unset($orderProductsArray['orders_id']);
      $orderProductsId = $orderProductsArray['orders_products_id'];
      unset($orderProductsArray['orders_products_id']);
      $orderProductsArray['orders_id'] = $order_id;
      tep_db_perform(TABLE_ORDERS_PRODUCTS, $orderProductsArray);

      $orders_products_id = tep_db_insert_id();
      $productsQry      = tep_db_query("select products_quantity, products_ordered from " . TABLE_PRODUCTS . " where products_id = " . tep_db_input($orderProductsArray['products_id']));
      $productsArray    = tep_db_fetch_array($productsQry);
      $productsQuantity = $productsArray['products_quantity']-$orderProductsArray['products_quantity'];
      $productsOrdered  = $productsArray['products_ordered']+$orderProductsArray['products_quantity'];
      ($productsQuantity < 1) ? tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $productsQuantity . "', products_ordered = '" .$productsOrdered. "', products_status = '0' where products_id = '" . $orderProductsArray['products_id'] . "'") : tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $productsQuantity . "', products_ordered = '" .$productsOrdered. "' where products_id = '" . $orderProductsArray['products_id'] . "'");
      $orderProductsAttrQry = tep_db_query("SELECT products_options, products_options_values, options_values_price, price_prefix FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = " . tep_db_input($orderId) . " AND orders_products_id=" . tep_db_input($orderProductsId));
      while ($orderProductsAttrArray = tep_db_fetch_array($orderProductsAttrQry)) {
        $orderProductsAttrArray['orders_id'] = $order_id;
        $orderProductsAttrArray['orders_products_id'] = $orderProductsId;
        tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $orderProductsAttrArray);
      }
      if(tep_db_num_rows(tep_db_query('SHOW TABLES LIKE "' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . '"'))) {
        $orderProductsDownQry = tep_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " WHERE orders_id = " . tep_db_input($orderId) . " AND orders_products_id=" . tep_db_input($orderProductsId));
        while ($orderProductsDownArray = tep_db_fetch_array($orderProductsDownQry)) {
          $orderProductsDownArray['orders_id'] = $order_id;
          $orderProductsDownArray['orders_products_id'] = $orders_products_id;
          tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $orderProductsDownArray);
        }
      }  
    }
    $nnTransDetails = self::getFullOrderDetails($orderId);
    $payment_name 	= strtoupper($nnTransDetails['payment_type']);
    $newOrderStatus = constant('MODULE_PAYMENT_' . $payment_name . '_ORDER_STATUS');
    $newOrderStatus = !empty($newOrderStatus) ? $newOrderStatus : DEFAULT_ORDERS_STATUS_ID;
    tep_db_perform(TABLE_ORDERS, array('orders_status' => $newOrderStatus), 'update', 'orders_id="'.$order_id.'"');
    $order_comments .= (in_array($nnTransDetails['payment_type'], array('novalnet_invoice', 'novalnet_prepayment'))) ? self::get_bankdetails($order_id) : '';

    self::insertUpdateShopDetails(array( 'order_status' => $newOrderStatus, 'tid' => $this->arycaptureparams['tid'],'total' => $this->arycaptureparams['amount'], 'total_amount' => $this->arycaptureparams['amount'], 'order_no' => $order_id, 'next_cycle' => $subs_response['next_subs_cycle'] ), $nnTransDetails, $nn_language);
    if (!empty($order_comments)) {
      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, array('orders_id' => $order_id, 'orders_status_id' => $newOrderStatus, 'date_added' => date("Y-m-d H:i:s"), 'customer_notified' => 1, 'comments' => $order_comments));
      tep_db_perform(TABLE_ORDERS, array('orders_status' => $newOrderStatus), 'update', 'orders_id="'.$order_id.'"');
    }
    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, array('orders_id' => $order_id, 'orders_status_id' => $newOrderStatus, 'date_added' => date("Y-m-d H:i:s"), 'customer_notified' => 1, 'comments' => $callbackComments));
    $query = tep_db_fetch_array(tep_db_query('SELECT termination_reason FROM novalnet_subscription_detail WHERE order_no = '.$orderId));
    tep_db_perform('novalnet_subscription_detail', array('order_no' => $order_id, 'subs_id' => $subs_response['subs_id'], 'tid' => $this->arycaptureparams['tid'], 'parent_tid' => $this->arycaptureparams['shop_tid'], 'signup_date' => date("Y-m-d H:i:s"), 'termination_reason' => $query['termination_reason'], 'termination_at' => ''));
  }

  /*
   * Get the transaction details
   * @param order_no
   *
   * @return array
   */
  function getOrderDetails($order_no) {
    $nnOrdersQry = tep_db_query("SELECT vendor, tid, product, auth_code, tariff, subs_id, payment_id, payment_type, amount, currency, status, gateway_status, test_mode, customer_id, language, active, process_key, additional_note, account_holder, refund_amount, total_amount, callback_status, date FROM novalnet_transaction_detail where order_no = " . $order_no);
    $results     = tep_db_fetch_array($nnOrdersQry);
    $results['order_no'] = $order_no;
    return $results;
  }

  /*
   * Get the all transaction details
   * @param order_no
   *
   * @return array
   */
  function getFullOrderDetails($order_no) {
    return tep_db_fetch_array(tep_db_query("SELECT vendor, auth_code, product, tariff, subs_id, payment_id, payment_type, gateway_status, customer_id, active, process_key, additional_note, account_holder, refund_amount, total_amount FROM novalnet_transaction_detail where order_no = " . tep_db_input($order_no)));
  }

  /*
   * Insert the transaction details into the shop system.
   * @param $shopInfo
   * @param $nnTransDetails
   *
   * return none
   */
  function insertUpdateShopDetails($shopInfo, $oldOrderDetails, $nn_language) {
    $nnTransDetails = $oldOrderDetails;
    $nnTransDetails['tid']             = $shopInfo['tid'];
    $nnTransDetails['amount']          = $shopInfo['total'];
    $nnTransDetails['date']            = date('Y-m-d H:i:s');
    $nnTransDetails['language']        = $nn_language;
    $nnTransDetails['currency']        = isset($this->arycaptureparams['currency']) ? $this->arycaptureparams['currency'] : '';
    $nnTransDetails['status']          = $this->arycaptureparams['status'];
    $nnTransDetails['test_mode']       = isset($this->arycaptureparams['test_mode']) ? $this->arycaptureparams['test_mode'] : 0;
    $nnTransDetails['order_no']        = $shopInfo['order_no'];
    $nnTransDetails['total_amount']        = $shopInfo['total'];
    $nnTransDetails['callback_status'] = 0;
    tep_db_perform('novalnet_transaction_detail', $nnTransDetails);
  }

  /*
   * Get the bank details for invoice & prepayment
   * @param $orderId
   *
   * @return string
   */
  function get_bankdetails($orderId) {
    $currencies = new currencies();
    $novalnet_comments = MODULE_PAYMENT_NOVALNET_INVOICE_COMMENTS_PARAGRAPH . PHP_EOL;
    $novalnet_comments .= MODULE_PAYMENT_NOVALNET_DUE_DATE . ' : ' . date(DATE_FORMAT, strtotime(!empty($this->arycaptureparams['due_date']) ? $this->arycaptureparams['due_date'] : '')) . PHP_EOL;
    $amount = (($currencies->format($this->arycaptureparams['amount']/100, false, (!empty($this->arycaptureparams['currency']) ? $this->arycaptureparams['currency'] : ''))));
    $novalnet_comments .= MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER . ' : NOVALNET AG' . PHP_EOL;
    $novalnet_comments .= 'IBAN : ' . (!empty($this->arycaptureparams['invoice_iban']) ? $this->arycaptureparams['invoice_iban'] : '') . PHP_EOL;
    $novalnet_comments .= 'BIC : ' . (!empty($this->arycaptureparams['invoice_bic']) ? $this->arycaptureparams['invoice_bic'] : '') . PHP_EOL;
    $novalnet_comments .= 'Bank : ' . (!empty($this->arycaptureparams['invoice_bankname']) ? trim($this->arycaptureparams['invoice_bankname']) : '') . ' ' . (!empty($this->arycaptureparams['invoice_bankplace']) ? trim($this->arycaptureparams['invoice_bankplace']) : '') . PHP_EOL;
    $novalnet_comments .= MODULE_PAYMENT_NOVALNET_AMOUNT . ' : ' . $amount;
    self::storeInvoiceTransaction($orderId);
    return $novalnet_comments;
  }

  /*
   * Store the invoice and prepayment transaction details
   * @param $nnInvoiceDetails
   * @param $amount
   * @param $orderNumber
   *
   * @return none
   */
  function storeInvoiceTransaction($orderNumber) {
    $nnInvoiceDetails['order_no']       = $orderNumber;
    $nnInvoiceDetails['tid']            = $this->arycaptureparams['tid'];
    $nnInvoiceDetails['test_mode']      = isset($this->arycaptureparams['test_mode']) ? $this->arycaptureparams['test_mode'] : 0;
    $nnInvoiceDetails['account_holder'] = 'NOVALNET AG';
    $nnInvoiceDetails['account_number'] = !empty($this->arycaptureparams['invoice_account']) ? $this->arycaptureparams['invoice_account'] : '' ;
    $nnInvoiceDetails['bank_code']      = !empty($this->arycaptureparams['invoice_bankcode']) ? $this->arycaptureparams['invoice_bankcode'] : '';
    $nnInvoiceDetails['bank_name']      = !empty($this->arycaptureparams['invoice_bankname']) ? $this->arycaptureparams['invoice_bankname'] : '';
    $nnInvoiceDetails['bank_name']      = !empty($this->arycaptureparams['invoice_bankname']) ? $this->arycaptureparams['invoice_bankname'] : '';
    $nnInvoiceDetails['bank_city']      = !empty($this->arycaptureparams['invoice_bankplace']) ? $this->arycaptureparams['invoice_bankplace'] : '';
    $nnInvoiceDetails['amount']         = !empty($this->arycaptureparams['amount']) ? $this->arycaptureparams['amount'] : '';
    $nnInvoiceDetails['currency']       = !empty($this->arycaptureparams['currency']) ? $this->arycaptureparams['currency'] : '';
    $nnInvoiceDetails['bank_iban']      = !empty($this->arycaptureparams['invoice_iban']) ? $this->arycaptureparams['invoice_iban'] : '';
    $nnInvoiceDetails['bank_bic']       = !empty($this->arycaptureparams['invoice_bic']) ? $this->arycaptureparams['invoice_bic'] : '';
    $nnInvoiceDetails['due_date']       = !empty($this->arycaptureparams['due_date']) ? $this->arycaptureparams['due_date'] : '';
    tep_db_perform('novalnet_preinvoice_transaction_detail', $nnInvoiceDetails);
  }

  function utf_text ($string) {
	return (strtoupper(CHARSET) == 'UTF-8') ? utf8_encode(html_entity_decode($string)) : html_entity_decode($string);
  }
}

/*
Level 0 Payments:
-----------------
CREDITCARD
INVOICE_START
DIRECT_DEBIT_SEPA
GUARANTEED_INVOICE_START
PAYPAL
ONLINE_TRANSFER
IDEAL
EPS
PAYSAFECARD

Level 1 Payments:
-----------------
RETURN_DEBIT_SEPA
GUARANTEED_RETURN_DEBIT_DE
REVERSAL
CREDITCARD_BOOKBACK
CREDITCARD_CHARGEBACK
REFUND_BY_BANK_TRANSFER_EU

Level 2 Payments:
-----------------
INVOICE_CREDIT
GUARANTEED_INVOICE_CREDIT
CREDIT_ENTRY_CREDITCARD
CREDIT_ENTRY_SEPA
DEBT_COLLECTION_SEPA
DEBT_COLLECTION_CREDITCARD

*/
?>
