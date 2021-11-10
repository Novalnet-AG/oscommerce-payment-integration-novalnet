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
 * Script : novalnet.php
 *
 */
require_once(dirname(__FILE__) . '/novalnet.php');

define('MODULE_PAYMENT_NOVALNET_TRUE', 'True');
define('MODULE_PAYMENT_NOVALNET_FALSE', 'False');

define('MODULE_PAYMENT_STATUS_TITLE', 'Enable payment method');
define('MODULE_PAYMENT_STATUS_DESC', '');

define('MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE', 'Enable test mode');
define('MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC', 'The payment will be processed in the test mode therefore amount for this transaction will not be charged');

define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TITLE', 'Enable fraud prevention');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_DESC', 'To authenticate the buyer for a transaction, the PIN will be automatically generated and sent to the buyer. This service is only available for customers from DE, AT, CH');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_LIMIT_TITLE', 'Minimum value of goods for the fraud module (in minimum unit of currency. E.g. enter 100 which is equal to 1.00) ');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_LIMIT_DESC', 'Enter the minimum value of goods from which the fraud module should be activated');

define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK', 'PIN by callback');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS', 'PIN by SMS');

define('MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE', 'Minimum value of goods (in minimum unit of currency. E.g. enter 100 which is equal to 1.00) ');
define('MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC', 'Enter the minimum value of goods from which the payment method is displayed to the customer during checkout');

define('MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE', 'Notification for the buyer');
define('MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC', 'The entered text will be displayed on the checkout page');

define('MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE', 'Define a sorting order');
define('MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC', 'This payment method will be sorted among others (in the ascending order) as per the given sort number.');

define('MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE', 'Order completion status');
define('MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC', '');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE', 'Payment zone');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC', 'This payment method will be displayed for the mentioned zone(-s)');

define('MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_TITLE', 'Transaction reference 1');
define('MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_DESC', 'This reference will appear in your bank account statement');

define('MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_TITLE', 'Transaction reference 2');
define('MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_DESC', 'This reference will appear in your bank account statement');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE1_TITLE', 'Payment Reference 1 (Novalnet Invoice Reference)');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE1_DESC', '');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE2_TITLE', 'Payment Reference 2 (TID)');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE2_DESC', '');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE3_TITLE', 'Payment Reference 3 (Order No)');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE3_DESC', '');

define('MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE', 'Shopping type');
define('MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC', 'Select shopping type');

define('MODULE_PAYMENT_OPTION_NONE', 'None');
define('MODULE_PAYMENT_NOVALNET_ONE_CLICK', 'One click shopping');
define('MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT', 'Zero amount booking');

define('MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG', '<span style="color:red;">The payment will be processed in the test mode therefore amount for this transaction will not be charged</span>');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE', 'The amount is invalid');

define('MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS', 'Novalnet transaction details');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ID', 'Novalnet transaction ID: ');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TRANSACTION_ID', 'PayPal transaction ID: ');
define('MODULE_PAYMENT_NOVALNET_REFERENCE_ORDER_TEXT', 'Reference Order number: ');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG', 'Test order');
define('MODULE_PAYMENT_NOVALNET_INVOICE_COMMETNS_PARAGRAPH', 'Please transfer the amount to the below mentioned account details of our payment processor Novalnet');
define('MODULE_PAYMENT_NOVALNET_DUE_DATE', 'Due date');
define('MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER', 'Account holder');
define('MODULE_PAYMENT_NOVALNET_IBAN', 'IBAN');
define('MODULE_PAYMENT_NOVALNET_BIC', 'BIC');
define('MODULE_PAYMENT_NOVALNET_BANK', 'Bank');
define('MODULE_PAYMENT_NOVALNET_AMOUNT', 'Amount');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT', 'Please use any one of the following references as the payment reference, as only through this way your payment is matched and assigned to the order:');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT', 'Please use the following payment reference for your money transfer, as only through this way your payment is matched and assigned to the order:');
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF', 'Payment Reference');
define('MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF', 'Payment Reference%s');
define('MODULE_PAYMENT_NOVALNET_ORDER_NUMBER', 'Order number');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_INFO', 'You will shortly receive a transaction PIN through phone call to complete the payment');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO', 'You will shortly receive an sms containing your transaction PIN to complete the payment');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC', 'Transaction PIN');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN', '&nbsp; Forgot your PIN?');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_EMPTY', 'Enter your PIN');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID', 'The PIN you entered is incorrect');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_AMOUNT_CHANGE_ERROR', 'The order amount has been changed, please proceed with the new order');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_CALLBACK_INPUT_TITLE', 'Telephone number ');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_INPUT_TITLE', 'Mobile number ');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR', 'Please enter your telephone number ');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_ERROR', 'Please enter your mobile number');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS', 'Novalnet Callback Script executed successfully for the TID: %s with amount: %s on %s & %s. Please refer PAID transaction in our Novalnet Merchant Administration with the TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_RECURRING_COMMENTS', 'Novalnet Callback Script executed successfully for the subscription TID: %s with amount: %s on %s & %s. Please refer PAID transaction in our Novalnet Merchant Administration with the TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_BOOKBACK_COMMENTS', 'Novalnet callback received. Refund/Bookback executed successfully for the TID: %s amount: %s on %s & %s. The subsequent TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS', 'Novalnet callback received. Chargeback executed successfully for the TID: %s amount: %s on %s & %s. The subsequent TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_STOP_COMMENTS', 'Novalnet callback script received. Subscription has been stopped for the TID: %s on %s & %s.');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_REASON_TEXT', 'Subscription has been canceled due to: ');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS', 'Next charging date: ');
define('MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR', 'Please fill in all the mandatory fields');
define('MODULE_PAYMENT_NOVALNET_REDIRECT_NOTICE_MSG', '<br />Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop ');
define('MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_BIRTH_DATE', 'Your date of birth');
define('MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR', 'Your account details are invalid');

define('MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_ERROR', 'Please enable the Javascript in your browser to proceed further with the payment');

define('MODULE_PAYMENT_NOVALNET_PHP_EXTENSION_MISSING', 'Mentioned PHP Package(s) not available in this Server. Please enable it');

define('MODULE_PAYMENT_NOVALNET_REFERENCE_ERROR', 'Please select atleast one payment reference.');

define('MODULE_PAYMENT_NOVALNET_AGE_ERROR', 'You need to be at least 18 years old');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS', 'Novalnet Callback Script executed successfully for the TID: %s with amount %s on %s & %s.');

define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR', 'Payment was not successful. An error occurred');

define('MODULE_PAYMENT_NOVALNET_GLOBAL_CONFIGURATION_DETAILS', '<h2>Payment guarantee configuration</h2><b>Basic requirements for payment guarantee</b><span style="font-weight:normal;"><br/><br/>Allowed countries: AT, DE, CH<br/>Allowed currency: EUR<br/>Minimum amount of order >= 20,00 EUR<br/>Maximum amount of order <= 5.000,00 EUR<br/>Minimum age of end customer >= 18 Years<br/>The billing address must be the same as the shipping address<br/>Gift certificates/vouchers are not allowed</span><br/><br/><b>Enable payment guarantee</b>');
define('MODULE_PAYMENT_NOVALNET_GLOBAL_CONFIGURATION_DETAILS_DESCRIPTION', '');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MINIMUM_ORDER_AMOUNT', 'Minimum order amount (in minimum unit of currency. E.g. enter 100 which is equal to 1.00) ');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MINIMUM_ORDER_AMOUNT_DESC', 'This setting will override the default setting made in the minimum order amount. Note that amount should be in the range of 20,00 EUR - 5.000,00 EUR');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MAXIMUM_ORDER_AMOUNT', 'Maximum order amount (in minimum unit of currency. E.g. enter 100 which is equal to 1.00) ');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MAXIMUM_ORDER_AMOUNT_DESC', 'This setting will override the default setting made in the maximum order amount. Note that amount should be greater than minimum order amount, but not more than 5.000,00 EUR');
define('MODULE_PAYMENT_NOVALNET_ENABLE_FORCE_GUARANTEE_PAYMENT', 'Force Non-Guarantee payment');
define('MODULE_PAYMENT_NOVALNET_ENABLE_FORCE_GUARANTEE_PAYMENT_DESC', 'If the payment guarantee is activated (True), but the above mentioned requirements are not met, the payment should be processed as non-guarantee payment.');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR', 'While redirecting some data has been changed. The hash check failed.');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_SUBJECT', 'Novalnet test order notification - osCommerce');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_MESSAGE', 'Dear client,<br/>We would like to inform you that test order (%s) has been placed in your shop recently.Please make sure your project is in LIVE mode at Novalnet administration portal and Novalnet payments are enabled in your shop system. Please ignore this email if the order has been placed by you for testing purpose.<br/>Regards, Novalnet AG');
define('MODULE_PAYMENT_NOVALNET_TARRIF_PERIOD_ERROR_MSG', 'Please enter the valid subscription period');
define('MODULE_PAYMENT_NOVALNET_TARRIF_PERIOD2_ERROR_MSG', 'Please enter the valid subscription period2');
define('MODULE_PAYMENT_NOVALNET_TARRIF_AMOUNT_ERROR_MSG', 'Please enter the valid subscription period2 amount');
define('MODULE_PAYMENT_NOVALNET_GURANTEE_PAYMENT_MIN_AMOUNT_ERROR_MSG', 'The minimum amount should be at least 20,00 EUR but not more than 5.000,00 EUR');
define('MODULE_PAYMENT_NOVALNET_GURANTEE_PAYMENT_MAX_AMOUNT_ERROR_MSG', 'The maximum amount should be greater than minimum order amount, but not more than 5.000,00 EUR');
define('MODULE_PAYMENT_NOVALNET_GURANTEE_PAYMENT_NOT_MATCH_ERROR_MSG', 'The payment cannot be processed, because the basic requirements haven’t been met.');
