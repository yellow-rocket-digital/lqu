/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_meta`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_meta`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_meta` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbsm_objtype` int(11) NOT NULL, `zbsm_objid` int(11) NOT NULL, `zbsm_key` varchar(255) NOT NULL, `zbsm_val` longtext, `zbsm_created` int(14) NOT NULL, `zbsm_lastupdated` int(14) NOT NULL, PRIMARY KEY (`ID`), KEY `zbsm_objid+zbsm_key+zbsm_objtype` (`zbsm_objid`,`zbsm_key`,`zbsm_objtype`) USING BTREE) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_zbs_meta` (`ID`, `zbs_site`, `zbs_team`, `zbs_owner`, `zbsm_objtype`, `zbsm_objid`, `zbsm_key`, `zbsm_val`, `zbsm_created`, `zbsm_lastupdated`) VALUES (1,1,1,-1,1,1,'extra_billingemail','manager@photonfactorydev.com',1668186179,1668187695),(2,1,1,-1,5,1,'extra_order_num',2248,1668186179,1668187695),(3,1,1,-1,5,2,'extra_order_num',2222,1668186179,1668187695),(4,1,1,-1,3,1,'extra_zbsid',-1,1668187212,1668187212);
