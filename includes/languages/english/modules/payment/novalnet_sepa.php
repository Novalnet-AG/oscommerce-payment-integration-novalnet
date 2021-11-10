<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnet_sepa.php 
 */

require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.NovalnetTranslator.php');
NovalnetTranslator::setConstantValues();
