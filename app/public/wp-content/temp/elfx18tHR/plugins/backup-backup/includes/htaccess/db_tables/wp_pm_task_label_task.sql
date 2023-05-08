/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_task_label_task`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_task_label_task`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_task_label_task` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `task_id` int(11) unsigned NOT NULL, `label_id` int(11) unsigned NOT NULL, PRIMARY KEY (`id`), KEY `task_id` (`task_id`), KEY `label_id` (`label_id`)) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_task_label_task` (`id`, `task_id`, `label_id`) VALUES (1,3,1),(2,3,2),(3,3,3),(4,4,1),(5,4,2),(6,4,3),(7,4,4);
