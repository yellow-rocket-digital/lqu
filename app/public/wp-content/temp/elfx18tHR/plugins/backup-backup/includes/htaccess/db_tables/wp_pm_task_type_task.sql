/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_task_type_task`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_task_type_task`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_task_type_task` ( `type_id` int(11) unsigned NOT NULL, `task_id` int(11) unsigned NOT NULL, `project_id` int(11) unsigned NOT NULL, `list_id` int(11) unsigned NOT NULL, UNIQUE KEY `task_id` (`task_id`), KEY `type_id` (`type_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
