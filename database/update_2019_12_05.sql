ALTER TABLE `messages` ADD COLUMN `is_removed_by_sender` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_read`;
