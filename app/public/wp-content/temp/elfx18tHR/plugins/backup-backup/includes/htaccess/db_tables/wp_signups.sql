/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_signups`; */
/* PRE_TABLE_NAME: `1668940036_wp_signups`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_signups` ( `signup_id` bigint(20) NOT NULL AUTO_INCREMENT, `domain` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `path` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `title` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', `activated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', `active` tinyint(1) NOT NULL DEFAULT '0', `activation_key` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `meta` longtext COLLATE utf8mb4_unicode_520_ci, PRIMARY KEY (`signup_id`), KEY `activation_key` (`activation_key`), KEY `user_email` (`user_email`), KEY `user_login_email` (`user_login`,`user_email`), KEY `domain_path` (`domain`(140),`path`(51))) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
