-- ---------------------------------------------------------------------
-- Jinjong shop - database schema
-- Import:  mysql -u root < application/database/schema.sql
-- ---------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `jinjong`
	DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `jinjong`;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
	`id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name`       VARCHAR(100) NOT NULL,
	`email`      VARCHAR(150) NOT NULL,
	`password`   VARCHAR(255) NOT NULL,
	`role`       ENUM('customer','admin') NOT NULL DEFAULT 'customer',
	`phone`      VARCHAR(30)  DEFAULT NULL,
	`address`    TEXT         DEFAULT NULL,
	`is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
	`created_at` DATETIME     NOT NULL,
	`updated_at` DATETIME     DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
	`id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name`        VARCHAR(100) NOT NULL,
	`slug`        VARCHAR(120) NOT NULL,
	`description` TEXT DEFAULT NULL,
	`created_at`  DATETIME NOT NULL,
	`updated_at`  DATETIME DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- products
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
	`id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`category_id` INT UNSIGNED DEFAULT NULL,
	`name`        VARCHAR(180) NOT NULL,
	`slug`        VARCHAR(200) NOT NULL,
	`sku`         VARCHAR(60)  DEFAULT NULL,
	`description` TEXT         DEFAULT NULL,
	`price`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	`stock`       INT NOT NULL DEFAULT 0,
	`image`       VARCHAR(255) DEFAULT NULL,
	`is_active`   TINYINT(1) NOT NULL DEFAULT 1,
	`created_at`  DATETIME NOT NULL,
	`updated_at`  DATETIME DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `products_slug_unique` (`slug`),
	KEY `products_category_id` (`category_id`),
	CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`)
		REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- orders
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
	`id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_number`     VARCHAR(30) NOT NULL,
	`user_id`          INT UNSIGNED DEFAULT NULL,
	`customer_name`    VARCHAR(100) NOT NULL,
	`customer_email`   VARCHAR(150) NOT NULL,
	`customer_phone`   VARCHAR(30) DEFAULT NULL,
	`shipping_address` TEXT NOT NULL,
	`notes`            TEXT DEFAULT NULL,
	`subtotal`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	`shipping_fee`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	`total`            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	`status`           ENUM('pending','paid','processing','shipped','completed','cancelled')
	                   NOT NULL DEFAULT 'pending',
	`payment_method`   VARCHAR(40) NOT NULL DEFAULT 'cod',
	`created_at`       DATETIME NOT NULL,
	`updated_at`       DATETIME DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `orders_number_unique` (`order_number`),
	KEY `orders_user_id` (`user_id`),
	CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`)
		REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- order_items  (product name/price are snapshotted at purchase time)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
	`id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id`     INT UNSIGNED NOT NULL,
	`product_id`   INT UNSIGNED DEFAULT NULL,
	`product_name` VARCHAR(180) NOT NULL,
	`price`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	`qty`          INT NOT NULL DEFAULT 1,
	`subtotal`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	PRIMARY KEY (`id`),
	KEY `order_items_order_id` (`order_id`),
	KEY `order_items_product_id` (`product_id`),
	CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`)
		REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`)
		REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- api_tokens  (bearer tokens for the mobile API)
--
-- Only a SHA-256 of the token is stored. The plaintext is shown once, at
-- issue; a leaked database therefore hands over no usable sessions.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_tokens` (
	`id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id`      INT UNSIGNED NOT NULL,
	`token_hash`   CHAR(64) NOT NULL,
	`device`       VARCHAR(120) DEFAULT NULL,
	`last_used_at` DATETIME DEFAULT NULL,
	`expires_at`   DATETIME DEFAULT NULL,
	`created_at`   DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `api_tokens_hash_unique` (`token_hash`),
	KEY `api_tokens_user_id` (`user_id`),
	CONSTRAINT `api_tokens_user_fk` FOREIGN KEY (`user_id`)
		REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
