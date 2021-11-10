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
require_once (dirname(__FILE__).'/novalnet.php');

define('MODULE_PAYMENT_NOVALNET_TRUE','Wahr');
define('MODULE_PAYMENT_NOVALNET_FALSE','Falsch');

define('MODULE_PAYMENT_STATUS_TITLE','Zahlungsart aktivieren');
define('MODULE_PAYMENT_STATUS_DESC','');

define('MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE','Testmodus aktivieren');
define('MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC','Die Zahlung wird im Testmodus durchgeführt, daher wird der Betrag für diese Transaktion nicht eingezogen.');

define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TITLE','Betrugsprüfung aktivieren');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_DESC','Automatische PIN-Generierung zur Authentifizierung von Käufern in DE, AT und CH. Weitere Informationen finden Sie in der Installationsanleitung.  ');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_LIMIT_TITLE','Mindestwarenwert f&uuml;r Betrugspr&uuml;fungsmodul (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_LIMIT_DESC','Geben Sie den Mindestwarenwert ein, von dem ab das Betrugspr&uuml;fungsmodul aktiviert sein soll');

define('MODULE_PAYMENT_OPTION_NONE','Keiner');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK','PIN-by-Callback');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS','PIN-by-SMS');

define('MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE','Mindestbestellsumme (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC','Mindestbestellsumme zur Anzeige der ausgewählten Zahlungsart(en) im Checkout');

define('MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE','Benachrichtigung des K&auml;ufers');
define('MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC','Der eingegebene Text wird auf der Checkout-Seite angezeigt');

define('MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE','Geben Sie eine Sortierreihenfolge an');
define('MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC','Die Zahlungsarten werden in Ihrem Checkout anhand der von Ihnen vorgegebenen Sortierreihenfolge angezeigt (in aufsteigender Reihenfolge).');

define('MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE','Status für erfolgreichen Auftragsabschluss');
define('MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC','Wählen Sie, welcher Status für erfolgreich abgeschlossene Bestellungen verwendet wird. ');

define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE','Zahlungsgebiet');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC','Diese Zahlungsart wird f&uuml;r die angegebenen Gebiete angezeigt');

define('MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE','Einkaufstyp');
define('MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC','Einkaufstyp ausw&auml;hlen');

define('MODULE_PAYMENT_NOVALNET_ONE_CLICK','Kauf mit einem Klick');
define('MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT','Transaktionen mit Betrag 0');

define('MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG','<span style="color:red;">Die Zahlung wird im Testmodus durchgeführt, daher wird der Betrag für diese Transaktion nicht eingezogen.</span>');

define('MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE','Ungültiger Betrag');

define('MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS', 'Novalnet-Transaktionsdetails');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ID', 'Novalnet Transaktions-ID: ');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TRANSACTION_ID', 'PayPal Transaktions-ID: ');
define('MODULE_PAYMENT_NOVALNET_REFERENCE_ORDER_TEXT', 'Verwendungszweck Bestellnummer: '); 
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG', 'Testbestellung');
define('MODULE_PAYMENT_NOVALNET_INVOICE_COMMETNS_PARAGRAPH', 'Bitte überweisen Sie den Betrag auf das unten stehende Konto.');
define('MODULE_PAYMENT_NOVALNET_DUE_DATE', 'Fälligkeitsdatum');
define('MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_NOVALNET_IBAN', 'IBAN');
define('MODULE_PAYMENT_NOVALNET_BIC', 'BIC');
define('MODULE_PAYMENT_NOVALNET_BANK', 'Bank');
define('MODULE_PAYMENT_NOVALNET_AMOUNT', 'Betrag');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT', 'Bitte verwenden Sie einen der unten angegebenen Verwendungszwecke für die Überweisung. Nur so kann Ihr Geldeingang Ihrer Bestellung zugeordnet werden:');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT', 'Bitte verwenden Sie nun der unten angegebenen Verwendungszweck für die Überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:');
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF', 'Verwendungszweck' );
define('MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF', 'Verwendungszweck%s');
define('MODULE_PAYMENT_NOVALNET_ORDER_NUMBER', 'Bestellnummer');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_INFO','In Kürze erhalten Sie einen Telefonanruf mit der PIN zu Ihrer Transaktion, um die Zahlung abzuschließen');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO','In Kürze erhalten Sie eine SMS mit der PIN zu Ihrer Transaktion, um die Zahlung abzuschließen');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC', 'PIN zu Ihrer Transaktion');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN', '&nbsp; PIN vergessen?');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_EMPTY', 'PIN eingeben.');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID', 'Die von Ihnen eingegebene PIN ist falsch');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_AMOUNT_CHANGE_ERROR', 'Der Bestellbetrag hat sich geändert, setzen Sie bitte die neue Bestellung fort.');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_CALLBACK_INPUT_TITLE', 'Telefonnummer ');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_INPUT_TITLE', 'Mobiltelefonnummer ');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR', 'Geben Sie bitte Ihre Telefonnummer ein');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_ERROR','Geben Sie bitte Ihre Mobiltelefonnummer ein');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_INVOICE_CREDIT_COMMENTS','Novalnet-Callback-Skript erfolgreich ausgeführt für die TID: %s mit dem Betrag %s am %s um %s Uhr. Bitte suchen Sie nach der bezahlten Transaktion in unserer Novalnet-Händleradministration mit der TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS','Chargeback erfolgreich importiert für die TID: %s Betrag: %s am %s um %s. TID der Folgebuchung: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_BOOKBACK_COMMENTS','Rückerstattung / Bookback erfolgreich ausgeführt für die TID: %s Betrag: %s am %s. TID der Folgebuchung: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS','Nächstes Belastungsdatum:');

define('MODULE_PAYMENT_NOVALNET_REDIRECT_NOTICE_MSG','<br />Bitte schlie&szlig;en Sie den Browser nach der erfolgreichen Zahlung nicht, bis Sie zum Shop zur&uuml;ckgeleitet wurden');
define('MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_BIRTH_DATE','Ihr Geburtsdatum');
define('MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR','Ihre Kontodaten sind ungültig.');

define('MODULE_PAYMENT_NOVALNET_JS_DEACTIVATE_ERROR','Aktivieren Sie bitte JavaScript in Ihrem Browser, um die Zahlung fortzusetzen.');

define('MODULE_PAYMENT_NOVALNET_PHP_EXTENSION_MISSING','Erwähnt PHP-Paket (e) in diesem Server nicht verfügbar ist. Bitte aktivieren Sie sie');

define('MODULE_PAYMENT_NOVALNET_REFERENCE_ERROR','Wählen Sie mindestens einen Verwendungszweck aus.');

define('MODULE_PAYMENT_NOVALNET_AGE_ERROR','Sie müssen mindestens 18 Jahre alt sein');

define('MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS','Novalnet-Callback-Skript erfolgreich ausgeführt für die TID: %s mit dem Betrag %s am %s um %s Uhr eingestellt.');

define( 'MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR','Die Zahlung war nicht erfolgreich. Ein Fehler trat auf');

define('MODULE_PAYMENT_NOVALNET_GLOBAL_CONFIGURATION_DETAILS', '<h2>Einstellungen für die Zahlungsgarantie</h2><b>Voraussetzungen für die Zahlungsgarantie</b><span style="font-weight:normal;"><br/><br/>Zugelassene Staaten: AT, DE, CH<br/>Zugelassene Währung: EUR<br/>Mindestbetrag der Bestellung: 9,99 EUR<br/>Mindestalter: 18 Jahre<br/>Rechnungsadresse und Lieferadresse müssen übereinstimmen</span><br/><br/><b>Zahlungsgarantie aktivieren</b>');
define('MODULE_PAYMENT_NOVALNET_GLOBAL_CONFIGURATION_DETAILS_DESCRIPTION', '');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MINIMUM_ORDER_AMOUNT', 'Mindestbestellbetrag für Zahlungsgarantie (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_PAYMENT_MINIMUM_ORDER_AMOUNT_DESC', 'Geben Sie den Mindestbetrag (in Cent) für die zu bearbeitende Transaktion mit Zahlungsgarantie ein. Geben Sie z.B. 100 ein, was 1,00 entspricht. Der Standbetrag ist 9,99 EUR.');
define('MODULE_PAYMENT_NOVALNET_ENABLE_FORCE_GUARANTEE_PAYMENT', 'Zahlung ohne Zahlungsgarantie erzwingen');
define('MODULE_PAYMENT_NOVALNET_ENABLE_FORCE_GUARANTEE_PAYMENT_DESC', 'Falls die Zahlungsgarantie zwar aktiviert ist, jedoch die Voraussetzungen für Zahlungsgarantie nicht erfüllt sind, wird die Zahlung ohne Zahlungsgarantie verarbeitet. Die Voraussetzungen finden Sie in der Installationsanleitung unter "Zahlungsgarantie aktivieren". ');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR', 'Während der Umleitung wurden einige Daten geändert. Die Überprüfung des Hashes schlug fehl');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_SUBJECT', 'Benachrichtigung zu Novalnet-Testbestellung - osCommerce');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_NOTIFICATION_MESSAGE', 'Sehr geehrte Kundin,<br/>sehr geehrter Kunde, wir möchten Sie darüber informieren, dass eine Testbestellung (%s) kürzlich in Ihrem Shop durchgeführt wurde. Stellen Sie bitte sicher, dass für Ihr Projekt im Novalnet-Administrationsportal der Live-Modus gesetzt wurde und Zahlungen über Novalnet in Ihrem Shopsystem aktiviert sind. Ignorieren Sie bitte diese E-Mail, falls die Bestellung von Ihnen zu Testzwecken durchgeführt wurde.<br/>Mit freundlichen Grüßen Novalnet AG');

define('MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY','Diese Transaktion wird mit Zahlungsgarantie verarbeitet');
define('MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY_CONFIRM','Ihre Bestellung wird derzeit überprüft. Wir werden Sie in Kürze über den Bestellstatus informieren. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann.');

define('MODULE_PAYMENT_NOVALNET_MENTION_GUARANTEE_PAYMENT_PENDING_TEXT','Ihre Bestellung wird überprüft. Nach der Bestätigung senden wir Ihnen unsere Bankverbindung, an die Sie bitte den Gesamtbetrag der Bestellung überweisen. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann.');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_SEPA_PAYMENT_PENDING_TEXT','Ihre Bestellung ist unter Bearbeitung. Sobald diese bestätigt wurde, erhalten Sie alle notwendigen Informationen zum Ausgleich der Rechnung.');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','Die Buchung wurde am %s um %s Uhr bestätigt.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_PENDING_TO_HOLD_MESSAGE','Der Status der Transaktion mit der TID: %s wurde am %s um %s Uhr  von ausstehend auf ausgesetzt geändert.');
define('MODULE_PAYMENT_NOVALNET_TARRIF_AMOUNT_ERROR_MSG', 'Geben Sie bitte einen gültigen Betrag für die 2. Abonnementsperiode ein');
define('MODULE_PAYMENT_NOVALNET_GURANTEE_PAYMENT_MIN_AMOUNT_ERROR_MSG', 'Der Mindestbetrag sollte bei mindestens 9,99 EUR liegen.');
define('MODULE_PAYMENT_NOVALNET_GURANTEE_PAYMENT_NOT_MATCH_ERROR_MSG', 'Die Zahlung kann nicht verarbeitet werden, weil die grundlegenden Anforderungen nicht erfüllt wurden.');
$novalnet_temp_status_text = 'Zahlung über NN steht noch aus';
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_SUBJECT','Bestellbestätigung – Ihre Bestellung %s bei %s wurde bestätigt!');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_MESSAGE','Wir freuen uns Ihnen mitteilen zu können, dass Ihre Bestellung bestätigt wurde.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_CANCELLED_MESSAGE','Die Transaktion wurde am %s um %s Uhr storniert');
define('MODULE_PAYMENT_NOVALNET_INVOICE_ON_HOLD_CONFIRM_TEXT','Die Transaktion mit der TID: %s wurde erfolgreich bestätigt und das Fälligkeitsdatum auf %s gesetzt.'); 
define('MODULE_PAYMENT_NOVALNET_FORCE_GUARANTEE_ERROR_MESSAGE','<span style="color:red;">Die Zahlung kann nicht verarbeitet werden, weil die grundlegenden Anforderungen nicht erfüllt wurden</span>');
define('MODULE_PAYMENT_NOVALNET_FORCE_GUARANTEE_ERROR','Die Zahlung kann nicht verarbeitet werden, weil die grundlegenden Anforderungen nicht erfüllt wurden');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_ADDRESS','Die Zahlung kann nicht ausgeführt werden, weil die Voraussetzungen für die Zahlungsgarantie nicht erfüllt sind (Die Lieferadresse muss mit der Rechnungsadresse identisch sein).');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_COUNTRY','Die Zahlung kann nicht ausgeführt werden, weil die Voraussetzungen für die Zahlungsgarantie nicht erfüllt sind (nur Deutschland, Österreich oder die Schweiz sind zulässig).');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_AMOUNT','Die Zahlung kann nicht ausgeführt werden, weil die Voraussetzungen für die Zahlungsgarantie nicht erfüllt sind (Mindestbestellwert %s EUR).');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_CURRENCY','Die Zahlung kann nicht ausgeführt werden, weil die Voraussetzungen für die Zahlungsgarantie nicht erfüllt sind (Nur EUR als Währung erlaubt).');

define('MODULE_PAYMENT_NOVALNET_AUTHORIZE','Zahlung autorisieren');
define('MODULE_PAYMENT_NOVALNET_CAPTURE','Zahlung einziehen');

define('DIR_WS_NOVALNET_ADMIN', DIR_FS_CATALOG .'admin/');
define('MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR','Bitte füllen Sie die erforderlichen Felder aus');
