ALTER TABLE `oc_baycik_sync_entries` 
ADD COLUMN `option_group1` VARCHAR(512) NULL AFTER `price4`,
ADD COLUMN `price_group1` VARCHAR(512) NULL AFTER `option_group1`,
ADD COLUMN `price` FLOAT NULL AFTER `price_group1`;
