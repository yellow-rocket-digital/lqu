/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_events`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_events`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_events` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbse_title` varchar(255) DEFAULT NULL, `zbse_desc` longtext, `zbse_start` int(14) NOT NULL, `zbse_end` int(14) NOT NULL, `zbse_complete` tinyint(1) NOT NULL DEFAULT '-1', `zbse_show_on_portal` tinyint(1) NOT NULL DEFAULT '-1', `zbse_show_on_cal` tinyint(1) NOT NULL DEFAULT '-1', `zbse_created` int(14) NOT NULL, `zbse_lastupdated` int(14) DEFAULT NULL, PRIMARY KEY (`ID`), KEY `title` (`zbse_title`), KEY `startint` (`zbse_start`), KEY `endint` (`zbse_end`), KEY `created` (`zbse_created`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
