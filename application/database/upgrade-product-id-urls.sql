-- ---------------------------------------------------------------------
-- Upgrade an existing database: products no longer carry a URL slug.
--
--     mysql -u root < application/database/upgrade-product-id-urls.sql
--
-- Product URLs are /product/{id} now, so the column and its unique index
-- have no reader left. Categories keep their slug - /category/{slug} is
-- unchanged.
--
-- NOTE: this is one-way. Any /product/{slug} links already published
-- elsewhere will 404 once the column is gone, so capture the mapping first
-- if you need to set up redirects:
--
--     SELECT id, slug FROM products;
-- ---------------------------------------------------------------------

USE `jinjong`;

ALTER TABLE `products`
	DROP INDEX `products_slug_unique`,
	DROP COLUMN `slug`;
