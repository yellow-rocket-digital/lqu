/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_task_custom_fields`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_task_custom_fields`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_task_custom_fields` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `field_id` int(11) DEFAULT NULL, `project_id` int(11) DEFAULT NULL, `list_id` int(11) DEFAULT NULL, `task_id` int(11) DEFAULT NULL, `value` text, `color` varchar(30) DEFAULT NULL, PRIMARY KEY (`id`), KEY `field_id` (`field_id`), KEY `project_id` (`project_id`), KEY `list_id` (`list_id`), KEY `task_id` (`task_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
