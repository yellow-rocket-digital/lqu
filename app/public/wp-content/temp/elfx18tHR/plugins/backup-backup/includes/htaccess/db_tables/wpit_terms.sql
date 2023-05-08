/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wpit_terms`; */
/* PRE_TABLE_NAME: `1668940036_wpit_terms`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wpit_terms` ( `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `term_group` bigint(10) NOT NULL DEFAULT '0', PRIMARY KEY (`term_id`), KEY `slug` (`slug`(191)), KEY `name` (`name`(191))) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `1668940036_wpit_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES (1,'Uncategorized','uncategorized',0),(2,'main','main',0),(3,'Chairs','chairs',0),(4,'Headboards','headboards',0),(5,'Ottoman','ottoman',0),(6,'Sofas','sofas',0);
