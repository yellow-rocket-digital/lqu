/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_roles`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_roles`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_roles` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `slug` varchar(255) NOT NULL, `description` text, `status` tinyint(2) unsigned NOT NULL DEFAULT '1', `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_roles` (`id`, `title`, `slug`, `description`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'Manager','manager','Manager is a person who manages the project.',1,5,5,'2022-11-04 03:18:55','2022-11-04 03:18:55'),(2,'Co-Worker','co_worker','Co-worker is person who works under a project.',1,5,5,'2022-11-04 03:18:55','2022-11-04 03:18:55'),(3,'Client','client','Client is a person who provid the project.',1,5,5,'2022-11-04 03:18:56','2022-11-04 03:18:56');
