ALTER TABLE `#__easyshop_discounts` ADD COLUMN `product_min_price` TEXT NOT NULL AFTER `order_min_amount`;
ALTER TABLE `#__easyshop_discounts` ADD COLUMN `product_max_price` TEXT NOT NULL AFTER `product_min_price`;
ALTER TABLE `#__easyshop_discounts` ADD COLUMN `params` TEXT NOT NULL AFTER `product_max_price`;