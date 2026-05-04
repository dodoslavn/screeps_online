-- Screeps Online Database Schema
-- Fresh installation schema

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `server_list` (
  `id_server` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_server`),
  UNIQUE KEY `address` (`address`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `server_info` (
  `id_info` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `online` tinyint(1) NOT NULL DEFAULT 0,
  `players_current` int(11) DEFAULT 0,
  `version` varchar(20) DEFAULT NULL,
  `last_check` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `availability` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id_info`),
  UNIQUE KEY `server_id` (`server_id`),
  KEY `idx_last_check` (`last_check`),
  KEY `idx_online` (`online`),
  FOREIGN KEY (`server_id`) REFERENCES `server_list`(`id_server`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `server_availability` (
  `id_availability` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `checks` int(11) NOT NULL DEFAULT 0,
  `successful` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_availability`),
  UNIQUE KEY `server_id` (`server_id`),
  FOREIGN KEY (`server_id`) REFERENCES `server_list`(`id_server`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `web_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(15) NOT NULL,
  `browser` varchar(50) NOT NULL,
  `os` varchar(50) NOT NULL,
  `all` text NOT NULL,
  `date` varchar(10) NOT NULL,
  `time` varchar(10) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `stime` varchar(255) NOT NULL,
  `sid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
