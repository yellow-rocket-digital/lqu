/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_tax_table`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_tax_table`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_tax_table` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbsc_tax_name` varchar(100) DEFAULT NULL, `zbsc_rate` decimal(18,2) NOT NULL DEFAULT '0.00', `zbsc_created` int(14) NOT NULL, `zbsc_lastupdated` int(14) NOT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
