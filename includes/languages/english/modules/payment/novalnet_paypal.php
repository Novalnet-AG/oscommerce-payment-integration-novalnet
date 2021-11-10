<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Author : Novalnet AG
 * Copyright (c) Novalnet
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnet_paypal.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_TITLE','PayPal ');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_DESCRIPTION','After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment');

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PUBLIC_TITLE',((defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? '<a href="https://www.novalnet.com/paypal" title="PayPal" target="_blank"/>'.tep_image(DIR_WS_ICONS.'novalnet/novalnet_paypal.png',"PayPal" ).'</a>':''));

define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_TITLE',MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_DESC',MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_TITLE','On-hold payment action');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_DESC','Enable authentication for onhold');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_TITLE',MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_DESC',MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_TITLE',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_DESC',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_TITLE',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_DESC',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_TITLE',MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_DESC',MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_TITLE','Order status for the pending payment');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_DESC','');

define('MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_TITLE',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_DESC',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_TITLE',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_DESC',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_BLOCK_TITLE','<b>PayPal API Configuration</b>');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_REDIRECTION_ERROR_MESSAGE', MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_TITLE', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_DESC', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC.'<br><span id="paypal_message" style=color:red></span>');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_NONE', MODULE_PAYMENT_OPTION_NONE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK', MODULE_PAYMENT_NOVALNET_ONE_CLICK);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ZERO_AMOUNT', MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_NEW_ACCOUNT', 'Proceed with new PayPal account details');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_GIVEN_ACCOUNT','Given PayPal account details');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK_SHOPPING_DESCRIPTION','Once the order is submitted, the payment will be processed as a reference transaction at Novalnet');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOW_MESSAGE','In order to use this option you must have billing agreement option enabled in your PayPal account. Please contact your account manager at PayPal.');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT_TITLE', 'Minimum transaction limit for authorization (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT_DESC', 'In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.');
