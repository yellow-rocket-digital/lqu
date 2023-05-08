/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_externalsources`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_externalsources`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_externalsources` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbss_objtype` int(3) NOT NULL DEFAULT '-1', `zbss_objid` int(32) NOT NULL, `zbss_source` varchar(20) NOT NULL, `zbss_uid` varchar(300) NOT NULL, `zbss_origin` varchar(400) DEFAULT NULL, `zbss_created` int(14) NOT NULL, `zbss_lastupdated` int(14) NOT NULL, PRIMARY KEY (`ID`), KEY `zbss_objid` (`zbss_objid`), KEY `zbss_origin` (`zbss_origin`), KEY `zbss_uid+zbss_source+zbss_objtype` (`zbss_uid`,`zbss_source`,`zbss_objtype`) USING BTREE) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_zbs_externalsources` (`ID`, `zbs_site`, `zbs_team`, `zbs_owner`, `zbss_objtype`, `zbss_objid`, `zbss_source`, `zbss_uid`, `zbss_origin`, `zbss_created`, `zbss_lastupdated`) VALUES (1,1,1,0,1,1,'woo',2248,'d:https://lqustg.wpengine.com',1668186179,1668187695),(2,1,1,0,5,1,'woo',2248,'d:https://lqustg.wpengine.com',1668186179,1668187695),(3,1,1,0,2,1,'woo',2222,'d:https://lqustg.wpengine.com',1668186179,1668187695),(4,1,1,0,5,2,'woo',2222,'d:https://lqustg.wpengine.com',1668186179,1668187695);
