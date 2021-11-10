CREATE TABLE IF NOT EXISTS `novalnet_callback_history` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Auto Increment ID',
  `date` datetime NOT NULL COMMENT 'Callback DATE TIME',
  `payment_type` varchar(100) NOT NULL COMMENT 'Callback Payment Type',
  `status` varchar(20) DEFAULT NULL COMMENT 'Callback Status',
  `callback_tid` bigint(20) unsigned NOT NULL COMMENT 'Callback Reference ID',
  `org_tid` bigint(20) unsigned DEFAULT NULL COMMENT 'Original Transaction ID',
  `amount` int(11) DEFAULT NULL COMMENT 'Amount in cents',
  `currency` varchar(5) DEFAULT NULL COMMENT 'Currency',
  `product_id` int(11) unsigned DEFAULT NULL COMMENT 'Callback Product ID',
  `order_no` int(11) unsigned NOT NULL COMMENT 'Order ID from shop',
  PRIMARY KEY (`id`),
  KEY `order_no` (`order_no`)
) COMMENT='Novalnet Callback History';

CREATE TABLE IF NOT EXISTS `novalnet_preinvoice_transaction_detail` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Auto Increment ID',
  `order_no` bigint(20) unsigned DEFAULT NULL COMMENT 'Order ID from shop ',
  `tid` bigint(20) unsigned NOT NULL COMMENT 'Novalnet Transaction Reference ID ',
  `test_mode` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `account_holder` varchar(150) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `bank_code` varchar(100) DEFAULT NULL,
  `bank_name` varchar(150) DEFAULT NULL,
  `bank_city` varchar(150) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `currency` char(3) NOT NULL,
  `bank_iban` varchar(150) DEFAULT NULL,
  `bank_bic` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `date` datetime NOT NULL,
  `payment_ref` text,
  PRIMARY KEY (`id`),
  KEY `order_no` (`order_no`)
) COMMENT='Novalnet Invoice and Prepayment transaction account History';

CREATE TABLE IF NOT EXISTS `novalnet_subscription_detail` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Auto increment ID',
  `order_no` bigint(20) unsigned NOT NULL COMMENT 'Order ID from shop',
  `subs_id` int(11) unsigned NOT NULL COMMENT 'Subscription ID',
  `tid` bigint(20) unsigned NOT NULL COMMENT 'Novalnet Transaction Reference ID',
  `parent_tid` bigint(20) unsigned NOT NULL COMMENT 'Parent TID',
  `signup_date` datetime NOT NULL COMMENT 'Subscription signup date',
  `termination_reason` varchar(255) DEFAULT NULL COMMENT 'Subscription termination reason by merchant',
  `termination_at` datetime DEFAULT NULL COMMENT 'Subscription terminated date',
  PRIMARY KEY (`id`),
  KEY `order_no` (`order_no`)
) COMMENT='Novalnet Subscription Transaction History';

CREATE TABLE IF NOT EXISTS `novalnet_transaction_detail` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Auto Increment ID',
  `tid` bigint(20) unsigned NOT NULL COMMENT 'Novalnet Transaction Reference ID',
  `vendor` int(11) unsigned NOT NULL COMMENT 'Vendor ID',
  `product` int(11) unsigned NOT NULL COMMENT 'Product ID',
  `auth_code` varchar(40) NOT NULL COMMENT 'Vendor Authcode',
  `tariff` int(11) unsigned NOT NULL COMMENT 'Tariff ID',
  `subs_id` int(11) unsigned DEFAULT NULL COMMENT 'Subscription Status',
  `payment_id` int(11) unsigned NOT NULL COMMENT 'Payment ID',
  `payment_type` varchar(50) NOT NULL COMMENT 'Executed Payment type of this order',
  `amount` int(11) NOT NULL COMMENT 'Transaction amount',
  `currency` char(3) NOT NULL COMMENT 'Transaction currency',
  `status` varchar(9) NOT NULL COMMENT 'Novalnet transaction status in respone',
  `gateway_status` varchar(9) NOT NULL COMMENT 'Novalnet transaction status',
  `test_mode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Transaction test mode status',
  `customer_id` int(11) unsigned DEFAULT NULL COMMENT 'Customer ID from shop',
  `order_no` bigint(20) unsigned NOT NULL COMMENT 'Order ID from shop',
  `callback_status` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Callback script execution status',
  `date` datetime NOT NULL COMMENT 'Transaction Date for reference',
  `language` varchar(10) DEFAULT NULL COMMENT 'Shop language',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Status',
  `process_key` varchar(255) DEFAULT NULL COMMENT 'Encrypted process key',
  `additional_note` text COMMENT 'Customer custom comments',
  `account_holder` varchar(150) DEFAULT NULL COMMENT 'Customer holder name for reference',
  `refund_amount` int(11) NOT NULL COMMENT 'Refunded amount',
  `total_amount` int(11) NOT NULL COMMENT 'Order total amount',
  `masked_acc_details` text COMMENT 'Masked account details of customer',
  `reference_transaction` enum('0','1') DEFAULT NULL COMMENT 'Notify the referenced order',
  `zerotrxnreference` bigint(20) unsigned DEFAULT NULL COMMENT 'Zero transaction TID',
  `zerotrxndetails` text COMMENT 'Zero amount order details',
  `zero_transaction` enum('0','1') DEFAULT NULL COMMENT 'Notify the zero amount order',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `payment_type` (`payment_type`),
  KEY `status` (`status`),
  KEY `active` (`active`),
  KEY `order_no` (`order_no`)
) COMMENT='Novalnet Transaction History';

CREATE TABLE IF NOT EXISTS `novalnet_aff_account_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) unsigned NOT NULL,
  `vendor_authcode` varchar(40) NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `product_url` varchar(200) NOT NULL,
  `activation_date` datetime DEFAULT NULL,
  `aff_id` int(11) unsigned NOT NULL,
  `aff_authcode` varchar(40) NOT NULL,
  `aff_accesskey` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `aff_id` (`aff_id`)
) COMMENT='Novalnet merchant / affiliate account information';

CREATE TABLE IF NOT EXISTS  `novalnet_aff_user_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff_id` int(11) unsigned NOT NULL,
  `customer_id` varchar(40) NOT NULL,
  `aff_order_no` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) COMMENT='Novalnet affiliate customer account information';

CREATE TABLE IF NOT EXISTS `novalnet_version_detail` (
  `version` varchar(10) NOT NULL,
  KEY `version` (`version`)
) COMMENT='Novalnet version information';

ALTER TABLE configuration MODIFY set_function varchar(512);
ALTER TABLE configuration MODIFY configuration_description varchar(512);
ALTER TABLE orders_status_history MODIFY comments text; 
