/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_form_view`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_form_view`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_form_view` ( `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT, `form_id` mediumint(10) unsigned NOT NULL, `date_created` datetime NOT NULL, `ip` char(15) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `count` mediumint(10) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`), KEY `date_created` (`date_created`), KEY `form_id` (`form_id`)) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
INSERT INTO `1668940036_wp_gf_form_view` (`id`, `form_id`, `date_created`, `ip`, `count`) VALUES (1,2,'2022-06-30 19:48:09','',1),(2,1,'2022-07-07 16:56:40','',5),(3,2,'2022-07-10 12:31:44','',1),(4,1,'2022-07-10 12:32:25','',1),(5,1,'2022-07-13 13:12:51','',1),(6,1,'2022-07-18 18:17:37','',2);
