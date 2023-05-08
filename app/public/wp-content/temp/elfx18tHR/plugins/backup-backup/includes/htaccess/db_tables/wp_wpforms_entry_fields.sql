/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wpforms_entry_fields`; */
/* PRE_TABLE_NAME: `1668940036_wp_wpforms_entry_fields`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wpforms_entry_fields` ( `id` bigint(20) NOT NULL AUTO_INCREMENT, `entry_id` bigint(20) NOT NULL, `form_id` bigint(20) NOT NULL, `field_id` int(11) NOT NULL, `value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `date` datetime NOT NULL, PRIMARY KEY (`id`), KEY `entry_id` (`entry_id`), KEY `form_id` (`form_id`), KEY `field_id` (`field_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
