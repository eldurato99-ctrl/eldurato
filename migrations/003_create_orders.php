<?php
// migrations/003_create_orders.php
return "CREATE TABLE IF NOT EXISTS `all_orders_list` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(15) NOT NULL,
  `shipping_address` TEXT NOT NULL, 
  `city` VARCHAR(100) NOT NULL,      
  `pincode` VARCHAR(10) NOT NULL,    
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'COD',
  `order_status` VARCHAR(50) DEFAULT 'pending',
  `tracking_status` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";