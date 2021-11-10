CREATE TABLE IF NOT EXISTS `novalnet_version_detail` (
  `version` varchar(10) NOT NULL,
  KEY `version` (`version`)
) COMMENT='Novalnet version information';

INSERT INTO `novalnet_version_detail` VALUES ('11.0.0');

ALTER TABLE novalnet_transaction_detail ADD `masked_acc_details` text COMMENT 'Masked account details of customer',
										 ADD `reference_transaction` enum('0','1') COMMENT 'Notify the referenced order',
										 ADD `zerotrxnreference` bigint(20) unsigned NULL COMMENT 'Zero transaction TID',
										 ADD `zerotrxndetails` text NULL COMMENT 'Zero amount order details',
										 ADD `zero_transaction` enum('0','1') NULL COMMENT 'Notify the zero amount order';
ALTER TABLE novalnet_preinvoice_transaction_detail ADD `payment_ref` text DEFAULT NULL;
