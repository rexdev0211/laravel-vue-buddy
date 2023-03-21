update users set country_code = 'RU' where country = 'Russia';
update users set country_code = 'EE' where country = 'Estonia';
update users set country_code = 'FR' where country = 'France';
update users set country_code = 'ES' where country = 'Spain' OR country = 'España' OR country = 'Espagne' OR country = 'Espanya';
update users set country_code = 'NL' where country = 'Netherlands' OR country = 'Nederland';
update users set country_code = 'LU' where country = 'Luxembourg';
update users set country_code = 'CH' where country = 'Switzerland';
update users set country_code = 'BE' where country = 'Belgium' OR country = 'België';
update users set country_code = 'IT' where country = 'Italy' OR country = 'Italia';
update users set country_code = 'US' where country = 'United States';
update users set country_code = 'DE' where country = 'Alemania' OR country = 'Germania';

update users set country_code = 'FR' where country = 'Frankreich';
update users set country_code = 'FR' where country = 'Frankrijk';
update users set country_code = 'DE' where country = 'Duitsland';
update users set country_code = 'AU' where country = 'Australia';
update users set country_code = 'AU' where country = '澳洲';
update users set country_code = 'AT' where country = 'Austria';
update users set country_code = 'AT' where country = 'Österreich';
update users set country_code = 'GB' where country = 'United Kingdom';
update users set country_code = 'GB' where country = 'Reino Unido';
update users set country_code = 'GB' where country = 'Verenigd Koninkrijk';
update users set country_code = 'CA' where country = 'Canada';
update users set country_code = 'CN' where country = '中国';

update users set country_code = 'DK' where country = 'Denmark';
update users set country_code = 'DK' where country = 'Danmark';
update users set country_code = 'CZ' where country = 'Czechia';
update users set country_code = 'HU' where country = 'Hungary';
update users set country_code = 'IT' where country = 'Italien';
update users set country_code = 'NO' where country = 'Norway';
update users set country_code = 'NO' where country = 'Norge';
update users set country_code = 'DE' where country = 'Allemagne';
update users set country_code = 'CH' where country = 'Schweiz';
update users set country_code = 'BE' where country = 'Belgique';
update users set country_code = 'SE' where country = 'Sweden';
update users set country_code = 'TR' where country = 'Turkey';
update users set country_code = 'TR' where country = 'Türkiye';
update users set country_code = 'BG' where country = 'Bulgarien';
update users set country_code = 'BG' where country = 'Bulgaria';
update users set country_code = 'CZ' where country = 'Česko';
update users set country_code = 'HR' where country = 'Kroatien';
update users set country_code = 'GR' where country = 'Griechenland';

update users set country_code = 'MX' where country = 'Mexico';
update users set country_code = 'CN' where country = 'China';
update users set country_code = 'PL' where country = 'Poland';
update users set country_code = 'LV' where country = 'Latvia';
update users set country_code = 'SI' where country = 'Slovenia';
update users set country_code = 'ZA' where country = 'South Africa';
update users set country_code = 'GB' where country = 'Storbritannia';
update users set country_code = 'AR' where country = 'Argentina';
update users set country_code = 'BR' where country = 'Brazil';
update users set country_code = 'PT' where country = 'Portugal';
update users set country_code = 'HR' where country = 'Croatia';
update users set country_code = 'AE' where country = 'United Arab Emirates';
update users set country_code = 'MY' where country = 'Malaysia';
update users set country_code = 'IN' where country = 'India';
update users set country_code = 'IE' where country = 'Ireland';
update users set country_code = 'PH' where country = 'Philippines';
update users set country_code = 'LK' where country = 'Sri Lanka';
update users set country_code = 'JP' where country = 'Japan';
update users set country_code = 'GR' where country = 'Greece';
update users set country_code = 'UA' where country = 'Ukraine';
update users set country_code = 'IL' where country = 'Israel';
update users set country_code = 'TW' where country = '台灣';
update users set country_code = 'FI' where country = 'Finland';
update users set country_code = 'DO' where country = 'Dominican Republic';
update users set country_code = 'EG' where country = 'Egypt';
update users set country_code = 'HK' where country = 'Hong Kong';
update users set country_code = 'NL' where country = 'Niederlande';
update users set country_code = 'ES' where country = 'Spanien';
update users set country_code = 'NZ' where country = 'New Zealand';
update users set country_code = 'SK' where country = 'Slovensko';
update users set country_code = 'SG' where country = 'Singapore';
update users set country_code = 'TH' where country = 'Thailand';
update users set country_code = 'TW' where country = 'Taiwan';
update users set country_code = 'BY' where country = 'Беларусь';
update users set country_code = 'GG' where country = 'Guernsey';
update users set country_code = 'FI' where country = 'Suomi';
update users set country_code = 'HK' where country = '香港';
update users set country_code = 'CH' where country = 'Suisse';
update users set country_code = 'GB' where country = 'Vereinigtes Königreich';
update users set country_code = 'NG' where country = 'Nigeria';
update users set country_code = 'MU' where country = 'Mauritius';
update users set country_code = 'BR' where country = 'Brasil';
update users set country_code = 'TH' where country = 'Thaïlande';
update users set country_code = 'DE' where country = '德国';
update users set country_code = 'MY' where country = 'Malaysia';

update users set country_code = 'BQ' where country = 'Caribbean Netherlands';


--------------------

-- NEED ALSO TO UPDATE country BY country_code TO ENGLISH IN THE END FOR THOSE WHICH LOOK STRANGE

update users set country = 'Spain' where country = 'España';
update users set country = 'Spain' where country = 'Espagne';
update users set country = 'Spain' where country = 'Espanya';
update users set country = 'Belgium' where country = 'België';
update users set country = 'Germany' where country = 'Alemania';
update users set country = 'Germany' where country = 'Germania';
update users set country = 'France' where country = 'Frankreich';
update users set country = 'France' where country = 'Frankrijk';
update users set country = 'Germany' where country = 'Duitsland';
update users set country = 'Austria' where country = 'Österreich';
update users set country = 'United Kingdom' where country = 'Reino Unido';
update users set country = 'United Kingdom' where country = 'Verenigd Koninkrijk';
update users set country = 'Czech Republic' where country = 'Czechia';
update users set country = 'Italy' where country = 'Italien';
update users set country = 'Norway' where country = 'Norge';
update users set country = 'Germany' where country = 'Allemagne';
update users set country = 'Switzerland' where country = 'Schweiz';
update users set country = 'Belgium' where country = 'Belgique';
update users set country = 'Turkey' where country = 'Türkiye';
update users set country = 'Bulgaria' where country = 'Bulgarien';
update users set country = 'Czech Republic' where country = 'Česko';
update users set country = 'Croatia' where country = 'Kroatien';
update users set country = 'Greece' where country = 'Griechenland';
update users set country = 'United Kingdom' where country = 'Storbritannia';
update users set country = 'Netherlands' where country = 'Niederlande';
update users set country = 'Spain' where country = 'Spanien';
update users set country = 'Slovakia' where country = 'Slovensko';
update users set country = 'Belarus' where country = 'Беларусь';
update users set country = 'Finland' where country = 'Suomi';
update users set country = 'Switzerland' where country = 'Suisse';
update users set country = 'United Kingdom' where country = 'Vereinigtes Königreich';
update users set country = 'Thailand' where country = 'Thaïlande';
update users set country = 'Australia' where country = '澳洲';
update users set country = 'China' where country = '中国';
update users set country = 'Taiwan' where country = '台灣';
update users set country = 'Hong Kong' where country = '香港';
update users set country = 'Germany' where country = '德国';

update users set country = 'Brazil' where country = 'Brasil';
update users set country = 'Germany' where country = 'Deutschland';
update users set country = 'Denmark' where country = 'Danmark';
update users set country = 'Italy' where country = 'Italia';
update users set country = 'Netherlands' where country = 'Nederland';




------------------




ALTER TABLE `users` ADD `address_lat` DECIMAL(11,8) NOT NULL AFTER `address`,
  ADD `address_lng` DECIMAL(11,8) NOT NULL AFTER `address_lat`;


update users set address_lat = lat;

update users set address_lng = lng;



ALTER TABLE `users` CHANGE `address_lat` `address_lat` DECIMAL(11,8) NULL DEFAULT NULL;
ALTER TABLE `users` CHANGE `address_lng` `address_lng` DECIMAL(11,8) NULL DEFAULT NULL;




SELECT * FROM `users` WHERE address_lat is null or address_lat = '';







-------------------------

-- we will need to put app in maintenance mode to apply all these modifications



ALTER TABLE `users` ADD `email_reminders` ENUM('daily','weekly','monthly','never') NOT NULL DEFAULT 'daily' AFTER `unit_system`, ADD INDEX (`email_reminders`);


ALTER TABLE `users` CHANGE `country_code` `country_code` VARCHAR(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `tags` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

CREATE TABLE `email_template_langs` (
  `id` int(11) NOT NULL,
  `email_template_id` int(11) NOT NULL,
  `lang` enum('en','de','fr') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `email_template_langs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `email_template_langs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_template_langs` ADD INDEX(`email_template_id`);







insert into `email_template_langs` (email_template_id, lang, subject, body) select id, lang, subject, body from email_templates;

update `email_template_langs` set email_template_id = (select id from email_templates where lang = 'en' and name = (select name from email_templates where id = email_template_id));

delete from email_templates where lang <> 'en';

ALTER TABLE `email_templates` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_template_langs` ADD  CONSTRAINT `fk_etl_etid` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `email_templates` DROP `subject`;

ALTER TABLE `email_templates` DROP `lang`;

ALTER TABLE `email_templates` DROP `body`;

UPDATE `email_templates` SET `sort_order` = '2' WHERE `email_templates`.`name` = 'forgot_password';

INSERT INTO `email_templates` (`id`, `name`, `notes`, `sort_order`, `created_at`, `updated_at`) VALUES
(NULL, 'daily_reminders', 'Sent to: User\r\nInfo: send daily reminders of unread messages\r\nVariables: {FULL_NAME}, {EMAIL}, {MESSAGES_COUNT}', '3', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(NULL, 'weekly_reminders', 'Sent to: User\r\nInfo: send weekly reminders of unread messages\r\nVariables: {FULL_NAME}, {EMAIL}, {MESSAGES_COUNT}', '4', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(NULL, 'monthly_reminders', 'Sent to: User\r\nInfo: send monthly reminders of unread messages\r\nVariables: {FULL_NAME}, {EMAIL}, {MESSAGES_COUNT}', '5', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(NULL, 'monthly_login_reminder', 'Sent to: User\r\nInfo: send monthly reminders when there are no messages\r\nVariables: {FULL_NAME}, {EMAIL}', '6', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

ALTER TABLE `email_template_langs` ADD UNIQUE( `email_template_id`, `lang`);

ALTER TABLE `users` ADD `language` ENUM('en','de','fr') NULL DEFAULT NULL AFTER `dob`;

ALTER TABLE `users` ADD `login_reminder_sent` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `last_login`, ADD INDEX (`login_reminder_sent`);

update users set login_reminder_sent = created_at;

ALTER TABLE `users` ADD `email_validation` ENUM('bounce','delivery') NULL DEFAULT NULL AFTER `email`, ADD INDEX (`email_validation`);






IMPORT COUNTRIES TABLE



SELECT DISTINCT country from users where country not in (select name from countries);





-- ALTER TABLE `users` CHANGE `country` `country_bak` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;


INSERT INTO `email_template_langs` (`email_template_id`, `lang`, `subject`, `body`, `created_at`, `updated_at`)
select id, 'en', 'Another BareBuddy has sent you a message', '<p>Hey {FULL_NAME},<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>you have received new messages in your BareBuddy mailbox.<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>To check your messages, please login to&nbsp;<a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com&amp;source=gmail&amp;ust=1536435960276000&amp;usg=AFQjCNFMRgN_gD1hJQsH8eAPFqTjmFzzkw\" href=\"https://barebuddy.com/\" target=\"_blank\">https://barebuddy.com</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>If you don&#39;t remember your password, go here to have it reset:<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com/recover-password&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNFPrmXcBZEXb867Az_Mkv8qSsizWw\" href=\"https://barebuddy.com/recover-password\" target=\"_blank\">https://barebuddy.com/recover-<wbr />password</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>Welcum back!!<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>----<u></u><u></u></p>\r\n\r\n<p>Your BareBuddy Team<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNGOUOpNlT25gUGVdM5CtjvPJKnQVg\" href=\"https://barebuddy.com/\" target=\"_blank\">https://barebuddy.com</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>PS: if you do not want to be emailed about incoming messages, or if you want to<u></u><u></u></p>\r\n\r\n<p>reduce the frequency, please go to your Profile and select &quot;Settings&quot;.</p>', '2018-09-07 19:54:52', '2018-09-07 19:54:52'
from email_templates where name = 'daily_reminders';

INSERT INTO `email_template_langs` (`email_template_id`, `lang`, `subject`, `body`, `created_at`, `updated_at`)
select id, 'en', 'Another BareBuddy has sent you a message', '<p>Hey {FULL_NAME},<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>you have received new messages in your BareBuddy mailbox.<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>To check your messages, please login to&nbsp;<a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com&amp;source=gmail&amp;ust=1536435960276000&amp;usg=AFQjCNFMRgN_gD1hJQsH8eAPFqTjmFzzkw\" href=\"https://barebuddy.com/\" target=\"_blank\">https://barebuddy.com</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>If you don&#39;t remember your password, go here to have it reset:<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com/recover-password&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNFPrmXcBZEXb867Az_Mkv8qSsizWw\" href=\"https://barebuddy.com/recover-password\" target=\"_blank\">https://barebuddy.com/recover-<wbr />password</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>Welcum back!!<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>----<u></u><u></u></p>\r\n\r\n<p>Your BareBuddy Team<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNGOUOpNlT25gUGVdM5CtjvPJKnQVg\" href=\"https://barebuddy.com/\" target=\"_blank\">https://barebuddy.com</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>PS: if you do not want to be emailed about incoming messages, or if you want to<u></u><u></u></p>\r\n\r\n<p>reduce the frequency, please go to your Profile and select &quot;Settings&quot;.</p>', '2018-09-07 19:54:52', '2018-09-07 19:54:52'
from email_templates where name = 'weekly_reminders';

INSERT INTO `email_template_langs` (`email_template_id`, `lang`, `subject`, `body`, `created_at`, `updated_at`)
select id, 'en', 'Another BareBuddy has sent you a message', '<p>Hey {FULL_NAME},<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>you have received new messages in your BareBuddy mailbox.<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>To check your messages, please login to&nbsp;<a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com&amp;source=gmail&amp;ust=1536435960276000&amp;usg=AFQjCNFMRgN_gD1hJQsH8eAPFqTjmFzzkw\" href=\"https://barebuddy.com/\" target=\"_blank\">https://barebuddy.com</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>If you don&#39;t remember your password, go here to have it reset:<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com/recover-password&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNFPrmXcBZEXb867Az_Mkv8qSsizWw\" href=\"https://barebuddy.com/recover-password\" target=\"_blank\">https://barebuddy.com/recover-<wbr />password</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>Welcum back!!<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>----<u></u><u></u></p>\r\n\r\n<p>Your BareBuddy Team<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNGOUOpNlT25gUGVdM5CtjvPJKnQVg\" href=\"https://barebuddy.com/\" target=\"_blank\">https://barebuddy.com</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>PS: if you do not want to be emailed about incoming messages, or if you want to<u></u><u></u></p>\r\n\r\n<p>reduce the frequency, please go to your Profile and select &quot;Settings&quot;.</p>', '2018-09-07 19:54:52', '2018-09-07 19:54:52'
from email_templates where name = 'monthly_reminders';

INSERT INTO `email_template_langs` (`email_template_id`, `lang`, `subject`, `body`, `created_at`, `updated_at`)
select id, 'en', 'BareBuddy is missing you!', '<p>Hey {FULL_NAME}!<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>it&#39;s been a while since you last logged in to BareBuddy - and you know what? We&#39;re really missing you!<u></u><u></u></p>\r\n\r\n<p>A lot of new members have signed up since your last visit. Cum back and check them out!<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>Now it&#39;s easier than ever to login - with our new BareBuddy App. It&#39;s not in the App-Store (cuz we show nude pictures), but you can install it like this:<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://blog.barebuddy.com/en/the-barebuddy-iphone-app/&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNF2akWizVX-Aq3Lq5LwPdkE-hmt_w\" href=\"https://blog.barebuddy.com/en/the-barebuddy-iphone-app/\" target=\"_blank\">https://blog.barebuddy.com/en/<wbr />the-barebuddy-iphone-app/</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>If you don&#39;t remember your password, go here to have it reset:<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com/recover-password&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNFPrmXcBZEXb867Az_Mkv8qSsizWw\" href=\"https://barebuddy.com/recover-password\" target=\"_blank\">https://barebuddy.com/recover-<wbr />password</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>Welcum back!!<u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>----<u></u><u></u></p>\r\n\r\n<p>Your BareBuddy Team<u></u><u></u></p>\r\n\r\n<p><a data-saferedirecturl=\"https://www.google.com/url?q=https://barebuddy.com&amp;source=gmail&amp;ust=1536435960277000&amp;usg=AFQjCNGOUOpNlT25gUGVdM5CtjvPJKnQVg\" href=\"https://barebuddy.com/\" target=\"_blank\">https://barebuddy.com</a><u></u><u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p><u></u>&nbsp;<u></u></p>\r\n\r\n<p>PS: if you do not want to be emailed about incoming messages, or if you want to<u></u><u></u></p>\r\n\r\n<p>reduce the frequency, please go to your Profile and select &quot;Settings&quot;.</p>', '2018-09-07 19:56:22', '2018-09-07 19:56:22'
from email_templates where name = 'monthly_login_reminder';


ALTER TABLE `users` ADD `has_unread_messages` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `password`;

update users set has_unread_messages = 'yes' where exists (SELECT distinct user_to FROM `messages` WHERE is_read = 'no' and user_to = users.id);

ALTER TABLE `users` ADD INDEX(`state`);

ALTER TABLE `users` ADD INDEX(`locality`);

ALTER TABLE `users` ADD `subscribed` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `email_reminders`, ADD INDEX (`subscribed`);





CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `language` enum('en','de','fr') DEFAULT 'en',
  `country_code` varchar(9) DEFAULT NULL,
  `subscribed` enum('yes','no') NOT NULL DEFAULT 'yes',
  `email_validation` enum('bounce','delivery') DEFAULT NULL,
  `hash_key` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `subscribed` (`subscribed`),
  ADD KEY `language` (`language`),
  ADD KEY `hash_key` (`hash_key`),
  ADD KEY `country` (`country_code`),
  ADD KEY `email_validation` (`email_validation`);

ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



ALTER TABLE `newsletter` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;




insert into newsletter (user_id, name, email, language, country_code, subscribed, email_validation, hash_key)
select id, name, email, language, country_code, subscribed, email_validation, SHA1(RAND()) from users where email is not null;


insert IGNORE into newsletter (user_id, name, email, language, country_code, subscribed, email_validation, hash_key)
select id, name, email_orig, language, country_code, subscribed, email_validation, SHA1(RAND()) from users where email is null;



ALTER TABLE `users_deleted` ADD `email` VARCHAR(255) DEFAULT NULL AFTER `name`;





--------------------------



WE ALSO HAVE undefined FOR country, WHY???

select id, name, email, address, lat, lng, last_active, created_at, viewed_other_profiles, time_spent_online, last_login from users where country = 'undefined';

select distinct country, country_code from users where country_code is null or country_code = '';

select * from (select distinct country, country_code from users) as tmp1 join (select distinct country, country_code from users) as tmp2 on tmp1.country_code = tmp2.country_code where tmp1.country < tmp2.country;

select distinct country, country_code from users order by country_code;



-----------------------


update users set email_validation = 'delivery';

update newsletter set email_validation = 'delivery';

ALTER TABLE `users` CHANGE `email_validation` `email_validation` ENUM('bounce','delivery') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'delivery';

ALTER TABLE `newsletter` CHANGE `email_validation` `email_validation` ENUM('bounce','delivery') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'delivery';

update users set email_validation = 'bounce' where email in (
  'soivendu@gmail.com', 'styxyk4-non-lu@yahoo.com', 'styxyk3-non-lu@yahoo.com', 'BerlinBalu@web.de', 'helurebi@trimsj.com', 'cumslut@mail.de', 'pasttense@comcast.net', 'b4603472@nwytg.net', 'a6097926@nwytg.net', 'hornyslut4rawloads@outlook.com', 'romaial@gmail.com', 'versncdude@yahoo.com', 'sommer@cuvox.de', 'hundkatzemauselefant@partyheld.de', 'guyblackforest@web.de', 'abhufen@web.de',
  'MAXOU750166@yahoo.com', 'm.m.arz@hotmail.com', 'vitobotraro@yahoo.de', 'frnk.bloching@gmx.net', 'styxyk2@yahoo.com', '007@gmail.com', 'blackforestguy@web.de', '235356643@qq.com',
  'markosnff@yahoo.com', 'Nein@rtl.de', 'troubleforyoy112@gmail.com', 'llt33@hotmail.com'
);

update newsletter set email_validation = 'bounce' where email in (
  'soivendu@gmail.com', 'styxyk4-non-lu@yahoo.com', 'styxyk3-non-lu@yahoo.com', 'BerlinBalu@web.de', 'helurebi@trimsj.com', 'cumslut@mail.de', 'pasttense@comcast.net', 'b4603472@nwytg.net', 'a6097926@nwytg.net', 'hornyslut4rawloads@outlook.com', 'romaial@gmail.com', 'versncdude@yahoo.com', 'sommer@cuvox.de', 'hundkatzemauselefant@partyheld.de', 'guyblackforest@web.de', 'abhufen@web.de',
  'MAXOU750166@yahoo.com', 'm.m.arz@hotmail.com', 'vitobotraro@yahoo.de', 'frnk.bloching@gmx.net', 'styxyk2@yahoo.com', '007@gmail.com', 'blackforestguy@web.de', '235356643@qq.com',
  'markosnff@yahoo.com', 'Nein@rtl.de', 'troubleforyoy112@gmail.com', 'llt33@hotmail.com'
);

update users set language = 'de' where country_code = 'DE' and language is null;

update newsletter set language = 'de' where country_code = 'DE' and language is null;


