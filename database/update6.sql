


upload default photo for thumbs





CREATE TABLE `user_videos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_name` varchar(255) NOT NULL,
  `orig_extension` varchar(10) NOT NULL,
  `base_name_bak` varchar(200) DEFAULT NULL,
  `video_path_bak` varchar(250) DEFAULT NULL,
  `thumb_path_bak` varchar(255) DEFAULT NULL,
  `visible_to` enum('public','private') NOT NULL DEFAULT 'private',
  `status` enum('waiting','processing','processed') DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `user_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `up_user_id` (`user_id`),
  ADD KEY `visible` (`visible_to`),
  ADD KEY `status` (`status`);

ALTER TABLE `user_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_videos`
  ADD CONSTRAINT `user_video_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;








ALTER TABLE `messages` CHANGE `msg_type` `msg_type` ENUM('text','image','location','video') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text';

ALTER TABLE `messages` ADD `video_id` INT NULL DEFAULT NULL AFTER `image_id`, ADD INDEX (`video_id`);

ALTER TABLE `messages` ADD CONSTRAINT `messages_video_id_fkey` FOREIGN KEY (`video_id`) REFERENCES `user_videos`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;



ALTER TABLE `event_messages` CHANGE `msg_type` `msg_type` ENUM('text','image','location','video') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text';

ALTER TABLE `event_messages` ADD `video_id` INT NULL DEFAULT NULL AFTER `image_id`, ADD INDEX (`video_id`);

ALTER TABLE `event_messages` ADD CONSTRAINT `event_messages_video_id_fkey` FOREIGN KEY (`video_id`) REFERENCES `user_videos`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;






CREATE TABLE `event_user_video` (
  `event_id` int(11) NOT NULL,
  `user_video_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `event_user_video`
  ADD UNIQUE KEY `event_id` (`event_id`,`user_video_id`),
  ADD KEY `photo_id` (`user_video_id`);

ALTER TABLE `event_user_video`
  ADD CONSTRAINT `fk_videv_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_videv_photo_id` FOREIGN KEY (`user_video_id`) REFERENCES `user_videos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;






ALTER TABLE `messages` DROP FOREIGN KEY `messages_image_id_fkey`;
ALTER TABLE `messages` ADD CONSTRAINT `messages_image_id_fkey` FOREIGN KEY (`image_id`) REFERENCES `user_photos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `messages` DROP FOREIGN KEY `messages_video_id_fkey`;
ALTER TABLE `messages` ADD CONSTRAINT `messages_video_id_fkey` FOREIGN KEY (`video_id`) REFERENCES `user_videos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;



ALTER TABLE `event_messages` DROP FOREIGN KEY `event_messages_image_id_fkey`;
ALTER TABLE `event_messages` ADD CONSTRAINT `event_messages_image_id_fkey` FOREIGN KEY (`image_id`) REFERENCES `user_photos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `event_messages` DROP FOREIGN KEY `event_messages_video_id_fkey`;
ALTER TABLE `event_messages` ADD CONSTRAINT `event_messages_video_id_fkey` FOREIGN KEY (`video_id`) REFERENCES `user_videos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;



ALTER TABLE `users` ADD INDEX(`has_unseen_user_messages`);
ALTER TABLE `users` ADD INDEX(`has_unseen_event_messages`);
ALTER TABLE `users` ADD INDEX(`has_unseen_notices`);



ALTER TABLE `events` CHANGE `description` `description` VARCHAR(510) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

