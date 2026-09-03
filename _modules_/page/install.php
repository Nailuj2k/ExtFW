<?php 


/******


DROP TABLE `CLI_PAGES`;

CREATE TABLE `CLI_PAGES` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(7) NOT NULL DEFAULT 1,
  `id_lang` int(5) NOT NULL DEFAULT 1,
  `id_module` int(5) DEFAULT 3,
  `id_menu` int(5) DEFAULT NULL,
  `item_parent` int(7) DEFAULT NULL,
  `item_level` int(8) DEFAULT NULL,
  `item_order` int(5) DEFAULT NULL,
  `item_fullscreen` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `item_name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_title` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_caption` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_text` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_keywords` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_active` int(1) DEFAULT 1,
  `item_visible` int(1) DEFAULT 1,
  `item_comments_enabled` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `item_rating_enabled` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `item_date_created` int(8) DEFAULT NULL,
  `item_date_modified` int(8) DEFAULT NULL,
  `item_votes` int(6) DEFAULT NULL,
  `item_points` decimal(5,2) DEFAULT 0.00,
  `item_rating` decimal(5,2) DEFAULT 0.00,
  `item_reads` int(6) DEFAULT NULL,
  `item_menuid` int(5) DEFAULT NULL,
  `inline_edit` int(1) DEFAULT 1,
  PRIMARY KEY  (item_id)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 


           //   $sql  = "INSERT INTO ".TB_PAGES."_FILES (id_item, image_date,image_name)VALUES($item_id,$ahora,'$filename') ";

CREATE TABLE `CLI_PAGES_FILES` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_item` int(7) NOT NULL DEFAULT 1,
  `file_name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_date` int(8) DEFAULT NULL,
  PRIMARY KEY  (`file_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 


******/