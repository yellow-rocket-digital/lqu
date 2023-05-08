/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_rest_api_keys`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_rest_api_keys`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_rest_api_keys` ( `key_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `user_id` bigint(20) unsigned NOT NULL, `description` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `permissions` varchar(10) COLLATE utf8mb4_unicode_520_ci NOT NULL, `consumer_key` char(64) COLLATE utf8mb4_unicode_520_ci NOT NULL, `consumer_secret` char(43) COLLATE utf8mb4_unicode_520_ci NOT NULL, `nonces` longtext COLLATE utf8mb4_unicode_520_ci, `truncated_key` char(7) COLLATE utf8mb4_unicode_520_ci NOT NULL, `last_access` datetime DEFAULT NULL, PRIMARY KEY (`key_id`), KEY `consumer_key` (`consumer_key`), KEY `consumer_secret` (`consumer_secret`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
