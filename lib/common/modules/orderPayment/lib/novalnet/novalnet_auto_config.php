<?php
/**
 * This file is used for auto configuration of merchant details
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * File: novalnet_auto_config.php
 *
 */
namespace common\modules\orderPayment\lib\novalnet;

use common\modules\orderPayment\lib\novalnet\NovalnetHelper;
chdir('../../../../../../');
include('includes/application_top.php');
$request = $_REQUEST;
if (!empty($request['access_key']) && !empty($request['activation_key']) && !empty($request['action'])) { // To get values and form request parameters
    $data = [];
    $data['merchant'] = [
        'signature' => $request['activation_key']
    ];
    $data['custom'] = [
        'lang' => $request['lang']
    ];
    if ($request['action'] == 'webhook_configure' && !empty($request['callback_url'])) {
        $data['webhook'] = [
            'url' => $request['callback_url']
        ];
    }
    $json_data = json_encode($data);
    $endpoint = NovalnetHelper::get_endpoint($request['action']);
    $response = NovalnetHelper::send_request($json_data, $endpoint, $request['access_key']); // Sending request to Novalnet
    echo json_encode($response);
}
