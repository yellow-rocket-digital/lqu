/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_task_types`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_task_types`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_task_types` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `description` text, `type` varchar(255) NOT NULL, `status` tinyint(4) NOT NULL DEFAULT '0', `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
