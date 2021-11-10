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
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_DESC', 'Automatic PIN generation to authenticate buyers in DE, AT, and CH. Refer the <b>Installation Guide</b> for more information.  ');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_LIMIT_TITLE', 'Minimum value of goods for the fraud module (in minimum unit of currency. E.g. enter 100 which is equal to 1.00) ');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_LIMIT_DESC', 'Enter the minimum value of goods from which the fraud module should be activated');

define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK', 'PIN by callback');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS', 'PIN by SMS');

define('MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE', 'Minimum order amount (in minimum unit of currency. E.g. enter 100 which is equal to 1.00) ');
define('MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC', 'Minimum order amount to display the selected payment method (s) at during checkout.');

define('MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE', 'Notification for the buyer');
define('MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC', 'The entered text will be displayed at the checkout page');

define('MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE', 'Define a sorting order');
define('MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC', 'The payment methods will be listed in your checkout (in ascending order) based on your given sorting order.');

define('MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE', 'Completed order status');
define('MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC', 'Status to be used for successful orders.');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE', 'Payment zone');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC', 'This payment method will be displayed for the mentioned zone(-s)');

define('MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE', 'Shopping type');
define('MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC', 'Select shopping type');

define('MODULE_PAYMENT_OPTION_NONE', 'None');
define('MODULE_PAYMENT_NOVALNET_ONE_CLICK', 'One-click shopping');
define('MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT', 'Zero amount booking');

define('MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG', '<span style="color:red;">The payment will be processed in the test mode therefore amount for this transaction will not be charged</span>');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE', 'The amount is invalid');

define('MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS', 'Novalnet transaction details');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ID', 'Novalnet transaction ID: ');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TRANSACTION_ID', 'PayPal transaction ID: ');
define('MODULE_PAYMENT_NOVALNET_REFERENCE_ORDER_TEXT', 'Reference Order number: ');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG', 'Test order');
define('MODULE_PAYMENT_NOVALNET_INVOICE_COMMETNS_PARAGRAPH', 'Please transfer the amount to the below mentioned account.');
define('MODULE_PAYMENT_NOVALNET_DUE_DATE', 'Due date');
define('MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER', 'Account holder');
define('MODULE_PAYMENT_NOVALNET_IBAN', 'IBAN');
define('MODULE_PAYMENT_NOVALNET_BIC', 'BIC');
define('MODULE_PAYMENT_NOVALNET_BANK', 'Bank');
define('MODULE_PAYMENT_NOVALNET_AMOUNT', 'Amount');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT', 'Please use any of the following payment references when transferring the amount. This is necessary to match it with your corresponding order.');
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

define('MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS', 'Novalnet Callback Script executed successfully for the TID: %s with amount: %s on %s & %s. Please refer PAID transaction in our Novalnet Admin Portal with the TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_BOOKBACK_COMMENTS', 'Refund/Bookback executed successfully for the TID: %s amount: %s on %s & %s. The subsequent TID: %s');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS', 'Chargeback executed successfully for the TID: %s amount: %s on %s & %s. The subsequent TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS', 'Next charging date: ');

define('MODULE_PAYMENT_NOVALNET_REDIRECT_NOTICE_MSG', '<br />Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop ');
define('MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_BIRTH_DATE', 'Your date of birth');
define('MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR', 'Your account details are invalid');

define('MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_ERROR', 'Please enable the Javascript in your browser to proceed further with the payment');

define('MODULE_PAYMENT_NOVALNET_PHP_EXTENSION_MISSING', 'Mentioned PHP Package(s) not available in this Server. Please enable it');

define('MODULE_PAYMENT_NOVALNET_REFERENCE_ERROR', 'Please select atleast one payment reference.');

define('MODULE_PAYMENT_NOVALNET_AGE_ERROR', 'You need to be at least 18 years old');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS', 'Novalnet Callback Script executed successfully for the TID: %s with amount %s on %s & %s.');

define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR', 'Payment was not successful. An error occurred');

define('MODULE_PAYMENT_NOVALNET_GLOBAL_CONFIGURATION_DETAILS', '<h2>Payment guarantee configuration</h2><b>Payment guarantee requirements: </b><span style="font-weight:normal;"><br/><br/>Allowed countries: AT, DE, CH<br/>Allowed currency: EUR<br/>Minimum order amount: 9,99 EUR or more<br/>Age limit: 18 years or more<br/>The billing address must be the same as the shipping address</span><br/><br/><b>Enable payment guarantee</b>');
define('MODULE_PAYMENT_NOVALNET_GLOBAL_CONFIGURATION_DETAILS_DESCRIPTION', '');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MINIMUM_ORDER_AMOUNT', 'Minimum order amount for payment guarantee (in minimum unit of currency. E.g. enter 100 which is equal to 1.00) ');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MINIMUM_ORDER_AMOUNT_DESC', 'Enter the minimum amount (in cents) for the transaction to be processed with payment guarantee. For example, enter 100 which is equal to 1,00. By default, the amount will be 9,99 EUR. ');
define('MODULE_PAYMENT_NOVALNET_ENABLE_FORCE_GUARANTEE_PAYMENT', 'Force Non-Guarantee payment');
define('MODULE_PAYMENT_NOVALNET_ENABLE_FORCE_GUARANTEE_PAYMENT_DESC', 'Even if payment guarantee is enabled, payments will still be processed as non-guarantee payments if the payment guarantee requirements are not met. Review the requirements under "Enable Payment Guarantee" in the Installation Guide. ');

define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_SUBJECT','Order Confirmation - Your Order %s with %s has been confirmed!');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR', 'While redirecting some data has been changed. The hash check failed.');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_SUBJECT', 'Novalnet test order notification - osCommerce');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_MESSAGE', 'Dear client,<br/>We would like to inform you that test order (%s) has been placed in your shop recently.Please make sure your project is in LIVE mode at Novalnet administration portal and Novalnet payments are enabled in your shop system. Please ignore this email if the order has been placed by you for testing purpose.<br/>Regards, Novalnet AG');

define('MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY','This is processed as a guarantee payment');
define('MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY_CONFIRM','Your order is under verification and we will soon update you with the order status. Please note that this may take upto 24 hours.');

define('MODULE_PAYMENT_NOVALNET_MENTION_GUARANTEE_PAYMENT_PENDING_TEXT','Your order is being verified. Once confirmed, we will send you our bank details to which
 the order amount should be transferred. Please note that this may take up to 24 hours.');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_SEPA_PAYMENT_PENDING_TEXT','Your order is under verification and once confirmed, we will send you our bank details to where the order amount should be transferred.');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','The transaction has been confirmed on %s, %s');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_PENDING_TO_HOLD_MESSAGE','The transaction status has been changed from pending to on hold for the TID: %s on %s %s.');

define('MODULE_PAYMENT_NOVALNET_GURANTEE_PAYMENT_MIN_AMOUNT_ERROR_MSG', 'The minimum amount should be at least 9,99 EUR.');
define('MODULE_PAYMENT_NOVALNET_GURANTEE_PAYMENT_NOT_MATCH_ERROR_MSG', 'The payment cannot be processed, because the basic requirements haven’t been met.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_MESSAGE','We are pleased to inform you that your order has been confirmed.');
define('MODULE_PAYMENT_NOVALNET_INVOICE_ON_HOLD_CONFIRM_TEXT','The transaction has been confirmed successfully for the TID: %s and the due date updated as %s');
$novalnet_temp_status_text = 'NN payment pending';
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_CANCELLED_MESSAGE','The transaction has been canceled on %s %s');
define('MODULE_PAYMENT_NOVALNET_FORCE_GUARANTEE_ERROR_MESSAGE','<span style="color:red;">The payment cannot be processed, because the basic requirements haven’t been met</span>');
define('MODULE_PAYMENT_NOVALNET_FORCE_GUARANTEE_ERROR','The payment cannot be processed, because the basic requirements haven’t been met');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_ADDRESS',"The payment cannot be processed, because the basic requirements for the payment guarantee are not met (The shipping address must be the same as the billing address)");
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_COUNTRY',"The payment cannot be processed, because the basic requirements for the payment guarantee are not met (Only Germany, Austria or Switzerland are allowed)");
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_AMOUNT',"The payment cannot be processed, because the basic requirements for the payment guarantee are not met (Minimum order amount must be %s EUR)");
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_CURRENCY',"The payment cannot be processed, because the basic requirements for the payment guarantee are not met (Only EUR currency allowed)");
define('MODULE_PAYMENT_NOVALNET_AUTHORIZE','Authorize');
define('MODULE_PAYMENT_NOVALNET_CAPTURE','Capture');

define('DIR_WS_NOVALNET_ADMIN', DIR_FS_CATALOG .'admin/');
define('MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR', 'Please fill in the required fields');
