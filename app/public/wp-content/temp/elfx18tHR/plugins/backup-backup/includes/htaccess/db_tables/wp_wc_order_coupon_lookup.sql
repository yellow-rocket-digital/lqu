/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wc_order_coupon_lookup`; */
/* PRE_TABLE_NAME: `1668940036_wp_wc_order_coupon_lookup`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wc_order_coupon_lookup` ( `order_id` bigint(20) unsigned NOT NULL, `coupon_id` bigint(20) NOT NULL, `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', `discount_amount` double NOT NULL DEFAULT '0', PRIMARY KEY (`order_id`,`coupon_id`), KEY `coupon_id` (`coupon_id`), KEY `date_created` (`date_created`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
