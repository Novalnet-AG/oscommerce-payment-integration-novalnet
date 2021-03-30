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
 * Script : novalnet_przelewy24.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_TEXT_TITLE','Przelewy24 ');
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_TEXT_DESCRIPTION','Poland based payment method which allows buyers pay using bank transfers or any other methods');
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_TEXT_DESC','You will be redirected to Przelewy24. Please don’t close or refresh the browser until the payment is completed');

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_PUBLIC_TITLE', ((defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ?  tep_image(DIR_WS_ICONS.'novalnet/novalnet_przelewy.png',"Przelewy24" )  : ''));

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_STATUS_TITLE',MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_STATUS_DESC',MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_TEST_MODE_TITLE',MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_TEST_MODE_DESC',MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_VISIBILITY_BY_AMOUNT_TITLE',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_VISIBILITY_BY_AMOUNT_DESC',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_CUSTOMER_INFO_TITLE',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_CUSTOMER_INFO_DESC',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_SORT_ORDER_TITLE',MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_SORT_ORDER_DESC',MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_ORDER_STATUS_TITLE',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_ORDER_STATUS_DESC',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_PENDING_ORDER_STATUS_TITLE', 'Payment pending order status');
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_PENDING_ORDER_STATUS_DESC', 'Status to be used for pending transactions.');

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_PAYMENT_ZONE_TITLE',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_PAYMENT_ZONE_DESC',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC);

define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_BLOCK_TITLE','<b>Przelewy24 Configuration</b>');
define('MODULE_PAYMENT_NOVALNET_PRZELEWY24_REDIRECTION_ERROR_MESSAGE', MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);
