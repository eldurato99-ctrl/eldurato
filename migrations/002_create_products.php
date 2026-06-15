<?php
// migrations/002_create_products.php
return "CREATE TABLE IF NOT EXISTS `all_products_list` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand` VARCHAR(100) NOT NULL DEFAULT 'ELDURATO',
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL,
  `old_price` DECIMAL(10,2) DEFAULT 0.00,
  `stock` INT NOT NULL DEFAULT 0,
  `stock_status` ENUM('In Stock', 'Out of Stock', 'Low Stock') NOT NULL DEFAULT 'In Stock',
  `description` TEXT NULL,
  `material` VARCHAR(100) NULL,
  `color` VARCHAR(50) NULL,
  `warranty` VARCHAR(100) NULL,
  `images` TEXT NOT NULL, 
  `model_name` VARCHAR(150) NULL,
  `belt_width` VARCHAR(50) NULL,
  `weight` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";