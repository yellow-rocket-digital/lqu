/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_task_label`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_task_label`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_task_label` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `description` text, `color` varchar(255) NOT NULL, `status` tinyint(4) NOT NULL DEFAULT '0', `project_id` int(11) unsigned NOT NULL, `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `project_id` (`project_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
