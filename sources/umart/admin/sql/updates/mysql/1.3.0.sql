ALTER TABLE `#__easyshop_prices`
  ADD COLUMN `valid_from_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `currency_id`;
ALTER TABLE `#__easyshop_prices`
  ADD COLUMN `valid_to_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `valid_from_date`;

CREATE TABLE IF NOT EXISTS `#__easyshop_price_days`
(
  `product_id` INT(10) UNSIGNED        NOT NULL,
  `week_day`   TINYINT(1) UNSIGNED     NOT NULL,
  `price`      DECIMAL(14, 2) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`, `week_day`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;