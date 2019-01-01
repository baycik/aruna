ALTER TABLE `oc_baycik_sync_entries` 
ADD COLUMN `is_changed` TINYINT NULL AFTER `sync_id`,
ADD COLUMN `mpn` VARCHAR(45) NULL AFTER `model`;
