/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_yith_wapo_blocks`; */
/* PRE_TABLE_NAME: `1668940036_wp_yith_wapo_blocks`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_yith_wapo_blocks` ( `id` int(3) NOT NULL AUTO_INCREMENT, `user_id` bigint(20) DEFAULT NULL, `vendor_id` bigint(20) DEFAULT NULL, `settings` longtext COLLATE utf8mb4_unicode_520_ci, `priority` decimal(9,5) DEFAULT NULL, `visibility` int(1) DEFAULT NULL, `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
INSERT INTO `1668940036_wp_yith_wapo_blocks` (`id`, `user_id`, `vendor_id`, `settings`, `priority`, `visibility`, `creation_date`, `last_update`) VALUES (1,'',0,'a:3:{s:4:\"name\";s:16:\"Chairs & Chaises\";s:8:\"priority\";s:1:\"1\";s:5:\"rules\";a:9:{s:7:\"show_in\";s:3:\"all\";s:16:\"show_in_products\";s:0:\"\";s:18:\"show_in_categories\";s:0:\"\";s:16:\"exclude_products\";s:0:\"\";s:25:\"exclude_products_products\";s:0:\"\";s:27:\"exclude_products_categories\";s:0:\"\";s:7:\"show_to\";s:3:\"all\";s:18:\"show_to_user_roles\";s:0:\"\";s:18:\"show_to_membership\";s:0:\"\";}}',1,1,'2022-11-07 23:51:53','0000-00-00 00:00:00');
