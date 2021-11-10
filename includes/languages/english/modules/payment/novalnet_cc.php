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
 * Script : novalnet_cc.php
 *
 */
require_once(dirname(__FILE__) . '/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE', 'Credit/Debit Cards ');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION', 'Funds are withdrawn from the buyer\'s account using credit/debit card details');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESC', 'The amount will be debited from your credit/debit card');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_TEXT_DESCRIPTION', 'After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment');

define('MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE', (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_visa.png', "Credit/Debit Cards").' '.tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_mastercard.png', "Credit/Debit Cards") .' '.tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_amex.png', "Credit/Debit Cards").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_maestro.png', "Credit/Debit Cards").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_cartasi.png', "Credit/Debit Cards").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_unionpay.png', "Credit/Debit Cards").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_discover.png', "Credit/Debit Cards").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_diners.png', "Credit/Debit Cards").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_jcb.png', "Credit/Debit Cards").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_carte-bleue.png', "Credit/Debit Cards") : '');

define('MODULE_PAYMENT_NOVALNET_CC_STATUS_TITLE', MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_DESC', MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_TITLE', 'Payment Action');
define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_DESC', 'Choose whether or not the payment should be charged immediately. Capture completes the transaction by transferring the funds from buyer account to merchant account. Authorize verifies payment details and reserves funds to capture it later, giving time for the merchant to decide on the order.');
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_TITLE', MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_DESC', MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_INLINE_FORM_TITLE', 'Enable inline form');
define('MODULE_PAYMENT_NOVALNET_CC_INLINE_FORM_DESC', '');


define('MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_TITLE', MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_DESC', MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_TITLE', MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_DESC', MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_TITLE', MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_DESC', MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_TITLE', MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_DESC', MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D_TITLE', 'Enforce 3D secure payment outside EU');
define('MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D_DESC', 'By enabling this option, all payments from cards issued outside the EU will be authenticated via 3DS 2.0 SCA');

define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC);

define('MODULE_PAYMENT_NOVALNET_OPTION_NONE', MODULE_PAYMENT_OPTION_NONE);
define('MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK', MODULE_PAYMENT_NOVALNET_ONE_CLICK);
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT', MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT);

define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_TITLE', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_DESC', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT', 'Add new card details for later purchases');
define('MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT', 'Given card details');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE', 'Type of card');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER', 'Card holder name');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO', 'Card number');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE', 'Expiry date');
define('MODULE_PAYMENT_NOVALNET_CC_BLOCK_TITLE', '<b>Credit Card Configuration</b>');
define('MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS', 'Your credit card details are invalid');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_ERROR_MESSAGE', MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION_TITLE', '<h2>Custom CSS settings</h2><h3>CSS settings for iframe form</h3> <span style="font-weight:normal;">Label</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT_TITLE', '<span style="font-weight:normal">Input</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS_TITLE', '<span style="font-weight:normal">CSS Text</span>');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_CC_TITLE', 'Minimum transaction amount for authorization (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT_DESC', 'In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.');
