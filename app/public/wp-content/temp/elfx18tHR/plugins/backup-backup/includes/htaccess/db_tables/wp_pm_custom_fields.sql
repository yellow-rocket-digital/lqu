/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_custom_fields`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_custom_fields`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_custom_fields` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `project_id` int(11) DEFAULT NULL, `title` varchar(100) DEFAULT NULL, `description` text, `type` varchar(50) DEFAULT NULL, `optional_value` text, `order` int(11) DEFAULT '0', PRIMARY KEY (`id`), KEY `project_id` (`project_id`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_custom_fields` (`id`, `project_id`, `title`, `description`, `type`, `optional_value`, `order`) VALUES (1,3,'Location','','text','',1),(2,4,'House Address','','text','',1);
