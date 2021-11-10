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
 * Script : english.php
 */
require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/version.php');
$module_version = getPaymentModuleVersion();
$localeValues = array(
  'MODULE_PAYMENT_NOVALNET_TRUE'           => 'True',
  'MODULE_PAYMENT_NOVALNET_LANGUAGE_TEXT'  => 'EN',
  'MODULE_PAYMENT_NOVALNET_FALSE'          => 'False',
  'MODULE_PAYMENT_NOVALNET_CC_TYPE_SELECT' => 'Please select',
  'MODULE_PAYMENT_NOVALNET_CONFIG_TITLE'   => '<b>Novalnet Global Configuration</b> (V_'.$module_version.')',
  'MODULE_PAYMENT_NOVALNET_CONFIG_DESC'    => '<span style="font-weight: bold; color:#878787;">For additional configurations login to <a href="'.DIR_WS_CATALOG.'admin/novalnet.php?guide=1&process=map" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet Merchant Administration portal</a>.<br/> To login to the Portal you need to have an account at Novalnet. If you don&#39;t have one yet, please contact <a style="font-weight: bold; color:#0080c9;"href="mailto:sales@novalnet.de">sales@novalnet.de</a> / tel. +49 (089) 923068320</span><br/><br/><span style="font-weight: bold; color:#878787;">To use the PayPal payment method please enter your PayPal API details in <a href="'.DIR_WS_CATALOG.'admin/novalnet.php?guide=1&process=map" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet Merchant Administration portal</a></span>',

  'MODULE_PAYMENT_NOVALNET_PAYPAL_BLOCK_TITLE'      => '<b>PayPal API Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE'        => '<b>SEPA Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_CC_BLOCK_TITLE'          => '<b>Credit Card Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE'     => '<b>Invoice Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_EPS_BLOCK_TITLE'         => '<b>EPS Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_SOFORTBANK_BLOCK_TITLE'  => '<b>Instant Bank Transfer Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_IDEAL_BLOCK_TITLE'       => '<b>iDEAL Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_PREPAYMENT_BLOCK_TITLE'  => '<b>Prepayment Configuration</b>',
  'MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE'      => '<b>Merchant API Configuration</b> (V_'.$module_version.')',
  'MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_FUNC_ERROR' => 'Mentioned PHP Package(s) not available in this Server. Please enable it.<br/>',

  'MODULE_PAYMENT_NOVALNET_PUBLIC_KEY_TITLE' 						 => 'Product activation key',
  'MODULE_PAYMENT_NOVALNET_PUBLIC_KEY_DESC' 						 => 'Enter Novalnet Product activation key',
  'MODULE_PAYMENT_NOVALNET_VENDOR_TITLE'                             => 'Merchant ID',
  'MODULE_PAYMENT_NOVALNET_VENDOR_DESC'                              => 'Enter Novalnet merchant ID',
  'MODULE_PAYMENT_NOVALNET_AUTH_CODE_TITLE'                          => 'Authentication code',
  'MODULE_PAYMENT_NOVALNET_AUTH_CODE_DESC'                           => 'Enter Novalnet authentication code',
  'MODULE_PAYMENT_NOVALNET_PROJECT_TITLE'                            => 'Project ID',
  'MODULE_PAYMENT_NOVALNET_PROJECT_DESC'                             => 'Enter Novalnet project ID',
  'MODULE_PAYMENT_NOVALNET_TARIFF_TITLE'                             => 'Tariff ID',
  'MODULE_PAYMENT_NOVALNET_TARIFF_DESC'                              => 'Select Tariff ID',
  'MODULE_PAYMENT_NOVALNET_ACCESS_KEY_TITLE'                         => 'Payment access key',
  'MODULE_PAYMENT_NOVALNET_ACCESS_KEY_DESC'                          => 'Enter the Novalnet payment access key',
  'MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_TITLE'                 => 'Set a limit for on-hold transaction (in cents)',
  'MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_DESC'                  => 'In case the order amount exceeds mentioned limit, the transaction will be set on hold till your confirmation of transaction',
  'MODULE_PAYMENT_NOVALNET_AUTO_REFILL_TITLE'                        => 'Enable auto-fill',
  'MODULE_PAYMENT_NOVALNET_AUTO_REFILL_DESC'                         => 'The payment details will be filled automatically in the payment form during the checkout process',
  'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_TITLE' => 'Enable default payment method',
  'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_DESC'  => 'For the registered users the last chosen payment method will be selected by default during the checkout',
  'MODULE_PAYMENT_NOVALNET_PROXY_TITLE'                  => 'Proxy server',
  'MODULE_PAYMENT_NOVALNET_PROXY_DESC'                   => 'Enter the IP address of your proxy server along with the port number in the following format IP Address : Port Number (if applicable)',
  'MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT_TITLE'           => 'Gateway timeout (in seconds)',
  'MODULE_PAYMENT_NOVALNET_CURL_TIMEOUT_DESC'            => 'In case the order processing time exceeds the gateway timeout, the order will not be placed',
  'MODULE_PAYMENT_NOVALNET_REFERRER_ID_TITLE'            => 'Referrer ID',
  'MODULE_PAYMENT_NOVALNET_REFERRER_ID_DESC'             => 'Enter the referrer ID of the person/company who recommended you Novalnet',
  'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_TITLE'  => '<h3>Order status management for on-hold transaction(-s)</h3>Confirmation order status ',
  'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_DESC'   => '',
  'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_TITLE' => 'Cancellation order status',
  'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_DESC'  => '',
  'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_TITLE'          => '<h3>Dynamic subscription management</h3>Tariff period',
  'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_DESC'           => 'The period of the first subscription cycle (E.g: 1d/1m/1y)',
  'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_TITLE'  => 'Amount for the subsequent subscription cycle (in cents)',
  'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_DESC'   => 'The amount for the subsequent subscription cycle',
  'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_TITLE'         => 'Period for subsequent subscription cycle',
  'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_DESC'          => 'The period of the subsequent subscription cycle (E.g: 1d/1m/1y)',
  'MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_TITLE'    => 'Cancellation status of subscription',
  'MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_DESC'     => '',
  'MODULE_PAYMENT_NOVALNET_DEBUG_MODE_TITLE'             => '<h3>Merchant script management</h3>Enable Debug Mode',
  'MODULE_PAYMENT_NOVALNET_DEBUG_MODE_DESC'              => 'Set the debug mode to execute the merchant script in test mode',
  'MODULE_PAYMENT_NOVALNET_TEST_MODE_CALLBACK_TITLE'     => 'Enable Test Mode',
  'MODULE_PAYMENT_NOVALNET_TEST_MODE_CALLBACK_DESC'      => '',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_TITLE'     => 'Enable E-mail notification for callback',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_DESC'      => '',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_TITLE'       => 'E-mail address (To)',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_DESC'        => 'E-Mail address of the recipient',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_TITLE'      => 'Email address (Bcc)',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_DESC'       => 'E-Mail address of the recipient for BCC',  
  'MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY_TITLE' 			 => '<h2>Logos display management</h2>You can activate or deactivate the logos display for the checkout page<br><br> Display Novalnet logo',
  'MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY_DESC' 			 => 'The Novalnet logo will be displayed on the checkout page',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_TITLE' 	 => 'Display payment method logo',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_DESC' 	 => 'The payment method logo will be displayed on the checkout page',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_URL_TITLE' 			 => 'Notification URL',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_URL_DESC' 			 => 'The notification URL is used to keep your database/system actual and synchronizes with the Novalnet transaction status',


  'MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE'           => 'Enable test mode',
  'MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC'            => 'The payment will be processed in the test mode therefore amount for this transaction will not be charged',
  'MODULE_PAYMENT_NOVALNET_ENABLE_MODULE_TITLE'       => 'Enable payment method',
  'MODULE_PAYMENT_NOVALNET_ENABLE_MODULE_DESC'        => '',
  'MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE' => 'Minimum value of goods (in cents)',
  'MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC'  => 'Enter the minimum value of goods from which the payment method is displayed to the customer during checkout',
  'MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_INFO_TITLE'    => 'Notification for the buyer',
  'MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_INFO_DESC'     => 'The entered text will be displayed on the checkout page',
  'MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE'          => 'Define a sorting order ',
  'MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC'           => 'This payment method will be sorted among others (in the ascending order) as per the given sort number',
  'MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE'        => 'Order completion status',
  'MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC'         => '',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE'        => 'Payment zone',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC'         => 'This payment method will be displayed for the mentioned zone(-s)',
  'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_TITLE'    => 'Transaction reference 1',
  'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_DESC'     => 'This reference will appear in your bank account statement',
  'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_TITLE'    => 'Transaction reference 2',
  'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_DESC'     => 'This reference will appear in your bank account statement',

  'MODULE_PAYMENT_NOVALNET_CC_DESC'                       => 'The amount will be debited from your credit card once the order is submitted',
  'MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE_TITLE'   => 'Enable fraud prevention',
  'MODULE_PAYMENT_NOVALNET_CC_ENABLE_FRAUDMODULE_DESC'    => 'To authenticate the buyer for a transaction, the PIN or E-Mail will be automatically generated and sent to the buyer. This service is only available for customers from DE, AT, CH',
  'MODULE_PAYMENT_NOVALNET_CC_CALLBACK_LIMIT_TITLE'       => 'Minimum value of goods for the fraud module (in cents)',
  'MODULE_PAYMENT_NOVALNET_CC_CALLBACK_LIMIT_DESC'        => 'Enter the minimum value of goods from which the fraud module should be activated',
  'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_TITLE'            => 'Enable 3D secure',
  'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_DESC'             => 'The 3D-Secure will be activated for credit cards. The issuing bank prompts the buyer for a password what, in turn, help to prevent a fraudulent payment. It can be used by the issuing bank as evidence that the buyer is indeed their card holder. This is intended to help decrease a risk of charge-back.',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT_TITLE' => 'Limit for expiry year',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_VALIDYEAR_LIMIT_DESC'  => 'Enter the number for the maximum limit of credit card expiry year. In case if the field is empty, limit of 25 years from the current year will be set by default',
  'MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_TITLE'          => 'Display AMEX logo',
  'MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_DESC'           => 'Display AMEX logo in checkout page',
   'MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_TITLE' 	  => 'Display Maestro logo',
  'MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_DESC' 		  => 'Display Maestro logo in checkout page',  
  'MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_TITLE' 	  => 'Display CartaSi logo',
  'MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_DESC' 		  => 'Display CartaSi logo in checkout page',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE'             => 'Type of card',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER'           => 'Card holder name',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO'               => 'Card number',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE'       => 'Expiry date',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC'              => 'CVC/CVV/CID',

  'MODULE_PAYMENT_NOVALNET_SEPA_DESC'                        => 'Your account will be debited upon the order submission',
  'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE_TITLE'    => 'Enable fraud prevention',
  'MODULE_PAYMENT_NOVALNET_SEPA_ENABLE_FRAUDMODULE_DESC'     => 'To authenticate the buyer for a transaction, the PIN or E-Mail will be automatically generated and sent to the buyer. This service is only available for customers from DE, AT, CH',
  'MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT_TITLE'        => 'Minimum value of goods for the fraud module (in cents)',
  'MODULE_PAYMENT_NOVALNET_SEPA_CALLBACK_LIMIT_DESC'         => 'Enter the minimum value of goods from which the fraud module should be activated',
  'MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_TITLE'              => 'SEPA payment duration (in days)',
  'MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_DESC'               => 'Enter the number of days after which the payment should be processed (must be greater than 6 days)',
  'MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER_TITLE' => 'Enable auto-fill for payment data',
  'MODULE_PAYMENT_NOVALNET_REFILL_BY_SUCCESSFUL_ORDER_DESC'  => 'For the registered users SEPA direct debit details will be filled automatically in the payment form',
  'MODULE_PAYMENT_NOVALNET_BANK_COUNTRY'                     => 'Bank Country',
  'MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER'                   => 'Account Holder',
  'MODULE_PAYMENT_NOVALNET_ACCOUNT_NUMBER'                   => 'Account Number',
  'MODULE_PAYMENT_NOVALNET_BANK_CODE'                        => 'Bank Code',
  'MODULE_PAYMENT_NOVALNET_BIC'                              => 'BIC',
  'MODULE_PAYMENT_NOVALNET_SEPA_FORM_MANDATE_CONFIRM_TEXT'   => '<strong>I hereby grant the SEPA direct debit mandate and confirm that the given IBAN and BIC are correct',
  'MODULE_PAYMENT_NOVALNET_SEPA_MANDATE_CONFIRM_ERROR'       => 'Please accept the SEPA direct debit mandate',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_CONFIRM_TITLE'       => 'Direct Debit SEPA mandate Confirmation ',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_PAYEE'               => 'Creditor',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_ENDUSER_FULLNAME'    => 'Name of the payer',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_COMPANY'             => 'Company name',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_ADDRESS'             => 'Street name and number',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_ZIPCODE_AND_CITY'    => 'Postal code and City',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_COUNTRY'             => 'Country',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_EMAIL'               => 'E-Mail',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_IBAN'                => 'IBAN',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_SWIFT_BIC'           => 'BIC',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_CONFIRM_BTN'         => 'Confirm',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_CANCEL_BTN'          => 'Cancel',
  'MODULE_PAYMENT_NOVALNET_CONFIRM_BTN'                      => 'Confirm',
  'MODULE_PAYMENT_NOVALNET_UPDATE_BTN'                       => 'Update',
  'MODULE_PAYMENT_NOVALNET_BACK_BTN'                         => 'Back',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_CREDITOR_IDENTIFICATION_NUMBER'    => 'Creditor identifier',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_MANDATE_REFERENCE'                 => 'Mandate reference',
  'MODULE_PAYMENT_NOVALNET_SEPA_OVERLAY_MANDATE_OVERLAY_CONFIRM_PARAGRAPH' => 'By granting this mandate form, I authorize (A) the creditor to send instructions to my bank to debit my account and (B) my bank to debit my account in accordance with the instructions from the creditor for this and future payments.<br/><br/>As part of my rights, I am entitled to a refund from my bank under the terms and conditions of my agreement with my bank. A refund must be claimed within eight weeks from the date on which my account was debited.<br /><br />',
  'MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_ERROR'    => 'SEPA Due date is not valid',
  'MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_ERROR' => 'Please enter valid due date',
  'MODULE_PAYMENT_NOVALNET_REFERENCE_ERROR'		   => 'Please select atleast one payment reference.',
  'MODULE_PAYMENT_NOVALNET_INVALID_DUE_DATE'	   => 'Invalid due date',
  
  'MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE_TITLE' 	   			 => 'Form mode',
  'MODULE_PAYMENT_NOVALNET_CC_FORM_TYPE_DESC' 				 => 'Based on this selection credit card form will be displayed',
  'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE' 				 => 'Shopping type',
  'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC' 				 => 'Select shopping type',
  'MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT_TITLE' => 'Force CVC confirmation on one click shopping',
  'MODULE_PAYMENT_NOVALNET_CC_CVC_ON_ONE_CLICK_ACCEPT_DESC'  => 'Force the customer to enter the CVC, while using the option one click shopping',
  'MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT' 					 => 'Enter new card details',
  'MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT' 				 => 'Given card details',
  
  'MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE_TITLE' => 'Form mode',
  'MODULE_PAYMENT_NOVALNET_SEPA_FORM_TYPE_DESC'  => 'Based on this selection Direct Debit SEPA form will be displayed',  
  'MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE_TITLE' => 'Shopping type',
  'MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE_DESC'  => 'Select shopping type',
  'MODULE_PAYMENT_NOVALNET_SEPA_NEW_ACCOUNT' 	 => 'Enter new account details',
  'MODULE_PAYMENT_NOVALNET_SEPA_GIVEN_ACCOUNT' 	 => 'Given account details',
  
  'MODULE_PAYMENT_NOVALNET_FORM_LOCAL'    => 'Local form',
  'MODULE_PAYMENT_NOVALNET_FORM_IFRAME'   => 'Iframe form',
  'MODULE_PAYMENT_NOVALNET_FORM_REDIRECT' => 'Redirection',
  'MODULE_PAYMENT_NOVALNET_ONE_CLICK' 	  => 'One click shopping',
  'MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT'   => 'Zero amount booking',

  'MODULE_PAYMENT_NOVALNET_INVOICE_DESC'                  	 => "Once you've submitted the order, you will receive an e-mail with account details to make payment",
  'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE_TITLE' => 'Fraud prevention through PIN by Callback/SMS/E-Mail',
  'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE_DESC'  => 'When activated by PIN Callback / SMS / E-Mail the customer to enter their phone / mobile number / E-Mail requested. By phone or SMS, the customer receives a PIN from Novalnet AG, which must enter before ordering. If the PIN is valid, the payment process has been completed successfully, otherwise the customer will be prompted again to enter the PIN. This service is only available for customers from specified countries.',
  'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT_TITLE'     => 'Minimum value of goods for the fraud module (in cents)',
  'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT_DESC'      => 'Enter the minimum value of goods from which the fraud module should be activated',
  'MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_TITLE'         	 => 'Payment due date (in days)',
  'MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE_DESC'        	 => 'Enter the number of days to transfer the payment amount to Novalnet (must be greater than 7 days). In case if the field is empty, 14 days will be set as due date by default',
  'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACKSCRIPT_ORDER_STATUS_TITLE' => 'Callback order status',
  'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACKSCRIPT_ORDER_STATUS_DESC'  => '',
  'MODULE_PAYMENT_NOVALNET_INVOICE_COMMENTS_PARAGRAPH' => 'Please transfer the amount to the following information to our payment service Novalnet',
  'MODULE_PAYMENT_NOVALNET_DUE_DATE'                   => 'Due date',
  'MODULE_PAYMENT_NOVALNET_IBAN'                       => 'IBAN',
  'MODULE_PAYMENT_NOVALNET_SWIFT_BIC'                  => 'BIC',
  'MODULE_PAYMENT_NOVALNET_BANK'                       => 'Bank',
  'MODULE_PAYMENT_NOVALNET_AMOUNT'                     => 'Amount',
  'MODULE_PAYMENT_NOVALNET_INVPRE_REF'         		   => 'Reference : ',
  'MODULE_PAYMENT_NOVALNET_ORDER_NUMBER'       		   => ' Order number',
  'MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF'  		   => 'Reference @i : ',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT' 	   => 'Please use the following payment reference for your money transfer, as only through this way your payment is matched and assigned to the order:',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT' 		   => 'Please use any one of the following references as the payment reference, as only through this way your payment is matched and assigned to the order:',

  'MODULE_PAYMENT_NOVALNET_PREPAYMENT_DESC'                              => "Once you've submitted the order, you will receive an e-mail with account details to make payment",
  'MODULE_PAYMENT_NOVALNET_PREPAYMENT_CALLBACKSCRIPT_ORDER_STATUS_TITLE' => 'Callback order status',
  'MODULE_PAYMENT_NOVALNET_PREPAYMENT_CALLBACKSCRIPT_ORDER_STATUS_DESC'  => '',

  'MODULE_PAYMENT_NOVALNET_REDIRECT_DESC'                  => 'After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment<br />Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop',
  'MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR'     => 'While redirecting some data has been changed. The hash check failed',
  'MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_PENDING'         => '<br/>PayPal : Payment Pending',
  'MODULE_PAYMENT_NOVALNET_PAYPAL_PAYPENDING_ORDER_STATUS_TITLE' => 'Order status for the pending payment',
  'MODULE_PAYMENT_NOVALNET_PAYPAL_PAYPENDING_ORDER_STATUS_DESC'  => '',

  'MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG'                    => '<span style="color:red;">The payment will be processed in the test mode therefore amount for this transaction will not be charged</span>',
  'MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE'               => 'Test order',
  'MODULE_PAYMENT_NOVALNET_TRANSACTION_ID'                   => 'Novalnet Transaction ID :',
  'MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR'                => 'Unfortunately, this order could not be processed. Please, place a new order',
  'MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR' => 'Please fill in all the mandatory fields',
  'MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR'  => 'Your account details are invalid',
  'MODULE_PAYMENT_NOVALNET_VERSION_ERROR' 					 => 'Kindly reinstall Novalnet payment modules',

  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_CALLBACK_INPUT_TITLE'    => 'Telephone number',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_INPUT_TITLE'         => 'Mobile number',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_EMAIL_INPUT_TITLE'       => 'E-mail address',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR'         => 'Please enter your telephone number',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_MOBILE_ERROR'            => 'Please enter your mobile number',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_EMAIL_ERROR'             => 'Your E-mail address is invalid',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TEL_PIN_INFO'            => 'You will shortly receive a transaction PIN through phone call to complete the payment',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO'            => 'You will shortly receive an SMS containing your transaction PIN to complete the payment',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC'        => 'Transaction PIN',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN'                 => '&nbsp; Forgot your PIN?',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID'            => 'The PIN you entered is incorrect',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_AMOUNT_CHANGE_ERROR'     => 'The order amount has been changed, please proceed with the new order',
  'MODULE_PAYMENT_NOVALNET_FRAUDMODULE_MAIL_INFO'               => 'You will shortly receive an information e-mail, please send the empty reply incl. the original e-mail',
  'MODULE_PAYMENT_NOVALNET_OPTION_NONE' 						=> 'None',
  'MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK'                => 'PIN by callback',
  'MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS'                     => 'PIN by SMS',
  'MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONEMAIL'                   => 'Reply via E-mail',

  'MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS'                    => 'Your credit card details are invalid',
  'MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_DETAILS'               => 'Your account details are invalid',
  'MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_MESSAGE'               => 'Manage transaction',
  'MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_MESSAGE_HEADING'       => 'Manage transaction process',
  'MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE' 	=> 'The transaction has been confirmed on %s, %s',
  'MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE' 			=> 'The transaction has been canceled on %s, %s',
  'MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_FAILED_MESSAGE'        => 'Transaction confirmation failed : ',
  'MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TEXT'                   => 'Update transaction amount',
  'MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE' 			    => 'The transaction amount ( %s )  has been updated successfully on %s , %s',
  'MODULE_PAYMENT_NOVALNET_PARTIAL_REFUND_MESSAGE'              => 'Refund Process',
  'MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_MESSAGE'          => 'Subscription has been canceled due to : ',
  'MODULE_PAYMENT_NOVALNET_MANDATE_CONFIRM_SUCCESS_MESSAGE'     => 'Mandate confirmed successfully',
  'MODULE_PAYMENT_NOVALNET_CP_PARTIAL_REFUND_AMT_MESSAGE'       => 'Please enter the refund amount',
  'MODULE_PAYMENT_NOVALNET_CP_REFUND_PAYMENTTYPE_MESSAGE'       => 'Select the refund option',
  'MODULE_PAYMENT_NOVALNET_CP_REFUND_METHOD_MESSAGE'            => 'Refund Method',
  'MODULE_PAYMENT_NOVALNET_CP_REFUND_AMOUNT_EX'                 => '(in cents)',
  'MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_PROBLEM'               => 'Please enable the Javascript in your browser to proceed further with the payment',
  'MODULE_PAYMENT_NOVALNET_ONHOLD_TRANS_STATUS_UPDATE_MESSAGE'  => 'OnHold cancellation / VOID Transaction status',
  'MODULE_PAYMENT_NOVALNET_SELECT_STATUS_MESSAGE'               => 'Please select status',
  'MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION'                => 'SELECT',
  'MODULE_PAYMENT_NOVALNET_SELECT_REASON_MESSAGE'               => 'Please select reason',
  'MODULE_PAYMENT_NOVALNET_CONFIRM_MESSAGE'                     => 'Confirm',
  'MODULE_PAYMENT_NOVALNET_CANCEL_MESSAGE'                      => 'Cancel',
  'MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TEXT'                    => 'Cancel Subscription',
  'MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_MESSAGE'                 => 'Cancel Subscription Process',
  'MODULE_PAYMENT_NOVALNET_PLEASE_SELECT_TER_REASON_MESSAGE'    => 'Please select the reason of subscription cancellation',
  'MODULE_PAYMENT_NOVALNET_TRANS_REFUND_TEXT'                   => 'Refund',
  'MODULE_PAYMENT_NOVALNET_TRANS_REFUND_MESSAGE'                => 'Refund process',
  'MODULE_PAYMENT_NOVALNET_TRANS_REFUND_REFERENCE'              => 'Refund reference',
  'MODULE_PAYMENT_NOVALNET_YEAR_TEXT_MESSAGE'                   => 'Year',
  'MODULE_PAYMENT_NOVALNET_MONTH_TEXT_MESSAGE'                  => 'Month',
  'MODULE_PAYMENT_NOVALNET_DAY_TEXT_MESSAGE'                    => 'Day',
  'MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_MESSAGE'              => 'Transaction due date',
  'MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TEXT'                  => 'Update',
  'MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_MESSAGE'               => 'Amount update',
  'MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_MESSAGE'      => 'Change the amount / due date',
  'MODULE_PAYMENT_NOVALNET_PLEASE_SPECIFY_AMOUNT_ERROR_MESSAGE' => 'The amount is invalid',
  'MODULE_PAYMENT_NOVALNET_SELECT_STATUS_ERROR_MESSAGE'         => 'Please select status',
  'MODULE_PAYMENT_NOVALNET_SUBS_OFFER_TOO_EXPENSIVE'            => 'Product is costly',
  'MODULE_PAYMENT_NOVALNET_SUBS_FRAUD'                          => 'Cheating',
  'MODULE_PAYMENT_NOVALNET_SUBS_PARTNER_HAS_INTERVENED'         => 'Partner interfered',
  'MODULE_PAYMENT_NOVALNET_SUBS_FINANCIAL_DIFFICULTIES'         => 'Financial problem',
  'MODULE_PAYMENT_NOVALNET_SUBS_CONTENT_DIDNOT_MEET_EXPECT'     => 'Content does not match my likes',
  'MODULE_PAYMENT_NOVALNET_SUBS_CONTENT_NOT_SUFFICIENT'         => 'Content is not enough',
  'MODULE_PAYMENT_NOVALNET_SUBS_INTEREST_ONLY_TEST_ACCESS'      => 'Interested only for a trial',
  'MODULE_PAYMENT_NOVALNET_SUBS_PAGE_TOO_SLOW'                  => 'Page is very slow',
  'MODULE_PAYMENT_NOVALNET_SUBS_SATISFIED_CUSTOMER'             => 'Satisfied customer',
  'MODULE_PAYMENT_NOVALNET_SUBS_ACCESS_PROBLEMS'                => 'Logging in problems',
  'MODULE_PAYMENT_NOVALNET_SUBS_OTHER'                          => 'Other',
  'MODULE_PAYMENT_NOVALNET_UPDATE_MESSAGE'                      => 'Update',
  'MODULE_PAYMENT_NOVALNET_REFUND_ZERO_AMOUNT_ERROR_MESSAGE'    => 'The amount is invalid',
  'MODULE_PAYMENT_NOVALNET_ACCOUNT_OR_IBAN'                     => 'IBAN&nbsp;or&nbsp;Account&nbsp;number',
  'MODULE_PAYMENT_NOVALNET_BANKCODE_OR_BIC'                     => 'BIC or Bank code',
  'MODULE_PAYMENT_NOVALNET_MAP_PAGE_HEADER'                     => 'Please login here with Novalnet merchant credentials. Please contact us on support@novalnet.de for activating payment methods!',
  'MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE'               => 'The date should be in future',
  'MODULE_PAYMENT_NOVALNET_ORDER_UPDATE'                        => 'Successful',
  'MODULE_PAYMENT_NOVALNET_ORDER_AMT_DUEDATE_UPDATE_TEXT'       => 'Are you sure you want to change the order amount or due date?',
  'MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT'               => 'Are you sure you want to change the order amount?',  
  'MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG' 				=> 'The refund has been executed for the TID: %s with the amount of %s',
  'MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG' 				=> '. Your new TID for the refund amount: %s',
  'MODULE_PAYMENT_NOVALNET_STATUS_SUCCESSFULL_TEXT'             => 'Successfull',
  'MODULE_PAYMENT_NOVALNET_BOOK_TITLE' 							=> 'Book amount',
  'MODULE_PAYMENT_NOVALNET_BOOK_BUTTON' 						=> 'Book',
  'MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE' 						=> 'Transaction booking amount',
  'MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE' 				=> 'Your order has been booked with the amount of %s. Your new TID for the booked amount:%s',

  'MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS' => 'Novalnet Callback Script executed successfully for the TID: %s with amount: %s on %s & %s. Please refer PAID transaction in our Novalnet Merchant Administration with the TID: %s',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS' 	 => 'Novalnet callback received. Chargeback executed successfully for the TID: %s with amount: %s on %s & %s. The subsequent TID: %s',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_STOP_COMMENTS' 	 => 'Novalnet callback script received. Subscription has been stopped for the TID: %s on %s & %s.',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_RECURRING_COMMENTS'		 => 'Reference order number : %s.',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS'  => 'Next charging date: ',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_SUBS_REASON_TEXT'		 => '. Reason for Cancellation: ',
  'MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS'		 => 'Novalnet Callback Script executed successfully for the TID: %s with amount %s on %s & %s.',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_1'				 => 'Payment Reference 1 (Novalnet Invoice Reference)',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_2'				 => 'Payment Reference 2 (TID)',
  'MODULE_PAYMENT_NOVALNET_PAYMENT_REFERENCE_3'				 => 'Payment Reference 3 (Order No)',

  'MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE'           => 'Novalnet Credit Card',
  'MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE'         => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"/></a>':'').' Credit Card '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Credit Card" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/visa_mastercard.png'.'" alt="Credit Card " height="25" /></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'True' && ((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True'))?'<a href="http://www.novalnet.com" title="Credit Card" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/amex.png'.'" alt="Credit Card " height="25"/></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'True' && ((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True'))?'<a href="http://www.novalnet.com" title="Credit Card" target="_blank"/><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/maestro.png'.'" alt="Credit Card " height="25"/></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT == 'True' && ((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True'))?'<a href="http://www.novalnet.com" title="Credit Card" target="_blank"/><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/cartasi.png'.'" alt="Credit Card " height="25"/></a>':''),
  'MODULE_PAYMENT_NOVALNET_SEPA_TEXT_TITLE'         => 'Novalnet Direct Debit SEPA',
  'MODULE_PAYMENT_NOVALNET_SEPA_PUBLIC_TITLE'       => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"/></a>':'').' Direct Debit SEPA '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Direct Debit SEPA" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/sepa.png" alt="Direct Debit SEPA" height="25" /></a>':''),
  'MODULE_PAYMENT_NOVALNET_SOFORTBANK_TEXT_TITLE'   => 'Novalnet Instant Bank Transfer',
  'MODULE_PAYMENT_NOVALNET_SOFORTBANK_PUBLIC_TITLE' => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"/></a>':'').' Instant Bank Transfer '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Instant Bank Transfer" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/sofortbank.png" alt="Instant Bank Transfer" height="25" /></a>':''),
  'MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_TITLE'       => 'Novalnet PayPal',
  'MODULE_PAYMENT_NOVALNET_PAYPAL_PUBLIC_TITLE'     => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"/></a>':'').' PayPal '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="PayPal" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/paypal.png" alt="PayPal" height="25" /></a>':''),
  'MODULE_PAYMENT_NOVALNET_EPS_TEXT_TITLE'          => 'Novalnet EPS',
  'MODULE_PAYMENT_NOVALNET_EPS_PUBLIC_TITLE'        => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"/></a>':'').' EPS '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="EPS" target="_blank"/><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/eps.png" alt="EPS" height="25" /></a>':''),
  'MODULE_PAYMENT_NOVALNET_PREPAYMENT_TEXT_TITLE'   => 'Novalnet Prepayment',
  'MODULE_PAYMENT_NOVALNET_PREPAYMENT_PUBLIC_TITLE' => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"></a>':'').' Prepayment '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Prepayment" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/prepayment.png" alt="Prepayment" height="25" /></a>':''),
  'MODULE_PAYMENT_NOVALNET_INVOICE_TEXT_TITLE'      => 'Novalnet Invoice',
  'MODULE_PAYMENT_NOVALNET_INVOICE_PUBLIC_TITLE'    => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"/></a>':'').' Invoice '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Invoice" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/invoice.png" alt="Invoice" height="25" /></a>':''),
  'MODULE_PAYMENT_NOVALNET_IDEAL_TEXT_TITLE'        => 'Novalnet iDEAL',
  'MODULE_PAYMENT_NOVALNET_IDEAL_PUBLIC_TITLE'      => (((!defined('MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="Novalnet AG"  target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/logo.png'.'" alt="Novalnet AG" height="25"/></a>':'').' iDEAL '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')?'<a href="http://www.novalnet.com" title="iDEAL" target="_blank"><img src="'.DIR_WS_CATALOG.'includes/classes/novalnet/img/ideal.png" alt="iDEAL" height="25" /></a>':''),
);
?>