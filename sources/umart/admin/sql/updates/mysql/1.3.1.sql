CREATE TABLE IF NOT EXISTS `#__easyshop_translations`
(
  `translationId` VARCHAR (191) NOT NULL,
  `originalValue` MEDIUMTEXT,
  `translatedValue` MEDIUMTEXT,
  PRIMARY KEY (`translationId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;
