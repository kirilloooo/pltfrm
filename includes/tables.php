<?php
$codesTable = "CREATE TABLE IF NOT EXISTS `codes` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `promocode` text NOT NULL,
 `tariff` int(11) NOT NULL,
 `long_time` int(11) NOT NULL,
 `time_end` date NOT NULL,
 `register_before_time` date NOT NULL,
 `limitation` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `promocode` (`promocode`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$filesTable = "CREATE TABLE IF NOT EXISTS `files` (
 `id` bigint(35) NOT NULL AUTO_INCREMENT,
 `file_id` varchar(1024) NOT NULL,
 `file_name` varchar(256) NOT NULL,
 `origin_file_id` text DEFAULT NULL,
 `file_dir` varchar(512) NOT NULL,
 `file_size` float NOT NULL,
 `user_id` bigint(35) NOT NULL,
 `message_id` varchar(512) NOT NULL,
 `date` bigint(35) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `file_id` (`file_id`) USING HASH,
 UNIQUE KEY `file_name` (`file_name`) USING HASH,
 UNIQUE KEY `message_id` (`message_id`) USING HASH
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$plansTable = "CREATE TABLE IF NOT EXISTS `plans` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `name` mediumtext NOT NULL,
 `description` mediumtext NOT NULL,
 `countFiles` bigint(20) NOT NULL,
 `hide` tinyint(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$promoUsageTable = "CREATE TABLE IF NOT EXISTS `promo_usage` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `promocode` text NOT NULL,
 `chat_id` bigint(128) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `promocode_chat_id` (`promocode`,`chat_id`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$usersTable = "CREATE TABLE IF NOT EXISTS `users` (
 `id` bigint(35) NOT NULL AUTO_INCREMENT,
 `chat_id` bigint(35) NOT NULL,
 `full_name` varchar(128) NOT NULL,
 `username` varchar(32) DEFAULT NULL,
 `files` bigint(35) NOT NULL,
 `date` bigint(35) NOT NULL,
 `oneStart` date NOT NULL,
 `plan` bigint(20) NOT NULL DEFAULT 1,
 `planEnd` date NOT NULL,
 `ban` tinyint(1) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `chat_id` (`chat_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
