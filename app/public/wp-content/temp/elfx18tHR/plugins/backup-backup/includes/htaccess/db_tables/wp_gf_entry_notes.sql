/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_entry_notes`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_entry_notes`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_entry_notes` ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT, `entry_id` int(10) unsigned NOT NULL, `user_name` varchar(250) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `user_id` bigint(10) DEFAULT NULL, `date_created` datetime NOT NULL, `value` longtext COLLATE utf8mb4_unicode_520_ci, `note_type` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `sub_type` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, PRIMARY KEY (`id`), KEY `entry_id` (`entry_id`), KEY `entry_user_key` (`entry_id`,`user_id`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
INSERT INTO `1668940036_wp_gf_entry_notes` (`id`, `entry_id`, `user_name`, `user_id`, `date_created`, `value`, `note_type`, `sub_type`) VALUES (1,1,'Admin Notification (ID: 62bc7d25afcd9)',0,'2022-07-07 19:20:33','WordPress successfully passed the notification email to the sending server.','notification','success');
