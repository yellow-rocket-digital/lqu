/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_admlog`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_admlog`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_admlog` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbsadmlog_status` int(3) NOT NULL, `zbsadmlog_cat` varchar(20) DEFAULT NULL, `zbsadmlog_str` varchar(500) DEFAULT NULL, `zbsadmlog_time` int(14) DEFAULT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
