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
 * Script : novalnet_cc.php
 *
 */
require_once(dirname(__FILE__) . '/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE', 'Kreditkarte ');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION', 'Der Betrag wird von Ihrer Kreditkarte abgebucht, sobald die Bestellung abgeschickt wird');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_TEXT_DESCRIPTION', 'Nach der erfolgreichen &Uuml;berpr&uuml;fung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen');

define('MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE', ((defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? '<a href="https://www.novalnet.de" title="Kreditkarte" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_visa.png', "Kreditkarte") . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_master.png', "Kreditkarte") . '</a>' : '') . ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT') || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'True' && (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')) ? '<a href="https://www.novalnet.de" title="Kreditkarte" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_amex.png', "Kreditkarte") . '</a>' : '') . ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT') || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'True' && (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')) ? '<a href="https://www.novalnet.de" title="Kreditkarte" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_maestro.png', "Kreditkarte") . '</a>' : '') . ((!defined('MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT') || MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT == 'True' && (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True')) ? '<a href="https://www.novalnet.de" title="Kreditkarte" target="_blank"/>' . tep_image(DIR_WS_ICONS . 'novalnet/novalnet_cc_cartasi.png', "Kreditkarte") . '</a>' : ''));

define('MODULE_PAYMENT_NOVALNET_CC_STATUS_TITLE', MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_DESC', MODULE_PAYMENT_STATUS_DESC);

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

define('MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE1_TITLE', MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE1_DESC', MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE2_TITLE', MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_TRANS_REFERENCE2_DESC', MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_TITLE', '3D-Secure aktivieren');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_DESC', '3D-Secure wird f&uuml;r Kreditkarten aktiviert. Die kartenausgebende Bank fragt vom K&auml;ufer ein Passwort ab, welches helfen soll, betr&uuml;gerische Zahlungen zu verhindern. Dies kann von der kartenausgebenden Bank als Beweis verwendet werden, dass der K&auml;ufer tats&auml;chlich der Inhaber der Kreditkarte ist. Damit soll das Risiko von Chargebacks verringert werden.');

define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_TITLE', 'AMEX-Logo anzeigen');
define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_DESC', 'AMEX-Logo auf der Checkout-Seite anzeigen');

define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_TITLE', 'Maestro-Logo anzeigen');
define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_DESC', 'Maestro-Logo auf der Checkout-Seite anzeigen');

define('MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_TITLE', 'CartaSi-Logo anzeigen');
define('MODULE_PAYMENT_NOVALNET_CC_CARTASI_ACCEPT_DESC', 'CartaSi-Logo auf der Checkout-Seite anzeigen');

define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC);

define('MODULE_PAYMENT_NOVALNET_OPTION_NONE', MODULE_PAYMENT_OPTION_NONE);
define('MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK', MODULE_PAYMENT_NOVALNET_ONE_CLICK);
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT', MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT);

define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_TITLE', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_DESC', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT', 'Neue Kartendaten eingeben');
define('MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT', 'Eingegebene Kartendaten');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE', 'Kartentyp');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER', 'Name des Karteninhabers');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO', 'Kreditkartennummer');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE', 'Ablaufdatum');
define('MODULE_PAYMENT_NOVALNET_CC_BLOCK_TITLE', '<b>Kreditkarte Konfiguration</b>');
define('MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS', 'Ihre Kreditkartendaten sind ungültig');

define('MODULE_PAYMENT_NOVALNET_CC_HOLDER_LABEL_CSS_TITLE', '<h2>Darstellung des Formulars</h2><h3>CSS-Einstellungen für Felder mit Kreditkartendaten <br/></h3><b>Formularfelder</b><br/><br/>Name des Karteninhabers<br/><br/><span style="font-weight:normal;">Beschriftung</span>');
define('MODULE_PAYMENT_NOVALNET_CC_HOLDER_LABEL_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_HOLDER_TEXTBOX_CSS_TITLE', '<span style="font-weight:normal;">Eingabefeld</span>');
define('MODULE_PAYMENT_NOVALNET_CC_HOLDER_TEXTBOX_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_NUMBER_LABEL_CSS_TITLE', 'Kreditkartennummer<br/><br/><span style="font-weight:normal;">Beschriftung</span>');
define('MODULE_PAYMENT_NOVALNET_CC_NUMBER_LABEL_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_NUMBER_TEXTBOX_CSS_TITLE', '<span style="font-weight:normal;">Eingabefeld</span>');
define('MODULE_PAYMENT_NOVALNET_CC_NUMBER_TEXTBOX_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_EXP_DATE_LABEL_CSS_TITLE', 'Ablaufdatum<br/><br/><span style="font-weight:normal;">Beschriftung</span>');
define('MODULE_PAYMENT_NOVALNET_CC_EXP_DATE_LABEL_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_EXP_DATE_TEXTBOX_CSS_TITLE', '<span style="font-weight:normal;">Eingabefeld</span>');
define('MODULE_PAYMENT_NOVALNET_CC_EXP_DATE_TEXTBOX_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_CVC_LABEL_CSS_TITLE', 'CVC/CVV/CID<br/><br/><span style="font-weight:normal;">Beschriftung</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CVC_LABEL_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_CVC_TEXTBOX_CSS_TITLE', '<span style="font-weight:normal;">Eingabefeld</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CVC_TEXTBOX_CSS_DESC', '');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_ERROR_MESSAGE', MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION_TITLE', '<h3>CSS-Einstellungen für den iFrame mit Kreditkartendaten </h3> <span style="font-weight:normal">Beschriftung</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT_TITLE', '<span style="font-weight:normal">Eingabe</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS_TITLE', '<span style="font-weight:normal">Text für das CSS</span>');
define('MODULE_PAYMENT_NOVALNET_CC_HOLDER_TEXT_PLACEHOLDER', 'Name auf der Kreditkarte');
define('MODULE_PAYMENT_NOVALNET_CC_NUMBER_TEXT', 'Kreditkartennummer');
define('MODULE_PAYMENT_NOVALNET_CC_NUMBER_TEXT_PLACEHOLDER', 'XXXX XXXX XXXX XXXX');
define('MODULE_PAYMENT_NOVALNET_CC_EXPIRY_TEXT_PLACEHOLDER', 'MM / YYYY');
define('MODULE_PAYMENT_NOVALNET_CC_CVC_TEXT', 'CVC/CVV/CID');
define('MODULE_PAYMENT_NOVALNET_CC_CVC_TEXT_PLACEHOLDER', 'XXX');
define('MODULE_PAYMENT_NOVALNET_CC_HELP_TEXT', 'Was ist das?');
