ALTER TABLE `oc_baycik_sync_entries` 
ADD COLUMN `leftovers` VARCHAR(45) NULL AFTER `min_order_size`;
