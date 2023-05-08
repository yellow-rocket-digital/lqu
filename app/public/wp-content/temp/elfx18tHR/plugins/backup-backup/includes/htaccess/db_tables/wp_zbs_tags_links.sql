/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_tags_links`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_tags_links`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_tags_links` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbstl_objtype` int(4) NOT NULL, `zbstl_objid` int(11) NOT NULL, `zbstl_tagid` int(11) NOT NULL, PRIMARY KEY (`ID`), KEY `zbstl_objid` (`zbstl_objid`), KEY `zbstl_tagid` (`zbstl_tagid`), KEY `zbstl_tagid+zbstl_objtype` (`zbstl_tagid`,`zbstl_objtype`) USING BTREE) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_zbs_tags_links` (`ID`, `zbs_site`, `zbs_team`, `zbs_owner`, `zbstl_objtype`, `zbstl_objid`, `zbstl_tagid`) VALUES (1,1,1,5,1,1,1),(2,1,1,5,5,1,2),(3,1,1,5,1,1,3),(4,1,1,5,5,2,4);
