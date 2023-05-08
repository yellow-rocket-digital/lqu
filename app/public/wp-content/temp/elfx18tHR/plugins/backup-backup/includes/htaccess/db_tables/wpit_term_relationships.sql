/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wpit_term_relationships`; */
/* PRE_TABLE_NAME: `1668940036_wpit_term_relationships`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wpit_term_relationships` ( `object_id` bigint(20) unsigned NOT NULL DEFAULT '0', `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0', `term_order` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`object_id`,`term_taxonomy_id`), KEY `term_taxonomy_id` (`term_taxonomy_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `1668940036_wpit_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES (1,1,0),(26,2,0),(28,2,0),(29,2,0),(31,2,0),(32,2,0),(33,2,0),(34,2,0),(36,2,0),(46,3,0),(49,3,0),(52,3,0),(55,3,0),(58,3,0),(61,3,0),(64,3,0),(67,3,0),(70,3,0),(73,3,0),(76,6,0),(79,6,0),(82,6,0),(85,6,0),(88,6,0),(91,6,0),(94,6,0),(97,6,0),(100,6,0),(103,6,0),(106,4,0),(109,4,0),(112,4,0),(115,4,0),(118,4,0),(122,4,0),(125,4,0),(128,4,0),(131,4,0),(134,4,0),(137,4,0),(140,5,0),(144,5,0),(147,5,0),(150,5,0),(153,5,0),(156,5,0),(160,5,0),(163,5,0),(166,5,0),(212,2,0),(216,2,0),(232,2,0),(481,2,0),(482,2,0),(483,2,0),(484,2,0),(485,2,0);
