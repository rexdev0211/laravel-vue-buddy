ALTER TABLE `user_photos` ADD `nudity_rating` FLOAT NULL DEFAULT NULL AFTER `visible_to`;

ALTER TABLE `user_photos` ADD INDEX(`nudity_rating`);




-------------------

ALTER TABLE `users` ADD `registered_via` ENUM('web','app') NOT NULL DEFAULT 'web' AFTER `login_reminder_sent`, ADD INDEX (`registered_via`);

ALTER TABLE `email_template_langs` CHANGE `lang` `lang` ENUM('en','de','fr','it','nl') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;


ALTER TABLE `newsletter` CHANGE `language` `language` ENUM('en','de','fr','it','nl') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'en';

ALTER TABLE `pages` CHANGE `lang` `lang` ENUM('en','de','fr','it','nl') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en';

ALTER TABLE `users` CHANGE `language` `language` ENUM('en','de','fr','it','nl') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-----


ALTER TABLE `users` ADD `push_notifications` ENUM('yes','no') NULL DEFAULT NULL AFTER `subscribed`;



-- ALTER TABLE `messages` ADD `to_push` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `deleted_for_user_to`, ADD INDEX (`to_push`);



CREATE TABLE `onesignal_players` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `player_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `onesignal_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `player_id` (`player_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `onesignal_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `onesignal_players`
  ADD CONSTRAINT `fk_os_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;




-------


ALTER TABLE `user_reported_map` DROP FOREIGN KEY `fk_urm_user_id`;
ALTER TABLE `user_reported_map` ADD CONSTRAINT `fk_urm_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


---------


ALTER TABLE `users` ADD `notification_sound` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `subscribed`;

ALTER TABLE `users` CHANGE `status` `status` ENUM('active','suspended','deactivated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active';

ALTER TABLE `users` ADD `user_group` ENUM('member','staff') NOT NULL DEFAULT 'member' AFTER `password`, ADD INDEX (`user_group`);

update users set user_group = 'staff' where id in (45, 46, 47, 299);

------------


RUN nudity rating script on events

ALTER TABLE `users` ADD `view_sensitive_events` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `notification_sound`;
