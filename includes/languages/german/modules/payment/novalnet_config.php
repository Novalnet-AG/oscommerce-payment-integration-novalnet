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
 * Script : novalnet_config.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_TITLE', 'Novalnet Haupteinstellungen (V_11.1.1)');
define('MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_DESCRIPTION', (strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') !== false) ? '<span style="font-weight: bold; color:#878787;"> Um zus&auml;tzliche Einstellungen vorzunehmen, loggen Sie sich in das <a href="'.DIR_WS_CATALOG.'admin/novalnet.php?process=map" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet-Administrationsportal</a> ein. <br/>Um sich in das Portal einzuloggen, ben&ouml;tigen Sie einen Account bei Novalnet. Falls Sie diesen noch nicht haben, kontaktieren Sie bitte <a style="font-weight: bold; color:#0080c9" href="mailto:sales@novalnet.de">sales@novalnet.de</a> (Tel: +49 (089) 923068320).</span><br/><br/><span style="font-weight: bold; color:#878787;">Um die Zahlungsart PayPal zu verwenden, geben Sie bitte Ihre PayPal-API-Daten in das <a href="'.DIR_WS_CATALOG.'admin/novalnet.php?process=map" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet-H&auml;ndleradministrationsportal</a> ein.</span>' : '<span style="font-weight: bold; color:#878787;"> Um zus&auml;tzliche Einstellungen vorzunehmen, loggen Sie sich in das <a href="https://admin.novalnet.de" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet-Administrationsportal</a> ein. <br/>Um sich in das Portal einzuloggen, ben&ouml;tigen Sie einen Account bei Novalnet. Falls Sie diesen noch nicht haben, kontaktieren Sie bitte <a style="font-weight: bold; color:#0080c9" href="mailto:sales@novalnet.de">sales@novalnet.de</a> (Tel: +49 (089) 923068320).</span><br/><br/><span style="font-weight: bold; color:#878787;">Um die Zahlungsart PayPal zu verwenden, geben Sie bitte Ihre PayPal-API-Daten in das <a href="https://admin.novalnet.de" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet-H&auml;ndleradministrationsportal</a> ein.</span>');

define('MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED_TITLE','');
define('MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED_DESC','');

define('MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY_TITLE','Aktivierungsschl&uuml;ssel des Produkts');
define('MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY_DESC','Novalnet-Aktivierungsschl&uuml;ssel f&uuml;r das Produkt eingeben');

define('MODULE_PAYMENT_NOVALNET_VENDOR_ID_TITLE','H&auml;ndler-ID');
define('MODULE_PAYMENT_NOVALNET_VENDOR_ID_DESC','');

define('MODULE_PAYMENT_NOVALNET_AUTH_CODE_TITLE','Authentifizierungscode');
define('MODULE_PAYMENT_NOVALNET_AUTH_CODE_DESC','');

define('MODULE_PAYMENT_NOVALNET_PRODUCT_ID_TITLE','Projekt-ID');
define('MODULE_PAYMENT_NOVALNET_PRODUCT_ID_DESC','');

define('MODULE_PAYMENT_NOVALNET_TARIFF_ID_TITLE','Tarif-ID');
define('MODULE_PAYMENT_NOVALNET_TARIFF_ID_DESC','Novalnet-Tarif-ID ausw&auml;hlen');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY_TITLE','Zahlungs-Zugriffsschl&uuml;ssel');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY_DESC','');

define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_TITLE','Limit f&uuml;r onhold-Buchungen setzen (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_DESC','Falls der Bestellbetrag das angegebene Limit &uuml;bersteigt, wird die Transaktion ausgesetzt, bis Sie diese selbst best&auml;tigen');

define('MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_TITLE','Default-Zahlungsart aktivieren');
define('MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_DESC','F&uuml;r registrierte Benutzer wird die letzte ausgew&auml;hlte Zahlungsart als Standardeinstellung beim Checkout ausgew&auml;hlt');

define('MODULE_PAYMENT_NOVALNET_PROXY_TITLE','Proxy-Server');
define('MODULE_PAYMENT_NOVALNET_PROXY_DESC','Geben Sie die IP-Adresse Ihres Proxyservers zusammen mit der Nummer des Ports ein und zwar in folgendem Format: IP-Adresse : Nummer des Ports (falls notwendig)');

define('MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT_TITLE','Zeitlimit der Schnittstelle (in Sekunden)');
define('MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT_DESC','Falls die Verarbeitungszeit der Bestellung das Zeitlimit der Schnittstelle &uuml;berschreitet, wird die Bestellung nicht ausgef&uuml;hrt');

define('MODULE_PAYMENT_NOVALNET_REFERRER_ID_TITLE','Partner-ID');
define('MODULE_PAYMENT_NOVALNET_REFERRER_ID_DESC','Geben Sie die Partner-ID der Person / des Unternehmens ein, welche / welches Ihnen Novalnet empfohlen hat');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_TITLE','<h2>Steuerung der angezeigten Logos </h2>Sie k&ouml;nnen die Anzeige der Logos auf der Checkout-Seite aktivieren oder deaktivieren<br><br> Logo der Zahlungsart anzeigen');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_DESC','Das Logo der Zahlungsart wird auf der Checkout-Seite angezeigt');

define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_TITLE','<h2>Verwaltung des Bestellstatus f&uuml;r ausgesetzte Zahlungen</h2>Bestellstatus f&uuml;r Best&auml;tigung');
define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_DESC','');

define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_TITLE','Bestellstatus f&uuml;r Stornierung');
define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_DESC','');

define('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_TITLE','<h2>Verwaltung dynamischer Abonnements</h2>Zeitraum des Tarifs');
define('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_DESC','Zeitraum des ersten Abonnementzyklus (z.B. 1d/1m/1y)');

define('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_TITLE','Betrag f&uuml;r den folgenden Abonnementzyklus (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_DESC','Betrag f&uuml;r den folgenden Abonnementzyklus');

define('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_TITLE','Zeitraum f&uuml;r den folgenden Abonnementzyklus');
define('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_DESC','Zeitraum des folgenden Abonnementzyklus (z.B. 1d/1m/1y)');

define('MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS_TITLE','Stornierungsstatus des Abonnements');
define('MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS_DESC','');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_DEBUG_MODE_TITLE','<h2>Verwaltung des H&auml;ndlerskripts</h2>Debugmodus aktivieren');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_DEBUG_MODE_DESC','Setzen Sie den Debugmodus, um das H&auml;ndlerskript im Debugmodus aufzurufen');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE_TITLE','Testmodus aktivieren');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE_DESC','');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_TITLE','Email-Benachrichtigung f&uuml;r Callback aktivieren');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_DESC','');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_TITLE','Emailadresse (An)');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_DESC','Emailadresse des Empf&auml;ngers');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_TITLE','Emailadresse (Bcc)');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_DESC','Emailadresse des Empf&auml;ngers f&uuml;r Bcc');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL_TITLE','URL f&uuml;r Benachrichtigungen');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL_DESC','Der URL f&uuml;r Benachrichtigungen dient dazu, Ihre Datenbank / Ihr System auf einem aktuellen Stand zu halten und den Novalnet-Transaktionsstatus abzugleichen.');

define('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE','<b>Novalnet Haupteinstellungen (V_11.1.1)</b>');

define('MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION_TITLE', 'E-Mail-Benachrichtigung für Testbuchungen aktivieren');
define('MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION_DESC', 'Sie erhalten ab jetzt E-Mail-Benachrichtigungen zu jeder Testbestellung im Webshop');
