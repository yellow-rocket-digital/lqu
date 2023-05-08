/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_categories`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_categories`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_categories` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `description` text, `categorible_type` varchar(255) DEFAULT NULL, `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_categories` (`id`, `title`, `description`, `categorible_type`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,'Custom Furniture','','project',5,5,'2022-11-04 00:25:44','2022-11-04 00:25:44'),(2,'Draperies','','project',5,5,'2022-11-04 00:25:53','2022-11-04 00:25:53'),(3,'Reupholstery','','project',5,5,'2022-11-04 00:26:07','2022-11-04 00:26:07');
