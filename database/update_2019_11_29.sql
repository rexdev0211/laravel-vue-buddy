ALTER TABLE `users` ADD COLUMN `invisible` TINYINT(1) NOT NULL DEFAULT 0 AFTER `email`;
ALTER TABLE `users` ADD COLUMN `discreet_mode` TINYINT(1) NOT NULL DEFAULT 0 AFTER `email`;
ALTER TABLE `user_visits_map` ADD COLUMN `invisible` TINYINT(1) NOT NULL DEFAULT 0 AFTER `visited_id`;

CREATE INDEX `invisible` ON `users` (`invisible`);
CREATE INDEX `discreet_mode` ON `users` (`discreet_mode`);
CREATE INDEX `invisible` ON `user_visits_map` (`invisible`);
