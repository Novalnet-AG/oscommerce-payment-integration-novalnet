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
 * Script : novalnet_cc.php
 *
 */

require_once(dirname(__FILE__) . '/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE', 'Credit Card ');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION', 'The amount will be debited from your credit card once the order is submitted');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_TEXT_DESCRIPTION', 'After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment');

define('MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE', ((defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? '<a href="https://www.novalnet.com/credit-card" title="Credit Card" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_visa.png', "Credit Card") . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_master.png', "Credit Card") . '</a>' : '') . ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT') || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'True' && (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')) ? '<a href="https://www.novalnet.com/credit-card" title="Credit Card" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_amex.png', "Credit Card") . '</a>' : '') . ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT') || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'True' && (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')) ? '<a href="https://www.novalnet.com/credit-card" title="Credit Card" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_maestro.png', "Credit Card") . '</a>' : '') . ((!defined('MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT') || MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT == 'True' && (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')) ? '<a href="https://www.novalnet.com/credit-card" title="Credit Card" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_cartasi.png', "Credit Card") . '</a>' : ''));
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_TITLE', MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_DESC', MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_TITLE', 'On-hold payment action');
define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_DESC', 'Enable authentication for onhold');
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_TITLE', MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_DESC', MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);


define('MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_TITLE', MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_DESC', MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_TITLE', MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_DESC', MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_TITLE', MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_DESC', MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_TITLE', MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_DESC', MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_TITLE', 'Enable 3D secure');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_DESC', 'The 3D-Secure will be activated for credit cards. The issuing bank prompts the buyer for a password what, in turn, help to prevent a fraudulent payment. It can be used by the issuing bank as evidence that the buyer is indeed their card holder. This is intended to help decrease a risk of charge-back.');

define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE_TITLE','Force 3D secure on predefined conditions');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE_DESC','If 3D secure is not enabled in the above field, then force 3D secure process as per the "Enforced 3D secure (as per predefined filters & settings)" module configuration at the Novalnet Merchant Administration portal. If the predefined filters & settings from Enforced 3D secure module are met, then the transaction will be processed as 3D secure transaction otherwise it will be processed as non 3D secure. Please note that the "Enforced 3D secure (as per predefined filters & settings)" module should be configured at Novalnet Merchant Administration portal prior to the activation here. For further information, please refer the description of this fraud module at "Fraud Modules" tab, below "Projects" menu, under the selected project in Novalnet Merchant Administration portal or contact Novalnet support team.');

define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_TITLE', 'Display AMEX logo ');
define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_DESC', 'Display AMEX logo in checkout page');

define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_TITLE', 'Display Maestro logo');
define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_DESC', 'Display Maestro logo in checkout page');

define('MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_TITLE', 'Display CartaSi logo');
define('MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_DESC', 'Display CartaSi logo in checkout page');

define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC);

define('MODULE_PAYMENT_NOVALNET_OPTION_NONE', MODULE_PAYMENT_OPTION_NONE);
define('MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK', MODULE_PAYMENT_NOVALNET_ONE_CLICK);
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT', MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT);

define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_TITLE', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_DESC', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT', 'Enter new card details');
define('MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT', 'Given card details');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE', 'Type of card');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER', 'Card holder name');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO', 'Card number');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE', 'Expiry date');
define('MODULE_PAYMENT_NOVALNET_CC_BLOCK_TITLE', '<b>Credit Card Configuration</b>');
define('MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS', 'Your credit card details are invalid');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_ERROR_MESSAGE', MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION_TITLE', '<h2>Form appearance</h2><h3>CSS settings for Credit Card iframe</h3> <span style="font-weight:normal;">Label</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT_TITLE', '<span style="font-weight:normal">Input</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS_TITLE', '<span style="font-weight:normal">CSS Text</span>');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_CC_TITLE', 'Minimum transaction limit for authorization (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT_DESC', 'In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.');
