/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_gf_form_revisions`; */
/* PRE_TABLE_NAME: `1668940036_wp_gf_form_revisions`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_gf_form_revisions` ( `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT, `form_id` mediumint(10) unsigned NOT NULL, `display_meta` longtext COLLATE utf8mb4_unicode_520_ci, `date_created` datetime NOT NULL, PRIMARY KEY (`id`), KEY `date_created` (`date_created`), KEY `form_id` (`form_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
