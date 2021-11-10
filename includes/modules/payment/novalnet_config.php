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
 * Script : novalnet_config.php 
 */
require_once(DIR_FS_CATALOG . 'includes/classes/novalnet/class.Novalnet.php');
class novalnet_config extends NovalnetCore {
  public $code, $title, $description, $enabled;
  function __construct() {
	parent::loadConstants();
	$this->code = 'novalnet_config';
	$this->title = MODULE_PAYMENT_NOVALNET_CONFIG_TITLE;
	$this->description = MODULE_PAYMENT_NOVALNET_CONFIG_DESC;
	$this->sort_order = 0;
	$this->enabled = false;
  }

  function check() {
	return parent::checkInstalledStatus($this->code);
  }

  function install() {
	parent::installModule($this->code);
  }

  function remove() {
	parent::uninstallModule($this->code);
  }

  function keys() {
    $language = $_SESSION['language'] == 'german' ? 'DE' : 'EN';
	echo '<input type="hidden" id="remote_ip" value="'. (($_SERVER['REMOTE_ADDR'] == '::1')? '127.0.0.1' : $_SERVER['REMOTE_ADDR']) .'" />';    
    echo '<input type="hidden" id="nn_api_shoproot" value="'. DIR_WS_CATALOG .'" />';
    echo '<input type="hidden" id="nn_language" value="'. $language .'" />';
    echo '<script src="'.DIR_WS_CATALOG . 'includes/classes/novalnet/js/novalnet_api.js" type="text/javascript"></script>';
	return parent::novalnetKeys($this->code);
  }	
}
?>
