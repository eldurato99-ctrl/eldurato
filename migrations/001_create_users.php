<?php
// migrations/001_create_users.php
return "CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `mobile` VARCHAR(15) NULL UNIQUE,
  `email` VARCHAR(100) NULL UNIQUE,
  `password` VARCHAR(255) NULL,
  `role` VARCHAR(20) DEFAULT 'user',
  `google_id` VARCHAR(255) NULL UNIQUE,
  `profile_pic` VARCHAR(255) NULL,
  `otp` VARCHAR(10) NULL,
  `otp_expiry` DATETIME NULL,
  `reset_token` VARCHAR(255) NULL UNIQUE,
  `token_expire` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";