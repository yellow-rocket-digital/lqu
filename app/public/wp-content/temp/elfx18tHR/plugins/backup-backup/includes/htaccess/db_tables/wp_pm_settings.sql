/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_settings`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_settings`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_settings` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `key` varchar(255) NOT NULL, `value` text, `project_id` int(11) unsigned DEFAULT NULL, `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `project_id` (`project_id`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_settings` (`id`, `key`, `value`, `project_id`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'front_end_page','s:4:\"2214\";','',5,5,'2022-11-03 23:35:41','2022-11-04 02:13:59'),(2,'woo_project','a:1:{i:0;a:4:{s:6:\"action\";s:9:\"duplicate\";s:10:\"project_id\";s:1:\"1\";s:11:\"product_ids\";a:1:{i:0;s:4:\"2195\";}s:9:\"assignees\";a:1:{i:0;a:2:{s:7:\"user_id\";s:1:\"5\";s:7:\"role_id\";s:1:\"1\";}}}}','',5,5,'2022-11-03 23:39:45','2022-11-04 00:32:56');
