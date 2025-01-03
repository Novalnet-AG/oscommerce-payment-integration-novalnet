<?php

/**
 * This file is used for helper functions for the all payments
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * File: NovalnetHelper.php
 *
 */

namespace common\modules\orderPayment\lib\novalnet;

class NovalnetHelper
{
    /**
     * Get API end point
     *
     * @param string $action
     *
     * @return string
     */
    public static function get_endpoint($action)
    {
        switch ($action) {
            case 'seamless_payment':
                return 'https://payport.novalnet.de/v2/seamless/payment';
                break;
            case 'payment':
                return 'https://payport.novalnet.de/v2/payment';
                break;
            case 'authorize':
                return 'https://payport.novalnet.de/v2/authorize';
                break;
            case 'capture':
                return 'https://payport.novalnet.de/v2/transaction/capture';
                break;
            case 'cancel':
                return 'https://payport.novalnet.de/v2/transaction/cancel';
                break;
            case 'update':
                return 'https://payport.novalnet.de/v2/transaction/update';
                break;
            case 'refund':
                return 'https://payport.novalnet.de/v2/transaction/refund';
                break;
            case 'merchant_details':
                return 'https://payport.novalnet.de/v2/merchant/details';
                break;
            case 'webhook_configure':
                return 'https://payport.novalnet.de/v2/webhook/configure';
                break;
            case 'transaction_details':
                return 'https://payport.novalnet.de/v2/transaction/details';
                break;
            case 'instalment_cancel':
                return 'https://payport.novalnet.de/v2/instalment/cancel';
                break;
        }
    }

    /**
     * Get the merchant credentials
     *
     * @param $data
     *
     * @return none
     */
    public static function get_merchant_details(&$data)
    {
        $data['merchant'] = [
            'signature' => self::get_plugin_configuration_value('MODULE_PAYMENT_NOVALNET_PAYMENTS_SIGNATURE'),
            'tariff' => self::get_plugin_configuration_value('MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF'),
        ];
    }

    /**
     * Get customer data for payment call
     *
     * @param $data
     * @param $order
     *
     * @return none
     */
    public static function get_customer_details(&$data, $order)
    {
        $data['customer'] = [
            'first_name' => $order->customer['firstname'],
            'last_name' => $order->customer['lastname'],
            'email' => $order->customer['email_address'],
            'customer_ip' => self::get_ip_address(),
            'customer_no' => $order->customer['customer_id'],
            'billing' => [
                'street' => $order->billing['street_address'],
                'city' => $order->billing['city'],
                'zip' => $order->billing['postcode'],
                'country_code' => $order->billing['country']['iso_code_2'],
            ],
            'shipping' => [
                'street' => $order->delivery['street_address'],
                'city' => $order->delivery['city'],
                'zip' => $order->delivery['postcode'],
                'country_code' => $order->delivery['country']['iso_code_2'],
            ]
        ];

        if ($data['customer']['billing'] == $data['customer']['shipping']) {
            $data['customer']['shipping'] = [];
            $data['customer']['shipping']['same_as_billing'] = 1;
        }

        if (!empty($order->delivery['company'])) {
            $data['customer']['shipping']['company'] = $order->delivery['company'];
        }

        if (!empty($order->customer['telephone'])) {
            $data['customer']['tel'] = $order->customer['telephone'];
        }
        if (!empty($_SESSION['nn_booking_details']['birth_date'])) {
            $data['customer']['birth_date'] = date("Y-m-d", strtotime($_SESSION['nn_booking_details']['birth_date']));
        }
        if (!empty($order->billing['company'])) {
            $data['customer']['billing']['company'] = $order->billing['company'];
        }
    }

    /**
     * Get customer data for seamless payment form
     *
     * @param $data
     * @param $address
     * @param $customer
     *
     * @return none
     */
    public static function get_customer_address_details(&$data, $address, $customer)
    {
        // Get country code
        $counrty_code = \common\helpers\Country::get_country_info_by_id(STORE_COUNTRY);
        // Get customer data for seamless payment form
        $data['customer'] = [
            'first_name' => $customer->customers_firstname,
            'last_name' => $customer->customers_lastname,
            'email' => $customer->customers_email_address,
            'customer_ip' => self::get_ip_address(),
            'customer_no' => $customer->customers_id,
            'billing' => [
                'street' => $address[1]->entry_street_address ?? $address[0]->entry_street_address,
                'city' => $address[1]->entry_city ?? $address[0]->entry_city,
                'zip' => $address[1]->entry_postcode ?? $address[0]->entry_postcode,
                'country_code' => isset($address[1]->country->countries_iso_code_2) ? $address[1]->country->countries_iso_code_2 : (isset($address[0]->country->countries_iso_code_2) ? $address[0]->country->countries_iso_code_2 : $counrty_code['countries_iso_code_2']),
            ],
            'shipping' => [
                'street' => $address[0]->entry_street_address,
                'city' => $address[0]->entry_city,
                'zip' => $address[0]->entry_postcode,
                'country_code' => $address[0]->country->countries_iso_code_2 ?? $counrty_code['countries_iso_code_2'] ?? $counrty_code['countries_iso_code_2'],
            ]
        ];
        if (!empty($address[0]->entry_telephone)) {
            $data['customer']['tel'] = $address[0]->entry_telephone;
        }
        if (!empty($address[0]->entry_company)) {
            $data['customer']['billing']['company'] = $address[0]->entry_company;
        }
    }

    /**
     * Get transaction details
     *
     * @param $data
     * @param $order_info
     *
     * @return none
     */
    public static function get_transaction_details(&$data, $order_info)
    {
        $total_amount = self::round_of_amount($order_info['total'] * $order_info['currency_value']);
        $data['transaction'] = [
            'amount' => $total_amount,
            'system_name' => PROJECT_VERSION_NAME,
            'system_version' => PROJECT_VERSION_MAJOR . '_' . PROJECT_VERSION_MINOR . '-NN13.0.1-NNT',
            'system_ip' => $_SERVER['SERVER_ADDR'],
            'currency' => $order_info['currency'],
            'system_url' => (defined('ENABLE_SSL') ? (ENABLE_SSL == true ? HTTPS_SERVER : HTTP_SERVER . DIR_WS_CATALOG) : (HTTPS_CATALOG_SERVER . DIR_WS_CATALOG)),
        ];

        if (isset($_SESSION['nn_payment_details']) && isset($_SESSION['nn_booking_details'])) {
            $booking_details = $_SESSION['nn_booking_details'];
            $payment_details = $_SESSION['nn_payment_details'];

            $payment_data_keys = ['token', 'pan_hash', 'unique_id', 'iban', 'wallet_token', 'bic', 'account_holder', 'account_number', 'routing_number'];
            foreach ($payment_data_keys as $key) {
                if (!empty($booking_details[$key])) {
                    $data['transaction']['payment_data'][$key] = $booking_details[$key];
                }
            }
            if (!empty($booking_details['payment_ref']['token'])) {
                $data['transaction']['payment_data']['token'] = $booking_details['payment_ref']['token'];
                unset($_SESSION['nn_booking_details']['payment_ref']['token']);
            }
            if (!empty($booking_details['create_token'])) {
                $data['transaction']['create_token'] = $booking_details['create_token'];
                unset($_SESSION['nn_booking_details']['create_token']);
            }
            if ($booking_details['payment_action'] == 'zero_amount') {
                $data['transaction']['amount'] = 0;
                $data['transaction']['create_token'] = 1;
            }
            $data['transaction']['payment_type'] = !empty($_SESSION['nn_payment_details']['type']) ? $_SESSION['nn_payment_details']['type'] : '';
            $data['transaction']['test_mode'] = isset($booking_details['test_mode']) ? $booking_details['test_mode'] : '0';
            if (!empty($booking_details['due_date'])) { // Only for due date available payments
                $data['transaction']['due_date'] = date("Y-m-d", strtotime('+' . $booking_details['due_date'] . 'days'));
            }
            if (!empty($booking_details['cycle'])) { // Only for instalment payments
                $data['instalment'] = [
                    'interval' => '1m',
                    'cycles' => $booking_details['cycle'],
                ];
            }
            if (!empty($booking_details['do_redirect']) && $booking_details['do_redirect'] == 1) { // For enforced 3D secure
                $data['transaction']['enforce_3d'] = $booking_details['do_redirect'];
            }
            if (!empty($payment_details['process_mode']) && $payment_details['process_mode'] == 'redirect') { // Only for online payments
                $data['transaction']['return_url'] = tep_href_link(FILENAME_CHECKOUT_PROCESS);
                $data['transaction']['error_return_url'] = tep_href_link(FILENAME_CHECKOUT_PROCESS);
            }
        }
    }

    /**
     * Get custom details
     *
     * @param $data
     *
     * @return none
     */
    public static function get_custom_details(&$data)
    {
        $data['custom'] = [
            'lang' => self::get_user_language(),
        ];
    }

    /**
     * Validate the checksum value
     *
     * @param $data
     *
     * @return bool
     */
    public static function validate_checksum($data)
    {
        $payment_access_key = self::get_plugin_configuration_value('MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY');
        if (
            !empty($data['checksum']) && !empty($data['tid']) && !empty($data['status']) && !empty($data['txn_secret'])
            && !empty($payment_access_key)
        ) {
            $checksum = hash('sha256', $data['tid'] . $data['txn_secret'] . $data['status'] . strrev($payment_access_key));
            if ($checksum == $data['checksum']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handling the redirect success response
     *
     * @param $request_data
     *
     * @return array
     */
    public static function get_transaction_data($request_data)
    {
        $data = [];
        $data['transaction'] = [
            'tid' => $request_data['tid'],
        ];
        self::get_custom_details($data);
        return self::send_request(json_encode($data), self::get_endpoint('transaction_details'));
    }

    /**
     * Get the transaction details comments
     *
     * @param $response
     *
     * @return string
     */
    public static function get_comments($response)
    {
        $comments = $bank_details_note = $instalment_details_note = '';
        // Get currency value
        $currencies = \Yii::$container->get('currencies');
        // Form common transaction comments
        $test_mode = ($response['transaction']['test_mode'] == 1) ? MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_TEST_ORDER : '';
        $comments = MODULE_PAYMENT_NOVALNET_PAYMENTS_DETAILS_TID . ': ' . $response['transaction']['tid'] . PHP_EOL . $test_mode;
        // concat the comments if payment action zero amount
        if ($_SESSION['nn_booking_details']['payment_action'] == 'zero_amount' && $response['transaction']['amount'] == '0') {
            $comments .= PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_ZEROAMOUNT_BOOKING_TEXT;
        }
        // Get amount value
        $amount = isset($response['instalment']['cycle_amount']) ? $response['instalment']['cycle_amount'] / 100 : $response['transaction']['amount'] / 100;
        $amount = $currencies->format($amount, false, $response['transaction']['currency']);
        // Form the comments only for Invoice, Prepayment, Guarantee and instalment payments
        if (in_array($response['transaction']['payment_type'], array('INVOICE', 'PREPAYMENT', 'GUARANTEED_INVOICE', 'INSTALMENT_INVOICE', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) {
            if (!in_array($response['transaction']['payment_type'], array('GUARANTEED_DIRECT_DEBIT_SEPA', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) { // Form Novalnet bank details
                $bank_details_note = self::get_bank_account_details($response);
            }
            if (!in_array($response['transaction']['payment_type'], array('INVOICE', 'PREPAYMENT'))) { // Except Invoice & Prepayment
                if ($response['transaction']['status'] === 'PENDING') { // Only for PENDING status
                    $bank_details_note = in_array($response['transaction']['payment_type'], array('GUARANTEED_INVOICE', 'INSTALMENT_INVOICE')) ? PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_GUARANTEE_INSTALMENT_PENDING_TEXT : PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_GUARANTEE_PAYMENT_PENDING_TEXT . PHP_EOL;
                }
                if (!empty($response['instalment'])) { // Instalment payments
                    $instalment_details_note = PHP_EOL . self::instalment_details($response);
                }
            }
            $comments .= $bank_details_note . $instalment_details_note;
        }

        // Form the comments only for Cash payment
        if ($response['transaction']['payment_type'] == 'CASHPAYMENT') {
            $comments .= self::get_nearest_store_details($response);
        }
        // Form the comments only for Muttibanco payment
        if ($response['transaction']['payment_type'] == 'MULTIBANCO') {
            $comments .= PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_MULTIBANCO_NOTE, $amount) . PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_REF . ' : ' . $response['transaction']['partner_payment_reference'] . PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_PARTNER_SUPPLIER_ID, $response['transaction']['service_supplier_id']) . PHP_EOL;
        }
        return $comments;
    }

    /**
     * Get Novalnet bank account details
     *
     * @param  array $response
     *
     * @return string $bank_details_note
     */
    public static function get_bank_account_details($response)
    {
        $currencies = \Yii::$container->get('currencies');
        // Get amount value
        $amount = $response['instalment']['cycle_amount'] ? $response['instalment']['cycle_amount'] / 100 : $response['transaction']['amount'] / 100;
        $amount = $currencies->format($amount, false, $response['transaction']['currency']);
        // Only for Guarantee and instalment payments
        $bank_details_note = !empty($response['instalment']) ? PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TRANSFER_NOTE, $amount) . PHP_EOL : PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_TRANSFER_NOTE, $amount) . PHP_EOL; // Assign ON_HOLD text by default
        if ($response['transaction']['status'] !== 'ON_HOLD') { // If not equal to on hold
            if (!empty($response['instalment'] && !empty($response['transaction']['due_date']))) {
                $bank_details_note = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TRANSFER_NOTE_DUE_DATE, $amount, $response['transaction']['due_date']) . PHP_EOL;
            } else if (!empty($response['instalment']) && empty($response['transaction']['due_date'])) {
                $bank_details_note = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TRANSFER_NOTE, $amount) . PHP_EOL;
            } else {
                $bank_details_note = PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_TRANSFER_NOTE_DUE_DATE, $amount, $response['transaction']['due_date']) . PHP_EOL;
            }
        }
        // Form the ban details
        $bank_details_note .= PHP_EOL . self::get_bank_details($response);
        // Form the Payment reference text
        $bank_details_note .= PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_REFERENCE_TEXT . PHP_EOL;
        $reference_txt = array('TID ' => $response['transaction']['tid']);
        if (!empty($response['transaction']['invoice_ref'])) {
            $reference_txt['invoiceRef'] = $response['transaction']['invoice_ref'];
        }
        $reference_txt_count = count($reference_txt);
        $increment_id = 0;
        foreach ($reference_txt as $key => $value) {
            $count_value = $reference_txt_count > 1 ? ' ' . ++$increment_id : '';
            $bank_details_note .= MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_REF . $count_value . ': ' . $value . PHP_EOL;
        }
        return $bank_details_note;
    }

    /**
     * Store the transaction details in the database
     *
     * @param $response
     * @param $order_no
     *
     * @return none
     */
    public static function store_transaction_details($response, $order_no = null)
    {
        $data = array(
            'order_no' => $response['transaction']['order_no'] ?? $order_no,
            'tid' => $response['transaction']['tid'],
            'amount' => $response['transaction']['amount'],
            'payment_type' => $response['transaction']['payment_type'],
            'status' => $response['transaction']['status'],
        );
        if ($response['transaction']['amount'] == 0) {
            $data['payment_details'] = $response['transaction']['payment_data']['token'];
        }
        if ($response['result']['status'] == 'SUCCESS' && in_array($response['transaction']['payment_type'], array('INVOICE', 'INSTALMENT_INVOICE', 'GUARANTEED_INVOICE', 'CASHPAYMENT', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) {
            $payment_details = $response['transaction']['bank_details'] ?? '';
            if ($response['transaction']['payment_type'] == 'CASHPAYMENT') {
                $payment_details = $response['transaction']['nearest_stores'];
                $payment_details['novalnet_checkout_token'] = $response['transaction']['checkout_token'];
                $payment_details['novalnet_checkout_js'] = $response['transaction']['checkout_js'];
                $payment_details['novalnet_due_date'] = $response['transaction']['due_date'];
            }
            if (in_array($response['transaction']['payment_type'], array('INSTALMENT_INVOICE', 'INSTALMENT_DIRECT_DEBIT_SEPA'))) {
                $instalment_details = self::store_instalment_details($response);
                $data['instalment_details'] = $instalment_details;
            }
            $data['payment_details'] = json_encode($payment_details);
        }
        tep_db_perform('novalnet_transaction_details', $data);
    }

    /**
     * Store the instalment details in the database
     *
     * @param $response
     *
     * @return mixed
     */
    public static function store_instalment_details($response)
    {
        if (empty($response['instalment'])) {
            return null;
        }
        $instalment = $response['instalment'];
        if (isset($instalment['cycle_dates'])) {
            $total_cycles = count($instalment['cycle_dates']);
        }
        $cycle_amount = $instalment['cycle_amount'];
        $total_amount = $response['instalment']['total_amount'];
        $last_cycle_amount = $total_amount - ($cycle_amount * ($total_cycles - 1));
        $cycles = isset($instalment['cycle_dates']) ? $instalment['cycle_dates'] : null;
        $cycle_details = array();
        if (!empty($cycles)) {
            foreach ($cycles as $cycle => $cycle_date) {
                $cycle_details[$cycle - 1]['date'] = $cycle_date;
                $cycle_details[$cycle - 1]['currency'] = $instalment['currency'];
                if (!empty($cycles[$cycle + 1])) {
                    $cycle_details[$cycle - 1]['next_instalment_date'] = $cycles[$cycle + 1];
                }
                $cycle_details[$cycle - 1]['status'] = 'Pending';
                if (!empty($instalment['cycles_executed']) && $cycle == $instalment['cycles_executed']) {
                    $cycle_details[$cycle - 1]['reference_tid'] = !empty($response['transaction']['tid']) ? $response['transaction']['tid'] : (!empty($instalment['tid']) ? $instalment['tid'] : '');
                    $cycle_details[$cycle - 1]['status'] = 'Paid';
                    $cycle_details[$cycle - 1]['paid_date'] = date('Y-m-d H:i:s');
                }
                $cycle_details[$cycle - 1]['instalment_cycle_amount'] = ($cycle == $total_cycles) ? $last_cycle_amount : $instalment['cycle_amount'];
                $cycle_details[$cycle - 1]['instalment_cycle_amount_orginal_amount'] = ($cycle == $total_cycles) ? $last_cycle_amount : $instalment['cycle_amount'];
            }
        }
        return (!empty($cycle_details) ? json_encode($cycle_details) : null);
    }

    /**
     * Get the bank details
     *
     * @param $response
     *
     * @return string
     */
    public static function get_bank_details($response)
    {
        if (isset($response['transaction']['order_no'])) {
            $payment_details = tep_db_fetch_array(tep_db_query("SELECT `payment_details` FROM `novalnet_transaction_details` WHERE `order_no` = {$response['transaction']['order_no']}"));
            $payment_details = json_decode($payment_details['payment_details'], true);
        }
        return MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_ACCHOLDER . ($response['transaction']['bank_details']['account_holder'] ?? $payment_details['account_holder']) . PHP_EOL .
            MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_IBAN . ($response['transaction']['bank_details']['iban'] ?? $payment_details['iban']) . PHP_EOL .
            MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BIC . ($response['transaction']['bank_details']['bic'] ?? $payment_details['bic']) . PHP_EOL .
            MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BANKNAME . ($response['transaction']['bank_details']['bank_name'] ?? $payment_details['bank_name']) . PHP_EOL .
            MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BANKPLACE . ($response['transaction']['bank_details']['bank_place'] ?? $payment_details['bank_place']) . PHP_EOL;
    }

    /**
     * Get the instalment details
     *
     * @param $response
     *
     * @return string $comments
     */
    public static function instalment_details($response)
    {
        $comments = '';
        $currencies = \Yii::$container->get('currencies');
        $cycle_amt = $response['instalment']['cycle_amount'] / 100;
        $amount = $currencies->format($cycle_amt, false, $response['instalment']['currency']);
        if ($response['transaction']['status'] == 'CONFIRMED') {
            $comments .= MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_INSTALMENTS_INFO . PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_PROCESSED_INSTALMENTS . $response['instalment']['cycles_executed'] . PHP_EOL;
            $comments .= MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_DUE_INSTALMENTS . $response['instalment']['pending_cycles'] . PHP_EOL;
            $comments .= MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CYCLE_AMOUNT . $amount . PHP_EOL;

            if (!empty($response['instalment']['next_cycle_date'])) {
                $comments .= MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_NEXT_INSTALMENT_DATE . $response['instalment']['next_cycle_date'] . PHP_EOL;
            }
        }
        return $comments;
    }

    /**
     * Get the nearest store details for cash payment
     *
     * @param $response
     *
     * @return string $store_details
     */
    public static function get_nearest_store_details($response)
    {
        $store_details = '';
        if (!empty($response['transaction']['due_date'])) {
            $store_details .= PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_TRANS_SLIP_EXPIRY_DATE . ' ' . date(DATE_FORMAT, strtotime($response['transaction']['due_date']));
        }
        $store_details .= PHP_EOL . MODULE_PAYMENT_NOVALNET_PAYMENTS_NEAREST_STORE_DETAILS . PHP_EOL;
        if (!empty($response['transaction']['nearest_stores'])) {
            foreach ($response['transaction']['nearest_stores'] as $store) {
                $store_details .= PHP_EOL . $store['store_name'];
                $store_details .= PHP_EOL . $store['street'];
                $store_details .= PHP_EOL . $store['zip'] . ' ' . $store['city'];
                $country_name = tep_db_fetch_array(tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . $store['country_code'] . "'"));
                if (!empty($country_name)) {
                    $store_details .= PHP_EOL . $country_name['countries_name'];
                }
                $store_details .= PHP_EOL;
            }
        }
        return $store_details;
    }

    /**
     * Update the order number for the transaction in the Novalnet
     *
     * @param $order_id
     *
     * @return none
     */
    public static function update_transaction_details($order_id)
    {
        $data = [];
        $data['transaction'] = [
            'tid' => $_SESSION['response']['transaction']['tid'],
            'order_no' => $order_id,
        ];
        self::get_custom_details($data);
        $endpoint = self::get_endpoint('update');
        $response = self::send_request(json_encode($data), $endpoint);
    }

    /**
     * Get the order status
     *
     * @param $order_id
     *
     * @return array
     */
    public static function get_order_status($order_id)
    {
        $get_order_status = tep_db_fetch_array(tep_db_query("SELECT `orders_status` FROM " . TABLE_ORDERS . " WHERE `orders_id` = $order_id"));
        return $get_order_status['orders_status'];
    }

    /**
     * Get the admin mail address
     *
     * @param none
     *
     * @return string
     */
    public static function get_admin_mail()
    {
        $admin_mail = tep_db_fetch_array(tep_db_query("SELECT `admin_email_address` FROM  admin limit 1"));
        return $admin_mail['admin_email_address'];
    }

    /**
     * Get order status
     *
     * @param  object $transaction_data
     *
     * @param string
     */
    public static function get_order_status_id($transaction_data)
    {
        switch ($transaction_data['status']) {
            case 'PENDING':
                if ($transaction_data['payment_type'] === 'INVOICE') {
                    return '2';
                    break;
                }
                return '100007';
                break;
            case 'ON_HOLD':
                return '100007';
            case 'FAILURE':
            case 'DEACTIVATED':
                return '5';
                break;
            case 'CONFIRMED':
                return '100006';
                break;
        }
    }
    /**
     * Get the customer IP addresss
     *
     * @param none
     *
     * @return string|bool
     */
    public static function get_ip_address()
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === true) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }

    /**
     * Handling the order table update process
     *
     * @return none
     */
    public static function update_order_table($comments, $order_status_id, $order_no)
    {
        $order_histroy_table_data = array(
            'orders_id' => $order_no,
            'orders_status_id' => $order_status_id,
            'date_added' => date('Y-m-d H:i:s'),
            'comments' => $comments,
        );
        $data = array(
            'orders_status' => $order_status_id
        );
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_histroy_table_data);
        tep_db_perform(TABLE_ORDERS, $data, 'update', "orders_id = '" . $order_no . "'");
    }

    /**
     * Get the novalnet transaction details from novalnet server
     *
     * @param $order_id
     *
     * @return array
     */
    public static function get_novalnet_transaction_details($order_id)
    {
        $get_order_details = tep_db_fetch_array(tep_db_query("SELECT * FROM `novalnet_transaction_details` WHERE `order_no` = $order_id"));
        return $get_order_details;
    }

    /** Get the user language from database
     *
     * @param none
     *
     * @return string
     */
    public static function get_user_language()
    {
        global $languages_id;
        $languages = tep_db_fetch_array(tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = " . (int) $languages_id . " limit 1"));
        return strtoupper($languages['code']);
    }

    /** Get the API headers
     *
     * @param $access_key
     *
     * @return $data
     */
    public static function get_headers($access_key)
    {
        $encoded_data = base64_encode($access_key);
        $data = [
            'Content-Type:application/json',
            'Charset:utf-8',
            'Accept:application/json',
            'X-NN-Access-Key:' . $encoded_data
        ];
        return $data;
    }

    /**
     * Get particular platform configuration value
     *
     * @param $configuration_key
     *
     * @return string
     */
    public static function get_plugin_configuration_value($configuration_key)
    {
        $plugin_configuration_value = tep_db_fetch_array(tep_db_query("SELECT configuration_value FROM " . TABLE_PLATFORMS_CONFIGURATION . " WHERE configuration_key='" . $configuration_key . "' AND platform_id='" . PLATFORM_ID . "'"));
        return $plugin_configuration_value['configuration_value'];
    }

    /**
     * Get particular platform configuration value using order id
     *
     * @param $configuration_key
     * @param $order_no
     *
     * @return string
     */
    public static function get_plugin_configuration_order_id($configuration_key, $order_no)
    {
        $platform_id = tep_db_fetch_array(tep_db_query("SELECT platform_id FROM " . TABLE_ORDERS . " WHERE orders_id=$order_no"));
        $plugin_configuration_value = tep_db_fetch_array(tep_db_query("SELECT configuration_value FROM " . TABLE_PLATFORMS_CONFIGURATION . " WHERE configuration_key='" . $configuration_key . "' AND platform_id='" . $platform_id['platform_id'] . "'"));
        return $plugin_configuration_value['configuration_value'];
    }

    /**
     * Get the amount to prefill the zero amount booking orders
     *
     * @param $order_no
     *
     * @return string
     */
    public static function get_prefill_amount($order_no)
    {
        $order_total = tep_db_fetch_array(tep_db_query("SELECT value, currency_value FROM " . TABLE_ORDERS_TOTAL . " where class = 'ot_total' AND orders_id = " . tep_db_input($order_no)));
        return self::round_of_amount($order_total['value'] * $order_total['currency_value']);
    }

    /**
     * Get the amount to rounds a given amount to two decimal places
     *
     * @param $amount
     *
     * @return number
     */
    public static function round_of_amount($amount)
    {
        return round($amount, 2) * 100;
    }
    /**
     * Make CURL payment request to Novalnet server
     *
     * @param $data
     * @param $url
     *
     * @return array
     */
    public static function send_request($data, $url, $access_key = null, $order_no = null)
    {
        $access_key = $access_key ?? self::get_plugin_configuration_value('MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY');
        if (!empty($order_no)) {
            $access_key = self::get_plugin_configuration_order_id('MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY', $order_no);
        }
        $headers = self::get_headers($access_key);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Request Error:' . curl_error($curl);
            return $result;
        }
        curl_close($curl);
        return json_decode($result, true);
    }

    public static function get_article_details($order, $isPaypalcart = false)
    {
        if (!empty($order->products)) {
            foreach ($order->products as $positionDetails) {
                if (!empty($positionDetails)) {
                    $productName = $positionDetails['name'];
                    $price = self::round_of_amount($positionDetails['final_price'] * $order->info['currency_value']);
                    preg_match('/\d+(\.\d{1,2})?/', $price, $matches);
                    if (!empty($matches)) {
                        $amount = $matches[0];
                        $totalProductAmount = str_replace(['.', ','], '', $amount);
                    }
                    $total_Amt['amt'][] = array('amt' => $totalProductAmount * $positionDetails['qty']);
                    if ($isPaypalcart) {
                        $cartInfo['line_items'][] = array(
                            'name' => $productName,
                            'price' => (int) $totalProductAmount,
                            'quantity' => $positionDetails['qty'],
                        );
                    } else {
                        $cartInfo[] = array(
                            'label' => '(' . $positionDetails['qty'] . ' X ' . $totalProductAmount . ') ' . $productName,
                            'amount' => intval($totalProductAmount),
                            'type' => 'LINE_ITEM'
                        );
                    }
                }
            }
        }

        foreach ($order->totals as $order_total) {
            if ($order_total['code'] == 'ot_shipping') {
                $shipping_value = ($order_total['text']);
            }
        }

        $shipping_value = empty($shipping_value) ? self::round_of_amount($order->info['shipping_cost'] * $order->info['currency_value']) : $shipping_value;
        preg_match('/\d+(\.\d{1,2})?/', $shipping_value, $matches);
        if (!empty($matches)) {
            $shipping_amount = $matches[0];
            $total_shipping_amount = str_replace('.', '', $shipping_amount);
        }
        if ($isPaypalcart) {
            $cartInfo['items_shipping_price'] = (int) $total_shipping_amount;
        } else {
            $label = (string) $order->info['shipping_method'];
            $label = strip_tags(($label));
            $label = str_replace('&nbsp;', '', $label);
            $cartInfo[] = array('label' => $label, 'amount' => (int) $total_shipping_amount, 'type' => 'SUBTOTAL');
        }
        $manager = \common\services\OrderManager::loadManager();
        $coupon_data = !empty($order->totals) ? $order->totals : $manager->getTotalOutput(false);
        foreach ($coupon_data as $order_total) {
            if ($order_total['title'] == 'Total discount:') {
                $coupon_value = $order_total['text'];
            }
        }
        preg_match('/\d+(\.\d{1,2})?/', $coupon_value, $matches);
        if (!empty($matches)) {
            $coupon_amount = $matches[0];
            $coupon_value = str_replace('.', '', $coupon_amount);
        }
        if ($isPaypalcart) {
            if (!empty($coupon_value)) {
                $cartInfo['line_items'][] = array('name' => MODULE_ORDER_TOTAL_COUPON_TITLE, 'price' => '-' . (int) $coupon_value, 'quantity' => 1);
            }
        } else {
            if (!empty($coupon_value)) {
                $cartInfo[] = array('label' => MODULE_ORDER_TOTAL_COUPON_TITLE, 'amount' => '-' . (int) $coupon_value, 'type' => 'SUBTOTAL');
            }
        }
        $total_amt = array_sum(array_column($total_Amt['amt'], 'amt'));
        $totalAmount = empty($coupon_value) ? $total_amt + $total_shipping_amount : $total_amt + $total_shipping_amount - $coupon_value;
        $order_total_amount = self::round_of_amount($order->info['total'] * $order->info['currency_value']);
        if ($totalAmount != $order_total_amount && $isPaypalcart) {
            $handling = $order_total_amount - $totalAmount;
            $handling = round($handling, 2);
            $cartInfo['line_items'][] = array('name' => 'handling', 'price' => (int) $handling, 'quantity' => 1);
        }
        return $cartInfo;
    }
}
new NovalnetHelper();
