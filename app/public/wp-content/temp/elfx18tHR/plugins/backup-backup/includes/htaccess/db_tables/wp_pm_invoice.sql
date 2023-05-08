/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_invoice`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_invoice`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_invoice` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `client_id` int(11) unsigned NOT NULL, `project_id` int(11) unsigned NOT NULL, `status` tinyint(4) NOT NULL DEFAULT '0', `start_at` timestamp NULL DEFAULT NULL, `due_date` timestamp NULL DEFAULT NULL, `discount` double(8,2) NOT NULL DEFAULT '0.00', `partial` tinyint(4) NOT NULL DEFAULT '0', `partial_amount` double(8,2) NOT NULL DEFAULT '0.00', `terms` text, `client_note` text, `items` longtext NOT NULL, `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `project_id` (`project_id`), KEY `client_id` (`client_id`), CONSTRAINT `wp_pm_invoice_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `wp_pm_projects` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8;
