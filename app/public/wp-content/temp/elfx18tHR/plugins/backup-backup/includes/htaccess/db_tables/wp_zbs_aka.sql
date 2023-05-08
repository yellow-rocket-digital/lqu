/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_aka`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_aka`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_aka` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `aka_type` int(11) DEFAULT NULL, `aka_id` int(11) NOT NULL, `aka_alias` varchar(200) NOT NULL, `aka_created` int(14) DEFAULT NULL, `aka_lastupdated` int(14) DEFAULT NULL, PRIMARY KEY (`ID`), KEY `aka_id` (`aka_id`,`aka_alias`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
