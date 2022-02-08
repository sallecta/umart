ALTER TABLE `#__easyshop_methods` ADD COLUMN `vendor_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `state`;
ALTER TABLE `#__easyshop_emails` ADD COLUMN `vendor_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `state`;
ALTER TABLE `#__easyshop_discounts` ADD COLUMN `vendor_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `state`;
ALTER TABLE `#__easyshop_customfields` ADD COLUMN `vendor_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `state`;
ALTER TABLE `#__easyshop_orders` ADD COLUMN `vendor_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `state`;
ALTER TABLE `#__easyshop_orders` ADD COLUMN `parent_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `vendor_id`;
ALTER TABLE `#__easyshop_logs` DROP COLUMN `zone_country`;
ALTER TABLE `#__easyshop_logs` DROP COLUMN `zone_state`;