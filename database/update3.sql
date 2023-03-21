
ALTER TABLE `users` ADD `locality` VARCHAR(100) NULL DEFAULT NULL AFTER `address`,
ADD `state` VARCHAR(100) NULL DEFAULT NULL AFTER `locality`,
ADD `country` VARCHAR(100) NULL DEFAULT NULL AFTER `state`;




ALTER TABLE `messages` CHANGE `message` `message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;




ALTER TABLE `users` ADD `country_code` VARCHAR(2) NOT NULL AFTER `country`, ADD INDEX (`country_code`);
ALTER TABLE `users` ADD INDEX(`locality`);



ALTER TABLE `users` CHANGE `country_code` `country_code` VARCHAR(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;



https://maps.googleapis.com/maps/api/geocode/json?latlng=50.94645164,6.81561521&sensor=false&key=AIzaSyAYVQzIZsOLu1NDJrVPqK9jx6uA4-c_bMo


update users set country_code = 'DE' where country = 'Germany';
update users set country_code = 'DE' where country = 'Deutschland';
update users set country_code = 'DE' where country = 'Almanya';

select distinct country, country_code from users where country_code is null or country_code = '';


ALTER TABLE `email_templates` CHANGE `lang` `lang` ENUM('en','de','fr') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en';

INSERT INTO `email_templates` (`id`, `name`, `subject`, `lang`, `body`, `notes`, `sort_order`, `created_at`, `updated_at`)
        VALUES (NULL, 'user_registration', '', 'fr', '', 'Sent to: User\r\nInfo: send welcome email to User on signup\r\nVariables: {FULL_NAME}, {EMAIL}, {PASSWORD}', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
              (NULL, 'forgot_password', '', 'fr', '', 'Sent to: User\r\nInfo: send Reset Password link to the user\'s email\r\nVariables: {FULL_NAME}, {RESET_LINK}', '5', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);




