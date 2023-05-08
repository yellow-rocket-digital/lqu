/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wpforms_entries`; */
/* PRE_TABLE_NAME: `1668940036_wp_wpforms_entries`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wpforms_entries` ( `entry_id` bigint(20) NOT NULL AUTO_INCREMENT, `form_id` bigint(20) NOT NULL, `post_id` bigint(20) NOT NULL, `user_id` bigint(20) NOT NULL, `status` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL, `type` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL, `viewed` tinyint(1) DEFAULT '0', `starred` tinyint(1) DEFAULT '0', `fields` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `meta` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `date` datetime NOT NULL, `date_modified` datetime NOT NULL, `ip_address` varchar(128) COLLATE utf8mb4_unicode_520_ci NOT NULL, `user_agent` varchar(256) COLLATE utf8mb4_unicode_520_ci NOT NULL, `user_uuid` varchar(36) COLLATE utf8mb4_unicode_520_ci NOT NULL, PRIMARY KEY (`entry_id`), KEY `form_id` (`form_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
