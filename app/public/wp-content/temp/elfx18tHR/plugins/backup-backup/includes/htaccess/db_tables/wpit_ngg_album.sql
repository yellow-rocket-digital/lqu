/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wpit_ngg_album`; */
/* PRE_TABLE_NAME: `1668940036_wpit_ngg_album`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wpit_ngg_album` ( `id` bigint(20) NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `slug` varchar(255) NOT NULL, `previewpic` bigint(20) NOT NULL DEFAULT '0', `albumdesc` mediumtext, `sortorder` longtext NOT NULL, `pageid` bigint(20) NOT NULL DEFAULT '0', `extras_post_id` bigint(20) NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `extras_post_id_key` (`extras_post_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
