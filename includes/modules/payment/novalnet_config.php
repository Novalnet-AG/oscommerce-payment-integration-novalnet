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
 * Script : novalnet_config.php
 *
 */
include_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.novalnetutil.php';
class novalnet_config
{
    var $code, $title, $description, $enabled, $sort_order;

    /**
     * Constructor
     *
     */
    function novalnet_config()
    {
        $this->code        = 'novalnet_config';
        $this->title       = MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_DESCRIPTION;
        $this->enabled     = false;
        $this->sort_order  = 0;
    }

    /**
     * Core Function : selection()
     *
     */
    function selection()
    {
        return false;
    }

    /**
     * Core Function : check()
     *
     */
    function check()
    {
        if (!isset($this->_check)) {
            $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /**
     * Core Function : install()
     *
     */
    function install()
    {
        global $request_type, $language;
        $novalnet_tmp_status_id  =  $this->createNovalnetOrderStatus();
        include_once DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/modules/payment/novalnet_config.php';
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_group_id, configuration_description, sort_order, set_function, use_function, date_added)
        VALUES
        ('" . MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED_TITLE . "','MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED','', '6','" . MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED_DESC . "', '', '', '', ''),
        ('" . MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY_TITLE . "','MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY', '', '6','" . MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY_DESC . "', '1', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_VENDOR_ID_TITLE . "','MODULE_PAYMENT_NOVALNET_VENDOR_ID', '', '6','" . MODULE_PAYMENT_NOVALNET_VENDOR_ID_DESC . "', '2', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_AUTH_CODE_TITLE . "','MODULE_PAYMENT_NOVALNET_AUTH_CODE', '', '6','" . MODULE_PAYMENT_NOVALNET_AUTH_CODE_DESC . "', '3', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PRODUCT_ID_TITLE . "','MODULE_PAYMENT_NOVALNET_PRODUCT_ID', '', '6','" . MODULE_PAYMENT_NOVALNET_PRODUCT_ID_DESC . "', '4', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_TARIFF_ID_TITLE . "','MODULE_PAYMENT_NOVALNET_TARIFF_ID', '', '6','" . MODULE_PAYMENT_NOVALNET_TARIFF_ID_DESC . "', '5', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY', '', '6','" . MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY_DESC . "', '6', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_TITLE . "','MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT', '', '6','" . MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT_DESC . "', '7', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_TITLE . "','MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION','False', '6','" . MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION_DESC . "', '8', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION\'," . MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PROXY_TITLE . "','MODULE_PAYMENT_NOVALNET_PROXY', '', '6','" . MODULE_PAYMENT_NOVALNET_PROXY_DESC . "', '9', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT_TITLE . "','MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT', '240', '6','" . MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT_DESC . "', '10', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_REFERRER_ID_TITLE . "','MODULE_PAYMENT_NOVALNET_REFERRER_ID', '', '6','" . MODULE_PAYMENT_NOVALNET_REFERRER_ID_DESC . "', '11', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION_TITLE . "','MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION','False', '6','" . MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION_DESC . "', '8', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION\'," . MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_TITLE . "','MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY','True', '6','" . MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY_DESC . "', '12', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY\'," . MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_TITLE . "','MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE', '0', '6','" . MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE_DESC . "', '13', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_TITLE . "','MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED', '0', '6','" . MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED_DESC . "', '14', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_TITLE . "','MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD', '', '6','" . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD_DESC . "', '15', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_TITLE . "','MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT', '', '6','" . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT_DESC . "', '16', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_TITLE . "','MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2', '', '6','" . MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_DESC . "', '17', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS_TITLE . "','MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS', '0', '6','" . MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS_DESC . "', '18', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE_TITLE . "','MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE','False', '6','" . MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE_DESC . "', '20', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE\'," . MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_TITLE . "','MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND','False', '6','" . MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND_DESC . "', '21', 'tep_mod_select_option(array(\'True\' => MODULE_PAYMENT_NOVALNET_TRUE,\'False\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND\'," . MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND . ",', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_TITLE . "','MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO', '', '6','" . MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO_DESC . "', '22', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_TITLE . "','MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC', '', '6','" . MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC_DESC . "', '23', '', '', now()),
        ('" . MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL_TITLE . "','MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL', '" . ((($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG) . 'ext/modules/payment/novalnet/callback/callback.php' . "', '6','" . MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL_DESC . "', '24', '', '', now())");

        $this->versionUpdateSql();
    }

    /**
     * Core Function : remove()
     *
     */
    function remove()
    {
        $keys = $this->keys();
        $keys[] .= 'MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED';
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $keys) . "')");
    }

    /**
     * Core Function : keys()
     *
     */
    function keys()
    {
        echo '<input type="hidden" id="server_ip" value="' . (($_SERVER['SERVER_ADDR'] == '::1') ? '127.0.0.1' : $_SERVER['SERVER_ADDR']) . '" /><input type="hidden" id="nn_api_shoproot" value="' . DIR_WS_CATALOG . '" />
	    <input type="hidden" id="remote_ip" value="' . (($_SERVER['REMOTE_ADDR'] == '::1') ? '127.0.0.1' : $_SERVER['REMOTE_ADDR']) . '" /><input type="hidden" id="nn_api_shoproot" value="' . DIR_WS_CATALOG . '" />
            <input type="hidden" id="nn_language" value="' . (($_SESSION['language'] == 'english') ? 'en' : 'de') . '" />        
            <script src="' . DIR_WS_CATALOG . 'ext/modules/payment/novalnet/js/novalnet_api.js"></script>';
        NovalnetUtil::checkMerchantConfiguration(true);
        return array(
            'MODULE_PAYMENT_NOVALNET_PRODUCT_ACTIVATION_KEY',
            'MODULE_PAYMENT_NOVALNET_VENDOR_ID',
            'MODULE_PAYMENT_NOVALNET_AUTH_CODE',
            'MODULE_PAYMENT_NOVALNET_PRODUCT_ID',
            'MODULE_PAYMENT_NOVALNET_TARIFF_ID',
            'MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY',
            'MODULE_PAYMENT_NOVALNET_MANUAL_CHECK_LIMIT',
            'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION',
            'MODULE_PAYMENT_NOVALNET_PROXY',
            'MODULE_PAYMENT_NOVALNET_CURL_TIME_OUT',
            'MODULE_PAYMENT_NOVALNET_REFERRER_ID',
            'MODULE_PAYMENT_NOVALNET_CONFIG_ENABLE_NOTIFICATION_FOR_TEST_TRANSACTION',
            'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY',
            'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE',
            'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED',
            'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD',
            'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT',
            'MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2',
            'MODULE_PAYMENT_NOVALNET_SUBSCRIPTION_CANCEL_STATUS',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_NOTIFY_URL'
        );
    }

    /**
     * Installing Novalnet tables
     *
     */
    function versionUpdateSql()
    {	
        $insert_novalnet_tables = true;
        $tables_sql             = tep_db_query('select table_name from information_schema.columns where table_schema = "' . DB_DATABASE . '"');
        while ($result = tep_db_fetch_array($tables_sql)) {
            if ($result['table_name'] == 'novalnet_transaction_detail')
                $insert_novalnet_tables = false;
        }

        if (!$insert_novalnet_tables) {
            $alter_sql = tep_db_fetch_array(tep_db_query('show columns from novalnet_transaction_detail like "reference_transaction"'));
            if (empty($alter_sql)) {
                //Import Novalnet version 11 package SQL tables
                $sql_file     = DIR_FS_CATALOG . 'ext/modules/payment/novalnet/install/update_10_to_11.sql';
                $sql_lines    = file_get_contents($sql_file);
                $sql_linesArr = explode(";", $sql_lines);
                foreach ($sql_linesArr as $sql) {
                    if (trim($sql) > '') {
                        tep_db_query($sql);
                    }
                }
            }
            $sql_version    = tep_db_query("select version from novalnet_version_detail where version='11.1.4'");
            $version_detail = tep_db_fetch_array($sql_version);
            if (empty($version_detail)) {
                tep_db_query("INSERT INTO novalnet_version_detail VALUES ('11.1.4')");
            }
        } else {
            //Import Novalnet Package SQL tables
            $sql_file     = DIR_FS_CATALOG . 'ext/modules/payment/novalnet/install/install_11.sql';
            $sql_lines    = file_get_contents($sql_file);
            $sql_linesArr = explode(";", $sql_lines);
            foreach ($sql_linesArr as $sql) {
                if (trim($sql) > '') {
                    tep_db_query($sql);
                }
            }
        }
    }
    /**
     * Create the Novalnet pending status
     *
     * @return int
     */
    function createNovalnetOrderStatus() {
		$languages = tep_db_query("select * from " . TABLE_LANGUAGES . " order by sort_order");

		$query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
		$status = tep_db_fetch_array($query);

		$status_id = $status['status_id'];

		while($language = tep_db_fetch_array($languages)) {

			if(file_exists(DIR_FS_LANGUAGES . $language['directory'].'/modules/payment/novalnet.php')) {
				include_once(DIR_FS_LANGUAGES . $language['directory'].'/modules/payment/novalnet.php');
			}
			if(empty($novalnet_temp_status_text)) {
				$novalnet_temp_status_text = 'NN payment pending';
			}
 
			$query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = '" . $novalnet_temp_status_text . "' AND language_id='".$language['languages_id']."' limit 1");
			if(tep_db_num_rows($query) < 1) {
				$status_id = $status['status_id']+1;
				$insert_values = array(
					'orders_status_id' => $status_id,
					'language_id' => $language['languages_id'],
					'orders_status_name' => $novalnet_temp_status_text,
				);
				tep_db_perform(TABLE_ORDERS_STATUS, $insert_values);
			}
		}
		return ($status_id != '') ? $status_id : DEFAULT_ORDERS_STATUS_ID;
	}
}
?>
