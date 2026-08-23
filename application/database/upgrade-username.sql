-- ---------------------------------------------------------------------
-- Upgrade an existing database: email -> username, and drop order emails.
--
-- Run once, on a database created before this change:
--     mysql -u root < application/database/upgrade-username.sql
--
-- A fresh install does not need this; schema.sql already has the new shape.
--
-- NOTE ON EXISTING DATA
-- Existing addresses are carried over as usernames by taking the part before
-- the "@". That can collide (alice@a.com and alice@b.com both become
-- "alice"), and the unique index will reject the second one. Check first:
--
--     SELECT SUBSTRING_INDEX(email,'@',1) AS u, COUNT(*) c
--     FROM users GROUP BY u HAVING c > 1;
--
-- Resolve any duplicates by hand before running the ALTER below.
-- ---------------------------------------------------------------------

USE `jinjong`;

-- Derive usernames from the local part of each address.
UPDATE `users` SET `email` = SUBSTRING_INDEX(`email`, '@', 1);

ALTER TABLE `users`
	DROP INDEX `users_email_unique`,
	CHANGE COLUMN `email` `username` VARCHAR(60) NOT NULL,
	ADD UNIQUE KEY `users_username_unique` (`username`);

-- Orders no longer hold a contact address.
ALTER TABLE `orders` DROP COLUMN `customer_email`;
