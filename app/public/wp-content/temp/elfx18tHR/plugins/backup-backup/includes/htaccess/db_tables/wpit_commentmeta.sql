/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wpit_commentmeta`; */
/* PRE_TABLE_NAME: `1668940036_wpit_commentmeta`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wpit_commentmeta` ( `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0', `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL, `meta_value` longtext COLLATE utf8mb4_unicode_ci, PRIMARY KEY (`meta_id`), KEY `comment_id` (`comment_id`), KEY `meta_key` (`meta_key`(191))) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
