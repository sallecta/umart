ALTER TABLE `#__easyshop_methods`
  ADD COLUMN `language` CHAR(7) NOT NULL DEFAULT '*' AFTER `ordering`;
