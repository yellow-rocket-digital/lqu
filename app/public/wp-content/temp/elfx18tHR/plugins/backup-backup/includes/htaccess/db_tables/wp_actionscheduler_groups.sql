/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_actionscheduler_groups`; */
/* PRE_TABLE_NAME: `1668940036_wp_actionscheduler_groups`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_actionscheduler_groups` ( `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `slug` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL, PRIMARY KEY (`group_id`), KEY `slug` (`slug`(191))) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
INSERT INTO `1668940036_wp_actionscheduler_groups` (`group_id`, `slug`) VALUES (1,'action-scheduler-migration'),(2,'woocommerce-db-updates'),(3,'wc-admin-data'),(4,'yith-wapo-db-updates'),(5,'wpforms'),(6,'woocommerce-remote-inbox-engine');
