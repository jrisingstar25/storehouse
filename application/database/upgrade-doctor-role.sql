-- ---------------------------------------------------------------------
-- Upgrade an existing database: add the doctor role and its applications.
--
--     mysql -u root < application/database/upgrade-doctor-role.sql
--
-- A fresh install does not need this; schema.sql already has both.
-- ---------------------------------------------------------------------

USE `jinjong`;

ALTER TABLE `users`
	MODIFY COLUMN `role` ENUM('customer','doctor','admin') NOT NULL DEFAULT 'customer';

CREATE TABLE IF NOT EXISTS `doctor_applications` (
	`id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id`          INT UNSIGNED NOT NULL,
	`diploma_file`     VARCHAR(255) NOT NULL,
	`graduation_file`  VARCHAR(255) NOT NULL,
	`status`           ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
	`review_note`      TEXT DEFAULT NULL,
	`reviewed_by`      INT UNSIGNED DEFAULT NULL,
	`reviewed_at`      DATETIME DEFAULT NULL,
	`created_at`       DATETIME NOT NULL,
	`updated_at`       DATETIME DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `doctor_apps_user_id` (`user_id`),
	KEY `doctor_apps_status` (`status`),
	CONSTRAINT `doctor_apps_user_fk` FOREIGN KEY (`user_id`)
		REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `doctor_apps_reviewer_fk` FOREIGN KEY (`reviewed_by`)
		REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
