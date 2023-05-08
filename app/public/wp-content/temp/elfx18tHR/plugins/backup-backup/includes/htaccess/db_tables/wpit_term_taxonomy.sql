/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wpit_term_taxonomy`; */
/* PRE_TABLE_NAME: `1668940036_wpit_term_taxonomy`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wpit_term_taxonomy` ( `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `term_id` bigint(20) unsigned NOT NULL DEFAULT '0', `taxonomy` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL, `parent` bigint(20) unsigned NOT NULL DEFAULT '0', `count` bigint(20) NOT NULL DEFAULT '0', PRIMARY KEY (`term_taxonomy_id`), UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`), KEY `taxonomy` (`taxonomy`)) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `1668940036_wpit_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES (1,1,'category','',0,1),(2,2,'nav_menu','',0,16),(3,3,'portfolio_category','',0,10),(4,4,'portfolio_category','',0,11),(5,5,'portfolio_category','',0,9),(6,6,'portfolio_category','',0,10);
