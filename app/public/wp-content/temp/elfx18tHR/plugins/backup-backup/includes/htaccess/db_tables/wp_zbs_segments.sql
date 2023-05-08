/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_segments`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_segments`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_segments` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbsseg_name` varchar(120) NOT NULL, `zbsseg_slug` varchar(45) NOT NULL, `zbsseg_matchtype` varchar(10) NOT NULL, `zbsseg_created` int(14) NOT NULL, `zbsseg_lastupdated` int(14) NOT NULL, `zbsseg_compilecount` int(11) DEFAULT '0', `zbsseg_lastcompiled` int(14) NOT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
