ALTER TABLE `#__easyshop_order_products`
    CHANGE `quantity` `quantity` INT(10) UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `#__easyshop_orders`
    ADD COLUMN `extra_cost` DECIMAL(14, 2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `total_fee`;

ALTER TABLE `#__easyshop_orders`
    ADD COLUMN `extra_cost_taxes` DECIMAL(14, 2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `extra_cost`;

ALTER TABLE `#__easyshop_methods`
    ADD COLUMN `taxes` TEXT AFTER `percentage_fee`;

UPDATE `#__easyshop_customfields`
SET `type` = 'flatpicker'
WHERE `type` = 'ui_datetimepicker';

--
-- Table structure for table `#__easyshop_order_field_price_xref`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_order_field_price_xref`
(
    `orderId` INT(10) UNSIGNED        NOT NULL,
    `fieldId` INT(10) UNSIGNED        NOT NULL,
    `label`   VARCHAR(255)            NOT NULL DEFAULT '',
    `price`   DECIMAL(14, 2) UNSIGNED NOT NULL DEFAULT 0.00,
    `tax`     DECIMAL(14, 2) UNSIGNED NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`orderId`, `fieldId`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------