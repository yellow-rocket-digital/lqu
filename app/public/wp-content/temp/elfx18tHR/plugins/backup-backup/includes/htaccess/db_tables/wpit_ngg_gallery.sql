/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wpit_ngg_gallery`; */
/* PRE_TABLE_NAME: `1668940036_wpit_ngg_gallery`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wpit_ngg_gallery` ( `gid` bigint(20) NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `slug` varchar(255) NOT NULL, `path` mediumtext, `title` mediumtext, `galdesc` mediumtext, `pageid` bigint(20) NOT NULL DEFAULT '0', `previewpic` bigint(20) NOT NULL DEFAULT '0', `author` bigint(20) NOT NULL DEFAULT '0', `extras_post_id` bigint(20) NOT NULL DEFAULT '0', PRIMARY KEY (`gid`), KEY `extras_post_id_key` (`extras_post_id`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wpit_ngg_gallery` (`gid`, `name`, `slug`, `path`, `title`, `galdesc`, `pageid`, `previewpic`, `author`, `extras_post_id`) VALUES (1,'chairs','chairs','/wp-content/gallery/chairs','Chairs','',0,1,3,274),(2,'headboards','headboards','/wp-content/gallery/headboards','Headboards','',0,45,3,364),(3,'ottomans','ottomans','/wp-content/gallery/ottomans','Ottomans','',0,56,3,388),(4,'sofas','sofas','/wp-content/gallery/sofas','Sofas','',0,65,3,408);
