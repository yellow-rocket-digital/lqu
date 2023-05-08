/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_sys_cronmanagerlogs`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_sys_cronmanagerlogs`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_sys_cronmanagerlogs` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `job` varchar(100) NOT NULL, `jobstatus` int(3) DEFAULT NULL, `jobstarted` int(14) NOT NULL, `jobfinished` int(14) NOT NULL, `jobnotes` longtext, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
