CREATE TABLE IF NOT EXISTS `#__easyshop_currencies`
(
    `id`               INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `symbol`           VARCHAR(255)            NOT NULL DEFAULT '',
    `code`             CHAR(7)                 NOT NULL DEFAULT '',
    `format`           VARCHAR(255)            NOT NULL DEFAULT '',
    `name`             VARCHAR(255)            NOT NULL DEFAULT '',
    `state`            TINYINT(1)              NOT NULL DEFAULT '0',
    `is_default`       TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `rate`             DECIMAL(16, 6) UNSIGNED NOT NULL DEFAULT '1.000000',
    `point`            CHAR(1)                 NOT NULL DEFAULT '.',
    `decimals`         TINYINT(1) UNSIGNED     NOT NULL DEFAULT '2',
    `separator`        CHAR(1)                 NOT NULL DEFAULT ',',
    `created_date`     DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `params`           TEXT,
    `ordering`         INT(10) UNSIGNED        NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    KEY `idx_code` (`code`),
    KEY `idx_is_default` (`is_default`),
    KEY `idx_state` (`state`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_customfields`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_customfields`
(
    `id`               INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `group_id`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `name`             VARCHAR(255)        NOT NULL DEFAULT '',
    `alias`            VARCHAR(255)        NOT NULL DEFAULT '',
    `state`            TINYINT(1)          NOT NULL DEFAULT '1',
    `vendor_id`        INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `type`             VARCHAR(20)         NOT NULL DEFAULT 'TEXT',
    `default_value`    TEXT,
    `required`         TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `created_date`     DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `ordering`         INT(10) UNSIGNED    NOT NULL DEFAULT '1',
    `params`           TEXT,
    `reflector`        TEXT,
    `protected`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `rules`            TEXT,
    `showon`           TINYINT(1)          NOT NULL DEFAULT '-1',
    `attributes`       TEXT,
    `categories`       TEXT,
    `checkout_field`   TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `field_name`       VARCHAR(50)         NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_state` (`state`),
    KEY `idx_group_id` (`group_id`),
    KEY `idx_protected` (`protected`),
    KEY `idx_checkout_field` (`checkout_field`),
    KEY `idx_field_name` (`field_name`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_customfield_values`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_customfield_values`
(
    `reflector`      VARCHAR(50)      NOT NULL,
    `reflector_id`   INT(10) UNSIGNED NOT NULL,
    `customfield_id` INT(10) UNSIGNED NOT NULL,
    `value`          TEXT,
    PRIMARY KEY (`reflector`, `customfield_id`, `reflector_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_discounts`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_discounts`
(
    `id`                     INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `name`                   VARCHAR(255)            NOT NULL DEFAULT '',
    `state`                  TINYINT(1)              NOT NULL DEFAULT '1',
    `vendor_id`              INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `type`                   TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `coupon_type`            TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `coupon_code`            VARCHAR(255)            NOT NULL DEFAULT '',
    `flat`                   DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `percentage`             TINYINT(3) UNSIGNED     NOT NULL DEFAULT '0',
    `currencies`             TEXT,
    `limit`                  TINYINT(4)              NOT NULL DEFAULT '-1',
    `start_date`             DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `end_date`               DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `description`            TEXT,
    `ordering`               INT(10) UNSIGNED        NOT NULL DEFAULT '1',
    `checked_out`            INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out_time`       DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_date`           DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`             INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `user_groups`            TEXT,
    `categories`             TEXT,
    `include_sub_categories` TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `products`               TEXT,
    `zone_type`              TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `zone_countries`         TEXT,
    `zone_states`            TEXT,
    `order_min_amount`       DECIMAL(14, 4)          NOT NULL DEFAULT '0.0000',
    `product_min_price`      DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `product_max_price`      DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `discount_max_price`     DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `params`                 MEDIUMTEXT,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`state`),
    KEY `idx_type` (`type`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_emails`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_emails`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(255)     NOT NULL DEFAULT '',
    `state`            TINYINT(1)       NOT NULL DEFAULT '1',
    `vendor_id`        INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `send_from_name`   VARCHAR(255)     NOT NULL DEFAULT '',
    `send_from_email`  VARCHAR(255)     NOT NULL DEFAULT '',
    `send_to_emails`   TEXT,
    `send_subject`     VARCHAR(255)     NOT NULL DEFAULT '',
    `send_body`        MEDIUMTEXT,
    `send_on`          VARCHAR(255)     NOT NULL DEFAULT '',
    `order_status`     VARCHAR(500)     NOT NULL DEFAULT '',
    `order_payment`    VARCHAR(500)     NOT NULL DEFAULT '',
    `created_date`     DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `ordering`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `language`         CHAR(7)          NOT NULL DEFAULT '*',
    PRIMARY KEY (`id`),
    KEY `idx_state` (`state`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_logs`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_logs`
(
    `id`            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `context`       VARCHAR(255)        NOT NULL DEFAULT '',
    `string_key`    VARCHAR(255)        NOT NULL DEFAULT '',
    `sprintf_data`  TEXT,
    `previous_data` TEXT,
    `modified_data` TEXT,
    `ip`            VARCHAR(50)         NOT NULL DEFAULT '',
    `juser_id`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `user_agent`    TEXT,
    `created_date`  DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `referer`       VARCHAR(1000)       NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_context` (`context`(100)),
    KEY `idx_juser_id` (`juser_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_medias`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_medias`
(
    `id`               BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`             CHAR(5)             NOT NULL DEFAULT 'IMAGE',
    `product_id`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `file_path`        VARCHAR(1000)       NOT NULL DEFAULT '',
    `title`            VARCHAR(255)        NOT NULL DEFAULT '',
    `description`      TEXT,
    `created_date`     DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `mime_type`        VARCHAR(255)        NOT NULL DEFAULT '',
    `ordering`         INT(10) UNSIGNED    NOT NULL DEFAULT '1',
    `hits`             INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `params`           MEDIUMTEXT,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_orders`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_orders`
(
    `id`               INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `order_code`       VARCHAR(20)             NOT NULL DEFAULT '',
    `user_id`          INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `user_email`       VARCHAR(255)            NOT NULL DEFAULT '',
    `state`            TINYINT(1)              NOT NULL DEFAULT '0',
    `vendor_id`        INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `parent_id`        INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `created_date`     DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_date`    DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `shipping_id`      INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `payment_id`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `currency_id`      INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `payment_status`   TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `payment_data`     TEXT,
    `payment_txn_id`   TEXT,
    `payment_date`     DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `total_price`      DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `total_taxes`      DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `total_fee`        DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `extra_cost`       DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `extra_cost_taxes` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `total_paid`       DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `total_shipping`   DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `total_discount`   DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `note`             TEXT,
    `viewed`           TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `token`            VARCHAR(40)             NOT NULL DEFAULT '',
    `track_country_id` INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `track_state_id`   INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `track_subzone_id` INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `language`         CHAR(7)                 NOT NULL DEFAULT '',
    `ip`               VARCHAR(50)             NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_order_code` (`order_code`),
    KEY `idx_state` (`state`),
    KEY `idx_shipping_id` (`shipping_id`),
    KEY `idx_payment_id` (`payment_id`),
    KEY `idx_currency_id` (`currency_id`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_track_country_id` (`track_country_id`),
    KEY `idx_track_state_id` (`track_state_id`),
    KEY `idx_track_subzone_id` (`track_subzone_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_order_products`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_order_products`
(
    `id`                    INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `order_id`              INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `product_id`            INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `product_name`          VARCHAR(255)            NOT NULL DEFAULT '',
    `product_taxes`         DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `product_price`         DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `product_discount_incl` DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `product_shipping`      DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `quantity`              TINYINT(4) UNSIGNED     NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_product_id` (`product_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_order_product_options`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_order_product_options`
(
    `id`               INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `order_product_id` INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `option_id`        INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `option_name`      VARCHAR(255)            NOT NULL DEFAULT '',
    `option_text`      VARCHAR(255)            NOT NULL DEFAULT '',
    `option_value`     TEXT,
    `option_price`     DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    PRIMARY KEY (`id`),
    KEY `idx_order_product_id` (`order_product_id`),
    KEY `idx_option_id` (`option_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_order_coupons`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_order_coupons`
(
    `order_id`  INT(10) UNSIGNED    NOT NULL,
    `coupon_id` INT(10) UNSIGNED    NOT NULL,
    `handled`   TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`order_id`, `coupon_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_methods`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_methods`
(
    `id`               INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `plugin_id`        INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `name`             VARCHAR(255)            NOT NULL DEFAULT '',
    `show_name`        TINYINT(1) UNSIGNED     NOT NULL DEFAULT '1',
    `order_status`     TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `state`            TINYINT(1)              NOT NULL DEFAULT '1',
    `vendor_id`        INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `is_default`       TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `image`            VARCHAR(1000)           NOT NULL DEFAULT '',
    `flat_fee`         DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `percentage_fee`   TINYINT(3) UNSIGNED     NOT NULL DEFAULT '0',
    `taxes`            TEXT,
    `description`      TEXT,
    `description_type` TINYINT(1) UNSIGNED     NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_date`     DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `params`           MEDIUMTEXT,
    `ordering`         INT(10) UNSIGNED        NOT NULL DEFAULT '1',
    `language`         CHAR(7)                 NOT NULL DEFAULT '*',
    PRIMARY KEY (`id`),
    KEY `idx_state` (`state`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_prices`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_prices`
(
    `product_id`      INT(10) UNSIGNED        NOT NULL,
    `price_value`     DECIMAL(14, 4) UNSIGNED NOT NULL,
    `min_quantity`    TINYINT(4) UNSIGNED     NOT NULL,
    `currency_id`     INT(10) UNSIGNED        NOT NULL,
    `valid_from_date` DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `valid_to_date`   DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`product_id`, `price_value`, `min_quantity`, `currency_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__easyshop_price_days`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_price_days`
(
    `product_id` INT(10) UNSIGNED        NOT NULL,
    `week_day`   TINYINT(1) UNSIGNED     NOT NULL,
    `price`      DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    PRIMARY KEY (`product_id`, `week_day`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_products`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_products`
(
    `id`                INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `asset_id`          INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `category_id`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `brand_id`          INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `taxes`             TEXT                    NOT NULL,
    `price`             DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `sku`               VARCHAR(50)             NOT NULL DEFAULT '',
    `name`              VARCHAR(255)            NOT NULL DEFAULT '',
    `alias`             VARCHAR(255)            NOT NULL DEFAULT '',
    `type`              VARCHAR(255)            NOT NULL DEFAULT 'normal',
    `state`             TINYINT(1)              NOT NULL DEFAULT '1',
    `created_date`      DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified`          DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `created_by`        INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out_time`  DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `stock`             INT(10)                 NOT NULL DEFAULT '-1',
    `summary`           VARCHAR(5000)           NOT NULL DEFAULT '',
    `description`       MEDIUMTEXT,
    `weight_unit`       VARCHAR(10)             NOT NULL DEFAULT '',
    `weight`            DECIMAL(12, 3)          NOT NULL DEFAULT '0.000',
    `dimension_unit`    VARCHAR(255)            NOT NULL DEFAULT 'm',
    `width`             DECIMAL(12, 3) UNSIGNED NOT NULL DEFAULT '0.000',
    `height`            DECIMAL(12, 3) UNSIGNED NOT NULL DEFAULT '0.000',
    `length`            DECIMAL(12, 3) UNSIGNED NOT NULL DEFAULT '0.000',
    `params`            MEDIUMTEXT,
    `hits`              INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `ordering`          INT(10) UNSIGNED        NOT NULL DEFAULT '1',
    `metatitle`         VARCHAR(160)            NOT NULL DEFAULT '',
    `metakey`           TEXT,
    `metadesc`          TEXT,
    `metadata`          TEXT,
    `robots`            VARCHAR(255)            NOT NULL DEFAULT '',
    `author`            VARCHAR(255)            NOT NULL DEFAULT '',
    `access`            INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `sale_from_date`    DATETIME                NULL     DEFAULT NULL,
    `sale_to_date`      DATETIME                NULL     DEFAULT NULL,
    `vendor_id`         INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `option_fields`     TEXT,
    `language`          CHAR(7)                 NOT NULL DEFAULT '*',
    `display_in_search` TINYINT(1) UNSIGNED     NOT NULL DEFAULT '1',
    `approved`          TINYINT(1) UNSIGNED     NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `idx_asset_id` (`asset_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_brand_id` (`brand_id`),
    KEY `idx_sku` (`sku`),
    KEY `idx_state` (`state`),
    KEY `idx_vendor_id` (`vendor_id`),
    KEY `idx_language` (`language`),
    KEY `idx_display_in_search` (`display_in_search`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_taxes`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_taxes`
(
    `id`               INT(10) UNSIGNED        NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(255)            NOT NULL DEFAULT '',
    `type`             TINYINT(1) UNSIGNED     NOT NULL DEFAULT '1',
    `rate`             TINYINT(3) UNSIGNED     NOT NULL DEFAULT '0',
    `flat`             DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT '0.0000',
    `vendor_id`        INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `state`            TINYINT(1)              NOT NULL DEFAULT '1',
    `created_date`     DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED        NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME                NOT NULL DEFAULT '0000-00-00 00:00:00',
    `ordering`         INT(10) UNSIGNED        NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `idx_state` (`state`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_users`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_users`
(
    `id`               INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`          INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `vendor`           TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `state`            TINYINT(1)          NOT NULL DEFAULT '0',
    `avatar`           VARCHAR(1000)       NOT NULL DEFAULT '',
    `scores`           INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `level`            TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `secret_key`       VARCHAR(40)         NOT NULL DEFAULT '',
    `created_date`     DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_state` (`state`),
    KEY `idx_vendor` (`vendor`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__easyshop_zones`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_zones`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id`        INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `name`             VARCHAR(255)     NOT NULL DEFAULT '',
    `name_english`     VARCHAR(255)     NOT NULL DEFAULT '',
    `code_2`           VARCHAR(10)      NOT NULL DEFAULT '',
    `code_3`           VARCHAR(10)      NOT NULL DEFAULT '',
    `type`             VARCHAR(20)      NOT NULL DEFAULT '',
    `state`            TINYINT(1)       NOT NULL DEFAULT '1',
    `currency_id`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_date`     DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `ordering`         INT(10) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_code_2` (`code_2`),
    KEY `idx_type` (`type`),
    KEY `idx_state` (`state`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__easyshop_params`
(
    `context` VARCHAR(125)     NOT NULL,
    `item_id` INT(10) UNSIGNED NOT NULL,
    `data`    MEDIUMTEXT,
    PRIMARY KEY (`context`, `item_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__easyshop_tags`
(
    `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `context`          VARCHAR(255)     NOT NULL DEFAULT '',
    `name`             VARCHAR(255)     NOT NULL DEFAULT '',
    `alias`            VARCHAR(255)     NOT NULL DEFAULT '',
    `state`            TINYINT(1)       NOT NULL DEFAULT '0',
    `created_date`     DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out_time` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
    `language`         CHAR(7)          NOT NULL DEFAULT '*',
    `ordering`         INT(10) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `idx_state` (`state`),
    KEY `idx_context` (`context`(191))
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for `#__easyshop_tag_items`
-- ----------------------------

CREATE TABLE IF NOT EXISTS `#__easyshop_tag_items`
(
    `tag_id`  INT(10) UNSIGNED NOT NULL,
    `item_id` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`tag_id`, `item_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for `#__easyshop_translations`
-- ----------------------------

CREATE TABLE IF NOT EXISTS `#__easyshop_translations`
(
    `translationId`   VARCHAR(191) NOT NULL,
    `originalValue`   MEDIUMTEXT,
    `translatedValue` MEDIUMTEXT,
    PRIMARY KEY (`translationId`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__easyshop_order_field_price_xref`
--

CREATE TABLE IF NOT EXISTS `#__easyshop_order_field_price_xref`
(
    `orderId` INT(10) UNSIGNED        NOT NULL,
    `fieldId` INT(10) UNSIGNED        NOT NULL,
    `label`   VARCHAR(255)            NOT NULL DEFAULT '',
    `price`   DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `tax`     DECIMAL(14, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    PRIMARY KEY (`orderId`, `fieldId`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------