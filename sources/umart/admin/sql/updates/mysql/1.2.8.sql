ALTER TABLE `#__easyshop_orders` ADD COLUMN `language` CHAR(7) NOT NULL DEFAULT '' AFTER `track_subzone_id`;
ALTER TABLE `#__easyshop_orders` ADD COLUMN `ip` VARCHAR(50) NOT NULL DEFAULT '' AFTER `language`;
