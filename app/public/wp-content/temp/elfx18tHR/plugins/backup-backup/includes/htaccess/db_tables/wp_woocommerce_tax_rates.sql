/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_woocommerce_tax_rates`; */
/* PRE_TABLE_NAME: `1668940036_wp_woocommerce_tax_rates`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_woocommerce_tax_rates` ( `tax_rate_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `tax_rate_country` varchar(2) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `tax_rate_state` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `tax_rate` varchar(8) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `tax_rate_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', `tax_rate_priority` bigint(20) unsigned NOT NULL, `tax_rate_compound` int(1) NOT NULL DEFAULT '0', `tax_rate_shipping` int(1) NOT NULL DEFAULT '1', `tax_rate_order` bigint(20) unsigned NOT NULL, `tax_rate_class` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '', PRIMARY KEY (`tax_rate_id`), KEY `tax_rate_country` (`tax_rate_country`), KEY `tax_rate_state` (`tax_rate_state`(2)), KEY `tax_rate_class` (`tax_rate_class`(10)), KEY `tax_rate_priority` (`tax_rate_priority`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
