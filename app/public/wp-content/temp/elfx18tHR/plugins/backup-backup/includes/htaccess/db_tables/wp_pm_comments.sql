/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_comments`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_comments`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_comments` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `content` text NOT NULL, `mentioned_users` varchar(255) DEFAULT NULL, `commentable_id` int(11) unsigned NOT NULL, `commentable_type` varchar(255) NOT NULL, `project_id` int(11) unsigned NOT NULL, `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `project_id` (`project_id`), KEY `commentable_id` (`commentable_id`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_comments` (`id`, `content`, `mentioned_users`, `commentable_id`, `commentable_type`, `project_id`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'Task marked as done','',1,'task_activity',1,7,7,'2022-11-04 00:09:25','2022-11-04 00:09:25'),(2,'Task reopened','',1,'task_activity',1,7,7,'2022-11-04 00:09:32','2022-11-04 00:09:32');
