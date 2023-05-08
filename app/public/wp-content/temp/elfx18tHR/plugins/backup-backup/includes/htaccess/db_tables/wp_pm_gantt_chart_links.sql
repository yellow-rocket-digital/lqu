/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_pm_gantt_chart_links`; */
/* PRE_TABLE_NAME: `1668940036_wp_pm_gantt_chart_links`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_pm_gantt_chart_links` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `source` int(11) unsigned NOT NULL, `target` int(11) unsigned NOT NULL, `type` int(11) unsigned NOT NULL, `created_by` int(11) unsigned DEFAULT NULL, `updated_by` int(11) unsigned DEFAULT NULL, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`), KEY `source` (`source`), KEY `target` (`target`), CONSTRAINT `wp_pm_gantt_chart_links_ibfk_1` FOREIGN KEY (`source`) REFERENCES `wp_pm_tasks` (`id`) ON DELETE CASCADE, CONSTRAINT `wp_pm_gantt_chart_links_ibfk_2` FOREIGN KEY (`target`) REFERENCES `wp_pm_tasks` (`id`) ON DELETE CASCADE) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_pm_gantt_chart_links` (`id`, `source`, `target`, `type`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (1,2,1,0,'','','2022-11-04 00:17:38','2022-11-04 00:17:38'),(2,2,1,0,'','','2022-11-04 00:17:38','2022-11-04 00:17:38');
