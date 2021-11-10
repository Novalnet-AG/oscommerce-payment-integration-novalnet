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
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_TITLE', 'PayPal ');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_DESCRIPTION', utf8_encode('Nach der erfolgreichen &Uumlberpr&uuml;fung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen'));

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PUBLIC_TITLE', ((defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? '<a href="https://www.novalnet.de/mit-paypal-weltweit-sicher-verkaufen" title="PayPal" target="_blank"/>'.tep_image(DIR_WS_ICONS.'novalnet/novalnet_paypal.png',"PayPal" ).'</a>':''));

define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_TITLE',MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_DESC',MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_TITLE',MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_DESC',MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_TITLE',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_DESC',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_TITLE',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_CUSTOMER_INFO_DESC',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_TITLE',MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_DESC',MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_TITLE','Bestellstatus der ausstehenden Zahlung');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_DESC','');

define('MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_TITLE',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_DESC',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_TITLE',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_DESC',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_BLOCK_TITLE','<b>PayPal API Konfiguration</b>');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_REDIRECTION_ERROR_MESSAGE', MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_TITLE', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_DESC', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC.'<br><span id="paypal_message" style=color:red></span>');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_NONE', MODULE_PAYMENT_OPTION_NONE);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK', MODULE_PAYMENT_NOVALNET_ONE_CLICK);
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ZERO_AMOUNT', MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT);

define('MODULE_PAYMENT_NOVALNET_PAYPAL_NEW_ACCOUNT', 'Mit neuen PayPal-Kontodetails fortfahren');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_GIVEN_ACCOUNT','Angegebene PayPal-Kontodetails');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK_SHOPPING_DESCRIPTION','Sobald die Bestellung abgeschickt wurde, wird die Zahlung bei Novalnet als Referenztransaktion verarbeitet.');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOW_MESSAGE','Um diese Option zu verwenden, müssen Sie die Option Billing Agreement (Zahlungsvereinbarung) in Ihrem PayPal-Konto aktiviert haben. Kontaktieren Sie dazu bitte Ihren Kundenbetreuer bei PayPal.');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_TITLE','Durchführung einer ausgesetzten Zahlung
');

define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_DESC','Die Zahlung wird in der Warteschleife verarbeitet, wenn die Autorisierung angeklickt wird, und das Limit für die Warteschleife wird in der Textbox festgelegt

');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT_TITLE', 'Mindesttransaktionsbetrag für die Autorisierung (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_PAYPAL_LIMIT_DESC', 'Übersteigt der Bestellbetrag das genannte Limit, wird die Transaktion, bis zu ihrer Bestätigung durch Sie, auf on hold gesetzt. Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.');
