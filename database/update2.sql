ALTER TABLE `users` ADD `status` ENUM('active','suspended') NOT NULL DEFAULT 'active' AFTER `last_login`, ADD INDEX (`status`);


ALTER TABLE `user_reported_map` ADD `report_type` ENUM('harassment', 'fake', 'spam', 'under_age', 'other') NOT NULL DEFAULT 'harassment' AFTER `user_reported_id`, ADD INDEX (`report_type`);

ALTER TABLE `user_reported_map` DROP INDEX `user_id`, ADD UNIQUE `user_id` (`user_id`, `user_reported_id`, `report_type`) USING BTREE;

ALTER TABLE `user_reported_map` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);






ALTER TABLE page_revisions DROP FOREIGN KEY page_revisions_ibfk_2;








php artisan queue:table

php artisan migrate




CREATE TABLE `email_templates` (
  `id` bigint(20) NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lang` enum('en','de') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `email_templates` (`id`, `name`, `subject`, `lang`, `body`, `notes`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'user_registration', 'Welcome to BareBuddy', 'en', '<p>Dear {FULL_NAME},</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Welcome to BareBuddy. Below you can find your account details:</p>\r\n\r\n<p>Username: {EMAIL}</p>\r\n\r\n<p>Password: {PASSWORD}</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Best regards,</p>\r\n\r\n<p>BareBuddy.com</p>', 'Sent to: User\r\nInfo: send welcome email to User on signup\r\nVariables: {FULL_NAME}, {EMAIL}, {PASSWORD}', 1, '2018-06-06 18:27:10', '2018-06-07 18:01:11'),
(2, 'forgot_password', 'Password Reset for BareBuddy Account', 'en', '<p>Dear {FULL_NAME},</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>We have received a request to reset the password for your account.</p>\r\n\r\n<p>To do this please open the link in your browser: {RESET_LINK}</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Best regards,</p>\r\n\r\n<p>BareBuddy.com</p>', 'Sent to: User\r\nInfo: send Reset Password link to the user\'s email\r\nVariables: {FULL_NAME}, {RESET_LINK}', 5, '2018-06-06 18:27:37', '2018-06-07 18:01:15'),
(8, 'user_registration', 'Hi', 'de', 'Welcome', 'Sent to: User\r\nInfo: send welcome email to User on signup\r\nVariables: {FULL_NAME}, {EMAIL}, {PASSWORD}', 1, '2018-06-06 18:22:18', '2018-06-07 18:01:05'),
(9, 'forgot_password', 'Password reset - German version', 'de', '<p>Liebe {FULL_NAME},</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>We have received a request to reset the password for your account.</p>\r\n\r\n<p>To do this please open the link in your browser: {RESET_LINK}</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Best regards,</p>\r\n\r\n<p>BareBuddy.com</p>', 'Sent to: User\r\nInfo: send Reset Password link to the user\'s email\r\nVariables: {FULL_NAME}, {RESET_LINK}', 5, '2018-06-06 18:22:39', '2018-06-06 15:29:54');


ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ac_comm_abbr` (`subject`),
  ADD KEY `ac_area` (`name`);


ALTER TABLE `email_templates`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;


ALTER TABLE `users` ADD `email_orig` VARCHAR(255) NULL AFTER `email`;

ALTER TABLE `users` ADD `deleted_at` DATETIME NULL DEFAULT NULL AFTER `status`;

ALTER TABLE `users` ADD INDEX(`deleted_at`);


ALTER TABLE `messages` CHANGE `user_from` `user_from` INT(11) NULL;

ALTER TABLE `messages` CHANGE `user_to` `user_to` INT(11) NULL;


-- do we need this? NO
-- ALTER TABLE `messages` ADD CONSTRAINT `messages_user_from_fkey` FOREIGN KEY (`user_from`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
-- ALTER TABLE `messages` ADD CONSTRAINT `messages_user_to_fkey` FOREIGN KEY (`user_to`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE `messages` DROP FOREIGN KEY `messages_user_from_fkey`;
ALTER TABLE `messages` DROP FOREIGN KEY `messages_user_to_fkey`;

ALTER TABLE `messages` DROP FOREIGN KEY `messages_image_id_fkey`;
ALTER TABLE `messages` ADD CONSTRAINT `messages_image_id_fkey` FOREIGN KEY (`image_id`) REFERENCES `user_photos`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;



CREATE TABLE `users_deleted` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `users_deleted`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `users` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;


test that image missing work

test that works with and w/o www







ALTER TABLE `messages` ADD `deleted_for_user_from` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `is_read`, ADD `deleted_for_user_to` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `deleted_for_user_from`;

ALTER TABLE `messages` ADD INDEX(`deleted_for_user_from`);

ALTER TABLE `messages` ADD INDEX(`deleted_for_user_to`);

