<?php
/**
 * This file is used for extensions(capture, cancel, refund)
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * File: novalnet_extension.php
 *
 */
namespace common\modules\orderPayment\lib\novalnet;

use common\modules\orderPayment\lib\novalnet\NovalnetHelper;
chdir('../../../../../../');
include('includes/application_top.php');
require_once(DIR_FS_CATALOG . 'lib/common/modules/orderPayment/lib/novalnet/const/novalnet_payments.const.php');

// Get reuest values from novalnet extension
if (!empty($_REQUEST['order_id'])) {
    $order_id = $_REQUEST['order_id'];
    // Set event and amount based on conditions
    if (!empty($_REQUEST['trans_status'])) {
        $event = $_REQUEST['trans_status'];
    } elseif (!empty($_REQUEST['action'])) {
        $event = strtoupper($_REQUEST['action']);
        $amount = $_REQUEST['amount'] ?? ''; // Use null coalescing operator
    } else {
        echo "Invalid values";
        exit;
    }
} else {
    echo "Invalid values";
    exit;
}

// Get novalnet transaction details
$order_details = NovalnetHelper::get_novalnet_transaction_details($order_id);
// Form the request parameter for CURL send to novalnet server
$data = [];
$data['transaction'] = [
    'tid' => (!empty($_REQUEST['refund_tid'])) ? $_REQUEST['refund_tid'] : $order_details['tid'],
];
if ($event == 'REFUND') {
    $data['transaction']['amount'] = $amount;
    if (!empty($_REQUEST['refund_reason'])) {
        $data['transaction']['reason'] = $_REQUEST['refund_reason'];
    }

    $endpoint = NovalnetHelper::get_endpoint('refund');
} elseif ($event == 'CONFIRM') {
    $data['transaction']['order_no'] = $order_id;
    $endpoint = NovalnetHelper::get_endpoint('capture');
} elseif ($event == 'BOOK_AMOUNT') {
    $manager = \common\services\OrderManager::loadManager();
    $order = $manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
    NovalnetHelper::get_customer_details($data, $order);
    NovalnetHelper::get_transaction_details($data, $order->info);
    NovalnetHelper::get_custom_details($data);
    $data['transaction']['payment_type'] = $order_details['payment_type'];
    $data['transaction']['order_no'] = $order_id;
    $data['transaction']['amount'] = $amount;
    $data['transaction']['payment_data']['token'] = $order_details['payment_details'];
    $data['merchant'] = [
        'signature' => NovalnetHelper::get_plugin_configuration_order_id('MODULE_PAYMENT_NOVALNET_PAYMENTS_SIGNATURE', $order_id),
        'tariff' => NovalnetHelper::get_plugin_configuration_order_id('MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF', $order_id),
    ];
    $endpoint = NovalnetHelper::get_endpoint('payment');
} else if ($event == 'REMAINING_CYCLES' || $event == 'ALL_CYCLES') {
    unset($data['transaction']);
    $data['instalment'] = [
        'cancel_type' => $event == 'ALL_CYCLES' ? 'CANCEL_ALL_CYCLES' : 'CANCEL_REMAINING_CYCLES',
        'tid' => $order_details['tid'],
    ];
    $endpoint = NovalnetHelper::get_endpoint('instalment_cancel');
} else {
    $endpoint = NovalnetHelper::get_endpoint('cancel');
}

NovalnetHelper::get_custom_details($data);
$data['custom']['shop_invoked'] = 1;
$json_data = json_encode($data);

// Send request to Novalnet
$response = NovalnetHelper::send_request($json_data, $endpoint, '', $order_id);
if ($response['result']['status'] == 'SUCCESS') {

    switch ($event) {
        case 'CONFIRM':
            handle_transaction_capture($response);
            break;
        case 'CANCEL':
            handle_transaction_cancel($response);
            break;
        case 'REFUND':
            handle_transaction_refund($response, $order_details);
            break;
        case 'BOOK_AMOUNT':
            handle_transaction_book_amount($response);
            break;
        case 'REMAINING_CYCLES':
        case 'ALL_CYCLES':
            handle_instalment_cancel($response, $order_details);
            break;
    }
}
echo json_encode($response);
function handle_transaction_capture($response)
{
    $novalnet_table_data = array(
        'status' => $response['transaction']['status'],
    );
    $comments = MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CAPTURE_COMMENT . ' ' . date("d-m-Y") . PHP_EOL;
    $comments .= PHP_EOL . NovalnetHelper::get_comments($response);
    $order_status_id = NovalnetHelper::get_order_status_id($response['transaction']);
    if (in_array($response['transaction']['payment_type'], array('INSTALMENT_INVOICE', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) {
        $instalment_details = NovalnetHelper::store_instalment_details($response);
        $novalnet_table_data['instalment_details'] = $instalment_details;
    }
    tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', "order_no = '" . $response['transaction']['order_no'] . "'");
    NovalnetHelper::update_order_table($comments, $order_status_id, $response['transaction']['order_no']);
}

function handle_transaction_cancel($response)
{
    $novalnet_table_data = array(
        'status' => $response['transaction']['status'],
    );
    $order_status_id = NovalnetHelper::get_order_status_id($response['transaction']);
    $comments = MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CANCEL_COMMENT . ' ' . date("d-m-Y");
    tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', "order_no = '" . $response['transaction']['order_no'] . "'");
    NovalnetHelper::update_order_table($comments, $order_status_id, $response['transaction']['order_no']);
}

function handle_transaction_refund($response, $order_details)
{
    $currencies = \Yii::$container->get('currencies');
    $refund_amount = $response['transaction']['refund']['amount'] / 100;
    $refunded_amount = $order_details['refund_amount'] + $response['transaction']['refund']['amount'];
    $formatted_amount = $currencies->format($refund_amount, false, $response['transaction']['refund']['currency']);
    $order_status_id = 5;
    $novalnet_table_data = array(
        'status' => 'REFUNDED',
        'refund_amount' => $refunded_amount,
    );
    $comments = !empty($response['transaction']['refund']['tid']) ? sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT_FULL, $response['transaction']['tid'], $formatted_amount, $response['transaction']['refund']['tid']) : sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT, $response['transaction']['tid'], $formatted_amount);
    if ($order_details['amount'] > $refunded_amount) {
        $order_status_id = 100027;
        $novalnet_table_data['status'] = 'CONFIRMED';
    }
    if (in_array($response['transaction']['payment_type'], array('INSTALMENT_INVOICE', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) {
        $instalment_details = json_decode($order_details['instalment_details'], true);
        if (!empty($instalment_details)) {
            $cycle = $_REQUEST['instalment_cycle'];
            $instalment_amount = $instalment_details[$cycle]['instalment_cycle_amount'];
            $instalment_amount = $instalment_amount - $refunded_amount;
            $instalment_details[$cycle]['instalment_cycle_amount'] = $instalment_amount;
            if ($instalment_details[$cycle]['instalment_cycle_amount'] <= 0) {
                $instalment_details[$cycle]['status'] = 'Refunded';
            }
        }
        $instalment_details = json_encode($instalment_details);
        $novalnet_table_data['instalment_details'] = $instalment_details;
    }
    tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', "order_no = '" . $response['transaction']['order_no'] . "'");
    NovalnetHelper::update_order_table($comments, $order_status_id, $response['transaction']['order_no']);
}

function handle_transaction_book_amount($response)
{
    $currencies = \Yii::$container->get('currencies');
    $booked_amount = $response['transaction']['amount'] / 100;
    $comments = sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_TRANS_BOOKED_MESSAGE, $currencies->format($booked_amount, false, $response['transaction']['currency']), $response['transaction']['tid']);
    $order_status_id = NovalnetHelper::get_order_status_id($response['transaction']);
    $novalnet_table_data = array(
        'status' => $response['transaction']['status'],
        'amount' => $response['transaction']['amount'],
        'tid' => $response['transaction']['tid'],
    );
    tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', "order_no = '" . $response['transaction']['order_no'] . "'");
    NovalnetHelper::update_order_table($comments, $order_status_id, $response['transaction']['order_no']);
}

function handle_instalment_cancel($response, $order_details)
{
    $currencies = \Yii::$container->get('currencies');
    $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_REMAINING_CYCLES_TEXT, $order_details['tid'], gmdate('d.m.Y'));
    $order_status_id = NovalnetHelper::get_order_status_id($response['transaction']);
    $instalment_details = json_decode($order_details['instalment_details'], true);
    if (!empty($instalment_details)) {
        $amount = (isset($response['transaction']['refund']['amount']) ? $response['transaction']['refund']['amount'] / 100 : '');
        if ($response['instalment']['cancel_type'] == 'ALL_CYCLES') {
            $comments = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALLCYCLES_TEXT, $order_details['tid'], gmdate('d.m.Y'), $currencies->format($amount, false, $response['transaction']['refund']['currency']));
        }
        foreach ($instalment_details as $key => $instalment_details_data) {
            if ($instalment_details_data['status'] == 'Pending') {
                $instalment_details[$key]['status'] = 'Canceled';
            }
            if ($response['instalment']['cancel_type'] == 'ALL_CYCLES' && $instalment_details_data['status'] == 'Paid') {
                $instalment_details[$key]['status'] = 'Refunded';
                $order_status_id = 5;
            }
        }
    }
    $instalment_details = json_encode($instalment_details);
    $novalnet_table_data = array(
        'instalment_details' => $instalment_details,
        'status' => $response['transaction']['status'],
    );
    tep_db_perform('novalnet_transaction_details', $novalnet_table_data, 'update', "order_no = '" . $response['transaction']['order_no'] . "'");
    NovalnetHelper::update_order_table($comments, $order_status_id, $response['transaction']['order_no']);
}
