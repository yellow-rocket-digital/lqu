/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wpit_layerslider`; */
/* PRE_TABLE_NAME: `1668940036_wpit_layerslider`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wpit_layerslider` ( `id` int(10) NOT NULL AUTO_INCREMENT, `author` int(10) NOT NULL DEFAULT '0', `name` varchar(100) NOT NULL, `slug` varchar(100) NOT NULL, `data` mediumtext NOT NULL, `date_c` int(10) NOT NULL, `date_m` int(11) NOT NULL, `flag_hidden` tinyint(1) NOT NULL DEFAULT '0', `flag_deleted` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
