CREATE TABLE IF NOT EXISTS novalnet_transaction_detail (
  id int(11) AUTO_INCREMENT COMMENT 'Auto Increment ID',
  tid bigint(20) unsigned COMMENT 'Novalnet Transaction Reference ID',
  vendor int(11) unsigned COMMENT 'Vendor ID',
  product int(11) unsigned COMMENT 'Product ID',
  auth_code varchar(40) COMMENT 'Vendor Authcode',
  tariff int(11) unsigned COMMENT 'Tariff ID',
  payment_id int(11) unsigned COMMENT 'Payment ID',
  payment_type varchar(50) COMMENT 'Executed Payment type of this order',
  amount int(11) unsigned COMMENT 'Transaction amount',
  total_amount int(11) unsigned COMMENT 'Order total amount',
  currency char(3) COMMENT 'Transaction currency',
  gateway_status int(11) unsigned NULL COMMENT 'Novalnet transaction status',
  test_mode tinyint(1) unsigned DEFAULT '0' COMMENT 'Transaction test mode status',
  customer_id int(11) unsigned COMMENT 'Customer ID from shop',
  order_no int(11) COMMENT 'Order ID from shop',
  `date` datetime COMMENT 'Transaction Date for reference',
  `language` varchar(10) COMMENT 'Shop language',
  process_key varchar(255) COMMENT 'Encrypted process key',
  reference_transaction enum('0','1') COMMENT 'Notify the referenced order',
  zerotrxnreference bigint(20) unsigned NULL COMMENT 'Zero transaction TID',
  zerotrxndetails text NULL COMMENT 'Zero amount order details',
  zero_transaction enum('0','1') NULL COMMENT 'Notify the zero amount order',
  payment_ref text NULL COMMENT 'Payment reference for Invoice/Prepayment', 
  payment_details text COMMENT 'Masked account details of customer',
  callback_amount int(11) unsigned COMMENT 'Callback amount',
  PRIMARY KEY (id),
  KEY tid (tid),
  KEY payment_type (payment_type),
  KEY order_no (order_no)
) COMMENT='Novalnet Transaction History';

CREATE TABLE IF NOT EXISTS novalnet_version_detail (
  version varchar(10),
  KEY version (version)
) COMMENT='Novalnet version information';

ALTER TABLE configuration MODIFY set_function varchar(512);
ALTER TABLE configuration MODIFY configuration_title varchar(512);
ALTER TABLE configuration MODIFY configuration_description varchar(960);
ALTER TABLE orders_status_history MODIFY comments text;
