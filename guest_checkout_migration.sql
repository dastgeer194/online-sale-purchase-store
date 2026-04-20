ALTER TABLE `tbl_payment`
  ADD COLUMN `customer_phone` varchar(50) NOT NULL DEFAULT '' AFTER `customer_email`,
  ADD COLUMN `customer_address` varchar(255) NOT NULL DEFAULT '' AFTER `customer_phone`,
  ADD COLUMN `customer_city` varchar(100) NOT NULL DEFAULT '' AFTER `customer_address`;
