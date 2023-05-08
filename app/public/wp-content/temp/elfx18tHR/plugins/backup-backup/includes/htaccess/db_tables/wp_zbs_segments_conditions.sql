/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_segments_conditions`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_segments_conditions`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_segments_conditions` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbscondition_segmentid` int(11) NOT NULL, `zbscondition_type` varchar(50) NOT NULL, `zbscondition_op` varchar(50) DEFAULT NULL, `zbscondition_val` varchar(250) DEFAULT NULL, `zbscondition_val_secondary` varchar(250) DEFAULT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
