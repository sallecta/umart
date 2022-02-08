ALTER TABLE `#__easyshop_taxes` DROP COLUMN `zone_country_id`;
ALTER TABLE `#__easyshop_taxes` DROP COLUMN `zone_state_id`;
ALTER TABLE `#__easyshop_taxes` ADD COLUMN `vendor_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `flat`;