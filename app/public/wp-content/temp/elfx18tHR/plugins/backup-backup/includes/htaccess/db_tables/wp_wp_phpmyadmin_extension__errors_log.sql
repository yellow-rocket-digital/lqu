/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wp_phpmyadmin_extension__errors_log`; */
/* PRE_TABLE_NAME: `1668940036_wp_wp_phpmyadmin_extension__errors_log`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wp_phpmyadmin_extension__errors_log` ( `id` int(50) NOT NULL AUTO_INCREMENT, `gmdate` datetime DEFAULT NULL, `function_name` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `function_args` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `message` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `id` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
