ALTER TABLE `oc_baycik_sync_entries` 
ADD COLUMN `product_name1` VARCHAR(225) CHARACTER SET 'utf8'  NULL AFTER `price`,
ADD COLUMN `product_name2` VARCHAR(225) CHARACTER SET 'utf8'  NULL AFTER `product_name1`,
ADD COLUMN `product_name3` VARCHAR(225) CHARACTER SET 'utf8'  NULL AFTER `product_name2`;