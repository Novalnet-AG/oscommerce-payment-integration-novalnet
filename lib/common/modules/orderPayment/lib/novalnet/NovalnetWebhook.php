<?php

/**
 * This file is used for handling webhook events
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * File: NovalnetWebhook.php
 *
 */

namespace common\modules\orderPayment\lib\novalnet;

chdir("../../../../../../");
require('includes/application_top.php');
require_once(DIR_FS_CATALOG . 'lib/common/modules/orderPayment/lib/novalnet/lang/english/novalnet_payments.lang.php');
use common\helpers\Mail;
use common\helpers\OrderPayment as OrderPaymentHelper;
use common\modules\orderPayment\lib\novalnet\NovalnetHelper;

/**
 * Novalnet Webhook.
 *
 */
class NovalnetWebhook
{
    /**
     * Mandatory Parameters.
     *
     * @var array
     */
    protected $mandatory = [
        'event' => [
            'type',
            'checksum',
            'tid',
        ],
        'merchant' => [
            'vendor',
            'project',
        ],
        'result' => [
            'status',
        ],
        'transaction' => [
            'tid',
            'payment_type',
            'status',
        ],
    ];

    /**
     * Request parameters.
     *
     * @var array
     */
    protected $event_data = [];

    /**
     * Order reference values.
     *
     * @var array
     */
    protected $order_reference = [];

    /**
     * Recived Event type.
     *
     * @var string
     */
    protected $event_type;

    /**
     * Recived Event TID.
     *
     * @var int
     */
    protected $event_tid;

    /**
     * Recived Event parent TID.
     *
     * @var int
     */
    protected $parent_tid;

    /**
     * Currency Values.
     *
     * @var int
     */
    protected $currency;

    /**
     * Novalnet table condition value.
     *
     * @var int
     */
    protected $novalnet_table_condition;

    /**
     * Novalnet_Webhooks constructor.
     */
    public function __construct()
    {
        // Validate event data.
        $this->validate_event_data();
        // Authenticate request host.
        $this->validate_ip_address();
        // Validate checksum.
        $this->validate_checksum();
        // Set event data.
        $this->event_type = $this->event_data['event']['type'];
        $this->event_tid = !empty($this->event_data['event']['tid']) ? $this->event_data['event']['tid'] : '';
        $this->parent_tid = $this->event_data['event']['parent_tid'] ? $this->event_data['event']['parent_tid'] : $this->event_tid;
        $this->currency = \Yii::$container->get('currencies');
        $this->novalnet_table_condition = "order_no = '" . $this->event_data['transaction']['order_no'] . "'";
        // Get order reference.
        $this->order_reference = $this->get_order_reference();
        if (!empty($this->event_data['transaction']['order_no']) && $this->order_reference['order_no'] !== $this->event_data['transaction']['order_no']) {
            $this->display_message(['message' => 'Order reference not matching.']);
        }
        switch ($this->event_type) {
            case 'PAYMENT':
                $this->display_message(['message' => "The webhook notification received ('" . $this->event_data['transaction']['payment_type'] . "') for the TID: '" . $this->event_tid . "'"]);
                break;
            case 'TRANSACTION_CAPTURE':
                $this->handle_transaction_capture();
                break;
            case 'TRANSACTION_CANCEL':
                $this->handle_transaction_cancel();
                break;
            case 'TRANSACTION_REFUND':
                $this->handle_transaction_refund();
                break;
            case 'TRANSACTION_UPDATE':
                $this->handle_transaction_update();
                break;
            case 'CREDIT':
                $this->handle_transaction_credit();
                break;
            case 'CHARGEBACK':
                $this->handle_chargeback();
                break;
            case 'INSTALMENT':
                $this->handle_instalment();
                break;
            case 'INSTALMENT_CANCEL':
                $this->handle_instalment_cancel();
                break;
            case 'PAYMENT_REMINDER_1':
            case 'PAYMENT_REMINDER_2':
                $this->handle_payment_reminder();
                break;
            case 'SUBMISSION_TO_COLLECTION_AGENCY':
                $this->handle_collection_submission();
                break;
            default:
                $this->display_message(['message' => "The webhook notification has been received for the unhandled EVENT type($this->event_type)"]);
        }
    }

    /**
     * Validate the IP control check
     *
     * @return none
     */
    public function validate_ip_address(): void
    {
        $novalnet_host_ip = gethostbyname('pay-nn.de');
        $request_received_ip = $this->get_remote_address($novalnet_host_ip);
        if (($novalnet_host_ip) == '') {
            $this->display_message(['message' => 'Novalnet HOST IP missing']);
        }
        // Condition to check whether the callback is called from authorized IP
        $webhook_test_mode = NovalnetHelper::get_plugin_configuration_order_id('MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK_TESTMODE', $this->event_data['transaction']['order_no']);
        if (($novalnet_host_ip !== $request_received_ip) && ($webhook_test_mode == 'False')) {
            $this->display_message(['message' => 'Unauthorised access from the IP ' . $request_received_ip]);
        }
    }

    /**
     * Retrieves the original remote ip address with and without proxy
     *
     * @return string
     */
    public function get_remote_address($novalnetHostIP)
    {
        $ip_keys = array('HTTP_X_FORWARDED_HOST', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                if (in_array($key, ['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_HOST'])) {
                    $forwardedIP = !empty($_SERVER[$key]) ? explode(',', $_SERVER[$key]) : [];
                    if (in_array($novalnetHostIP, $forwardedIP)) {
                        return $novalnetHostIP;
                    } else {
                        return $_SERVER[$key];
                    }
                }
                return $_SERVER[$key];
            }
        }
    }

    /**
     * Validate event data
     *
     * @return void
     */
    public function validate_event_data()
    {
        try {
            $this->event_data = json_decode(file_get_contents('php://input'), true);
        } catch (Exception $e) {
            $this->display_message(['message' => "Received data is not in the JSON format $e"]);
        }

        if (!empty($this->event_data['custom']['shop_invoked'])) {
            $this->display_message(['message' => 'Process already handled in the shop.']);
        }

        // Validate request parameters.
        foreach ($this->mandatory as $category => $parameters) {
            if (empty($this->event_data[$category])) {
                // Could be a possible manipulation in the event data.
                $this->display_message(['message' => "Required parameter category($category) not received"]);
            } elseif (!empty($parameters)) {
                foreach ($parameters as $parameter) {
                    if (empty($this->event_data[$category][$parameter])) {
                        // Could be a possible manipulation in the event data.
                        $this->display_message(['message' => "Required parameter($parameter) in the category($category) not received"]);
                    } elseif (in_array($parameter, ['tid', 'parent_tid'], true) && !preg_match('/^\d{17}$/', $this->event_data[$category][$parameter])) {
                        $this->display_message(['message' => "Invalid TID received in the category($category) not received $parameter"]);
                    }
                }
            }
        }
    }

    /**
     * Validate checksum
     *
     * @return void
     */
    public function validate_checksum()
    {
        $payment_access_key = NovalnetHelper::get_plugin_configuration_order_id('MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY', $this->event_data['transaction']['order_no']);
        $token_string = $this->event_data['event']['tid'] . $this->event_data['event']['type'] . $this->event_data['result']['status'];
        if (isset($this->event_data['transaction']['amount'])) {
            $token_string .= $this->event_data['transaction']['amount'];
        }
        if (isset($this->event_data['transaction']['currency'])) {
            $token_string .= $this->event_data['transaction']['currency'];
        }
        if (!empty($payment_access_key)) {
            $token_string .= strrev($payment_access_key);
        }
        $generated_checksum = hash('sha256', $token_string);
        if ($generated_checksum !== $this->event_data['event']['checksum']) {
            $this->display_message(['message' => 'While notifying some data has been changed. The hash check failed']);
        }
    }

    /**
     * Get order reference.
     *
     * @return array
     */
    public function get_order_reference()
    {
        $novalnet_order_details = NovalnetHelper::get_novalnet_transaction_details($this->event_data['transaction']['order_no']);
        if (isset($novalnet_order_details['order_no'])) {
            $order_number = $novalnet_order_details['order_no'];
        }
        if (empty($order_number) && !empty($this->event_data['transaction']['order_no'])) {
            $orders_id = tep_db_fetch_array(tep_db_query("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = '" . $this->event_data['transaction']['order_no'] . "'"));
            $order_number = !empty($orders_id) ? $orders_id['orders_id'] : $order_number;
            if (!isset($novalnet_order_details['order_no']) && empty($order_number)) {
                $this->display_message(['message' => 'Order reference not found in the Shop']);
            }
            if (empty($novalnet_order_details['order_no']) && !empty($orders_id['orders_id'])) {
                $this->handle_communication_break($orders_id);
            }
        }
        return $novalnet_order_details;
    }

    /**
     * Handle communication break
     *
     * @param $order_details
     *
     * @return array
     */
    function handle_communication_break($order_details)
    {
        if ($this->event_data['result']['status'] == 'SUCCESS') {
            $order_details['comments'] = PHP_EOL . NovalnetHelper::get_comments($this->event_data);
            $novalnet_table_data = array(
                'order_no' => $this->event_data['transaction']['order_no'],
                'tid' => $this->event_data['transaction']['tid'],
                'amount' => $this->event_data['transaction']['amount'],
                'payment_type' => $this->event_data['transaction']['payment_type'],
                'status' => $this->event_data['transaction']['status'],
            );
            tep_db_perform('novalnet_transaction_details', $novalnet_table_data);
            NovalnetHelper::update_order_table($order_details['comments'], NovalnetHelper::get_order_status_id($this->event_data['transaction']), $this->event_data['transaction']['order_no']);
            $this->webhook_insert_order_payment_table($order_details['comments']);
            $this->send_webhook_mail($order_details['comments']);
        } else {
            $order_details['comments']  = PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_DETAILS_TID  . ' ' . $this->event_data['transaction']['tid']; 
            $order_details['comments'] .= $this->event_data['transaction']['test_mode'] == 1 ? PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_TEST_ORDER : ''; 
            $order_details['comments'] .= PHP_EOL . $this->event_data['result']['status_text'];
            $this->webhook_insert_order_payment_table($order_details['comments']);
            NovalnetHelper::update_order_table($order_details['comments'], NovalnetHelper::get_order_status_id($this->event_data['transaction']), $order_details['orders_id']);
        }
        $this->display_message(['message' => "Novalnet webhook received"]);
    }

    /**
     * Handling the transaction capture process
     *
     * @return none
     */
    public function handle_transaction_capture()
    {
        $comments = MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CAPTURE_COMMENT . ' ' . date("d-m-Y") . PHP_EOL;
        $novalnet_table_data = array(
            'status' => $this->event_data['transaction']['status'],
        );
        $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
        $amount = $this->event_data['instalment']['cycle_amount'] ? $this->event_data['instalment']['cycle_amount'] / 100 : $this->event_data['transaction']['amount'] / 100;
        $amount = $this->currency->format($amount, false, $this->event_data['transaction']['currency']);
        if (in_array($this->event_data['transaction']['payment_type'], array('GUARANTEED_INVOICE', 'INVOICE', 'INSTALMENT_INVOICE', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) {
            $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
            if ($this->event_data['transaction']['payment_type'] != 'INSTALMENT_DIRECT_DEBIT_SEPA') {
                $comments .= NovalnetHelper::get_bank_account_details($this->event_data);
            }

            if (!empty($this->event_data['instalment'])) {
                $instalment_details = NovalnetHelper::store_instalment_details($this->event_data);
                $novalnet_table_data['instalment_details'] = $instalment_details;
                $comments .= PHP_EOL . NovalnetHelper::instalment_details($this->event_data);
            }
        }
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', $this->novalnet_table_condition);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the transaction cancel process
     *
     * @return none
     */
    public function handle_transaction_cancel()
    {
        $comments = MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CANCEL_COMMENT . ' ' . date("d-m-Y") . PHP_EOL;
        $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        $novalnet_table_data = array(
            'status' => $this->event_data['transaction']['status'],
        );
        tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', $this->novalnet_table_condition);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the transaction refund process
     *
     * @return none
     */
    public function handle_transaction_refund()
    {
        if ($this->event_data['result']['status'] == 'SUCCESS') {
            $refunded_amount = $this->order_reference['refund_amount'] + $this->event_data['transaction']['refund']['amount'];
            $formatted_amount = $this->event_data['transaction']['refund']['amount'] / 100;
            $formatted_amount = $this->currency->format($formatted_amount, false, $this->event_data['transaction']['currency']);
            $order_status_id = NovalnetHelper::get_order_status($this->event_data['transaction']['order_no']);

            if ($this->event_data['transaction']['status'] == 'DEACTIVATED') {
                $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
            }
            $comments = sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT, $this->parent_tid, $formatted_amount);
            if (!empty($this->event_data['transaction']['refund']['tid'])) {
                $comments = sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT_FULL, $this->parent_tid, $formatted_amount, $this->event_data['transaction']['refund']['tid']);
                $order_status_id = 100027;
                $novalnet_table_data = array(
                    'refund_amount' => $refunded_amount,
                );
            } else {
                $novalnet_table_data['amount'] = $this->order_reference['amount'] - $refunded_amount;
            }
            if ($this->order_reference['amount'] <= $refunded_amount) {
                $novalnet_table_data = array(
                    'status' => 'REFUNDED',
                    'refund_amount' => $refunded_amount,
                );
                $order_status_id = 5;
            }
            if (in_array($this->event_data['transaction']['payment_type'], array('INSTALMENT_INVOICE', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) {
                $instalment_details = json_decode($this->order_reference['instalment_details'], true);
                if (!empty($instalment_details)) {
                    foreach ($instalment_details as $cycle => $cycle_details) {
                        if (!empty($cycle_details['reference_tid']) && ($cycle_details['reference_tid'] == $this->parent_tid)) {
                            $instalment_amount = $instalment_details[$cycle]['instalment_cycle_amount'] - $this->event_data['transaction']['refund']['amount'];
                            $instalment_details[$cycle]['instalment_cycle_amount'] = $instalment_amount;
                            if ($instalment_details[$cycle]['instalment_cycle_amount'] <= 0) {
                                $instalment_details[$cycle]['status'] = 'Refunded';
                            }
                        }
                    }
                }
                $instalment_details = json_encode($instalment_details);
                $novalnet_table_data['instalment_datails'] = $instalment_details;
            }
            NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
            tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', $this->novalnet_table_condition);
            $this->send_webhook_mail($comments);
        } else {
            $comments = $this->event_data['result']['status_text'];
        }
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the transaction credit process
     *
     * @return none
     */
    public function handle_transaction_credit()
    {
        $credit_amount = $this->order_reference['credited_amount'] ?? null;
        $credited_amount = $credit_amount + $this->event_data['transaction']['amount'];
        $formatted_amount = $this->event_data['transaction']['amount'] / 100;
        $comments = sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CREDIT_COMMENT, $this->parent_tid, $this->currency->format($formatted_amount, false, $this->event_data['transaction']['currency']), date("d-m-Y"), $this->event_tid);
        $order_status_id = NovalnetHelper::get_order_status($this->event_data['transaction']['order_no']);

        $novalnet_table_data = array(
            'status' => $this->event_data['transaction']['status'],
        );
        if (in_array($this->event_data['transaction']['payment_type'], ['INVOICE_CREDIT', 'CASHPAYMENT_CREDIT', 'MULTIBANCO_CREDIT', 'ONLINE_TRANSFER_CREDIT'])) {
            $novalnet_table_data = array(
                'credited_amount' => $credited_amount,
                'status' => $this->event_data['transaction']['status'],
            );
            if ($credited_amount >= $this->order_reference['amount']) {
                $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
                $novalnet_table_data['credited_amount'] = $credited_amount;
            }
        }
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', $this->novalnet_table_condition);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the transaction update process
     *
     * @return none
     */
    public function handle_transaction_update()
    {
        // Status update process
        if ($this->event_data['transaction']['update_type'] == 'STATUS') {
            $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_TRANS_DEACTIVATED_MESSAGE, $this->event_data['transaction']['tid'], gmdate('d.m.Y') . ',' . gmdate('H:i:s'));
            $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
            $novalnet_table_data = array('status' => $this->event_data['transaction']['status']);
            if (in_array($this->event_data['transaction']['status'], ['ON_HOLD', 'CONFIRMED'])) {
                $comments = PHP_EOL . sprintf(
                    MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_STATUS_PENDING_TO_ONHOLD_TEXT,
                    $this->event_data['transaction']['tid'],
                    gmdate('d.m.Y') . ',' . gmdate('H:i:s')
                );
                if ($this->event_data['transaction']['status'] == 'CONFIRMED') {
                    $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_STATUS_PENDING_TO_CONFIRMED_TEXT, $this->event_data['transaction']['tid'], gmdate('d.m.Y') . ',' . gmdate('H:i:s')) . PHP_EOL;
                }
                $comments .= !empty($this->event_data['transaction']['bank_details']) ? NovalnetHelper::get_bank_account_details($this->event_data) . PHP_EOL : '';
                $comments .= !empty($this->event_data['instalment']) && $this->event_data['transaction']['status'] == 'CONFIRMED' ? PHP_EOL . NovalnetHelper::instalment_details($this->event_data) : '';
            }
        }
        // Amount and due date update process
        if (in_array($this->event_data['transaction']['update_type'], array('DUE_DATE', 'AMOUNT', 'AMOUNT_DUE_DATE'))) { // Form the note based on the update_type
            $amount = $this->event_data['transaction']['amount'] / 100;
            $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_AMOUNT_UPDATE_NOTE, $this->currency->format($amount, false, $this->event_data['transaction']['currency']), gmdate('d.m.Y')) . PHP_EOL;
            if (!empty($this->event_data['transaction']['due_date'])) { // If due_date is not empty
                $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_DUEDATE_UPDATE_NOTE, $this->event_data['transaction']['due_date'], gmdate('d.m.Y')) . PHP_EOL;
            } else {
                $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_AMOUNT_DUEDATE_UPDATE_NOTE, $this->currency->format($amount, false, $this->event_data['transaction']['currency']), $this->event_data['transaction']['due_date'], gmdate('d.m.Y')) . PHP_EOL;
            }
            $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
        }
        tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', $this->novalnet_table_condition);
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the instalment update process
     *
     * @return none
     */
    public function handle_instalment()
    {
        $comments = '';
        $instalment_details = json_decode($this->order_reference['instalment_details'], true);
        $instalment = $this->event_data['instalment'];
        $cycle_index = $instalment['cycles_executed'] - 1;
        if (!empty($instalment)) {
            $instalment_details[$cycle_index]['next_instalment_date'] = (!empty($instalment['next_cycle_date'])) ? $instalment['next_cycle_date'] : '-';
            if (!empty($this->event_data['transaction']['tid'])) {
                $instalment_details[$cycle_index]['reference_tid'] = $this->event_data['transaction']['tid'];
                $instalment_details[$cycle_index]['status'] = 'Paid';
                $instalment_details[$cycle_index]['paid_date'] = date('Y-m-d H:i:s');
            }
        }
        $amount = $this->event_data['instalment']['cycle_amount'] / 100;
        $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK_NEW_INSTALMENT_NOTE, $this->parent_tid, $this->event_tid, $this->currency->format($amount, false, $this->event_data['transaction']['currency']), gmdate('d-m-Y'));
        if ($this->event_data['transaction']['payment_type'] == 'INSTALMENT_INVOICE') {
            $comments .= PHP_EOL . NovalnetHelper::get_bank_account_details($this->event_data);
        }
        $comments .= PHP_EOL . NovalnetHelper::instalment_details($this->event_data);
        $instalment_details = json_encode($instalment_details);
        $novalnet_table_data = array(
            'instalment_details' => $instalment_details,
        );
        tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', $this->novalnet_table_condition);
        NovalnetHelper::update_order_table($comments, NovalnetHelper::get_order_status($this->event_data['transaction']['order_no']), $this->event_data['transaction']['order_no']);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the instalment cancel process
     *
     * @return none
     */
    public function handle_instalment_cancel()
    {
        $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_REMAINING_CYCLES_TEXT, $this->parent_tid, gmdate('d.m.Y'));
        $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
        $instalment_details = json_decode($this->order_reference['instalment_details'], true);
        if (!empty($instalment_details)) {
            $amount = (isset($this->event_data['transaction']['refund']['amount']) ? $this->event_data['transaction']['refund']['amount'] / 100 : '');
            if ($this->event_data['instalment']['cancel_type'] == 'ALL_CYCLES') {
                $comments = !empty($amount) ? PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALLCYCLES_TEXT, $this->parent_tid, gmdate('d.m.Y'), $this->currency->format($amount, false, $this->event_data['transaction']['refund']['currency'])) : PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALLCYCLES, $this->parent_tid, gmdate('d.m.Y'));
            }
            foreach ($instalment_details as $key => $instalment_details_data) {
                if ($instalment_details_data['status'] == 'Pending') {
                    $instalment_details[$key]['status'] = 'Canceled';
                }
                if ($this->event_data['instalment']['cancel_type'] == 'ALL_CYCLES' && $instalment_details_data['status'] == 'Paid') {
                    $instalment_details[$key]['status'] = 'Refunded';
                    $order_status_id = 5;
                }
            }
        }
        $instalment_details = json_encode($instalment_details);
        $novalnet_table_data = array(
            'instalment_details' => $instalment_details,
            'status' => $this->event_data['transaction']['status'],
        );
        tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', $this->novalnet_table_condition);
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the transaction chargeback process
     *
     * @return none
     */
    public function handle_chargeback()
    {
        $amount = $this->event_data['transaction']['amount'] / 100;
        $comments = sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CHARGEBACK_COMMENT, $this->parent_tid, $this->currency->format($amount, false, $this->event_data['transaction']['currency']), date("d-m-Y h:i:sa"), $this->event_tid);
        $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the payment remainder process
     *
     * @return none
     */
    public function handle_payment_reminder()
    {
        $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_REMINDER_NOTE, explode('_', $this->event_type)[2]);
        $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Handling the collection process
     *
     * @return none
     */
    public function handle_collection_submission()
    {
        $comments = sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_COLLECTION_SUBMISSION_NOTE, $this->event_data['collection']['reference']);
        $order_status_id = NovalnetHelper::get_order_status_id($this->event_data['transaction']);
        NovalnetHelper::update_order_table($comments, $order_status_id, $this->event_data['transaction']['order_no']);
        $this->send_webhook_mail($comments);
        $this->display_message(['message' => $comments]);
    }

    /**
     * Insert order payment table
     *
     * @return none
     */
    function webhook_insert_order_payment_table($comments)
    {
        if ($orderPayment = OrderPaymentHelper::searchRecord($this->event_data['transaction']['payment_type'], $this->event_data['transaction']['tid'])) {
            $orderPayment->orders_payment_order_id = $this->event_data['transaction']['order_no'];
            $orderPayment->orders_payment_transaction_commentary = $comments;
            $orderPayment->orders_payment_transaction_date = new \yii\db\Expression('now()');
            $orderPayment->orders_payment_status = OrderPaymentHelper::OPYS_SUCCESSFUL;
            $orderPayment->orders_payment_transaction_status = $this->event_data['transaction']['status'];
            $orderPayment->orders_payment_amount = $this->event_data['transaction']['amount'] / 100;
            $orderPayment->orders_payment_currency = $this->event_data['transaction']['currency'];
            $orderPayment->orders_payment_module_name = ucwords(strtolower($this->event_data['transaction']['payment_type']));
            $orderPayment->save(false);
        }
    }

    /**
     * Send the Webhook mail
     *
     * @return none
     */
    public function send_webhook_mail($email_text)
    {
        $mail = NovalnetHelper::get_plugin_configuration_order_id('MODULE_PAYMENT_NOVALNET_PAYMENTS_SENDMAIL', $this->event_data['transaction']['order_no']);
        if (!empty($mail)) {
            $admin_mail = NovalnetHelper::get_admin_mail();
            Mail::send($this->event_data['customer']['first_name'] . '' . $this->event_data['customer']['last_name'], $mail, MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_MAIL_SUBJECT, $email_text, PROJECT_VERSION_NAME, $admin_mail, []);
        } else {
            $this->display_message(['message' => $email_text]);
        }
    }

    /**
     * Print the Webhook messages.
     *
     * @param array $data
     *
     * @return void
     */
    public function display_message($data)
    {
        print_r($data['message']);
        exit;
    }
}
new NovalnetWebhook();
