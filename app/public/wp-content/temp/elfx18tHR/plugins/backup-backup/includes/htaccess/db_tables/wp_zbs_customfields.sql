/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_customfields`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_customfields`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_customfields` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbscf_objtype` int(4) NOT NULL, `zbscf_objid` int(32) NOT NULL, `zbscf_objkey` varchar(100) NOT NULL, `zbscf_objval` varchar(2000) DEFAULT NULL, `zbscf_created` int(14) NOT NULL, `zbscf_lastupdated` int(14) NOT NULL, PRIMARY KEY (`ID`), KEY `TYPEIDKEY` (`zbscf_objtype`,`zbscf_objid`,`zbscf_objkey`), FULLTEXT KEY `search` (`zbscf_objval`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
