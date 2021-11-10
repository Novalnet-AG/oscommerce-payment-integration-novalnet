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
 * Script : class.novalnetutil.php
 *
 */
ob_start();
class NovalnetUtil
{

    /**
     * Validate the global configuration and display the error/warning message
     * @param $admin
     *
     * @return boolean
     */
    static public function checkMerchantConfiguration($admin = false)
    {
        $merchant_api_error = self::merchantValidate();
        if (!function_exists('base64_decode') || !function_exists('base64_encode') || !function_exists('md5') || !function_exists('curl_init') || !function_exists('crc32') || !function_exists('pack')) {
            if ($admin)
                echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_PHP_EXTENSION_MISSING);

            return false;
        }
        elseif (!trim(MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY)  || $merchant_api_error && !isset($_GET['action']) && $_GET['action'] != 'edit' ) {
            if (strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false) {
                if ($admin)
                    echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE);
            }
            return false;
        }
        elseif (trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD) != '' && !preg_match('/^\d+(d|m|y){1}$/',MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD)) {
            if ($admin)
                    echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_TARRIF_PERIOD_ERROR_MSG);
            return false;
        }
        elseif (trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2) != '' && trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT) == '') {    if ($admin)
                    echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_TARRIF_AMOUNT_ERROR_MSG);
            return false;
        }
        elseif (trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT) != '' && !preg_match('/^\d+(d|m|y){1}$/',MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2)) {
            if ($admin)
                    echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE, MODULE_PAYMENT_NOVALNET_TARRIF_PERIOD2_ERROR_MSG);
            return false;
        }

        return true;
    }

    /**
     * Validate the payment configuration and display the error/warning message
     * @param none
     *
     * @return mixed
     */
    static public function merchantValidate()
    {
        $merchant_api_error = false;
        $pattern            = "/^\d+\|\d+\|[\w-]+\|\w+\|\w+\|(|\d+)\|(|\d+)\|(|\d+)\|(|\d+)\|(|\w+)\|(|\w+)$/";
        $value              = MODULE_PAYMENT_NOVALNET_VENDOR_ID . '|' . MODULE_PAYMENT_NOVALNET_PRODUCT_ID . '|' . MODULE_PAYMENT_NOVALNET_TARIFF_ID . '|' . MODULE_PAYMENT_NOVALNET_AUTH_CODE . '|' . MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY . '|' . MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT . '|' . MODULE_PAYMENT_NOVALNET_REFERRER_ID . '|' . MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT . '|' . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT . '|' . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2 . '|' . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD;
        preg_match($pattern, $value, $match);
        if (empty($match[0])) {
            $merchant_api_error = true;
        } elseif ((MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO != '' && !self::validateEmail(MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO)) || (MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC != '' && !self::validateEmail(MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC))) {
            $merchant_api_error = true;
        }
        return $merchant_api_error;
    }

    /**
     * Validate E-mail address
     * @param $emails
     *
     * @return boolean
     */
    static public function validateEmail($emails)
    {
        $email = explode(',', $emails);
        foreach ($email as $value) {
            if (!tep_validate_email($value))
                return false;
        }
        return true;
    }

    /**
     * Show validation error in backend
     * @param $error_payment
     * @param $other_error
     *
     * @return string
     */
    static public function novalnetBackEndShowError($error_payment, $other_error = '')
    {
        return '<div style="border: 1px solid #0080c9; background-color: #FCA6A6; padding: 10px; font-family: Arial, Verdana; font-size: 11px; margin:0px 5px 5px 0;"><b>' . $error_payment . '</b><br/><br/>' . ($other_error != '' ? $other_error : MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR) . '</div>';
    }

    /**
     * Setting up the flag for payment visibility
     * @param $order_amount
     * @param $payment_visible_amount
     *
     * @return boolean
     */
    static public function hidePaymentVisibility($order_amount, $payment_visible_amount)
    {
        return ($payment_visible_amount == '' || ((int) $payment_visible_amount <= (int) $order_amount));
    }

    /**
     * To get the payment name of last successful order
     * @param $payment_code
     *
     * @return none
     */
    static public function getLastSuccessPayment($payment_code)
    {
        global $order, $payment;

        if (empty($payment) && MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION == 'True' && (self::getLastSuccessTransPayment($order->customer['email_address'], $payment_code)) && empty($_SESSION['novalnet'][$payment]['tid'])) {
            $payment = $payment_code;
        }
    }

    /**
     * Return last successful transaction payment method
     * @param $customer_email_address
     * @param $payment_code
     *
     * @return boolean
     */
    static public function getLastSuccessTransPayment($customer_email_address = '', $payment_code = '')
    {
        if ($customer_email_address == '' || $payment_code == '' || (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 1)) {
            return false;
        }
        
        $sql_query        = tep_db_query("SELECT payment_type FROM novalnet_transaction_detail WHERE customer_id='" . tep_db_input($_SESSION['customer_id']) . "' ORDER BY id DESC LIMIT 1");
        $sqlQuerySet      = tep_db_fetch_array($sql_query);
        if ($sqlQuerySet['payment_type'] == $payment_code) {
            return true;
        }
        return false;
    }

    /**
     * Get payment amount of given order
     * @param $order
     *
     * @return integer
     */
    static public function getPaymentAmount($order)
    {
        $total = $order['info']['total'] * $order['info']['currency_value'];
        return sprintf('%0.2f', $total) * 100;
    }

    /**
     * Generate Novalnet gateway parameters based on payment selection
     * @param $datas
     *
     * @return array
     */
    static public function getRequestParams($datas)
    {
		global $languages_id;     
		
        if ($datas['payment_amount'] == '') {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE, 'SSL'));
        }    
        
        $language_code_query = "select code from " . TABLE_LANGUAGES . " where languages_id = ".$languages_id.""; 
		$language_code = tep_db_fetch_array(tep_db_query($language_code_query));
		
        $tariff_details      = explode('-', MODULE_PAYMENT_NOVALNET_TARIFF_ID);
        $tariff_id           = $tariff_details[1];
        $remote_ip           = tep_get_ip_address();
        $system_ip           = $_SERVER['SERVER_ADDR'];
        $customer_details    = self::getCustomerDetails($datas['customer']['email_address']);
        $urlparam            = array(
            'vendor'           => MODULE_PAYMENT_NOVALNET_VENDOR_ID,
            'product'          => MODULE_PAYMENT_NOVALNET_PRODUCT_ID,
            'tariff'           => $tariff_id,
            'auth_code'        => MODULE_PAYMENT_NOVALNET_AUTH_CODE,
            'test_mode'        => self::getPaymentTestMode($datas['payment']),
            'amount'           => $datas['payment_amount'],
            'currency'         => $datas['info']['currency'],
            'first_name'       => $datas['billing']['firstname'],
            'last_name'        => $datas['billing']['lastname'],
            'street'           => $datas['billing']['street_address'],
            'search_in_street' => '1',
            'city'             => $datas['billing']['city'],
            'zip'              => $datas['billing']['postcode'],
            'email'            => $datas['customer']['email_address'],
            'tel'              => $datas['customer']['telephone'],
            'gender'           => 'u',
            'customer_no'      => $customer_details['customers_id'],
            'birth_date'       => $customer_details['customers_dob'],
            'fax'              => $customer_details['customers_fax'],
            'remote_ip'        => (filter_var($remote_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $remote_ip == '::1' || empty($remote_ip)) ? '127.0.0.1' : $remote_ip,
            'system_name'      => 'oscommerce',
            'system_ip'        => (filter_var($system_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $system_ip == '::1' || empty($system_ip)) ? '127.0.0.1' : $system_ip,
            'system_url'       => ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER),
            'system_version'   => (function_exists('tep_get_version') ? tep_get_version() : PROJECT_VERSION) . '-NN11.1.0' 
        );
        $urlparam['country'] = $urlparam['country_code'] = $datas['billing']['country']['iso_code_2'];
        $urlparam['lang']    = $language_code['code'];
        self::getAffDetails($urlparam);
        if (!empty($datas['billing']['company']))
            $urlparam['company'] = $datas['billing']['company'];

        $notify_url = trim(MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL);
        if (!empty($notify_url))
            $urlparam['notify_url'] = $notify_url;
        if (MODULE_PAYMENT_NOVALNET_REFERRER_ID != '') {
            $urlparam['referrer_id'] = trim(MODULE_PAYMENT_NOVALNET_REFERRER_ID);
        }
        if (MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD != '') {
            $urlparam['tariff_period'] = trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD);
        }
        if (MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2 != '') {
            $urlparam['tariff_period2'] = trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2);
        }
        if (MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT != '') {
            $urlparam['tariff_period2_amount'] = trim(MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT);
        }
        $input_reference = self::appendCustomParameters(); // append custom parameters (Transaction reference)
        if (!empty($input_reference))
            $urlparam = array_merge($urlparam, $input_reference);

        return $urlparam;
    }

    /**
     * Get test mode status
     * @param $payment_name
     *
     * @return boolean;
     */
    static public function getPaymentTestMode($payment_name)
    {
        $test_mode = defined('MODULE_PAYMENT_' . strtoupper($payment_name) . '_TEST_MODE') ? constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_TEST_MODE') : '';
        return (isset($test_mode) && $test_mode == 'True') ? 1 : 0;
    }

    /**
     * Return reference param
     * @param none
     *
     * @return array
     */
    static public function appendCustomParameters()
    {
        global $payment;

        $trans_refer1 = trim(strip_tags(constant('MODULE_PAYMENT_' . strtoupper($payment) . '_TRANS_REFERENCE1')));
        $trans_refer2 = trim(strip_tags(constant('MODULE_PAYMENT_' . strtoupper($payment) . '_TRANS_REFERENCE2')));

        if ($trans_refer1 != '') {
            $urlparam['input1']    = 'reference1';
            $urlparam['inputval1'] = $trans_refer1;
        }
        if ($trans_refer2 != '') {
            $urlparam['input2']    = 'reference2';
            $urlparam['inputval2'] = $trans_refer2;
        }
        return $urlparam;
    }

    /**
     * Get the redirect payment params
     * @param $params
     *
     * @return none
     */
    static public function getRedirectParams(&$params)
    {
        $encoded_values = self::generateHashValue(array(
            'auth_code' => $params['auth_code'],
            'product'   => $params['product'],
            'tariff'    => $params['tariff'],
            'amount'    => $params['amount'],
            'test_mode' => $params['test_mode'],
            'uniqid'    => time()
        ));

        $params['implementation']  = 'PHP';
        $params['auth_code']       = $encoded_values['auth_code'];
        $params['product']         = $encoded_values['product'];
        $params['tariff']          = $encoded_values['tariff'];
        $params['amount']          = $encoded_values['amount'];
        $params['test_mode']       = $encoded_values['test_mode'];
        $params['uniqid']          = $encoded_values['uniqid'];
        $params['hash']            = $encoded_values['hash'];
        $params['return_method']   = $params['error_return_method'] = 'POST';
        $params['return_url']      = $params['error_return_url'] = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
        if($params['payment_type'] != 'CREDITCARD') {
            $params['user_variable_0'] = ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER);
        }
    }

    /**
     * Fetch the customer details from database
     * @param $email
     *
     * @return array
     */
    static public function getCustomerDetails($email)
    {
        if ($email != '') {
            $query_search         = (isset($_SESSION['customer_id'])) ? 'customers_id ="' . tep_db_input($_SESSION['customer_id']) . '"' : 'customer_email_address = "' . tep_db_input($_SESSION[$email]) . '"';
            $sqlquery             = tep_db_query("SELECT customers_id, customers_gender, customers_dob, customers_fax FROM " . TABLE_CUSTOMERS . " WHERE " . $query_search . "ORDER BY customers_id DESc");
            $get_customer_dbvalue = tep_db_fetch_array($sqlquery);
            if (!empty($get_customer_dbvalue)) {
                $get_customer_dbvalue['customers_dob'] = ($get_customer_dbvalue['customers_dob'] != '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($get_customer_dbvalue['customers_dob'])) : '';
            }
        }
        return $get_customer_dbvalue;
    }

    /**
     * Perform HASH Generation process for redirection payment methods
     * @param $datas
     *
     * @return string
     */
    static public function generateHashValue($datas)
    {
        foreach (array('auth_code', 'product', 'tariff', 'amount', 'test_mode', 'uniqid') as $key) {
            $datas[$key] = self::generateEncode($datas[$key]); // Encoding process
        }

        $datas['hash'] = self::generatemd5Value($datas); // Generate hash value
        return $datas;
    }

    /*
     * Perform the encoding process for redirection payment methods
     * @param $data
     *
     * @return string
     */
    static public function generateEncode($data = '')
    {
        try {
            $crc  = sprintf('%u', crc32($data));
            $data = $crc . "|" . $data;
            $data = bin2hex($data . $_SESSION['nn_access_key']); // Using payment access key
            $data = strrev(base64_encode($data));
        }
        catch (Exception $e) { // Error log for the exception
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, utf8_decode('error_message=' . $e), 'SSL'));
        }
        return $data;
    }

    /**
     * Perform the decoding paygate response process for redirection payment methods
     * @param $datas
     *
     * @return string
     */
    static public function decodePaygateResponse($datas)
    {
        $result = array();

        $data['auth_code'] = $datas['auth_code'];
        $data['tariff']    = $datas['tariff'];
        $data['product']   = $datas['product'];
        $data['amount']    = $datas['amount'];
        $data['test_mode'] = $datas['test_mode'];
        foreach ($data as $key => $value) {
            $result[$key] = self::generateDecode($value); // Decode process
        }
        return array_merge($datas, $result);
    }

    /**
     * Perform HASH Validation with paygate response
     * @param $datas
     *
     * @return boolean
     */
    static public function validateHashResponse($datas)
    {
        return ($datas['hash2'] != self::generatemd5Value($datas));
    }

    /**
     * Perform the decoding process for redirection payment methods
     * @param $data
     *
     * @return string
     */
    static public function generateDecode($data = '')
    {
        try {
            $data = base64_decode(strrev($data));
            $data = pack("H" . strlen($data), $data);
            $data = substr($data, 0, stripos($data, $_SESSION['nn_access_key'])); // Using payment access key
            $pos  = strpos($data, "|");
            if ($pos === false) {
                return ("Error: CKSum not found!");
            }
            $crc   = substr($data, 0, $pos);
            $value = trim(substr($data, $pos + 1));
            if ($crc != sprintf('%u', crc32($value))) {
                return ("Error; CKSum invalid!");
            }
            return $value;
        }
        catch (Exception $e) { // Error log for the exception
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, utf8_decode('error_message=' . $e), 'SSL'));
        }
        return $data;
    }

    /**
     * Get hash value
     * @param $datas
     *
     * @return string
     */
    static public function generatemd5Value($datas)
    {
        return md5($datas['auth_code'] . $datas['product'] . $datas['tariff'] . $datas['amount'] . $datas['test_mode'] . $datas['uniqid'] . strrev($_SESSION['nn_access_key']));
    }

    /**
     * Check transaction status message
     * @param $response
     *
     * @return string
     */
    static public function getTransactionMessage($response)
    {
        return (!empty($response['status_text']) ? self::setUTFText($response['status_text']) : (!empty($response['status_desc']) ? self::setUTFText($response['status_desc']) : (!empty($response['status_message']) ? self::setUTFText($response['status_message']) : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR)));
    }

    /**
     * Function to communicate transaction parameters with Novalnet Paygate
     * @param $paygate_url
     * @param $request_params
     * @param $build_query
     *
     * @return array
     */
    static public function doPaymentCall($paygate_url, $request_params, $build_query = true)
    {
		$paygate_query = ($build_query) ? http_build_query($request_params) : $request_params;
        // Initiate cURL
        $curl_process  = curl_init($paygate_url);

        // Set cURL options
        curl_setopt($curl_process, CURLOPT_POST, 1);
        curl_setopt($curl_process, CURLOPT_POSTFIELDS, $paygate_query);
        curl_setopt($curl_process, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl_process, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_process, CURLOPT_TIMEOUT, ((MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT != '' && MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT > 240) ? MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT : 240)); //Custom CURL time-out

        if (trim(MODULE_PAYMENT_NOVALNET_PROXY) != '') {
            curl_setopt($curl_process, CURLOPT_PROXY, trim(MODULE_PAYMENT_NOVALNET_PROXY));
        } //Custom Proxy option

        // Execute cURL
        $response = curl_exec($curl_process);
        if (curl_errno($curl_process)) { // cURL error
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, utf8_decode('error_message=' . curl_error($curl_process)), 'SSL'));
        }

        // Close cURL
        curl_close($curl_process);
        return $response;

    }

    /**
     * Return Invoice / Prepayment comments
     * @param $response
     *
     * @return array
     */
    static public function formInvoicePrepaymentComments($response)
    {
        $currencies       = new currencies();
        $trans_comments   = PHP_EOL . self::setUTFText(MODULE_PAYMENT_NOVALNET_INVOICE_COMMETNS_PARAGRAPH) . PHP_EOL;
        $trans_comments .= ($response['due_date'] != '') ? self::setUTFText(MODULE_PAYMENT_NOVALNET_DUE_DATE) . ': ' . date(DATE_FORMAT, strtotime($response['due_date'])) . PHP_EOL : '';
        $amount     = isset($_SESSION['novalnet'][$_SESSION['payment']]['order_amount']) ? ($_SESSION['novalnet'][$_SESSION['payment']]['order_amount'] / 100) : $response['amount'];
        $trans_comments .= MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER . ': NOVALNET AG' . PHP_EOL;
        $trans_comments .= MODULE_PAYMENT_NOVALNET_IBAN . ': ' . $response['invoice_iban'] . PHP_EOL;
        $trans_comments .= MODULE_PAYMENT_NOVALNET_BIC . ': ' . $response['invoice_bic'] . PHP_EOL;
        $trans_comments .= MODULE_PAYMENT_NOVALNET_BANK . ': ' . self::setUTFText($response['invoice_bankname']) . $response['invoice_bankplace'] . PHP_EOL;
        $trans_comments .= MODULE_PAYMENT_NOVALNET_AMOUNT . ': ' . $currencies->format($amount, false, $response['currency']) . PHP_EOL;
        return array(
            $trans_comments,
            array(
                'order_no'       => '',
                'tid'            => $response['tid'],
                'test_mode'      => $response['test_mode'],
                'account_holder' => 'NOVALNET AG',
                'bank_name'      => $response['invoice_bankname'],
                'bank_city'      => $response['invoice_bankplace'],
                'amount'         => $response['amount'] * 100,
                'currency'       => $response['currency'],
                'bank_iban'      => $response['invoice_iban'],
                'bank_bic'       => $response['invoice_bic'],
                'due_date'       => $response['due_date']
            )
        );
    }

    /**
     * Return Invoice / Prepayment payment reference comments
     * @param $reference
     * @param $order_id
     * @param $payment
     * @param $datas
     *
     * @return array
     */
    static public function novalnetReferenceComments($reference, $order_id, $payment, $datas)
    {
        $payment_ref     = unserialize($reference);
        $refrences       = array($payment_ref['payment_reference1'], $payment_ref['payment_reference2'], $payment_ref['payment_reference3']);
        $refernce_count  = array_count_values($refrences);
        $i               = 1;
        $invpre_comments = (($refernce_count['1'] > 1) ? self::setUTFText(MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT) : self::setUTFText(MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT)) . PHP_EOL;

        foreach ($refrences as $k => $v) {

            if ($refrences[$k] == '1') {
                $invpre_comments .= ($refernce_count['1'] == 1) ? MODULE_PAYMENT_NOVALNET_INVPRE_REF : sprintf(MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF, ' ' . $i++);
                $invpre_comments .= (($k == 0) ? ': BNR-' . (isset($datas['product']) ? $datas['product'] : $_SESSION['novalnet'][$payment]['product']) . '-' . $order_id . '' : ($k == 1 ? ': TID' . ' ' . (isset($datas['tid']) ? $datas['tid'] : $_SESSION['novalnet'][$payment]['tid']) : ': ' . MODULE_PAYMENT_NOVALNET_ORDER_NUMBER . ' ' . $order_id)) . PHP_EOL;
            }
        }
        return $invpre_comments;
    }

    /**
     * Perform the Novalnet Second call to Novalnet Server
     * @param $datas
     *
     * @return boolean
     */
    static public function doSecondCallProcess($datas)
    {
        self::logInitialTransaction($datas);
        if (isset($_SESSION['novalnet'][$datas['payment']]['subs_id']) && $_SESSION['novalnet'][$datas['payment']]['subs_id'] != '') {
            tep_db_perform('novalnet_subscription_detail', array(
                'order_no'           => $datas['order_no'],
                'subs_id'            => $_SESSION['novalnet'][$datas['payment']]['subs_id'],
                'tid'                => $_SESSION['novalnet'][$datas['payment']]['tid'],
                'parent_tid'         => $_SESSION['novalnet'][$datas['payment']]['tid'],
                'signup_date'        => date('Y-m-d H:i:s'),
                'termination_reason' => (!empty($datas['termination_reason']) ? $datas['termination_reason'] : ''),
                'termination_at'     => (!empty($datas['termination_at']) ? $datas['termination_at'] : '')
            ), "insert");
        }
        if (isset($_SESSION['nn_aff_id'])) {
            tep_db_perform('novalnet_aff_user_detail', array(
                'aff_id'       => $_SESSION['nn_aff_id'],
                'customer_id'  => $_SESSION['customer_id'],
                'aff_order_no' => $datas['order_no']
            ), 'insert');
            unset($_SESSION['nn_aff_id']);
        }
        $urlparam = array(
            'vendor'    => $_SESSION['novalnet'][$datas['payment']]['vendor'],
            'auth_code' => $_SESSION['novalnet'][$datas['payment']]['auth_code'],
            'product'   => $_SESSION['novalnet'][$datas['payment']]['product'],
            'tariff'    => $_SESSION['novalnet'][$datas['payment']]['tariff'],
            'key'       => $_SESSION['novalnet'][$datas['payment']]['payment_id'],
            'tid'       => $_SESSION['novalnet'][$datas['payment']]['tid'],
            'order_no'  => $datas['order_no'],
            'status'    => 100
        );
        if ($_SESSION['novalnet'][$datas['payment']]['payment_id'] == 27) {
            $urlparam['invoice_ref'] = 'BNR-' . $urlparam['product'] . '-' . $datas['order_no'];
        }
        if (!empty($urlparam)) {
            self::doPaymentCall("https://payport.novalnet.de/paygate.jsp", $urlparam);
        }
        if (isset($_SESSION['novalnet'])) {
            unset($_SESSION['novalnet']);
        }
    }

    /**
     * To get the masked account details
     * @param $customer_id
     * @param $payment_code
     *
     * @return mixed
     */
    static public function getPaymentDetails($customer_id, $payment_code)
    {
        if($customer_id != '') {
            $query           = tep_db_query('select payment_details, process_key FROM novalnet_transaction_detail WHERE customer_id="' . tep_db_input($customer_id) . '" and payment_type="' . $payment_code . '" AND reference_transaction = "0" AND payment_details != "" ORDER BY id DESC LIMIT 1');
            $sqlquery_result = tep_db_fetch_array($query);
            return $sqlquery_result;
        }
    }

    /**
     * Generate unique string
     *
     * @return string
     */
    static public function randomString()
    {
        $randomwordarray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
        shuffle($randomwordarray);
        return substr(implode($randomwordarray, ''), 0, 30);

    }

    /**
     * Get SEPA due date
     * @param none
     *
     * @return integer
     */
    static public function sepaDuedate()
    {
        $sepa_due_date_limit_tmp = MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE;
        $sepa_due_date_limit     = (($sepa_due_date_limit_tmp != '' && $sepa_due_date_limit_tmp >= 7) ? $sepa_due_date_limit_tmp : 7);
        unset($sepa_due_date_limit_tmp);
        return date('Y-m-d', strtotime('+' . $sepa_due_date_limit . ' days'));
    }

    /**
     * Validate status of fraud module
     * @param $payment
     * @param $fraud_module
     *
     * @return boolean
     */
    static public function setFraudModuleStatus($payment, $fraud_module = '')
    {
        global $order;
        $customer_iso_code = strtoupper($order->billing['country']['iso_code_2']);
        $allowed_country   = ($customer_iso_code && in_array($customer_iso_code, array('DE', 'AT', 'CH'))) ? true : false;
        if (!$fraud_module || !$allowed_country || constant('MODULE_PAYMENT_' . strtoupper($payment) . '_CALLBACK_LIMIT') > self::getPaymentAmount((array) $order)) { // Check country code, fraud module limit
            return false;
        }
        return true;
    }

    /**
     * Validate input form callback parameters
     * @param $datas
     * @param $fraud_module
     * @param $fraud_module_status
     * @param $code
     *
     * @return string
     */
    static public function validateCallbackFields($datas, $fraud_module, $fraud_module_status, $code)
    {

        if (empty($_SESSION['novalnet'][$code]['tid']) && $fraud_module_status) {
            if ($fraud_module == 'CALLBACK' && (empty($datas[$code . '_fraud_tel']) || !is_numeric(trim($datas[$code . '_fraud_tel'])))) { // Check telephone number for fraud module
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . utf8_decode(MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR), 'SSL'));
            } elseif ($fraud_module == 'SMS' && (empty($datas[$code . '_fraud_mobile']) || !is_numeric(trim($datas[$code . '_fraud_mobile'])) || trim($datas[$code . '_fraud_mobile']))) { // Check mobile number for fraud module
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . utf8_decode(MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_ERROR), 'SSL'));
            }
        }
    }

    /**
     * Redirect to checkout on success using fraud module
     * @param $payment
     * @param $fraud_module
     * @param $fraud_module_status
     *
     * @return boolean
     */
    static public function gotoPaymentOnCallback($payment, $fraud_module = NULL, $fraud_module_status = NULL)
    {
        if ($fraud_module && $fraud_module_status) {
            $_SESSION['novalnet'][$payment]['secondcall'] = true;
            $error_message                                = ($fraud_module == 'SMS') ? self::setUTFText(MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO) : self::setUTFText(MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_INFO);
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . trim($error_message), 'SSL')); // Display fraud module message for payment page after getting response
        }
        return true;
    }

    /**
     * Build input fields to get PIN
     * @param $fraud_module
     * @param $code
     *
     * @return array
     */
    static public function buildCallbackFieldsAfterResponse($fraud_module, $code)
    {
        $pin_field = array();
        if (in_array($fraud_module, array('CALLBACK', 'SMS'))) { // Display pin number field after getting response
            $pin_field[] = array(
                'title' => MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC . "<span style='color:red'> * </span>",
                'field' => tep_draw_input_field($code . '_fraud_pin', '', 'autocomplete="off" id="' . $code . '-' . strtolower($fraud_module) . 'pin"')
            );
            $pin_field[] = array(
                'title' => '',
                'field' => tep_draw_checkbox_field($code . '_new_pin', '1', false, 'id="' . $code . '-' . strtolower($fraud_module) . 'new_pin"') . MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN
            );
        }

        return $pin_field;
    }

    /**
     * Validate pin field
     * @param $payment_module
     * @param $datas
     * @param $fraud_module
     *
     * @return void
     */
    static public function validateUserInputsOnCallback($payment_module = '', $datas = array(), $fraud_module = '')
    {
        $error_message = '';
        $datas         = array_map('trim', $datas);
        if (in_array($fraud_module, array('CALLBACK', 'SMS')) && $error_message == '') {
            if (!isset($datas[$payment_module . '_new_pin']) && isset($datas[$payment_module . '_fraud_pin']) && (empty($datas[$payment_module . '_fraud_pin']) || !preg_match('/^[a-zA-Z0-9]+$/', $datas[$payment_module . '_fraud_pin']))) {
                $error_message = $datas[$payment_module . '_fraud_pin'] == '' ? MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_EMPTY : MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID;
            } else {
                $_SESSION['novalnet'][$payment_module . '_new_pin'] = !isset($datas[$payment_module . '_new_pin']) ? 0 : '';
            }
        }
        if ($error_message != '') {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . trim($error_message), 'SSL'));
        }
    }

    /**
     * Validate for users over 18 only
     * @param $birthdate
     *
     * @return boolean
     */
    static public function validateAge($birthdate)
    {
        return (time() < strtotime('+18 years', strtotime($birthdate))) ? false : true;
    }

    /**
     * Perform server XML request
     * @param $request_type
     * @param $payment_type
     *
     * @return array
     */
    static public function doXMLCallbackRequest($request_type, $payment_type)
    {
        // Perform XML request
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <nnxml>
            <info_request>
              <vendor_id>' . $_SESSION['novalnet'][$payment_type]['vendor'] . '</vendor_id>
              <vendor_authcode>' . $_SESSION['novalnet'][$payment_type]['auth_code'] . '</vendor_authcode>
              <request_type>' . $request_type . '</request_type>
              <tid>' . $_SESSION['novalnet'][$payment_type]['tid'] . '</tid>';
        if ($request_type == 'PIN_STATUS')
            $xml .= '<pin>' . trim($_SESSION['novalnet'][$payment_type][$payment_type . '_fraud_pin']) . '</pin>';
        $xml .= '</info_request></nnxml>';
        $xml_response = self::doPaymentCall('https://payport.novalnet.de/nn_infoport.xml', $xml, false); // Send Xml call
        return $xml_response;
    }

    /**
     * Get status and message from server response
     * @param $response
     *
     * @return array
     */
    static public function getStatusFromXmlResponse($response)
    {
        $xml          = simplexml_load_string($response);
        $xml_response = json_decode(json_encode((array) $xml), TRUE);
        return $xml_response;
    }

    /**
     * Validate callback status
     * @param $payment
     * @param $callback
     *
     * @return boolean
     */
    static public function validateCallbackStatus($payment, $callback = false)
    {
        if ($callback) {
            if (isset($_SESSION[$payment . '_nn_payment_lock']) && $_SESSION[$payment . '_callback_max_time_nn'] > time()) {
                if (!empty($_SESSION) && $_SESSION['payment'] == $payment) {
                    unset($_SESSION['payment']);
                }
                return false;
            } elseif (isset($_SESSION[$payment . '_nn_payment_lock']) && $_SESSION[$payment . '_callback_max_time_nn'] < time()) {
                unset($_SESSION[$payment . '_nn_payment_lock']);
                unset($_SESSION[$payment . '_callback_max_time_nn']);
                unset($_SESSION['novalnet']);
            }
        }
        return true;
    }

    /**
     * Perform manual check limit functionality
     *
     * @return none
     */
    static public function validatecallbacksession()
    {
        if (isset($_SESSION['customer_id']) && isset($_SESSION['novalnet']['login'])) {
            $sql                                  = tep_db_fetch_array(tep_db_query("select customers_info_number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $_SESSION['customer_id'] . "'"));
            $_SESSION['novalnet']['new_login_id'] = $sql['customers_info_number_of_logons'];
            if ($_SESSION['novalnet']['login'] != $_SESSION['novalnet']['new_login_id']) {
                unset($_SESSION['novalnet']);
                if (isset($_SESSION['novalnet_sepa_nn_payment_lock'])) {
                    unset($_SESSION['novalnet_sepa_nn_payment_lock']);
                }
                if (isset($_SESSION['novalnet_invoice_nn_payment_lock'])) {
                    unset($_SESSION['novalnet_invoice_nn_payment_lock']);
                }
            }
        }
        $query                         = tep_db_fetch_array(tep_db_query("select customers_info_number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $_SESSION['customer_id'] . "'"));
        $_SESSION['novalnet']['login'] = $query['customers_info_number_of_logons'];
    }

    /**
     * Return JS script to disable confirm button
     * @param none
     *
     * @return string
     */
    static public function confirmButtonDisableActivate()
    {
        return '<script type="text/javascript">
        if(typeof(jQuery) != "undefined" ) {
          $(document).ready(function(){
            $("form[name=checkout_confirmation]").submit(function(){
              $(this).find("button[type=submit]").attr("disabled", "disabled");
              $(this).find("button[type=submit]").css("opacity", "0.3");
            });
          });
        }
        </script>';
    }

    /**
     * Check condition for displaying birthdate field
     * @param $payment_name
     *
     * @return string
     */
    static public function displayBirthdateField($payment_name)
    {
        global $order;
        $guarantee_payment = defined('MODULE_PAYMENT_' . strtoupper($payment_name) . '_GUARANTEE_PAYMENT_CONFIGURATION') ? constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_GUARANTEE_PAYMENT_CONFIGURATION') : '';
        $guarantee_force   = defined('MODULE_PAYMENT_' . strtoupper($payment_name) . '_ENABLE_FORCE_GUARANTEE_PAYMENT') ? constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_ENABLE_FORCE_GUARANTEE_PAYMENT') : '';
        $customer_iso_code = strtoupper($order->customer['country']['iso_code_2']); // Get country code
        $amount            = self::getPaymentAmount((array) $order); // Get formate order amount
        if ($guarantee_payment == 'True' && ($amount > 1999 && $amount <= 500000) && in_array($customer_iso_code, array('DE', 'AT', 'CH')) && $order->info['currency'] == 'EUR') {
            return 'guarantee';
        } elseif ($guarantee_payment == 'False' || $guarantee_force == 'True') {
            return 'normal';
        } else {
            return '';
        }
    }

    /**
     * Return Affiliate details
     * @param $urlparam
     *
     * @return void
     */
    static public function getAffDetails(&$urlparam)
    {
        $_SESSION['nn_access_key'] = MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY;
        if ($_SESSION['customer_id'] != '' && (!isset($_SESSION['nn_aff_id']) || $_SESSION['nn_aff_id'] == '')) {
            $sql_query = tep_db_query('SELECT aff_id FROM novalnet_aff_user_detail WHERE customer_id = "' . tep_db_input($_SESSION['customer_id']) . '" ORDER BY id DESC LIMIT 1');
            // Check for previous affilliate
            $db_value  = tep_db_fetch_array($sql_query);
            if ($db_value['aff_id'] != '') {
                $_SESSION['nn_aff_id'] = $db_value['aff_id'];
            }
        }
        if (isset($_SESSION['nn_aff_id'])) {
            $sql_query = tep_db_query('SELECT aff_authcode, aff_accesskey FROM novalnet_aff_account_detail WHERE aff_id = "' . tep_db_input($_SESSION['nn_aff_id']) . '" and vendor_id = "' . tep_db_input(MODULE_PAYMENT_NOVALNET_VENDOR_ID) . '"');
            // Select affiliate details
            $db_value  = tep_db_fetch_array($sql_query);
            if (trim($db_value['aff_accesskey']) != '' && trim($db_value['aff_authcode']) != '' && $_SESSION['nn_aff_id'] != '') {
                $urlparam['vendor']        = $_SESSION['nn_aff_id'];
                $urlparam['auth_code']     = $db_value['aff_authcode'];
                $_SESSION['nn_access_key'] = $db_value['aff_accesskey'];
            }
        }
    }

    /**
     * Function to log all Novalnet transaction in novalnet_transaction_detail table
     * @param $datas
     *
     * @return void
     */
    static public function logInitialTransaction($datas)
    {
        $table_values = array(
            'tid'                   => $_SESSION['novalnet'][$datas['payment']]['tid'],
            'vendor'                => $_SESSION['novalnet'][$datas['payment']]['vendor'],
            'product'               => $_SESSION['novalnet'][$datas['payment']]['product'],
            'tariff'                => $_SESSION['novalnet'][$datas['payment']]['tariff'],
            'auth_code'             => $_SESSION['novalnet'][$datas['payment']]['auth_code'],
            'subs_id'               => $_SESSION['novalnet'][$datas['payment']]['subs_id'],
            'payment_id'            => $_SESSION['novalnet'][$datas['payment']]['payment_id'],
            'payment_type'          => $datas['payment'],
            'amount'                => $_SESSION['novalnet'][$datas['payment']]['amount'],
            'total_amount'          => $_SESSION['novalnet'][$datas['payment']]['total_amount'],
            'currency'              => $_SESSION['novalnet'][$datas['payment']]['currency'],
            'gateway_status'        => $_SESSION['novalnet'][$datas['payment']]['gateway_status'],
            'order_no'              => $datas['order_no'],
            'date'                  => date('Y-m-d H:i:s'),
            'language'              => $_SESSION['language'],
            'test_mode'             => ((isset($_SESSION['novalnet'][$datas['payment']]['test_mode']) && $_SESSION['novalnet'][$datas['payment']]['test_mode'] == 1) ? 1 : 0),
            'payment_details'       => $_SESSION['novalnet'][$datas['payment']]['payment_details'],
            'customer_id'           => $_SESSION['novalnet'][$datas['payment']]['customer_id'],
            'reference_transaction' => $_SESSION['novalnet'][$datas['payment']]['reference_transaction'],
            'zerotrxnreference'     => $_SESSION['novalnet'][$datas['payment']]['zerotrxnreference'],
            'zerotrxndetails'       => $_SESSION['novalnet'][$datas['payment']]['zerotrxndetails'],
            'zero_transaction'      => $_SESSION['novalnet'][$datas['payment']]['zero_transaction'],
            'payment_ref'           => isset($_SESSION['novalnet'][$datas['payment']]['payment_ref']) ? $_SESSION['novalnet'][$datas['payment']]['payment_ref'] : '',
            'process_key'           => isset($_SESSION['novalnet'][$datas['payment']]['process_key']) ? $_SESSION['novalnet'][$datas['payment']]['process_key'] : '',
            'callback_amount'       => (in_array($datas['payment'], array('novalnet_invoice', 'novalnet_prepayment')) || ($datas['payment'] == 'novalnet_paypal' && $_SESSION['novalnet'][$datas['payment']]['gateway_status'] == 90) || ($datas['payment'] == 'novalnet_przelewy24' && $_SESSION['novalnet'][$datas['payment']]['gateway_status'] == 86)) ? '0' : $_SESSION['novalnet'][$datas['payment']]['amount']
        );
        tep_db_perform('novalnet_transaction_detail', $table_values, "insert");
    }

    /**
     * To control the UTF-8 characters
     * @param $data
     *
     * @return integer
     */
    static public function setUTFText($data)
    {
        return (strtoupper(CHARSET) == 'UTF-8') ? html_entity_decode($data) : utf8_decode($data);
    }

    /**
     * Send notification to merchant for test transaction
     * @param $data
     * @param $order_no
     *
     * @return void
     */
    static public function sendTestTransactionNotification($data, $order_no)
    {
        if ($data['test_mode'] == '1') {
            tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_SUBJECT, sprintf(MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_MESSAGE, $order_no), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
    }

    /**
     * Returning payment reference and transaction id
     * @param $customer_id
     *
     * @return array
     */
    static public function paypalTransReference($customer_id)
    {
        if($customer_id != '') {
            $transaction_ref = tep_db_fetch_array(tep_db_query("select tid, payment_details from novalnet_transaction_detail where customer_id = '" . $customer_id . "' and payment_type='novalnet_paypal' and payment_details != '' order by id desc"));
            return $transaction_ref;
        }
    }

    /**
     * Billing and delivery address verification
     * @param $order
     *
     * @return boolean
     */
    static public function addressVerification($order)
    {
        $delivery_address = array(
            'city'           => $order->delivery['city'],
            'postcode'       => $order->delivery['postcode'],
            'state'          => $order->delivery['state'],
            'country'        => $order->delivery['country']['iso_code_2'],
            'street_address' => $order->delivery['street_address']
        );

        $billing_address = array(
            'city'           => $order->billing['city'],
            'postcode'       => $order->billing['postcode'],
            'state'          => $order->billing['state'],
            'country'        => $order->billing['country']['iso_code_2'],
            'street_address' => $order->billing['street_address']
        );

        return ($delivery_address === $billing_address);

    }

    /**
     * Prepare parameter for zero amount
     * @param $payment_module
     * @param $urlparam
     *
     * @return none
     */
    static public function novalnetZeroAmountProcess($payment_module, $urlparam)
    {
        $_SESSION['novalnet'][$payment_module]['zero_transaction'] = '';
        $tariff_type = explode('-', MODULE_PAYMENT_NOVALNET_TARIFF_ID);
        if ($tariff_type['0'] == 2) {
            $_SESSION['novalnet'][$payment_module]['zero_transaction'] = 1;
            $urlparam['amount']                                        = 0;
            if ($payment_module == 'novalnet_sepa') {
                $urlparam['sepa_due_date_val'] = (MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE != '' && MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE >= 7) ? MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE : 7;
            }
            $_SESSION['novalnet'][$payment_module]['zerotrxndetails'] = serialize($urlparam);
        }
        return $urlparam;
    }

}
?>
