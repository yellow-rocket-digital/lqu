/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_imports`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_imports`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_imports` ( `id` int(20) unsigned NOT NULL AUTO_INCREMENT, `type` varchar(40) NOT NULL, `remote_id` varchar(150) NOT NULL, `local_id` varchar(150) NOT NULL, `creator_id` int(15) unsigned DEFAULT NULL, `source` varchar(30) NOT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
