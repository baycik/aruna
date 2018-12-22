ALTER TABLE `oc_baycik_sync_entries` 
ADD COLUMN `is_changed` TINYINT NULL AFTER `sync_id`;
