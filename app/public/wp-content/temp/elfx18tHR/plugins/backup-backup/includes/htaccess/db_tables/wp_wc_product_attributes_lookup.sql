/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wc_product_attributes_lookup`; */
/* PRE_TABLE_NAME: `1668940036_wp_wc_product_attributes_lookup`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wc_product_attributes_lookup` ( `product_id` bigint(20) NOT NULL, `product_or_parent_id` bigint(20) NOT NULL, `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL, `term_id` bigint(20) NOT NULL, `is_variation_attribute` tinyint(1) NOT NULL, `in_stock` tinyint(1) NOT NULL, PRIMARY KEY (`product_or_parent_id`,`term_id`,`product_id`,`taxonomy`), KEY `is_variation_attribute_term_id` (`is_variation_attribute`,`term_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
