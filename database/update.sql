update users set body = lower(body);

update users set position = lower(position);

update users set hiv = lower(hiv);

update users set drugs = lower(drugs);


---


ALTER TABLE `users` CHANGE `height` `height` INT(11) NULL DEFAULT '0';

ALTER TABLE `users` CHANGE `weight` `weight` INT(11) NULL DEFAULT '0';


--



update`users` set created_at = created_at + interval 1 month WHEre year(created_at) = 2016;

update`users` set updated_at = updated_at + interval 1 month WHEre year(updated_at) = 2016;

update`users` set last_active = last_active + interval 1 month WHEre year(last_active) = 2016;

update`users` set last_login = last_login + interval 1 month WHEre year(last_login) = 2016;





CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lang` enum('en','de') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `meta_keywords` text COLLATE utf8_unicode_ci,
  `meta_description` text COLLATE utf8_unicode_ci,
  `is_required` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `pages` (`id`, `title`, `url`, `lang`, `content`, `meta_keywords`, `meta_description`, `is_required`, `created_at`, `updated_at`) VALUES
(3, 'Imprint', 'imprint', 'en', '<p>Your page content <strong>goes</strong> here imprint en</p>', NULL, NULL, 'no', '2018-05-06 20:20:54', '2018-05-06 20:39:29'),
(4, 'Imprint', 'imprint', 'de', '<p>Your page content goes <strong>here</strong> imprrint de</p>', NULL, NULL, 'no', '2018-05-06 20:21:19', '2018-05-06 20:39:35'),
(5, 'About us', 'about_us', 'en', '<p>Since its creation in 2001, <strong>Wikipedia</strong> has grown rapidly into one of the largest reference websites, attracting 374 million unique visitors monthly as of September 2015.[1] There are about 71,000 active contributors working on more than 47,000,000 articles in 299 languages. As of today, there are 5,644,778 articles in English. Every day, hundreds of thousands of visitors from around the world collectively make tens of thousands of edits and create thousands of new articles to augment the knowledge held by the Wikipedia encyclopedia. (See the statistics page for more information.) People of all ages, cultures and backgrounds can add or edit article prose, references, images and other media here. What is contributed is more important than the expertise or qualifications of the contributor. What will remain depends upon whether the content is free of copyright restrictions and contentious material about living people, and whether it fits within Wikipedia&#39;s policies, including being verifiable against a published reliable source, thereby excluding editors&#39; opinions and beliefs and unreviewed research. Contributions cannot damage Wikipedia because the software allows easy reversal of mistakes and many experienced editors are watching to help ensure that edits are cumulative improvements. Begin by simply clicking the Edit link at the top of any editable page!</p>\r\n\r\n<p><a href=\"http://wiki.com\">Wikipedia</a> is a live collaboration differing from paper-based reference sources in important ways. Unlike printed encyclopedias, Wikipedia is continually created and updated, with articles on historic events appearing within minutes, rather than months or years. Because everybody can help improve it, Wikipedia has become more comprehensive than any other encyclopedia. In addition to quantity, its contributors work on improving quality as well. Wikipedia is a work-in-progress, with articles in various stages of completion. As articles develop, they tend to become more comprehensive and balanced. Quality also improves over time as misinformation and other errors are removed or repaired. However, because anyone can click &quot;edit&quot; at any time and add stuff in, any article may contain undetected misinformation, errors, or vandalism. Awareness of this helps the reader to obtain valid information, avoid recently added misinformation (see Wikipedia:Researching with Wikipedia), and fix the article.</p>', NULL, NULL, 'no', '2018-05-06 20:53:30', '2018-05-07 19:42:13'),
(6, 'About us', 'about_us', 'de', '<p>Your page content goes here</p>', NULL, NULL, 'no', '2018-05-06 20:53:41', '2018-05-06 20:53:41'),
(7, 'Support', 'support', 'en', '<p>Your page content goes here</p>', NULL, NULL, 'no', '2018-05-06 20:53:54', '2018-05-06 20:53:54'),
(8, 'Support', 'support', 'de', '<p>Your page content goes here</p>', NULL, NULL, 'no', '2018-05-06 20:54:03', '2018-05-06 20:54:03'),
(9, 'Terms', 'terms', 'en', '<p>Your page content goes here</p>', NULL, NULL, 'no', '2018-05-06 20:54:15', '2018-05-06 20:54:15'),
(10, 'Terms', 'terms', 'de', '<p>Your page content goes here</p>', NULL, NULL, 'no', '2018-05-06 20:54:22', '2018-05-06 20:54:22'),
(11, 'Privacy', 'privacy', 'en', '<p>Your page content goes here</p>', NULL, NULL, 'no', '2018-05-06 20:54:32', '2018-05-06 20:54:32'),
(12, 'Privacy', 'privacy', 'de', '<p>Your page content goes here</p>', NULL, NULL, 'no', '2018-05-06 20:54:45', '2018-05-06 20:54:45');


ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url_2` (`url`,`lang`),
  ADD KEY `title` (`title`),
  ADD KEY `lang` (`lang`);


ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;






CREATE TABLE `page_revisions` (
  `id` int(11) NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `page_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);


ALTER TABLE `page_revisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

ALTER TABLE `page_revisions`
  ADD CONSTRAINT `page_revisions_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `page_revisions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;





CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `admins` (`id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Sergiu', 'styxyk@gmail.com', '$2y$10$e61ejWNQgCOydUxNMkHJA.x8VxJgJhpjbw2YPcDOLshqkvfoOm3XC', 'POUN6nwB0AS8FpbybRKfI571M8Xy6zXjxMYpCIsp3kknBu5M1mq41Luw7GMI', '2018-05-04 21:01:04', '2018-05-07 20:00:08'),
(2, 'Mike', 'lemaik@gmx.net', '$2y$10$JTxJO6cTkIqTwcSSwE3f8epb.Fzanhj3a8V2MSN7iOXhHK9UYItpe', NULL, '2018-05-04 21:01:04', NULL);


ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

