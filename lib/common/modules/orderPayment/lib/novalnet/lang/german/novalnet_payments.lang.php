<?php

/**
 * This file is used for language(german)
 *
 * @author      Novalnet
 * @copyright   Copyright (c) Novalnet
 * @license     https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * File: novalnet_payments.lang.php
 *
 */

[
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TEXT_TITLE' => 'Novalnet Zahlung',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TEXT_DESCRIPTION' => 'Novalnet Zahlung',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_DETAILS_TID' => 'Novalnet-Transaktion ID:',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_TRANSFER_NOTE' => 'Bitte überweisen Sie den Betrag  %s  auf das folgende Konto.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_TRANSFER_NOTE_DUE_DATE' => 'Bitte überweisen Sie den Betrag von  %s  spätestens bis zum %s auf das folgende Konto',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TRANSFER_NOTE_DUE_DATE' => 'Bitte überweisen Sie den anzahl der raten von  %s  spätestens bis zum  %s  auf das folgende Konto',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_AMOUNT_TRANSFER_NOTE' => 'Bitte überweisen Sie den anzahl der raten  %s  auf das folgende Konto.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_ACCHOLDER' => 'Kontoinhaber: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BANKNAME' => 'Bank: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BANKPLACE' => 'Ort: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_IBAN' => 'IBAN: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_BIC' => 'BIC: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ADDCOMMENT_REF' => 'Verwendungszweck ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TRANS_SLIP_EXPIRY_DATE' => 'Verfallsdatum des Zahlscheins: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_NEAREST_STORE_DETAILS' => 'Barzahlen-Partnerfilialen in Ihrer Nähe: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_REFERENCE_TEXT' => 'Bitte verwenden Sie nur den unten angegebenen Verwendungszweck für die Überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_INSTALMENTS_INFO' => 'Informationen zur Ratenzahlung',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_PROCESSED_INSTALMENTS' => 'Bearbeitete Raten:  ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_DUE_INSTALMENTS' => 'Fällige Raten:  ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CYCLE_AMOUNT' => 'Zyklusmenge: ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_NEXT_INSTALMENT_DATE' => 'Nächstes Ratenzahlungsdatum:  ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_GUARANTEE_PAYMENT_PENDING_TEXT' => 'Ihre Bestellung wird derzeit überprüft. Wir werden Sie in Kürze über den Bestellstatus informieren. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_MAIL_SUBJECT' => 'Novalnet Callback Script Zugriffsbericht -osCommerce ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CAPTURE_COMMENT' => 'Die Buchung wurde am',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CANCEL_COMMENT' => 'Die Transaktion wurde am',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT_FULL' => 'Die Rückerstattung für die TID : %s mit dem Betrag %s wurde veranlasst. Die neue TID für den erstatteten Betrag lautet: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_REFUND_COMMENT' => 'Die Rückerstattung für die TID : %s mit dem Betrag %s wurde veranlasst.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CREDIT_COMMENT' => 'Die Gutschrift für die TID ist erfolgreich eingegangen: %s mit Betrag %s am %s . Bitte entnehmen Sie die TID den Einzelheiten der Bestellung bei BEZAHLT in unserem Novalnet Adminportal: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_TRANS_DEACTIVATED_MESSAGE' => 'Die Transaktion wurde am %s um %s Uhr storniert',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_STATUS_PENDING_TO_ONHOLD_TEXT' => 'Der Status der Transaktion mit der TID: %s wurde am %s um von ausstehend auf ausgesetzt geändert ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_STATUS_PENDING_TO_CONFIRMED_TEXT' => 'Der Status der Transaktion mit der TID: %s wurde am %s Uhr von ausstehend auf Abgeschlossen geändert',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK_NEW_INSTALMENT_NOTE' => 'Für die Transaktions-ID ist eine neue Rate eingegangen: %s mit Betrag %s am %s. Die Transaktions-ID der neuen Rate lautet: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_AMOUNT_UPDATE_NOTE' => 'Der Transaktionsbetrag %s erfolgreich aktualisiert wurde am %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_DUEDATE_UPDATE_NOTE' => ' Fälligkeitsdatum der Transaktion  %s erfolgreich aktualisiert wurde am %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_EVENT_TRANSACTION_CHARGEBACK_COMMENT' => 'Chargeback erfolgreich importiert für die TID: %s Betrag: %s am %s. TID der Folgebuchung: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_NOVALNET_AMOUNT_DUEDATE_UPDATE_NOTE' => 'der Transaktion Betrag %s und Fälligkeitsdatum %s wurde erfolgreich aktualisiert auf %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_REMINDER_NOTE' => 'Zahlungserinnerung %s wurde an den Kunden gesendet.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CALLBACK_COLLECTION_SUBMISSION_NOTE' => 'Die Transaktion wurde an das Inkassobüro übergeben. Inkasso-Referenz: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALLCYCLES_TEXT' => 'Die Ratenzahlung für die TID wurde gekündigt: %s am %s und die Rückerstattung wurde mit dem Betrag %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_ALLCYCLES' => 'Die Ratenzahlung für die TID wurde gekündigt: %s am %s.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_INSTALMENT_CANCEL_REMAINING_CYCLES_TEXT' => 'Die Ratenzahlung für die TID wurde gestoppt: %s um %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BARZAHLEN_SUCCESS_BUTTON' => 'Bezahlen mit Barzahlen',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_MULTIBANCO_NOTE' => 'Bitte verwenden Sie die folgende Zahlungsreferenz, um den Betrag von %s an einem Multibanco-Geldautomaten oder über Ihr Onlinebanking zu bezahlen.',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TRANS_BOOKED_MESSAGE' => 'Ihre Bestellung wurde mit einem Betrag von %s gebucht. Ihre neue TID für den gebuchten Betrag:',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PARTNER_PAYMENT_REFERENCE' => 'Partner-Zahlungsreferenz: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PARTNER_SUPPLIER_ID' => 'Entität: %s',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_TEST_ORDER' => 'Testbestellung ',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CHECKSUM_ERROR_TEXT' => 'Während der Umleitung wurden einige Daten geändert. Die Hash-Prüfung ist fehlgeschlagen',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_ZEROAMOUNT_BOOKING_TEXT' => 'Diese Transaktion wird mit Nullbuchung bearbeitet',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SELECT_STATUS_TEXT' => 'Bitte Status auswählen',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOKING_AMOUNT' => 'Buchungsbetrag der Transaktion',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_INVALID_TEXT' => 'Der Betrag ist ungültig',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_REASON_TEXT' => 'Grund der Rückerstattung/Stornierung',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOK_TRANSACTION_TEXT' => 'Transaktion durchführen',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TITLE_TEXT' => 'Geben Sie bitte den erstatteten Betrag ein',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_TEXT' => 'Erstattungsprozess',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CONFIRM_TEXT' => 'Bestätigen',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_TEXT' => 'Stornieren',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SELECT_TEXT' => 'Wählen Sie',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BACK_TEXT' => 'zurück',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_MANAGE_TRANSACTION' => 'Transaktion verwalten',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_AMOUNT_FORMAT' => '(in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CAPTURE_TEXT' => 'Sind Sie sicher, dass Sie die Zahlung einziehen möchten?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_CANCEL_ALERT_TEXT' => 'Sind Sie sicher, dass Sie die Zahlung stornieren wollen?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_REFUND_ALERT_TEXT' => 'Sind Sie sicher, dass Sie den Betrag zurückerstatten möchten?',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_BOOK_AMOUNT_ALERT_TEXT' => 'Sind Sie sich sicher, dass Sie den Bestellbetrag buchen wollen?',

    // For configuration text values
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY ( Configuration Title )' => 'Zahlungs-Zugriffsschlüssel',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_PAYMENT_ACCESS_KEY ( Configuration Description )' => 'Ihren Paymentzugriffsschlüssel finden Sie im Novalnet Admin-Portal. <input type="hidden" name="getlang" id="getlang" value="DE">',

    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SENDMAIL ( Configuration Title )' => 'E-Mails senden an',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SENDMAIL ( Configuration Description )' => 'E-Mail-Benachrichtigungen werden an diese E-Mail-Adresse gesendet',

    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SIGNATURE ( Configuration Title )' => 'Produktaktivierungsschlüssel',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_SIGNATURE ( Configuration Description )' => 'Ihren Produktaktivierungsschlüssel finden Sie im Novalnet Admin-Portal',

    'MODULE_PAYMENT_NOVALNET_PAYMENTS_STATUS ( Configuration Title )' => 'Aktivieren Sie das Novalnet-Zahlungsmodul',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_STATUS ( Configuration Description )' => 'Möchten Sie Novalnet-Zahlungen akzeptieren?',

    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF ( Configuration Title )' => 'Auswahl der Tarif-ID',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_TARIFF ( Configuration Description )' => 'Wählen Sie eine Tarif-ID, die dem bevorzugten Tarifplan entspricht, den Sie im Novalnet Admin-Portal für dieses Projekt erstellt haben',

    'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK_TESTMODE ( Configuration Title )' => 'Manuelles Testen der Benachrichtigungs- / Webhook-URL erlauben',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK_TESTMODE ( Configuration Description )' => 'Aktivieren Sie diese Option, um die Novalnet-Benachrichtigungs-/Webhook-URL manuell zu testen. Deaktivieren Sie die Option, bevor Sie Ihren Shop liveschalten, um unautorisierte Zugriffe von Dritten zu blockieren',

    'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK ( Configuration Title )' => 'Benachrichtigungs- / Webhook-URL festlegen',
    'MODULE_PAYMENT_NOVALNET_PAYMENTS_WEBHOOK ( Configuration Description )' => 'Sie müssen die folgende Webhook-URL im Novalnet Admin-Portal hinzufügen. Dadurch können Sie Benachrichtigungen über den Transaktionsstatus erhalten.<br><button class="btn-primary px-2 conf">Konfigurieren</button>',
];
