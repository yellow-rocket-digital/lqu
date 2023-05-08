/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_e_notes_users_relations`; */
/* PRE_TABLE_NAME: `1668940036_wp_e_notes_users_relations`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_e_notes_users_relations` ( `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `type` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'The relation type between user and note (e.g mention, watch, read).', `note_id` bigint(20) unsigned NOT NULL, `user_id` bigint(20) unsigned NOT NULL, `created_at` datetime NOT NULL, `updated_at` datetime NOT NULL, PRIMARY KEY (`id`), KEY `type_index` (`type`), KEY `note_id_index` (`note_id`), KEY `user_id_index` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
