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
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_BUTTON','Manage Transaction');
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_TITLE','Manage transaction process');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT', 'Please select status');
define('MODULE_PAYMENT_NOVALNET_SELECT_CONFIRM_TEXT', 'Are you sure you want to capture the payment?');
define('MODULE_PAYMENT_NOVALNET_SELECT_CANCEL_TEXT', 'Are you sure you want to cancel the payment?');
define('MODULE_PAYMENT_NOVALNET_REFUND_AMOUNT_TEXT', 'Are you sure you want to refund the amount?');
define('MODULE_PAYMENT_NOVALNET_BOOK_AMOUNT_TEXT', 'Are you sure you want to book the order amount?');
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','The transaction has been confirmed on %s, %s');
define('MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE','The transaction has been canceled on %s, %s');
define('MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE','The transaction amount ( %s )  has been updated successfully on %s, %s');
define('MODULE_PAYMENT_NOVALNET_REFUND_BUTTON','Refund');
define('MODULE_PAYMENT_NOVALNET_REFUND_AMT_TITLE','Please enter the refund amount');
define('MODULE_PAYMENT_NOVALNET_REFUND_TITLE','Refund process');
define('MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG','The refund has been executed for the TID: %s with the amount of %s');
define('MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG',' .Your new TID for the refund amount: %s');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_EX',' (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT','Confirm');
define('MODULE_PAYMENT_NOVALNET_BACK_TEXT', 'Back');
define('MODULE_PAYMENT_NOVALNET_UPDATE_TEXT','Confirm');
define('MODULE_PAYMENT_NOVALNET_CANCEL_TEXT','Cancel');
define('MODULE_PAYMENT_NOVALNET_ORDER_UPDATE','Successful');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION', '--Select--');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TITLE','Cancel Subscription Process');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_BUTTON','Cancel Subscription');
define('MODULE_PAYMENT_NOVALNET_SUBS_SELECT_REASON','Please select reason');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_MESSAGE','Subscription has been canceled due to: ');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_TITLE', 'Please select the reason of subscription cancellation');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_1','Product is costly');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_2','Cheating');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_3','Partner interfered');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_4', 'Financial problem');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_5','Content does not match my likes');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_6','Content is not enough');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_7','Interested only for a trial');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_8','Page is very slow');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_9','Satisfied customer');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_10','Logging in problems');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_11','Other');
define('MODULE_PAYMENT_NOVALNET_BOOK_TITLE','Book transaction');
define('MODULE_PAYMENT_NOVALNET_BOOK_BUTTON','Book');
define('MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE','Transaction booking amount');
define('MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE','Your order has been booked with the amount of %s. Your new TID for the booked amount:%s');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE','Amount update');
define('MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TITLE', 'Update transaction amount');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_BUTTON','Change the amount / due date');
define('MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_TITLE','Transaction due date');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT','Are you sure you want to change the order amount?');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_DATE_UPDATE_TEXT','Are you sure you want to change the order amount or due date?');
define('MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE','The date should be in future');
define('MODULE_PAYMENT_NOVALNET_REFUND_PAYMENTTYPE_TITLE','Select the refund option');
define('MODULE_PAYMENT_NOVALNET_PAYMENTTYPE_NONE', 'None');
define('MODULE_PAYMENT_NOVALNET_SEPA_TEXT_TITLE','Direct Debit SEPA');
define('MODULE_PAYMENT_NOVALNET_MAP_PAGE_HEADER', 'Login here with Novalnet merchant credentials. For the activation of new payment methods please contact <a href="mailto:support@novalnet.de">support@novalnet.de</a>');
define('MODULE_PAYMENT_NOVALNET_REFUND_REFERENCE_TEXT','Refund reference');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS', 'Novalnet transaction details');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ID', 'Novalnet transaction ID: ');
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
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF', 'Payment Reference' );
define('MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF', 'Payment Reference%s');
define('MODULE_PAYMENT_NOVALNET_ORDER_NUMBER', 'Order number');
define('MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR','Your account details are invalid');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE','The amount is invalid');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TEXT','Are you sure you want to cancel the subscription?');
define('MODULE_PAYMENT_NOVALNET_INVALID_DATE','Invalid due date');
