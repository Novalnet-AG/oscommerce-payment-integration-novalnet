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
        } else if (empty(MODULE_PAYMENT_NOVALNET_VENDOR_ID) || empty(MODULE_PAYMENT_NOVALNET_CLIENT_KEY)) {
			if ($admin)
				echo self::novalnetBackEndShowError(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE);
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
        $value              = MODULE_PAYMENT_NOVALNET_VENDOR_ID . '|' . MODULE_PAYMENT_NOVALNET_PRODUCT_ID . '|' . MODULE_PAYMENT_NOVALNET_TARIFF_ID . '|' . MODULE_PAYMENT_NOVALNET_AUTH_CODE . '|' . MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY . '|' . MODULE_PAYMENT_NOVALNET_CLIENT_KEY;
        preg_match($pattern, $value, $match);
        if (empty($match[0])) {
            $merchant_api_error = true;
        } elseif (MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO != '' && !self::validateEmail(MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO)) {
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
        $remote_ip           = self::getIpAddress('REMOTE_ADDR');
        $system_ip           = self::getIpAddress('SERVER_ADDR');
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
            'remote_ip'        => $remote_ip,
            'system_name'      => 'oscommerce',
            'system_ip'        => $system_ip,
            'system_url'       => ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER.DIR_WS_CATALOG),
            'system_version'   => (function_exists('tep_get_version') ? tep_get_version() : PROJECT_VERSION) . '-NN11.1.6'
        );
        if ($customer_details['customers_fax'] !=''){
        $urlparam['fax']   = $customer_details['customers_fax'];
	    }
        $urlparam['country'] = $urlparam['country_code'] = $datas['billing']['country']['iso_code_2'];
        $urlparam['lang']    = strtoupper($language_code['code']);
        
        if (!empty($datas['billing']['company']))
            $urlparam['company'] = $datas['billing']['company'];

        $notify_url = trim(MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL);
        if (!empty($notify_url))
            $urlparam['notify_url'] = $notify_url;
        
        return $urlparam;
    }
    
    /**
     * Get / Validate IP address
     * @param $ip_type
     *
     * @return string
     */
    public static function getIpAddress($ip_type)
    {
        if ($ip_type == 'REMOTE_ADDR') {
            $ipAddress = tep_get_ip_address();
        } else {
            if (empty($_SERVER[$ip_type]) && !empty($_SERVER['SERVER_NAME'])) {
                // Handled for IIS server
                $ipAddress = gethostbyname($_SERVER['SERVER_NAME']);
            } else {
                $ipAddress = $_SERVER[$ip_type];
            }
        }
        return $ipAddress;
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
     * Generate 30 digit unique string
     ***
     * return string
     */
    public static function uniqueRandomString() {
     $randomwordarray = explode(',', '8,7,6,5,4,3,2,1,9,0,9,7,6,1,2,3,4,5,6,7,8,9,0');
    shuffle($randomwordarray);
    return substr(implode($randomwordarray, ''), 0, 16);
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
            'uniqid'    => self::uniqueRandomString()
        ));
        
        $params['implementation']  = 'ENC';
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
        foreach (array('auth_code', 'product', 'tariff', 'amount', 'test_mode') as $key) {
            $datas[$key] = self::generateEncode($datas[$key],$datas['uniqid']); // Encoding process
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
    static public function generateEncode($data = '',$uniqid)
    {
        try {
            $data = htmlentities(base64_encode(openssl_encrypt($data, "aes-256-cbc", MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY, true, $uniqid)));
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
        $data['uniqid'] = $datas['uniqid'];
        
        foreach ($data as $key => $value) {
            $result[$key] = self::generateDecode($value,$data['uniqid']); // Decode process
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
    static public function generateDecode($data = '',$uniqid)
    {  
        try {
            
            $data = openssl_decrypt(base64_decode($data), "aes-256-cbc", MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY, true, $uniqid); 
            
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
        
        return hash('sha256', ($datas['auth_code'].$datas['product'].$datas['tariff'].$datas['amount'].$datas['test_mode'].$datas['uniqid'].strrev(MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY)));
        
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
		global $languages_id;
		
		if($build_query) {
			$language_code_query = "select code from " . TABLE_LANGUAGES . " where languages_id = ".$languages_id."";
			$language_code = tep_db_fetch_array(tep_db_query($language_code_query));
			
			$request_params['lang']    = strtoupper($language_code['code']);
		}
        
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
        curl_setopt($curl_process, CURLOPT_TIMEOUT, 240); //Custom CURL time-out

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
        $trans_comments   = '';
        if( $response['tid_status'] != 75 ){
			$trans_comments   = PHP_EOL . self::setUTFText(MODULE_PAYMENT_NOVALNET_INVOICE_COMMETNS_PARAGRAPH) . PHP_EOL;
			
			if( in_array($response['payment_id'], array(27,41)) && $response['tid_status'] == 100 ) {
				$trans_comments .= ($response['due_date'] != '') ? self::setUTFText(MODULE_PAYMENT_NOVALNET_DUE_DATE) . ': ' . date(DATE_FORMAT, strtotime($response['due_date'])) . PHP_EOL : '';
			}
			$amount     = isset($_SESSION['novalnet'][$_SESSION['payment']]['order_amount']) ? ($_SESSION['novalnet'][$_SESSION['payment']]['order_amount'] / 100) : $response['amount'];
			$trans_comments .= MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER . ': Novalnet AG' . PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_IBAN . ': ' . $response['invoice_iban'] . PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_BIC . ': ' . $response['invoice_bic'] . PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_BANK . ': ' . self::setUTFText($response['invoice_bankname']) . $response['invoice_bankplace'] . PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_AMOUNT . ': ' . $currencies->format($amount, false, $response['currency']) . PHP_EOL;
      	} 
        return array(
            $trans_comments,
            array(
                'order_no'       => !empty($response['order_no']) ? $response['order_no'] : '' ,
                'tid'            => $response['tid'],
                'test_mode'      => $response['test_mode'],
                'account_holder' => $response['invoice_account_holder'],
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
     * @param $order_id
     * @param $payment
     * @param $datas
     *
     * @return array
     */
    static public function novalnetReferenceComments($order_id, $payment, $datas)
    {
        // Form payment reference comments
		$payment_references = array_filter(
			array(
			 ': BNR-'. (isset($datas['product']) ? $datas['product'] : $_SESSION['novalnet'][$payment]['product']) .'-'.$order_id => 'payment_reference1',
			 ': TID '. (isset($datas['tid']) ? $datas['tid'] : $_SESSION['novalnet'][$payment]['tid']) => 'payment_reference2',
			)
		);
		
		$i = 1;
		$invpre_comments .= MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT. PHP_EOL;
		foreach ($payment_references as $key => $value) {
			$invpre_comments .= sprintf(MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF, ' ' . $i++).$key. PHP_EOL;
		}
        return $invpre_comments;
    }
    
    /**
     * Built Barzahlen comments
     * @param $response
     * @param $due_date
     *
     * @return array
     */
    public static function formBarzahlenComments($response, $due_date = false)
    {
        $barzahlen_comments = '';

        $slip_due_date = !empty($due_date) ? $due_date : $response['cp_due_date'];

        $barzahlen_comments .= MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE_TEXT . ': '.date(DATE_FORMAT, strtotime($slip_due_date)).PHP_EOL;

        $nearest_store =  self::getNearestStore($response);
        $nearest_store['nearest_store'] = $nearest_store;
        if (!empty($nearest_store)) {
            $barzahlen_comments .= PHP_EOL . self::setUtf8Mode(MODULE_PAYMENT_NOVALNET_BARZAHLEN_NEAREST_STORE_DETAILS_TEXT).PHP_EOL;
        }

        $nearest_store['cp_due_date'] = $slip_due_date;
        $i =0;
        foreach ($nearest_store as $key => $values) {
            $i++;
            if (!empty($nearest_store['nearest_store_title_'.$i])) {
                $barzahlen_comments .= PHP_EOL . self::setUtf8Mode($nearest_store['nearest_store_title_'.$i]);
            }
            if (!empty($nearest_store['nearest_store_street_'.$i])) {
                $barzahlen_comments .= PHP_EOL . self::setUtf8Mode($nearest_store['nearest_store_street_'.$i]);
            }
            if (!empty($nearest_store['nearest_store_city_'.$i])) {
                $barzahlen_comments .= PHP_EOL . self::setUtf8Mode($nearest_store['nearest_store_city_'.$i]);
            }
            if (!empty($nearest_store['nearest_store_zipcode_'.$i])) {
                $barzahlen_comments .= PHP_EOL . $nearest_store['nearest_store_zipcode_'.$i];
            }

            if (!empty($nearest_store['nearest_store_country_'.$i])) {
                $query = tep_db_query("select countries_name from countries where countries_iso_code_2='". $nearest_store['nearest_store_country_'.$i] ."'");
                $get_country = tep_db_fetch_array($query);
                $barzahlen_comments .= PHP_EOL . $get_country['countries_name'].PHP_EOL;
            }
        }
        $nearest_store['cp_checkout_token'] = $response['cp_checkout_token'];
        return array($barzahlen_comments, $nearest_store);
    }
    
    /**
     * Get nearest store details
     * @param $response
     *
     * @return array
     */
    public static function getNearestStore($response)
    {
        $stores = array();
        foreach ($response as $keys => $values) {
            if (stripos($keys, 'nearest_store')!==false) {
                $stores[$keys] = $values;
            }
        }
        return $stores;
    }
    
    /**
     * To control the UTF-8 characters
     * @param $string
     *
     * @return integer
     */
    public static function setUtf8Mode($string)
    {
        if (in_array($_SESSION['language_charset'], array('iso-8859-1', 'iso-8859-15'))) {
            return utf8_decode($string);
        }        
        return $string;
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
        if (in_array($_SESSION['novalnet'][$datas['payment']]['payment_id'], array(27, 41))) {
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
		$remote_ip  = self::getIpAddress('REMOTE_ADDR');
        // Perform XML request
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <nnxml>
            <info_request>
              <vendor_id>' . $_SESSION['novalnet'][$payment_type]['vendor'] . '</vendor_id>
              <vendor_authcode>' . $_SESSION['novalnet'][$payment_type]['auth_code'] . '</vendor_authcode>
              <request_type>' . $request_type . '</request_type>
              <remote_ip>' . $remote_ip . '</remote_ip>
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
    static public function confirmButtonDisableActivate( $action_url = '' )
    {
		$script = '<script type="text/javascript">
					if(typeof(jQuery) != "undefined" ) {
					$(document).ready(function(){';
					
		if ( !empty($action_url) ) {
			$script .= '$("form[name=checkout_confirmation]").attr("action","' . $action_url . '");';
		}

		$script .= '$("form[name=checkout_confirmation]").submit(function(){
					$(this).find("button[type=submit]").attr("disabled", "disabled");
					$(this).find("button[type=submit]").css("opacity", "0.3");
				});
			});
		}
		</script>';
        return $script;
    }

    /**
     * Check condition for displaying birthdate field
     * @param $payment_name
     *
     * @return string
     */
    static public function displayBirthdateField($order, $payment_name)
    {
        global $order;
        
         // Get payment name in caps
        $payment_name_caps = strtoupper($payment_name);
        
        $guarantee_payment = defined('MODULE_PAYMENT_' . $payment_name_caps . '_GUARANTEE_PAYMENT_CONFIGURATION') ? constant('MODULE_PAYMENT_' . $payment_name_caps . '_GUARANTEE_PAYMENT_CONFIGURATION') : '';
        $customer_iso_code = strtoupper($order->customer['country']['iso_code_2']); // Get country code
        $amount            = self::getPaymentAmount((array) $order); // Get formate order amount
        
        $minimum_amount = constant('MODULE_PAYMENT_'.$payment_name_caps.'_GUARANTEE_PAYMENT_MINIMUM_AMOUNT');
        
        $minimum_amount = $minimum_amount != '' ? $minimum_amount : '999';
        
        // Delivery address
        $delivery_address = array(
            'street_address' => $order->delivery['street_address'],
            'city'           => $order->delivery['city'],
            'postcode'       => $order->delivery['postcode'],
            'country'        => $order->delivery['country']['iso_code_2'],
        );

        // Billing address
        $billing_address = array(
            'street_address' => $order->billing['street_address'],
            'city'           => $order->billing['city'],
            'postcode'       => $order->billing['postcode'],
            'country'        => $order->billing['country']['iso_code_2'],
        );
        
        if ($guarantee_payment == 'True') {
			 if ((((int) $amount >= (int) $minimum_amount) && in_array($customer_iso_code, array('DE', 'AT', 'CH')) && $order->info['currency'] == 'EUR' && $delivery_address === $billing_address)) {	 
                return 'guarantee';
            } elseif (constant('MODULE_PAYMENT_'.$payment_name_caps.'_ENABLE_FORCE_GUARANTEE_PAYMENT') == 'True') {
                return 'normal';
            } else {
                return 'error';
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
            'callback_amount'       => (in_array($datas['payment'], array('novalnet_invoice', 'novalnet_prepayment', 'novalnet_barzahlen')) || ($datas['payment'] == 'novalnet_paypal' && $_SESSION['novalnet'][$datas['payment']]['gateway_status'] == 90) || ($datas['payment'] == 'novalnet_przelewy24' && $_SESSION['novalnet'][$datas['payment']]['gateway_status'] == 86)) ? '0' : $_SESSION['novalnet'][$datas['payment']]['amount']
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
    
    /**
     * To form guarantee payment order confirmation mail 
     * 
     * @param $datas array
     */
    public static function guarantee_mail ($datas,$db_details = array() ){
		if( !isset($db_details['payment_details']) && $db_details['payment_details'] == ''){
			$customer_id = tep_db_fetch_array(tep_db_query("SELECT customers_id FROM " . TABLE_ORDERS . " WHERE orders_id= ". $datas['order_no'] ."  ORDER BY orders_id DESC LIMIT 1"));
			$customer_dbvalue = tep_db_fetch_array(tep_db_query("SELECT customers_firstname,customers_lastname,customers_email_address FROM " . TABLE_CUSTOMERS . " WHERE customers_id= ". $customer_id['customers_id'] . "  ORDER BY customers_id DESC LIMIT 1"));
			$customername  = $customer_dbvalue['customers_firstname'].$customer_dbvalue['customers_lastname'];
			$customeremail = $customer_dbvalue['customers_email_address'];
		}else {
			$customer_dbvalue = tep_db_fetch_array(tep_db_query("SELECT customers_firstname,customers_lastname,customers_email_address FROM " . TABLE_CUSTOMERS . " WHERE customers_id= ". $db_details['customer_id'] ."  ORDER BY customers_id DESC LIMIT 1"));
			$customername  = $customer_dbvalue['customers_firstname'].$customer_dbvalue['customers_lastname'];
			$customeremail = $customer_dbvalue['customers_email_address'];
		}
		$subject = sprintf(MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_SUBJECT,$datas['order_no'],STORE_NAME) . PHP_EOL;
        $message = '<body style="background:#F6F6F6; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:14px; margin:0; padding:0;"><div style="width:55%;height:auto;margin: 0 auto;background:rgb(247, 247, 247);border: 2px solid rgb(223, 216, 216);border-radius: 5px;box-shadow: 1px 7px 10px -2px #ccc;"><div style="min-height: 300px;padding:20px;"><b>Dear Mr./Ms./Mrs.</b>'.$customername.'<br><br>'.MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_MESSAGE.'<br><br><b>Payment Information:</b><br>'.nl2br($datas['comments']).'</div><div style="width:100%;height:20px;background:#00669D;"></div></div></body>';
		tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $subject, $message , STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
	}
	
	 /**
     * To check data is empty or not  
     * 
     * @param $datas array
     */
     public static function check_data ($datas){
		 global $order;
		 if(empty($datas) && $order->billing['company'] == ''){
			 return false;
		 }else{
			 return true;
		 }
	 }

}
?>
