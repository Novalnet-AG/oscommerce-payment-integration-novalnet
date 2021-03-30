CREATE TABLE IF NOT EXISTS novalnet_version_detail (
  version varchar(10),
  KEY version (version)
) COMMENT='Novalnet version information';

ALTER TABLE novalnet_transaction_detail ADD COLUMN reference_transaction enum('0','1') COMMENT 'Notify the referenced order';
ALTER TABLE novalnet_transaction_detail ADD COLUMN zerotrxnreference bigint(20) unsigned NULL COMMENT 'Zero transaction TID';
ALTER TABLE novalnet_transaction_detail ADD COLUMN zerotrxndetails text NULL COMMENT 'Zero amount order details';
ALTER TABLE novalnet_transaction_detail ADD COLUMN zero_transaction enum('0','1') NULL COMMENT 'Notify the zero amount order';
ALTER TABLE novalnet_transaction_detail ADD COLUMN payment_ref text NULL COMMENT 'Payment reference for Invoice/Prepayment';
ALTER TABLE novalnet_transaction_detail ADD COLUMN payment_details text COMMENT 'Masked account details of customer';
ALTER TABLE novalnet_transaction_detail ADD COLUMN callback_amount int(11) unsigned COMMENT 'Callback amount';
