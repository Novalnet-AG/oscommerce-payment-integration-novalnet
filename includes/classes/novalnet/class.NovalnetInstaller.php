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
 * Script : class.NovalnetInstaller.php
 */
require_once('class.NovalnetPaymentKeys.php');

class NovalnetModuleInstaller {

  /**
   * Function for installing particular module
   * @param $module
   *
   * @return boolean
   */
  static public function install($module = '') {
	$insert_novalnet_tables = true;
    $tables_sql = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '"');
    while ($result = tep_db_fetch_array($tables_sql)) {
	  if ($result['table_name'] == 'novalnet_transaction_detail')
		$insert_novalnet_tables = false;
	}
	if ($insert_novalnet_tables) {
      //Import Novalnet Package SQL tables
      $sql_file = DIR_FS_CATALOG . 'includes/classes/novalnet/db.sql';
	  $sql_lines = file_get_contents($sql_file);
      $sql_linesArr = explode(";",$sql_lines);
      foreach ($sql_linesArr as $sql) {
        if (trim($sql) > '') {
          tep_db_query($sql);
        }
      }
      $sql_version = tep_db_query('select version from novalnet_version_detail');
	  $version_detail = tep_db_fetch_array($sql_version);
	  if($version_detail['version'] != '11.0.0') {
	    tep_db_query("INSERT INTO `novalnet_version_detail` VALUES ('11.0.0')");
	  }
    }
	$alter = true;
    $column_sql = tep_db_query('select column_name from information_schema.columns where table_schema = "' . DB_DATABASE . '" AND table_name= "novalnet_subscription_detail"');    
    while ($column = tep_db_fetch_array($column_sql)) {
	  if ($column['column_name'] == 'parent_tid')
	    $alter = false;
	}
	if ($alter)
	  tep_db_query('ALTER TABLE novalnet_subscription_detail ADD `parent_tid` bigint(20) unsigned NOT NULL COMMENT "Parent TID"');
    
    $alter_version = true;
    $tables_sql = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '"');
    while ($result = tep_db_fetch_array($tables_sql)) {
	  if($result['table_name'] == 'novalnet_version_detail')
		$alter_version = false;
	}
	if ($alter_version) {
	  $sql_file = DIR_FS_CATALOG . 'includes/classes/novalnet/db_version11.sql';
	  $sql_lines = file_get_contents($sql_file);
      $sql_linesArr = explode(";",$sql_lines);
      foreach ($sql_linesArr as $sql) {
        if (trim($sql) > '') {
          tep_db_query($sql);
        }
      }
	}
	$module_keys = NovalnetPaymentKeys::getKeyValues($module);
	if (!empty($module_keys)) {
	  $sort_order = 1;
	  foreach ($module_keys as $mkey => $mval) {
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('".$mval['title']."', '".$mkey."', '".$mval['value']."', '".$mval['desc']."', '6', '".($sort_order++)."', '".str_replace("'","\'",$mval['set_function'])."', '".str_replace("'","\'",$mval['use_function'])."', now())");
	  }
	  unset($sort_order);
	}
	return true;
  }

  /**
   * Function for checking particular module installed status
   * @param $module
   *
   * @return integer
   */
  static public function installedStatus($module = '') {
	$module_keys = NovalnetPaymentKeys::getKeyValues($module);
	$noofrowcheck = 0;
	if (!empty($module_keys)) {
	  $conf_key = array_keys($module_keys);
	  $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '".$conf_key[1]."'");
	  $noofrowcheck = tep_db_num_rows($check_query);
	}
	return $noofrowcheck;
  }

  /**
   * Function for uninstalling particular module
   * @param $module
   *
   * @return boolean
   */
  static public function uninstall($module = '') {
	$module_keys = NovalnetPaymentKeys::getKeyValues($module);
	if (!empty($module_keys)) {
	  tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", array_keys($module_keys)) . "')");
	}
	return true;
  }
}
?>
