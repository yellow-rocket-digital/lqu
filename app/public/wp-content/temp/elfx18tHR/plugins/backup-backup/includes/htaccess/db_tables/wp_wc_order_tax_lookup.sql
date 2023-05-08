/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wc_order_tax_lookup`; */
/* PRE_TABLE_NAME: `1668940036_wp_wc_order_tax_lookup`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wc_order_tax_lookup` ( `order_id` bigint(20) unsigned NOT NULL, `tax_rate_id` bigint(20) unsigned NOT NULL, `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', `shipping_tax` double NOT NULL DEFAULT '0', `order_tax` double NOT NULL DEFAULT '0', `total_tax` double NOT NULL DEFAULT '0', PRIMARY KEY (`order_id`,`tax_rate_id`), KEY `tax_rate_id` (`tax_rate_id`), KEY `date_created` (`date_created`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
