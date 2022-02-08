ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `total_price` `total_price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `total_taxes` `total_taxes` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `total_fee` `total_fee` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `extra_cost` `extra_cost` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `extra_cost_taxes` `extra_cost_taxes` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `total_paid` `total_paid` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `total_shipping` `total_shipping` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_orders`
    CHANGE COLUMN `total_discount` `total_discount` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_order_field_price_xref`
    CHANGE COLUMN `price` `price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_order_field_price_xref`
    CHANGE COLUMN `tax` `tax` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_discounts`
    CHANGE COLUMN `flat` `flat` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_discounts`
    CHANGE COLUMN `order_min_amount` `order_min_amount` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_discounts`
    CHANGE COLUMN `product_min_price` `product_min_price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_discounts`
    CHANGE COLUMN `product_max_price` `product_max_price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_discounts`
    ADD COLUMN `discount_max_price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `product_max_price`;

ALTER TABLE `#__easyshop_order_products`
    CHANGE COLUMN `product_taxes` `product_taxes` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_order_products`
    CHANGE COLUMN `product_price` `product_price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_order_products`
    CHANGE COLUMN `product_discount_incl` `product_discount_incl` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_order_products`
    CHANGE COLUMN `product_shipping` `product_shipping` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_order_product_options`
    CHANGE COLUMN `option_price` `option_price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_methods`
    CHANGE COLUMN `flat_fee` `flat_fee` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_prices`
    CHANGE COLUMN `price_value` `price_value` DECIMAL(14, 4) UNSIGNED NOT NULL;

ALTER TABLE `#__easyshop_price_days`
    CHANGE COLUMN `price` `price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_taxes`
    CHANGE COLUMN `flat` `flat` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__easyshop_products`
    CHANGE COLUMN `price` `price` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000';