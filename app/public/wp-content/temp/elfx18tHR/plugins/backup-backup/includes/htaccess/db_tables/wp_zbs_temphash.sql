/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_temphash`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_temphash`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_temphash` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbstemphash_status` int(11) DEFAULT '-1', `zbstemphash_objtype` varchar(50) NOT NULL, `zbstemphash_objid` int(11) DEFAULT NULL, `zbstemphash_objhash` varchar(256) DEFAULT NULL, `zbstemphash_created` int(14) NOT NULL, `zbstemphash_lastupdated` int(14) NOT NULL, `zbstemphash_expiry` int(14) NOT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
