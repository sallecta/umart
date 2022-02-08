CREATE TABLE IF NOT EXISTS `#__easyshop_order_coupons` (
  `order_id`  INT(10) UNSIGNED    NOT NULL,
  `coupon_id` INT(10) UNSIGNED    NOT NULL,
  `handled`   TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`, `coupon_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;
ALTER TABLE `#__easyshop_order_products` ADD `product_discount_incl` NUMERIC(14,2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `product_price`;