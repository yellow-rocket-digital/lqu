/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_integrations`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_integrations`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_integrations` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `project_id` int(20) DEFAULT NULL, `primary_key` int(20) DEFAULT NULL, `foreign_key` int(20) DEFAULT NULL, `type` varchar(25) DEFAULT NULL, `source` varchar(30) DEFAULT NULL, `username` varchar(40) DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
