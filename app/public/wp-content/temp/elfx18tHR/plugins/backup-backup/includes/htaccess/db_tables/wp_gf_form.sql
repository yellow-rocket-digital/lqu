/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_form`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_form`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_form` ( `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL, `date_created` datetime NOT NULL, `date_updated` datetime DEFAULT NULL, `is_active` tinyint(10) NOT NULL DEFAULT '1', `is_trash` tinyint(10) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
INSERT INTO `1668940036_wp_gf_form` (`id`, `title`, `date_created`, `date_updated`, `is_active`, `is_trash`) VALUES (1,'Request for Quote','2022-06-29 16:26:13','',1,0),(2,'New Account','2022-06-29 17:05:54','',1,0);
