
CREATE TABLE `jobs_bak` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `jobs_bak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`);

ALTER TABLE `jobs_bak`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL;







insert into jobs_bak SELECT * FROM `jobs` WHERE payload like '%ProcessNewVideo%';


delete from jobs where id in (select id from jobs_bak);







update jobs set queue = 'videos-queue' where payload like '%ProcessNewVideo%';