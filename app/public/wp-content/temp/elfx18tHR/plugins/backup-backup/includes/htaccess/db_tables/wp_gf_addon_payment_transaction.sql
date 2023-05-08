/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_addon_payment_transaction`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_addon_payment_transaction`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_addon_payment_transaction` ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT, `lead_id` int(10) unsigned NOT NULL, `transaction_type` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL, `transaction_id` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `subscription_id` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `is_recurring` tinyint(1) NOT NULL DEFAULT '0', `amount` decimal(19,2) DEFAULT NULL, `date_created` datetime DEFAULT NULL, PRIMARY KEY (`id`), KEY `lead_id` (`lead_id`), KEY `transaction_type` (`transaction_type`), KEY `type_lead` (`lead_id`,`transaction_type`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
