/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wpforms_entry_meta`; */
/* PRE_TABLE_NAME: `1668940036_wp_wpforms_entry_meta`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wpforms_entry_meta` ( `id` bigint(20) NOT NULL AUTO_INCREMENT, `entry_id` bigint(20) NOT NULL, `form_id` bigint(20) NOT NULL, `user_id` bigint(20) NOT NULL, `status` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL, `type` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL, `data` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `date` datetime NOT NULL, PRIMARY KEY (`id`), KEY `entry_id` (`entry_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
