ALTER TABLE `oc_baycik_sync_entries` 
CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL ,
ADD COLUMN `leftovers` VARCHAR(45) NULL AFTER `mpn`,
ADD COLUMN `attribute_group` TEXT NULL AFTER `price_group1`;