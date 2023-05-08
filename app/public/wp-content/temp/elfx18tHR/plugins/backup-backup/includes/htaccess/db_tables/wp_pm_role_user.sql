/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_role_user`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_role_user`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_role_user` ( `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `user_id` int(11) unsigned NOT NULL, `role_id` int(11) unsigned NOT NULL, `project_id` int(11) unsigned DEFAULT NULL, `assigned_by` int(11) unsigned NOT NULL, PRIMARY KEY (`id`), KEY `project_id` (`project_id`), KEY `role_id` (`role_id`), KEY `user_id` (`user_id`), KEY `assigned_by` (`assigned_by`)) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_role_user` (`id`, `user_id`, `role_id`, `project_id`, `assigned_by`) VALUES (13,7,3,2,0),(14,6,3,2,0),(15,5,1,2,0),(16,8,3,1,0),(17,7,3,1,0),(18,6,3,1,0),(19,5,1,1,0),(21,5,1,4,0),(22,5,1,5,0);
