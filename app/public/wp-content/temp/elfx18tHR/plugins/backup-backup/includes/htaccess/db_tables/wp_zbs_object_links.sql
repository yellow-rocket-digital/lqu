/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_object_links`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_object_links`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_object_links` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbsol_objtype_from` int(4) NOT NULL, `zbsol_objtype_to` int(4) NOT NULL, `zbsol_objid_from` int(11) NOT NULL, `zbsol_objid_to` int(11) NOT NULL, PRIMARY KEY (`ID`), KEY `zbsol_objid_from` (`zbsol_objid_from`), KEY `zbsol_objid_to` (`zbsol_objid_to`)) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_zbs_object_links` (`ID`, `zbs_site`, `zbs_team`, `zbs_owner`, `zbsol_objtype_from`, `zbsol_objtype_to`, `zbsol_objid_from`, `zbsol_objid_to`) VALUES (13,1,1,-1,1,2,2,1),(32,1,1,-1,3,1,1,1),(39,1,1,-1,1,2,1,1),(40,1,1,-1,10,5,13,2),(41,1,1,-1,5,1,2,1),(42,1,1,-1,5,2,2,1),(43,1,1,-1,10,5,14,1),(44,1,1,-1,5,1,1,1);
