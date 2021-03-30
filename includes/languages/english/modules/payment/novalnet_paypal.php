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
 * Script : novalnet_paypal.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_TITLE','PayPal ');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_DESCRIPTION','<span style="font-weight: bold; color:#878787;">To accept PayPal transactions, configure your PayPal API info in the <a href="https://admin.novalnet.de" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet Admin Portal</a> > PROJECT > "Project" Information > Payment Methods > Paypal > Configure.</span><br/>Electronic wallet that alows buyers to pay using any payment modes they have added to their PayPal account');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_DESC','You will be redirected to PayPal. Please don’t close or refresh the browser until the payment is completed');

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PUBLIC_TITLE',((defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? tep_image(DIR_WS_ICONS.'novalnet/novalnet_paypal.png',"PayPal" ) :''));

define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_TITLE',MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_DESC',MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_TITLE','Payment Action');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_DESC','Choose whether or not the payment should be charged immediately. Capture completes the transaction by transferring the funds from buyer account to merchant account. Authorize verifies payment details and reserves funds to capture it later, giving time for the merchant to decide on the order.');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_TITLE',MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_DESC',MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_TITLE',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_DESC',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_TITLE',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_DESC',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_TITLE',MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_DESC',MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_TITLE','Payment pending order status');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_DESC','Status to be used for pending transactions.');

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
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT_TITLE', 'Minimum transaction amount for authorization (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT_DESC', 'In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.');
