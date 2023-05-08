/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_notifications`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_notifications`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_notifications` ( `id` int(32) unsigned NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbsnotify_recipient_id` int(32) NOT NULL, `zbsnotify_sender_id` int(32) NOT NULL, `zbsnotify_unread` tinyint(1) NOT NULL DEFAULT '1', `zbsnotify_emailed` tinyint(1) NOT NULL DEFAULT '0', `zbsnotify_type` varchar(255) NOT NULL DEFAULT '', `zbsnotify_parameters` text NOT NULL, `zbsnotify_reference_id` int(32) NOT NULL, `zbsnotify_created_at` int(18) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
