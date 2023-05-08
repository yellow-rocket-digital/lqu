/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_draft_submissions`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_draft_submissions`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_draft_submissions` ( `uuid` char(32) COLLATE utf8mb4_unicode_520_ci NOT NULL, `email` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `form_id` mediumint(10) unsigned NOT NULL, `date_created` datetime NOT NULL, `ip` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL, `source_url` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `submission` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, PRIMARY KEY (`uuid`), KEY `form_id` (`form_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
