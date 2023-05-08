/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_dbmigration_meta`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_dbmigration_meta`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_dbmigration_meta` ( `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `wpID` bigint(20) unsigned NOT NULL, `zbsID` bigint(20) unsigned NOT NULL, `post_id` bigint(20) unsigned NOT NULL DEFAULT '0', `meta_key` varchar(255) DEFAULT NULL, `meta_value` longtext, PRIMARY KEY (`meta_id`), KEY `post_id` (`post_id`), KEY `meta_key` (`meta_key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
