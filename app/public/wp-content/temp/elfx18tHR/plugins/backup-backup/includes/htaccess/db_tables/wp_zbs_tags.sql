/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_tags`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_tags`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_tags` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbstag_objtype` int(11) NOT NULL, `zbstag_name` varchar(200) NOT NULL, `zbstag_slug` varchar(200) NOT NULL, `zbstag_created` int(14) NOT NULL, `zbstag_lastupdated` int(14) NOT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_zbs_tags` (`ID`, `zbs_site`, `zbs_team`, `zbs_owner`, `zbstag_objtype`, `zbstag_name`, `zbstag_slug`, `zbstag_created`, `zbstag_lastupdated`) VALUES (1,1,1,-1,1,'Product: Q1020','product-q1020',1668186178,1668186178),(2,1,1,-1,5,'Product: Q1020','product-q1020',1668186179,1668186179),(3,1,1,-1,1,'Product: Q1000','product-q1000',1668186179,1668186179),(4,1,1,-1,5,'Product: Q1000','product-q1000',1668186179,1668186179);
