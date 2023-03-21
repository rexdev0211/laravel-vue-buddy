
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` enum('wave') NOT NULL,
  `sub_type` varchar(50) DEFAULT NULL,
  `user_from` int(11) DEFAULT NULL,
  `user_to` int(11) NOT NULL,
  `is_read` enum('yes','no') NOT NULL DEFAULT 'no',
  `idate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `sub_type` (`sub_type`),
  ADD KEY `user_from` (`user_from`),
  ADD KEY `user_to` (`user_to`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `idate` (`idate`);

ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notif-fk-user_from` FOREIGN KEY (`user_from`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notif-fk-user_to` FOREIGN KEY (`user_to`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;



ALTER TABLE `pages` CHANGE `lang` `lang` ENUM('en','de','fr') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en';


ALTER TABLE `messages` ADD INDEX( `user_from`, `deleted_for_user_from`);
ALTER TABLE `messages` ADD INDEX( `user_to`, `deleted_for_user_to`);







-------------------
-- START FROM HERE -->
-------------------







CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `event_date` date NOT NULL,
  `time` varchar(50) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `locality` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `country_code` varchar(9) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `lat` decimal(11,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `gps_geom` point DEFAULT NULL,
  `likes` int(11) DEFAULT NULL,
  `event_type` enum('private','public') NOT NULL,
  `address_type` enum('full_address','city_only') NOT NULL DEFAULT 'full_address',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`event_date`),
  ADD KEY `lat` (`lat`),
  ADD KEY `lng` (`lng`),
  ADD KEY `is_active` (`status`);

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `events`
  ADD CONSTRAINT `fk_ev_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;






CREATE TABLE `event_user_photo` (
  `event_id` int(11) NOT NULL,
  `user_photo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `event_user_photo`
  ADD UNIQUE KEY `event_id` (`event_id`,`user_photo_id`),
  ADD KEY `photo_id` (`user_photo_id`);

ALTER TABLE `event_user_photo`
  ADD CONSTRAINT `fk_phev_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phev_photo_id` FOREIGN KEY (`user_photo_id`) REFERENCES `user_photos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;








CREATE TABLE `event_likes` (
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `event_likes`
  ADD UNIQUE KEY `user_id` (`user_id`,`event_id`) USING BTREE,
  ADD KEY `event_id` (`event_id`);

ALTER TABLE `event_likes`
  ADD CONSTRAINT `fk_eve_like_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eve_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;







ALTER TABLE `notifications` CHANGE `type` `type` ENUM('wave','event') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `notifications` ADD `event_id` INT NULL AFTER `user_to`, ADD INDEX (`event_id`);
ALTER TABLE `notifications` ADD CONSTRAINT `notif-fk-event_id` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;





-----------





















ALTER TABLE `user_reported_map` ADD INDEX(`idate`);


---





CREATE TABLE `event_tags_map` (
  `event_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `event_tags_map`
  ADD UNIQUE KEY `event_id` (`event_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

ALTER TABLE `event_tags_map`
  ADD CONSTRAINT `event_tags_fk_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `event_tags_fk_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;





ALTER TABLE `event_user_photo` ADD `is_default` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `user_photo_id`, ADD INDEX (`is_default`);







--   `interlocutor_id` int(11) NOT NULL,



CREATE TABLE `event_messages` (
  `id` int(11) NOT NULL,
  `user_from` int(11) DEFAULT NULL,
  `user_to` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `msg_type` enum('text','image','location') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `image_id` int(11) DEFAULT NULL,
  `is_read` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `deleted_for_user_from` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `deleted_for_user_to` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `idate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `event_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `public_event_messages_idate4_idx` (`idate`),
  ADD KEY `public_event_messages_is_read3_idx` (`is_read`),
  ADD KEY `public_event_messages_user_from1_idx` (`user_from`),
  ADD KEY `public_event_messages_user_to2_idx` (`user_to`),
  ADD KEY `image_id` (`image_id`),
  ADD KEY `deleted_for_user_from` (`deleted_for_user_from`),
  ADD KEY `deleted_for_user_to` (`deleted_for_user_to`),
  ADD KEY `user_from` (`user_from`,`deleted_for_user_from`),
  ADD KEY `user_to` (`user_to`,`deleted_for_user_to`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `owner_id` (`owner_id`);

ALTER TABLE `event_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `event_messages`
  ADD CONSTRAINT `event_messages_event_id_fkey` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `event_messages_image_id_fkey` FOREIGN KEY (`image_id`) REFERENCES `user_photos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `event_messages_owner_id_fkey` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;





ALTER TABLE `users` CHANGE `has_unread_messages` `has_unseen_notices` ENUM('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no';

ALTER TABLE `users` ADD `has_unseen_user_messages` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `has_unseen_notices`;

ALTER TABLE `users` ADD `has_unseen_event_messages` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `has_unseen_user_messages`;















----

ALTER TABLE `events` CHANGE `title` `title` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `events` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `events` CHANGE `address` `address` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `events` CHANGE `time` `time` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;











