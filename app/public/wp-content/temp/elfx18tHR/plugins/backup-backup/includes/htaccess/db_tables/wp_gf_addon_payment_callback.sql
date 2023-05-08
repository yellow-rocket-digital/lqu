/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_addon_payment_callback`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_addon_payment_callback`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_addon_payment_callback` ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT, `lead_id` int(10) unsigned NOT NULL, `addon_slug` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL, `callback_id` varchar(250) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `date_created` datetime DEFAULT NULL, PRIMARY KEY (`id`), KEY `addon_slug_callback_id` (`addon_slug`(50),`callback_id`(100))) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
