<?php

/**
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * File: novalnet_payments.php
 *
 */

namespace common\modules\orderPayment;

use common\classes\modules\ModulePayment;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;
use common\helpers\Order;
use common\helpers\OrderPayment as OrderPaymentHelper;
use common\modules\orderPayment\lib\novalnet\NovalnetHelper;

class novalnet_payments extends ModulePayment
{
  var $code, $title, $description, $enabled;
  protected $defaultTranslationArray = [
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TEXT_TITLE' => 'Novalnet Payments',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TEXT_DESCRIPTION' => 'Novalnet Payments',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_DETAILS_TID' => 'Novalnet Transaction ID',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_TRANSFER_NOTE' => 'Please transfer the amount  %s  to the following account.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_TRANSFER_NOTE_DUE_DATE' => 'Please transfer the amount of  %s  to the following account on or before  %s ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TRANSFER_NOTE_DUE_DATE' => 'Please transfer the instalment cycle amount of  %s  to the following account on or before  %s ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TRANSFER_NOTE' => 'Please transfer the instalment cycle amount of  %s  to the following account.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_ACCHOLDER' => 'Account Holder : ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BANKNAME' => 'Bank : ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BANKPLACE' => 'Place : ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_IBAN' => 'IBAN : ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BIC' => 'BIC : ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_REF' => 'Payment Reference ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TRANS_SLIP_EXPIRY_DATE' => 'Slip expiry date : ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_NEAREST_STORE_DETAILS' => 'Store(s) near to you: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_REFERENCE_TEXT' => 'Please use the following payment references when transferring the amount. This is necessary to match it with your corresponding order',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_INSTALMENTS_INFO' => 'Instalment information',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_PROCESSED_INSTALMENTS' => 'Current Instalment Cycle: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_DUE_INSTALMENTS' => 'Due instalments: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CYCLE_AMOUNT' => 'Cycle amount: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_NEXT_INSTALMENT_DATE' => 'Next Instalment Date: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_GUARANTEE_PAYMENT_PENDING_TEXT' => 'Your order is under verification and we will soon update you with the order status. Please note that this may take upto 24 hours.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_MAIL_SUBJECT' => 'Novalnet Callback Script Access Report -osCommerce ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CAPTURE_COMMENT' => 'The transaction has been confirmed on ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CANCEL_COMMENT' => 'The transaction has been canceled on' . ' ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT_FULL' => 'Refund has been initiated for the TID: %s with the amount %s. New TID: %s for the refunded amount.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT' => 'Refund has been initiated for the TID: %s with the amount %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CREDIT_COMMENT' => 'Credit has been successfully received for the TID: %s with amount %s on %s . Please refer PAID order details in our Novalnet Admin Portal for the TID: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CHARGEBACK_COMMENT' => 'Chargeback executed successfully for the TID: %s amount: %s on %s. The subsequent TID: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_TRANS_DEACTIVATED_MESSAGE' => 'The transaction has been canceled for the TID: %s on %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_STATUS_PENDING_TO_ONHOLD_TEXT' => 'The transaction status has been changed from pending to on-hold for the TID: %s on %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_STATUS_PENDING_TO_CONFIRMED_TEXT' => 'The transaction status has been changed from pending to confirmed for the TID: %s on %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK_NEW_INSTALMENT_NOTE' => 'A new instalment transaction has been received for the Transaction ID: %s. The new instalment transaction ID is: %s with the amount %s on %s.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_AMOUNT_UPDATE_NOTE' => 'Transaction amount %s has been updated successfully on %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_DUEDATE_UPDATE_NOTE' => 'Transaction due date %s has been updated successfully on %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_AMOUNT_DUEDATE_UPDATE_NOTE' => 'Transaction amount %s and due date %s has been updated successfully on %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_REMINDER_NOTE' => 'Payment Reminder %s has been sent to the customer.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_COLLECTION_SUBMISSION_NOTE' => 'The transaction has been submitted to the collection agency. Collection Reference: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALLCYCLES_TEXT' => 'Instalment has been cancelled for the TID: %s on %s & Refund has been initiated with the amount %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_REMAINING_CYCLES_TEXT' => 'Instalment has been stopped for the TID: %s on %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BARZAHLEN_SUCCESS_BUTTON' => 'Pay now with Barzahlen',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_MULTIBANCO_NOTE' => 'Please use the following payment reference details to pay the amount of %s at a Multibanco ATM or through your internet banking.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TRANS_BOOKED_MESSAGE' => 'Your order has been booked with the amount of %s. Your new TID for the booked amount: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PARTNER_PAYMENT_REFERENCE' => 'Partner Payment Reference: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PARTNER_SUPPLIER_ID' => 'Entity: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_TEST_ORDER' => 'Test order ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CHECKSUM_ERROR_TEXT' => 'While redirecting some data has been changed. The hash check failed',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ZEROAMOUNT_BOOKING_TEXT' => 'This order processed as a zero amount booking',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SELECT_STATUS_TEXT' => 'Please select status',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOKING_AMOUNT' => ' Transaction booking amount',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_INVALID_TEXT' => 'The amount is invalid',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_REASON_TEXT' => ' Refund / Cancellation Reason',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_REASON_PLACEHOLDER' => 'Reason for refund',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TITLE' => 'Refund Extension',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOK_TRANSACTION_TEXT' => 'Book Transaction',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TITLE_TEXT' => 'Please enter the refund amount',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CONFIRM_TEXT' => 'Confirm',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_TEXT' => 'Cancel',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SELECT_TEXT' => 'Select',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TEXT' => 'Refund',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BACK_TEXT' => 'Back',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_MANAGE_TRANSACTION' => 'Manage transaction',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_FORMAT' => '(in minimum unit of currency. E.g. enter 100 which is equal to 1.00)',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CAPTURE_TEXT' => 'Are you sure you want to capture the payment?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_ALERT_TEXT' => 'Are you sure you want to cancel the payment?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_ALERT_TEXT' => 'Are you sure you want to refund the amount?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOK_AMOUNT_ALERT_TEXT' => 'Are you sure you want to book the order amount?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_REMAINING_ALERT_TEXT' => 'Are you sure you want to cancel remaining cycles?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_ALL_ALERT_TEXT' => 'Are you sure you want to cancel all cycles?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_SUMMARY_TEXT' => 'Instalment summary',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_TEXT' => 'Instalment Cancel',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALL' => 'Cancel All Cycles',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_REMAINING' => 'Cancel Remaining Cycle',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TEXT' => 'Amount',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_NEXT_DATE' => 'Next Instalment Date',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_STATUS' => 'Status',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_REFUND_TITLE' => 'Refund Extension',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_REFUNDED_TEXT' => 'Refunded',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_PAID_TEXT' => 'Paid',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_PENDING_TEXT' => 'Pending',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCELED_TEXT' => 'Canceled',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_WAIT_TEXT' => 'Please wait...',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SNO' => 'S.NO',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_GUARANTEE_INSTALMENT_PENDING_TEXT' => 'Your order is being verified. Once confirmed, we will send you our bank details to which the order amount should be transferred. Please note that this may take up to 24 hours',
  ];

  /**
   * Core Function : Constructor()
   *
   */
  public function __construct()
  {
    parent::__construct();
    $this->code = 'novalnet_payments';
    $this->title = MODULE_PAYMENT_NOVALNET_PAYMENTS_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_NOVALNET_PAYMENTS_TEXT_DESCRIPTION;
    if (!defined('MODULE_PAYMENT_NOVALNET_PAYMENTS_STATUS')) {
      $this->enabled = false;
      return false;
    }
    $this->enabled = (MODULE_PAYMENT_NOVALNET_PAYMENTS_STATUS && (MODULE_PAYMENT_NOVALNET_PAYMENTS_STATUS == 'True') ? true : false);
    $this->sort_order = 1;
  }

  /**
   * Core Function : selection()
   *
   * Display checkout form in chekout payment page
   * @return array
   */
  public function selection()
  {
    if (!empty($_SESSION['nn_payment_details']) || !empty($_SESSION['nn_booking_details'])) {
      unset($_SESSION['nn_payment_details'], $_SESSION['nn_booking_details']);
    }
    $customer = $this->manager->getCustomersIdentity();
    $address = $this->manager->getCustomersAddresses();
    $order = $this->manager->getOrderInstance();
    $data = [];
    NovalnetHelper::get_merchant_details($data);
    NovalnetHelper::get_transaction_details($data, $order->info);
    NovalnetHelper::get_customer_address_details($data, $address, $customer);
    NovalnetHelper::get_custom_details($data);
    $article_details = htmlentities(json_encode(NovalnetHelper::get_article_details($order)));
    // Get the theme name based on the platform
    $platform_themes = \Yii::$app->getDb()->createCommand("SELECT p2t.platform_id, t.theme_name, t.title " . "FROM " . TABLE_THEMES . " t " . "left join " . TABLE_PLATFORMS_TO_THEMES . " p2t on t.id = p2t.theme_id " . "WHERE p2t.is_default = 1")->queryAll();
    foreach ($platform_themes as $theme) {
      if ($theme['platform_id'] == $order->info['platform_id']) {
        $theme_name_for_platform = $theme['theme_name'];
        break; // Exit the loop as soon as the platform_id is found
      }
    }
    $data['transaction']['system_version'] = PROJECT_VERSION_MAJOR . '_' . PROJECT_VERSION_MINOR . '-NN13.0.1-NNT' . $theme_name_for_platform;
    $data['hosted_page']['type'] = 'PAYMENTFORM';
    $endpoint = NovalnetHelper::get_endpoint('seamless_payment');
    $response = NovalnetHelper::send_request(json_encode($data), $endpoint);
    $redirect_url = $response['result']['redirect_url'];
    return array(
      'id' => $this->code,
      'module' => $this->title,
      'fields' => [
        [
          'title' => "<script type='text/javascript' src='https://cdn.novalnet.de/js/pv13/checkout.js' ></script>" . "<script   integrity='sha384-EqGBn6fy/q0N9GU9524UkXJHe+boceHpqHqH9hfLcRohZG5NBppfcoGIGURlc8Hh' src='" . DIR_WS_CATALOG . "lib/common/modules/orderPayment/lib/novalnet/js/novalnet_payment_form.min.js'></script>",
          'field' => '<iframe  style = "width:100%;border: 0; margin-left: -15px;" id = "novalnet_iframe" src = "' . $redirect_url . '" allow = "payment" referrerPolicy="origin"></iframe><input type="hidden" id="nn_payment_details" name="nn_payment_details"/><input type="hidden" id="nn_line_items" name="nn_line_items" value="' . $article_details . '">'
        ],
      ]
    );
  }
  /**
   * Core Function : pre_confirmation_check()
   *
   * Perform validations for post values
   * @return boolean
   */
  function pre_confirmation_check()
  {
    $response = (!empty($_POST['nn_payment_details'])) ? json_decode($_POST['nn_payment_details'], true) : [];
    if ($response['result']['status'] == 'SUCCESS') {
      $_SESSION['nn_payment_details'] = $response['payment_details'];
      $_SESSION['nn_booking_details'] = $response['booking_details'];
      define('MODULE_PAYMENT_' . $_SESSION['nn_payment_details']['type'] . '_TEXT_TITLE', $_SESSION['nn_payment_details']['name']);
    } else {
      $_SESSION['payment_error'] = $response['result']['message'];
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
    }
  }

  /**
   * Core Function : before_process()
   *
   * Send payment call request to Novalnet server
   * @return boolean
   */
  function before_process()
  {
    $order = $this->manager->getOrderInstance();
    $order->info['payment_class'] = $_SESSION['nn_payment_details']['name'];
    $order->info['payment_method'] = $_SESSION['nn_payment_details']['name'];
    $this->title = $_SESSION['nn_payment_details']['name'];
    define('MODULE_PAYMENT_' . $_SESSION['nn_payment_details']['type'] . '_TEXT_TITLE', $_SESSION['nn_payment_details']['name']);
    $request = $_REQUEST;
    if (isset($request['checksum'])) { // Only for online payments
      if (NovalnetHelper::validate_checksum($request)) { // If checksum validation success
        $response = NovalnetHelper::get_transaction_data($request);
        if ($response['result']['status'] == 'SUCCESS') { // If transaction details call success
          $order = $this->manager->getOrderInstance();
          $order->info['comments'] = $order->info['comments'] . PHP_EOL . NovalnetHelper::get_comments($response);
          NovalnetHelper::store_transaction_details($response);
          Order::setStatus($response['transaction']['order_no'], NovalnetHelper::get_order_status_id($response['transaction']), $order->info);
          $order->info['order_status'] = NovalnetHelper::get_order_status_id($response['transaction']);
          $order->order_id = $response['transaction']['order_no'];
          $this->insert_order_payment_table($order, $response);
          $email_params = \common\helpers\Mail::emailParamsFromOrder($order);
          $email_params['ORDER_COMMENTS'] = $order->info['comments'];
          list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Order Update', $email_params, -1, $order->info['platform_id']);
          \common\helpers\Mail::send(
            $order->customer['firstname'] . ' ' . $order->customer['lastname'],
            $order->customer['email_address'],
            $email_subject,
            $email_text,
            PROJECT_VERSION_NAME,
            NovalnetHelper::get_admin_mail(),
            [],
            '',
            '',
            ['add_br' => 'no', 'platform_id' => $order->info['platform_id']]
          );
          $this->no_process_after($order);
        } else { // If transaction details call failure
          $order->info['comments'] = $order->info['comments'] . PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_DETAILS_TID . ': ' . $response['transaction']['tid'];
          $order->info['comments'] .= $response['transaction']['test_mode'] == 1 ? PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_TEST_ORDER : '';
          $order->info['comments'] .= PHP_EOL . $response['result']['status_text'];
          $_SESSION['payment_error'] = $response['result']['status_text'];
        }
      } else { // Failure
        $order->info['comments'] = $order->info['comments'] . PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_CHECKSUM_ERROR_TEXT;
        $_SESSION['payment_error'] = MODULE_PAYMENT_NOVALNET_PAYMENTS_CHECKSUM_ERROR_TEXT;
      }
      $this->insert_order_payment_table($order, $response);
      Order::setStatus($response['transaction']['order_no'], 5, $order->info);
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
    } else {
      $data = [];
      if ($_SESSION['nn_payment_details']['process_mode'] == 'redirect') {
        $tmp_order_id = $this->saveOrder('TmpOrder');
        $order = $this->manager->getParentToInstanceWithId('\common\classes\TmpOrder', $tmp_order_id);
        $order_id = $order->createOrder();
        $order = $this->manager->getOrderInstance();
      }
      NovalnetHelper::get_merchant_details($data);
      NovalnetHelper::get_transaction_details($data, $order->info);
      NovalnetHelper::get_customer_details($data, $order);
      NovalnetHelper::get_custom_details($data);

      if ($_SESSION['nn_payment_details']['type'] == 'PAYPAL') {
        $cart_info = NovalnetHelper::get_article_details($order, true);
        if (!empty($cart_info)) {
          $data['cart_info'] = $cart_info;
        }
      }

      if (isset($order_id)) {
        $data['transaction']['order_no'] = $order_id;
      }
      $payment_action = isset($_SESSION['nn_booking_details']['payment_action']) && $_SESSION['nn_booking_details']['payment_action'] == 'authorized' ? 'authorize' : 'payment';
      $endpoint = NovalnetHelper::get_endpoint($payment_action);
      $response = NovalnetHelper::send_request(json_encode($data), $endpoint);
      $_SESSION['response'] = $response;
      if ($response['result']['status'] == 'SUCCESS') { // Payment call success
        if (!empty($response['result']['redirect_url'])) { // Only for online payments
          tep_redirect($response['result']['redirect_url']);
        } else {
          $order->info['comments'] = $order->info['comments'] . PHP_EOL . NovalnetHelper::get_comments($_SESSION['response']);
          return true;
        }
      } else { // Payment call failure
        $_SESSION['payment_error'] = $response['result']['status_text'];
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
      }
    }
    return false;
  }

  /**
   * Core Function : after_process()
   *
   * Update the transaction details
   * return none
   */
  public function after_process()
  {
    $order = $this->manager->getOrderInstance();
    NovalnetHelper::update_transaction_details($order->order_id);
    NovalnetHelper::store_transaction_details($_SESSION['response'], $order->order_id);
    Order::setStatus($order->order_id, NovalnetHelper::get_order_status_id($_SESSION['response']['transaction']), $order->info);
    $this->insert_order_payment_table($order, $_SESSION['response']);
    $order->info['order_status'] = NovalnetHelper::get_order_status_id($_SESSION['response']['transaction']);
    unset($_SESSION['response']);
  }

  /**
   * Insert transaction details to order payment table
   *
   * @return none
   */
  public function insert_order_payment_table($order, $transaction_details)
  {
    if ($order_payment = $this->searchRecord($transaction_details['transaction']['tid'])) {
      $order_payment->orders_payment_order_id = $order->order_id;
      $order_payment->orders_payment_module_name = $transaction_details['transaction']['payment_type'];
      $order_payment->orders_payment_snapshot = json_encode(OrderPaymentHelper::getOrderPaymentSnapshot($order));
      $order_payment->orders_payment_transaction_commentary = $order->info['comments'];
      $order_payment->orders_payment_transaction_date = new \yii\db\Expression('now()');
      $order_payment->orders_payment_status = OrderPaymentHelper::OPYS_SUCCESSFUL;
      $order_payment->orders_payment_transaction_status = $transaction_details['transaction']['status'];
      $order_payment->orders_payment_amount = $transaction_details['transaction']['amount'] / 100;
      $order_payment->orders_payment_currency = $transaction_details['transaction']['currency'];
      $order_payment->save(true);
    }
  }

  /**
   * Core Function : get_error()
   *
   * Show validation / error message
   * @return array
   */
  public function get_error()
  {
    $error = [
      'title' => 'ERROR',
      'error' => $_SESSION['payment_error'] ?? $_REQUEST['status_text'],
    ];
    return $error;
  }

  /**
   * Core Function : configure_keys()
   *
   * Payment module installation
   */
  public function configure_keys()
  {
    $lang = NovalnetHelper::get_user_language();
    tep_db_query("
        CREATE TABLE IF NOT EXISTS `novalnet_transaction_details` (
            `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `order_no` VARCHAR(20) NOT NULL,
            `tid` BIGINT(20) NOT NULL,
            `amount` INT(10) NOT NULL,
            `credited_amount` INT(10) NULL,
            `refund_amount` INT(10) NOT NULL,
            `payment_type` VARCHAR(30) NOT NULL,
            `payment_details` LONGTEXT NULL,
            `instalment_details` LONGTEXT,
            `status` VARCHAR(30) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
    return array(
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_STATUS' => array(
        'title' => 'Enable Novalnet Payment Module',
        'value' => 'True',
        'description' => 'Do you want to accept Novalnet payments?',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_SIGNATURE' => array(
        'title' => 'Product activation key',
        'value' => '',
        'description' => 'Get your Product activation key from the Novalnet Admin Portal',
        'sort_order' => '0',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY' => array(
        'title' => 'Payment access key',
        'value' => '',
        'description' => 'Get your Payment access key from the Novalnet Admin Portal <input type="hidden" name="getlang" id="getlang" value="' . $lang . '">',
        'sort_r' => '0',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF' => array(
        'title' => 'Select Tariff ID',
        'value' => '',
        'description' => 'Select a Tariff ID to match the preferred tariff plan you created at the Novalnet Admin Portal for this project',
        'sort_order' => '0',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK_TESTMODE' => array(
        'title' => 'Allow manual testing of the Notification / Webhook URL',
        'value' => 'False',
        'description' => 'Enable this to test the Novalnet Notification / Webhook URL manually. Disable this before setting your shop live to block unauthorized calls from external parties',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK' => array(
        'title' => 'Notification / Webhook URL',
        'value' => ((defined('ENABLE_SSL_CATALOG') && ENABLE_SSL_CATALOG === true) ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . 'lib/common/modules/orderPayment/lib/novalnet/NovalnetWebhook.php',
        'description' => 'You must configure the webhook endpoint in your Novalnet Admin Portal. This will allow you to receive notifications about the transaction<br><button class="btn-primary px-2 conf">Configure</button>',
        'sort_order' => '0',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_SENDMAIL' => array(
        'title' => 'Send e-mail to',
        'value' => '',
        'description' => 'Notification / Webhook URL execution messages will be sent to this e-mail',
        'sort_order' => '0',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_JS_FILE' => array(
        'title' => '',
        'value' => '',
        'description' => '<script integrity="sha384-Q1DpRfqWVSVo0HbSDA7tz6CVRmXX61Ffb3drzubaECbDJWVsBqvcQHl1Nt5jB8ai" src="' . DIR_WS_CATALOG . 'lib/common/modules/orderPayment/lib/novalnet/js/novalnet_auto_config.min.js"></script>',
        'sort_order' => '0',
      ),
      'MODULE_PAYMENT_NOVALNET_PAYMENTS_DIR_PATH' => array(
        'title' => '',
        'value' => '',
        'description' => '<input type="hidden" name="dir" id="dir" value="' . DIR_WS_CATALOG . '">',
        'sort_order' => '0',
      ),
    );
  }

  /**
   * Core Function : isOnline()
   */
  function isOnline()
  {
    return true;
  }

  /**
   * Core Function : describe_status_key()
   */
  public function describe_status_key()
  {
    return new ModuleStatus('MODULE_PAYMENT_NOVALNET_PAYMENTS_STATUS', 'True', 'False');
  }

  /**
   * Core Function : describe_sort_key()
   */
  public function describe_sort_key()
  {
    return new ModuleSortOrder(1);
  }

  /**
   * Core Function : getVersionHistory()
   */
  public static function getVersionHistory()
  {
    return [
      '13.0.1' => 'Novalnet Payments',
    ];
  }
}
