/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_wc_admin_note_actions`; */
/* PRE_TABLE_NAME: `1668940036_wp_wc_admin_note_actions`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_wc_admin_note_actions` ( `action_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `note_id` bigint(20) unsigned NOT NULL, `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL, `label` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL, `query` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL, `status` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL, `actioned_text` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL, `nonce_action` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, `nonce_name` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL, PRIMARY KEY (`action_id`), KEY `note_id` (`note_id`)) ENGINE=InnoDB AUTO_INCREMENT=2566 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
INSERT INTO `1668940036_wp_wc_admin_note_actions` (`action_id`, `note_id`, `name`, `label`, `query`, `status`, `actioned_text`, `nonce_action`, `nonce_name`) VALUES (39,33,'notify-refund-returns-page','Edit page','https://lqustg.wpengine.com/wp-admin/post.php?post=1309&action=edit','actioned','','',''),(78,34,'connect','Connect','?page=wc-addons&section=helper','unactioned','','',''),(269,35,'learn-more','Learn more','https://woocommerce.com/document/managing-orders/?utm_source=inbox&utm_medium=product','actioned','','',''),(954,36,'visit-the-theme-marketplace','Visit the theme marketplace','https://woocommerce.com/product-category/themes/?utm_source=inbox&utm_medium=product','actioned','','',''),(955,37,'day-after-first-product','Learn more','https://woocommerce.com/document/woocommerce-customizer/?utm_source=inbox&utm_medium=product','actioned','','',''),(956,38,'affirm-insight-first-product-and-payment','Yes','','actioned','Thanks for your feedback','',''),(957,38,'affirm-insight-first-product-and-payment','No','','actioned','Thanks for your feedback','',''),(958,39,'learn-more','Learn more','https://woocommerce.com/posts/pre-launch-checklist-the-essentials/?utm_source=inbox&utm_medium=product','actioned','','',''),(959,40,'update-store-details','Update store details','https://lqustg.wpengine.com/wp-admin/admin.php?page=wc-admin&path=/setup-wizard','actioned','','',''),(999,42,'learn-more','Learn more','https://woocommerce.com/mobile/?utm_medium=product','actioned','','',''),(2053,43,'view-payment-gateways','Learn more','https://woocommerce.com/product-category/woocommerce-extensions/payment-gateways/?utm_medium=product','actioned','','',''),(2093,44,'affirm-insight-first-sale','Yes','','actioned','Thanks for your feedback','',''),(2094,44,'deny-insight-first-sale','No','','actioned','Thanks for your feedback','',''),(2095,45,'learn-more','Learn more','https://woocommerce.com/payments/?utm_medium=product','unactioned','','',''),(2096,45,'get-started','Get started','https://lqustg.wpengine.com/wp-admin/admin.php?page=wc-admin&action=setup-woocommerce-payments','actioned','','setup-woocommerce-payments',''),(2409,46,'customize-store-with-blocks','Learn more','https://woocommerce.com/posts/how-to-customize-your-online-store-with-woocommerce-blocks/?utm_source=inbox&utm_medium=product','actioned','','',''),(2527,1,'browse_extensions','Browse extensions','https://lqustg.wpengine.com/wp-admin/admin.php?page=wc-addons','unactioned','','',''),(2528,2,'wayflyer_bnpl_q4_2021','Level up with funding','https://woocommerce.com/products/wayflyer/?utm_source=inbox_note&utm_medium=product&utm_campaign=wayflyer_bnpl_q4_2021','actioned','','',''),(2529,3,'wc_shipping_mobile_app_usps_q4_2021','Get WooCommerce Shipping','https://woocommerce.com/woocommerce-shipping/?utm_source=inbox_note&utm_medium=product&utm_campaign=wc_shipping_mobile_app_usps_q4_2021','actioned','','',''),(2530,4,'learn-more','Learn more','https://docs.woocommerce.com/document/woocommerce-shipping-and-tax/?utm_source=inbox','unactioned','','',''),(2531,5,'learn-more','Learn more','https://woocommerce.com/posts/ecommerce-shipping-solutions-guide/?utm_source=inbox_note&utm_medium=product&utm_campaign=learn-more','actioned','','',''),(2532,6,'optimizing-the-checkout-flow','Learn more','https://woocommerce.com/posts/optimizing-woocommerce-checkout?utm_source=inbox_note&utm_medium=product&utm_campaign=optimizing-the-checkout-flow','actioned','','',''),(2533,7,'qualitative-feedback-from-new-users','Share feedback','https://automattic.survey.fm/wc-pay-new','actioned','','',''),(2534,8,'share-feedback','Share feedback','http://automattic.survey.fm/paypal-feedback','unactioned','','',''),(2535,9,'get-started','Get started','https://woocommerce.com/products/google-listings-and-ads?utm_source=inbox_note&utm_medium=product&utm_campaign=get-started','actioned','','',''),(2536,10,'update-wc-subscriptions-3-0-15','View latest version','https://lqustg.wpengine.com/wp-admin/&page=wc-addons&section=helper','actioned','','',''),(2537,11,'update-wc-core-5-4-0','How to update WooCommerce','https://docs.woocommerce.com/document/how-to-update-woocommerce/','actioned','','',''),(2538,14,'ppxo-pps-install-paypal-payments-1','View upgrade guide','https://docs.woocommerce.com/document/woocommerce-paypal-payments/paypal-payments-upgrade-guide/','actioned','','',''),(2539,15,'ppxo-pps-install-paypal-payments-2','View upgrade guide','https://docs.woocommerce.com/document/woocommerce-paypal-payments/paypal-payments-upgrade-guide/','actioned','','',''),(2540,16,'learn-more','Learn more','https://woocommerce.com/posts/critical-vulnerability-detected-july-2021/?utm_source=inbox_note&utm_medium=product&utm_campaign=learn-more','unactioned','','',''),(2541,16,'dismiss','Dismiss','','actioned','','',''),(2542,17,'learn-more','Learn more','https://woocommerce.com/posts/critical-vulnerability-detected-july-2021/?utm_source=inbox_note&utm_medium=product&utm_campaign=learn-more','unactioned','','',''),(2543,17,'dismiss','Dismiss','','actioned','','',''),(2544,18,'learn-more','Learn more','https://woocommerce.com/posts/critical-vulnerability-detected-july-2021/?utm_source=inbox_note&utm_medium=product&utm_campaign=learn-more','unactioned','','',''),(2545,18,'dismiss','Dismiss','','actioned','','',''),(2546,19,'learn-more','Learn more','https://woocommerce.com/posts/critical-vulnerability-detected-july-2021/?utm_source=inbox_note&utm_medium=product&utm_campaign=learn-more','unactioned','','',''),(2547,19,'dismiss','Dismiss','','actioned','','',''),(2548,20,'share-feedback','Share feedback','https://automattic.survey.fm/store-management','unactioned','','',''),(2549,21,'share-navigation-survey-feedback','Share feedback','https://automattic.survey.fm/feedback-on-woocommerce-navigation','actioned','','',''),(2550,22,'learn-more','Learn more','https://developer.woocommerce.com/2022/03/10/woocommerce-3-5-10-6-3-1-security-releases/','unactioned','','',''),(2551,22,'woocommerce-core-paypal-march-2022-dismiss','Dismiss','','actioned','','',''),(2552,23,'learn-more','Learn more','https://developer.woocommerce.com/2022/03/10/woocommerce-3-5-10-6-3-1-security-releases/','unactioned','','',''),(2553,23,'dismiss','Dismiss','','actioned','','',''),(2554,24,'pinterest_03_2022_update','Update Instructions','https://woocommerce.com/document/pinterest-for-woocommerce/?utm_source=inbox_note&utm_medium=product&utm_campaign=pinterest_03_2022_update#section-3','actioned','','',''),(2555,25,'store_setup_survey_survey_q2_2022_share_your_thoughts','Tell us how it’s going','https://automattic.survey.fm/store-setup-survey-2022','actioned','','',''),(2556,26,'wc-admin-wisepad3','Grow my business offline','https://woocommerce.com/products/wisepad3-card-reader/?utm_source=inbox_note&utm_medium=product&utm_campaign=wc-admin-wisepad3','actioned','','',''),(2557,27,'learn-more','Find out more','https://developer.woocommerce.com/2022/08/09/woocommerce-payments-3-9-4-4-5-1-security-releases/','unactioned','','',''),(2558,27,'dismiss','Dismiss','','actioned','','',''),(2559,28,'learn-more','Find out more','https://developer.woocommerce.com/2022/08/09/woocommerce-payments-3-9-4-4-5-1-security-releases/','unactioned','','',''),(2560,28,'dismiss','Dismiss','','actioned','','',''),(2561,29,'shipping_category_q4_2022_click','Automate my shipping','https://woocommerce.com/product-category/woocommerce-extensions/shipping-delivery-and-fulfillment/?categoryIds=28685&collections=product&page=1&utm_source=inbox_note&utm_medium=product&utm_campaign=shipping_category_q4_2022_click','unactioned','','',''),(2562,41,'woocommerce_admin_deprecation_q4_2022','Deactivate WooCommerce Admin','https://lqustg.wpengine.com/wp-admin/plugins.php','actioned','','',''),(2563,30,'tiktok-targeted-q4-2022-click','Launch a campaign','https://woocommerce.com/products/tiktok-for-woocommerce/?utm_source=inbox_note&utm_medium=product&utm_campaign=tiktok-targeted-q4-2022-click','unactioned','','',''),(2564,31,'paypal_paylater_g3_q4_22','Install PayPal Payments','https://woocommerce.com/products/woocommerce-paypal-payments/?utm_source=inbox_note&utm_medium=product&utm_campaign=paypal_paylater_g3_q4_22','unactioned','','',''),(2565,32,'paypal_paylater_g2_q4_22','Install PayPal Payments','https://woocommerce.com/products/woocommerce-paypal-payments/?utm_source=inbox_note&utm_medium=product&utm_campaign=paypal_paylater_g2_q4_22','unactioned','','','');
