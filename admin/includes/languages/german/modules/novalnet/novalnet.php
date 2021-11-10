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
 * Script : novalnet.php
 *
 */
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_BUTTON','Transaktion verwalten');
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_TITLE','Ablauf der Buchung steuern');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT', 'Wählen Sie bitte einen Status aus');
define('MODULE_PAYMENT_NOVALNET_SELECT_CONFIRM_TEXT', 'Sind Sie sicher, dass Sie die Zahlung einziehen möchten?');
define('MODULE_PAYMENT_NOVALNET_SELECT_CANCEL_TEXT', 'Sind Sie sicher, dass Sie die Zahlung stornieren wollen?');
define('MODULE_PAYMENT_NOVALNET_REFUND_AMOUNT_TEXT', 'Sind Sie sicher, dass Sie den Betrag zurückerstatten möchten?');
define('MODULE_PAYMENT_NOVALNET_BOOK_AMOUNT_TEXT', 'Sind Sie sich sicher, dass Sie den Bestellbetrag buchen wollen?');
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','Die Buchung wurde am %s um %s Uhr bestätigt.');
define('MODULE_PAYMENT_NOVALNET_INVOICE_ON_HOLD_CONFIRM_TEXT','Die Transaktion mit der TID: %s wurde erfolgreich bestätigt und das Fälligkeitsdatum auf %s gesetzt.');
define('MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE','Die Transaktion wurde am %s um %s Uhr storniert.');
define('MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE','Der Betrag der Transaktion %s wurde am %s um %s Uhr erfolgreich geändert');
define('MODULE_PAYMENT_NOVALNET_REFUND_BUTTON','R&uuml;ckerstattung');
define('MODULE_PAYMENT_NOVALNET_REFUND_AMT_TITLE','Geben Sie bitte den erstatteten Betrag ein');
define('MODULE_PAYMENT_NOVALNET_REFUND_TITLE','Ablauf der R&uuml;ckerstattung');
define('MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG','Die Rückerstattung wurde für die TID: %s mit dem Betrag %s durchgeführt.');
define('MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG',' Ihre neue TID für den erstatteten Betrag: %s');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_EX',' (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT','Best&auml;tigen');
define('MODULE_PAYMENT_NOVALNET_BACK_TEXT', 'Zur&uuml;ck');
define('MODULE_PAYMENT_NOVALNET_SLIP_DATE_CHANGE_TITLE' ,'Betrag/Verfallsdatum des Zahlscheins ändern');
define('MODULE_PAYMENT_NOVALNET_UPDATE_TEXT','&Auml;ndern');
define('MODULE_PAYMENT_NOVALNET_CANCEL_TEXT','Stornieren');
define('MODULE_PAYMENT_NOVALNET_TRANS_SLIP_EXPIRY_DATE','Verfallsdatum des Zahlscheins: ');
define('MODULE_PAYMENT_NOVALNET_NEAREST_STORE_DETAILS','Barzahlen-Partnerfiliale in Ihrer Nähe');
define('MODULE_PAYMENT_NOVALNET_ORDER_UPDATE','erfolgreichen');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION', '--Ausw&auml;hlen--');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TITLE','Stornierung von Abonnements');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_BUTTON','Abonnement k&uuml;ndigen');
define('MODULE_PAYMENT_NOVALNET_SUBS_SELECT_REASON','W&auml;hlen Sie bitte den Grund aus');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_MESSAGE','Das Abonnement wurde gekündigt wegen: ');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_REASON_TITLE', 'Wählen Sie bitte den Grund für die Abonnementskündigung aus.');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_1','Angebot zu teuer');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_2','Betrug');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_3','(Ehe-)Partner hat Einspruch eingelegt');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_4', 'Finanzielle Schwierigkeiten');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_5','Inhalt entsprach nicht meinen Vorstellungen');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_6','Inhalte nicht ausreichend');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_7','Nur an Probezugang interessiert');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_8','Seite zu langsam');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_9','Zufriedener Kunde');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_10','Zugangsprobleme');
define('MODULE_PAYMENT_NOVALNET_SUBS_REASON_11','Sonstige');
define('MODULE_PAYMENT_NOVALNET_BOOK_TITLE','Transaktion durchf&uuml;hren');
define('MODULE_PAYMENT_NOVALNET_BOOK_BUTTON','Buchen');
define('MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE','Buchungsbetrag der Transaktion');
define('MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE','Ihre Bestellung wurde mit einem Betrag von %s gebucht. Ihre neue TID für den gebuchten Betrag:%s');
define('MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR','Ihre Kontodaten sind ungültig.');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE','Betrag &auml;ndern');
define('MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TITLE', 'Betrag der Transaktion &auml;ndern');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_BUTTON','Betrag / F&auml;lligkeitsdatum &auml;ndern');
define('MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_TITLE','F&auml;lligkeitsdatum der Transaktion');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT','Sind Sie sich sicher, dass Sie den Bestellbetrag ändern wollen?');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_DATE_UPDATE_TEXT','Sind Sie sich sicher, dass Sie den Betrag / das Fälligkeitsdatum der Bestellung ändern wollen?');
define('MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE','Das Datum sollte in der Zukunft liegen.');
define('MODULE_PAYMENT_NOVALNET_SEPA_TEXT_TITLE','Lastschrift SEPA');
define('MODULE_PAYMENT_NOVALNET_MAP_PAGE_HEADER','Loggen Sie sich hier mit Ihren Novalnet H&auml;ndler-Zugangsdaten ein.Um neue Zahlungsarten zu aktivieren, kontaktieren Sie bitte <a href="mailto:support@novalnet.de">support@novalnet.de</a>');
define('MODULE_PAYMENT_NOVALNET_REFUND_REFERENCE_TEXT', 'Referenz f&uuml;r die R&uuml;ckerstattung');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_DETAILS', 'Novalnet-Transaktionsdetails');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ID', 'Novalnet Transaktions-ID: ');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_MSG', 'Testbestellung');
define('MODULE_PAYMENT_NOVALNET_INVOICE_COMMETNS_PARAGRAPH', 'Überweisen Sie bitte den Betrag an die unten aufgeführte Bankverbindung unseres Zahlungsdienstleisters Novalnet.');
define('MODULE_PAYMENT_NOVALNET_DUE_DATE', 'Fälligkeitsdatum');
define('MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_NOVALNET_IBAN', 'IBAN');
define('MODULE_PAYMENT_NOVALNET_BIC', 'BIC');
define('MODULE_PAYMENT_NOVALNET_BANK', 'Bank');
define('MODULE_PAYMENT_NOVALNET_AMOUNT', 'Betrag');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT', 'Bitte verwenden Sie einen der unten angegebenen Verwendungszwecke für die Überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT', 'Bitte verwenden Sie nun der unten angegebenen Verwendungszweck für die Überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:');
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF', 'Verwendungszweck' );
define('MODULE_PAYMENT_NOVALNET_INVPRE_MULTI_REF', 'Verwendungszweck%s');
define('MODULE_PAYMENT_NOVALNET_ORDER_NUMBER', 'Bestellnummer');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE','Ungültiger Betrag');
define('MODULE_PAYMENT_NOVALNET_INVALID_DATE','Ungültiges Fälligkeitsdatum');
define('MODULE_PAYMENT_NOVALNET_SUBS_CANCEL_TEXT','Sind Sie sicher, dass Sie das Abonnement kündigen wollen?');
