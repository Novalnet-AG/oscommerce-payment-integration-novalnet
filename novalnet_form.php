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
 * Script : novalnet_form.php
 */
  include('includes/application_top.php');
  require_once('includes/classes/novalnet/class.Novalnet.php');
  $data['vendor_id']  = NovalnetCore::getVendorID();
  $data['product_id'] = NovalnetCore::getProductID();
  $data['authcode']   = NovalnetCore::getVendorAuthCode();
  NovalnetCore::getAffDetails($data);
  if($_REQUEST['iframe'] == 'creditcard') {
	$request['nn_lang_nn'] 		 = isset($_REQUEST['nn_language']) ? $_REQUEST['nn_language'] : '';
	$request['nn_vendor_id_nn']  = $data['vendor_id'];
	$request['nn_product_id_nn'] = $data['product_id'];
	$request['nn_payment_id_nn'] = 6; //default
	$request['nn_authcode_nn'] 	 = $data['authcode'];
	$request['nn_hash'] 		 = $_REQUEST['hash'];
	$request['fldVdr'] 			 = $_REQUEST['fldvdr'];
	$url      					 = 'https://payport.novalnet.de/direct_form.jsp';
  } else {
	$request['lang'] 	   = isset($_REQUEST['nn_language']) ? $_REQUEST['nn_language'] : '';
	$request['vendor_id']  = $data['vendor_id'];
	$request['product_id'] = $data['product_id'];
	$request['payment_id'] = 37; //default
	$request['authcode']   = $data['authcode'];
	$request['country']    = isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '';
	$request['panhash']    = isset($_REQUEST['hash']) ? $_REQUEST['hash'] : '';
	$request['fldVdr'] 	   = (isset($_REQUEST['fldvdr']) && !empty($_REQUEST['hash'])) ? $_REQUEST['fldvdr'] : '';
	$request['name'] 	   = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
	$request['comp'] 	   = isset($_REQUEST['comp']) ? $_REQUEST['comp'] : '';
	$request['address']    = isset($_REQUEST['address']) ? $_REQUEST['address'] : '';
	$request['zip'] 	   = isset($_REQUEST['zip']) ? $_REQUEST['zip'] : '';
	$request['city'] 	   = isset($_REQUEST['city']) ? $_REQUEST['city'] : '';
	$request['email'] 	   = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
	$url 	     		   = 'https://payport.novalnet.de/direct_form_sepa.jsp';
  }
  $request = array_map('trim',$request);
  echo NovalnetInterface::doPaymentCurlCall($url, $request);
?>
