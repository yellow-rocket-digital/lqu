/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_tracking`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_tracking`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_tracking` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbst_contactid` int(11) NOT NULL, `zbst_action` varchar(50) NOT NULL, `zbst_action_detail` longtext NOT NULL, `zbst_referrer` varchar(300) NOT NULL, `zbst_utm_source` varchar(200) NOT NULL, `zbst_utm_medium` varchar(200) NOT NULL, `zbst_utm_name` varchar(200) NOT NULL, `zbst_utm_term` varchar(200) NOT NULL, `zbst_utm_content` varchar(200) NOT NULL, `zbst_created` int(14) NOT NULL, `zbst_lastupdated` int(14) NOT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
