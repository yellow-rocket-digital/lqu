/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_event_reminders`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_event_reminders`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_event_reminders` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbser_event` int(11) NOT NULL, `zbser_remind_at` int(11) NOT NULL DEFAULT '-1', `zbser_sent` tinyint(4) NOT NULL DEFAULT '-1', `zbser_created` int(14) NOT NULL, `zbser_lastupdated` int(14) DEFAULT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
