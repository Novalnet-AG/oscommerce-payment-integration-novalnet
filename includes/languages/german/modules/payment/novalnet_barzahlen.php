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
 * Script : novalnet_barzahlen.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEXT_TITLE', 'Barzahlen/viacash ');

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEXT_DESCRIPTION','Die Transaktion wird in Ländern wie Deutschland und Österreich durch Barzahlungen mit Kassenzetteln abgeschlossen');
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEXT_DESC','Nach erfolgreichem Bestellabschluss erhalten Sie einen Zahlschein bzw. eine SMS. Damit können Sie Ihre Online-Bestellung bei einem unserer Partner im Einzelhandel (z.B. Drogerie, Supermarkt etc.) bezahlen');

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_PUBLIC_TITLE',((defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY') && MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'True') ? tep_image(DIR_WS_ICONS.'novalnet/novalnet_barzahlen.png',"Barzahlen/viacash" ) :''));

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS_TITLE',MODULE_PAYMENT_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_STATUS_DESC',MODULE_PAYMENT_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE_TITLE',MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE);
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_TEST_MODE_DESC',MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC);

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE_TITLE','Verfallsdatum des Zahlscheins (in Tagen)');
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE_DESC','Anzahl der Tage, die der K&auml;ufer Zeit hat, um den Betrag in einer Filiale zu bezahlen. Wenn Sie dieses Feld leer lassen, ist der Zahlschein standardm&auml;&szlig;ig 14 Tage lang g&uuml;ltig');

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT_TITLE',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE);
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_VISIBILITY_BY_AMOUNT_DESC',MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC);

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_CUSTOMER_INFO_TITLE',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_TITLE);
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_CUSTOMER_INFO_DESC',MODULE_PAYMENT_NOVALNET_CUSTOMER_INFO_DESC);

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER_TITLE',MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE);
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SORT_ORDER_DESC',MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC);

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS_TITLE',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE);
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_ORDER_STATUS_DESC',MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC);

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_CALLBACK_ORDER_STATUS_TITLE','Callback-Bestellstatus');
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_CALLBACK_ORDER_STATUS_DESC','Wählen Sie, welcher Status nach der erfolgreichen Ausführung des Novalnet-Callback-Skripts (ausgelöst bei erfolgreicher Zahlung) verwendet wird. ');

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE_TITLE',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE);
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_PAYMENT_ZONE_DESC',MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC);

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_BLOCK_TITLE','<b>Barzahlen/viacash Konfiguration</b>');

define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SLIP_EXPIRY_DATE_TEXT', 'Verfallsdatum des Zahlscheins');
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_NEAREST_STORE_DETAILS_TEXT', 'Barzahlen-Partnerfiliale in Ihrer Nähe');
define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_BUTTON', 'Bezahlen mit Barzahlen');
