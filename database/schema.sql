-- Fresh Installation Schema
-- Run this for a new installation

CREATE DATABASE IF NOT EXISTS screepsdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE screepsdb;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id_user` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(20) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (`name`),
    INDEX idx_email (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server list table
CREATE TABLE IF NOT EXISTS `server_list` (
    `id_server` INT AUTO_INCREMENT PRIMARY KEY,
    `address` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_address (`address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server info table
CREATE TABLE IF NOT EXISTS `server_info` (
    `id_info` INT AUTO_INCREMENT PRIMARY KEY,
    `server_id` INT NOT NULL UNIQUE,
    `name` VARCHAR(100),
    `description` TEXT,
    `version` VARCHAR(50),
    `players_current` INT DEFAULT 0,
    `online` TINYINT DEFAULT 0 COMMENT '0=unknown, 1=online, 2=offline',
    `last_check` TIMESTAMP NULL,
    `availability` DECIMAL(5,2) DEFAULT 0.00,
    `user_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`server_id`) REFERENCES `server_list`(`id_server`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id_user`) ON DELETE SET NULL,
    INDEX idx_online (`online`),
    INDEX idx_last_check (`last_check`),
    INDEX idx_user_id (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server availability tracking
CREATE TABLE IF NOT EXISTS `server_availability` (
    `id_availability` INT AUTO_INCREMENT PRIMARY KEY,
    `server_id` INT NOT NULL UNIQUE,
    `checks` INT DEFAULT 0,
    `successful` INT DEFAULT 0,
    FOREIGN KEY (`server_id`) REFERENCES `server_list`(`id_server`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
    INDEX idx_token (`token`),
    INDEX idx_expires (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts tracking (for rate limiting)
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username_ip (`username`, `ip_address`),
    INDEX idx_attempted (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Web statistics (optional - from original)
CREATE TABLE IF NOT EXISTS `web_statistic` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user` VARCHAR(15) NOT NULL,
    `browser` VARCHAR(50) NOT NULL,
    `os` VARCHAR(50) NOT NULL,
    `all` TEXT NOT NULL,
    `date` VARCHAR(10) NOT NULL,
    `time` VARCHAR(10) NOT NULL,
    `ip` VARCHAR(15) NOT NULL,
    `stime` VARCHAR(255) NOT NULL,
    `sid` VARCHAR(255) NOT NULL UNIQUE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
