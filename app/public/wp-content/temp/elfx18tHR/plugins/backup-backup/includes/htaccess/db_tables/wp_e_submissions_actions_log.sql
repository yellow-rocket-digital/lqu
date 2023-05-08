/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_e_submissions_actions_log`; */
/* PRE_TABLE_NAME: `1668940036_wp_e_submissions_actions_log`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_e_submissions_actions_log` ( `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `submission_id` bigint(20) unsigned NOT NULL, `action_name` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL, `action_label` varchar(60) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL, `log` text COLLATE utf8mb4_unicode_520_ci, `created_at_gmt` datetime NOT NULL, `updated_at_gmt` datetime NOT NULL, `created_at` datetime NOT NULL, `updated_at` datetime NOT NULL, PRIMARY KEY (`id`), KEY `submission_id_index` (`submission_id`), KEY `action_name_index` (`action_name`), KEY `status_index` (`status`), KEY `created_at_gmt_index` (`created_at_gmt`), KEY `updated_at_gmt_index` (`updated_at_gmt`), KEY `created_at_index` (`created_at`), KEY `updated_at_index` (`updated_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
