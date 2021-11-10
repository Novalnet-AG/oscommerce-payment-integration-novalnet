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
 * Script : class.NovalnetTranslator.php
 */

class NovalnetTranslator {
		
  /**
   * Set constant values
   * 
   * @return none
   */
  static public function setConstantValues() {
	global $language;
	$lang_values = self::loadLocaleContents($language);
	if (!empty($lang_values)) {
	  $lang_values = self::mapConstantValues($lang_values);
	  foreach ($lang_values as $lkey => $lval) {
		defined($lkey) || define($lkey, utf8_decode($lval));
	  }
	}
  }
	
  /**
   * Load the language contents from the novalnet package language file (classes directory)
   * @param $langID
   *
   * @return array
   */
  static public function loadLocaleContents($langID = 'english') {
	$lang_file = DIR_FS_CATALOG . 'includes/classes/novalnet/languages/'.strtolower($langID).'.php';		
	if (!file_exists($lang_file)) {
	  $lang_file = DIR_FS_CATALOG . 'includes/classes/novalnet/languages/english.php'; // Default language file
	}
	require($lang_file);
	return $localeValues;
  }
	
  /**
   * Set language constant mapping to avoid multiple entries in language file
   * @param $input_lang
   *
   * @return array
   */
  static public function mapConstantValues($input_lang = array()) {
	if (!empty($input_lang)) {
	  $enabled_payment_modules = array('CC','SEPA','SOFORTBANK','PAYPAL','IDEAL','INVOICE','PREPAYMENT','EPS');
	  $common_lang_key_for_map = array(
				'MODULE_PAYMENT_NOVALNET_ENABLE_MODULE_TITLE',
				'MODULE_PAYMENT_NOVALNET_ENABLE_MODULE_DESC',
				'MODULE_PAYMENT_NOVALNET_TEST_MODE_TITLE',
				'MODULE_PAYMENT_NOVALNET_TEST_MODE_DESC',
				'MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_TITLE',
				'MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_DESC',
				'MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_TITLE',
				'MODULE_PAYMENT_NOVALNET_VISIBILITY_BYAMOUNT_DESC',
				'MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_INFO_TITLE',
				'MODULE_PAYMENT_NOVALNET_ENDCUSTOMER_INFO_DESC',
				'MODULE_PAYMENT_NOVALNET_SORT_ORDER_TITLE',
				'MODULE_PAYMENT_NOVALNET_SORT_ORDER_DESC',
				'MODULE_PAYMENT_NOVALNET_ORDER_STATUS_TITLE',
				'MODULE_PAYMENT_NOVALNET_ORDER_STATUS_DESC',
				'MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_TITLE',
				'MODULE_PAYMENT_NOVALNET_PAYMENT_ZONE_DESC',
				'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_TITLE',
				'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE1_DESC',
				'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_TITLE',
				'MODULE_PAYMENT_NOVALNET_TRANS_REFERENCE2_DESC'
			);
	  foreach ($enabled_payment_modules as $pkey => $pval) {
		foreach ($common_lang_key_for_map as $lkey => $lval) {
		  $original_lang_key = $lval;
		  $split_target_key = explode('MODULE_PAYMENT_NOVALNET', $original_lang_key);
		  $target_lang_key = 'MODULE_PAYMENT_NOVALNET_'.$pval.$split_target_key[1];
		  if (isset($input_lang[$original_lang_key]) && !isset($input_lang[$target_lang_key])) {
			$input_lang[$target_lang_key] = $input_lang[$original_lang_key];
		  }
		}
	  }
    }
	return $input_lang;
  }
}
?>
