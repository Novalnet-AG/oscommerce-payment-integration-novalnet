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
 * Script : novalnet_config.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_TITLE', 'Novalnet Haupteinstellungen (V_11.1.6)');
define('MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_DESCRIPTION', '<span style="font-weight: bold; color:#878787;">Bevor Sie beginnen, lesen Sie bitte die Installationsanleitung und melden Sie sich mit Ihrem Händlerkonto im <a href="https://admin.novalnet.de" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet Admin-Portal</a> an. Um ein Händlerkonto zu erhalten, senden Sie bitte eine E-Mail an sales@novalnet.de oder rufen Sie uns unter +49 (089) 923068320 an.</span>');

define('MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED_TITLE','');
define('MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED_DESC','');

define('MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY_TITLE',' Produktaktivierungsschlüssel');
define('MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY_DESC','Bitte geben Sie den Novalnet-Produktaktivierungsschlüssel an. Dieser ist für die Authentifizierung und Zahlungsabwicklung erforderlich. Sie finden den Produktaktivierungsschlüssel im Novalnet Admin-Portal: PROJEKT > Wählen Sie Ihr Projekt > Shop-Parameter > API-Signatur (Produktaktivierungsschlüssel). ');

define('MODULE_PAYMENT_NOVALNET_CLIENT_KEY_TITLE', 'Schlüsselkunde');
define('MODULE_PAYMENT_NOVALNET_CLIENT_KEY_DESC', '');

define('MODULE_PAYMENT_NOVALNET_VENDOR_ID_TITLE','Händler-ID');
define('MODULE_PAYMENT_NOVALNET_VENDOR_ID_DESC','');

define('MODULE_PAYMENT_NOVALNET_AUTH_CODE_TITLE',' Authentifizierungscode');
define('MODULE_PAYMENT_NOVALNET_AUTH_CODE_DESC','');

define('MODULE_PAYMENT_NOVALNET_PRODUCT_ID_TITLE','Projekt-ID');
define('MODULE_PAYMENT_NOVALNET_PRODUCT_ID_DESC','');

define('MODULE_PAYMENT_NOVALNET_TARIFF_ID_TITLE','Auswahl der Tarif-ID');
define('MODULE_PAYMENT_NOVALNET_TARIFF_ID_DESC','Wählen Sie eine Tarif-ID, die dem bevorzugten Tarifplan entspricht, den Sie im Novalnet Admin-Portal für dieses Projekt erstellt haben');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY_TITLE','Zahlungs-Zugriffsschlüssel');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY_DESC','');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_TITLE','Zahlungslogo anzeigen');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_DESC','Das Logo der Zahlungsart wird auf der Checkout-Seite angezeigt');

define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_TITLE','<h2>Verwaltung des Bestellstatus für ausgesetzte Zahlungen</h2>On-hold-Bestellstatus');
define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_DESC','Wählen Sie, welcher Status für On-hold-Bestellungen verwendet wird, solange diese nicht bestätigt oder storniert worden sind');

define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_TITLE','Status für stornierte Bestellungen');
define('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_DESC','Wählen Sie, welcher Status für stornierte oder voll erstattete Bestellungen verwendet wird');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE_TITLE','<h2>Benachrichtigungs- / Webhook-URL festlegen</h2>Manuelles Testen der Benachrichtigungs / Webhook-URL erlauben');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE_DESC','Aktivieren Sie diese Option, um die Novalnet-Benachrichtigungs-/Webhook-URL manuell zu testen. Deaktivieren Sie die Option, bevor Sie Ihren Shop liveschalten, um unautorisierte Zugriffe von Dritten zu blockieren');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_TITLE','E-Mail-Benachrichtigungen einschalten');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_DESC','Aktivieren Sie diese Option, um die angegebene E-Mail-Adresse zu benachrichtigen, wenn die Benachrichtigungs- / Webhook-URL erfolgreich ausgeführt wurde.');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_TITLE','E-Mails senden an');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_DESC','E-Mail-Benachrichtigungen werden an diese E-Mail-Adresse gesendet');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL_TITLE','Benachrichtigungs- / Webhook-URL');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL_DESC','Sie müssen die folgende Webhook-URL im <a href="https://admin.novalnet.de" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Novalnet Admin-Portal</a> hinzufügen. Dadurch können Sie Benachrichtigungen über den Transaktionsstatus erhalten');

define('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE','<b>Novalnet Haupteinstellungen (V_11.1.6)</b>');


