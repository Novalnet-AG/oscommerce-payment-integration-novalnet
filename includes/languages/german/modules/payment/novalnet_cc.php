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
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE', 'Kredit- / Debitkarte ');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION', 'Der Betrag wird von dem Konto des Käufers bei Nutzung einer Kredit-/Bankkarte eingezogen');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESC', 'Der Betrag wird Ihrer Kredit-/Debitkarte belastet');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_TEXT_DESCRIPTION', 'Nach der erfolgreichen &Uuml;berpr&uuml;fung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen');

define('MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE', (defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_visa.png', "Kredit- / Debitkarte").' '.tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_mastercard.png', "Kredit- / Debitkarte") .' '.tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_amex.png', "Kredit- / Debitkarte").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_maestro.png', "Kredit- / Debitkarte").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_cartasi.png', "Kredit- / Debitkarte").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_unionpay.png', "Kredit- / Debitkarte").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_discover.png', "Kredit- / Debitkarte").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_diners.png', "Kredit- / Debitkarte").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_jcb.png', "Kredit- / Debitkarte").' '. tep_image(DIR_WS_ICONS.'novalnet/novalnet_cc_carte-bleue.png', "Kredit- / Debitkarte") : '');

define('MODULE_PAYMENT_NOVALNET_CC_STATUS_TITLE', MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_DESC', MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_TITLE', MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_DESC', MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_INLINE_FORM_TITLE', 'Inline-Formular erm&ouml;glichen');
define('MODULE_PAYMENT_NOVALNET_CC_INLINE_FORM_DESC', '');

define('MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_TITLE', MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_CUSTOMER_INFO_DESC', MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_TITLE', MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_DESC', MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_TITLE', MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_DESC', MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_TITLE', MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_DESC', MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D_TITLE', '3D-Secure-Zahlungen außerhalb der EU erzwingen');
define('MODULE_PAYMENT_NOVALNET_CC_ENFORCED_3D_DESC', 'Wenn Sie diese Option aktivieren, werden alle Zahlungen mit Karten, die außerhalb der EU ausgegeben wurden, mit der starken Kundenauthentifizierung (Strong Customer Authentication, SCA) von 3D-Secure 2.0 authentifiziert.');

define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC', MODULE_PAYMENT_NOVALNET_SHOP_TYPE_DESC);

define('MODULE_PAYMENT_NOVALNET_OPTION_NONE', MODULE_PAYMENT_OPTION_NONE);
define('MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK', MODULE_PAYMENT_NOVALNET_ONE_CLICK);
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT', MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT);

define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_TITLE', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_DESC', MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT', 'Neue Kartendaten f&uuml;r sp&auml;tere K&auml;ufe hinzuf&uuml;gen');
define('MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT', 'Eingegebene Kartendaten');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE', 'Kartentyp');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER', 'Name des Karteninhabers');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO', 'Kreditkartennummer');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE', 'Ablaufdatum');
define('MODULE_PAYMENT_NOVALNET_CC_BLOCK_TITLE', '<b>Kreditkarte Konfiguration</b>');
define('MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS', 'Ihre Kreditkartendaten sind ungültig');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_ERROR_MESSAGE', MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CONFIGURATION_TITLE', '<h2>Angepasste CSS-Einstellungen</h2><h3>CSS-Einstellungen für den iFrameformular</h3> <span style="font-weight:normal">Beschriftung</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_INPUT_TITLE', '<span style="font-weight:normal">Eingabe</span>');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_STANDARD_STYLE_CSS_TITLE', '<span style="font-weight:normal">Text für das CSS</span>');
define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_TITLE','Aktion für vom Besteller autorisierte Zahlungen');

define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_DESC','Wählen Sie, ob die Zahlung sofort belastet werden soll oder nicht. Zahlung einziehen: Betrag sofort belasten. Zahlung autorisieren: Die Zahlung wird überprüft und autorisiert, aber erst zu einem späteren Zeitpunkt belastet. So haben Sie Zeit, über die Bestellung zu entscheiden.');

define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT_TITLE', 'Limit f&uuml;r onhold-Buchungen setzen (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');

define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_CC_TITLE', 'Mindesttransaktionsbetrag für die Autorisierung (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_CC_LIMIT_DESC', 'Übersteigt der Bestellbetrag das genannte Limit, wird die Transaktion, bis zu ihrer Bestätigung durch Sie, auf on hold gesetzt. Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.');
