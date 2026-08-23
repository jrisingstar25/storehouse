-- ---------------------------------------------------------------------
-- Jinjong shop - demo data
-- Import after schema.sql:  mysql -u root < application/database/seed.sql
--
-- Accounts created here:
--   admin    / admin123     (admin)
--   customer / customer123  (customer)
-- CHANGE THESE PASSWORDS BEFORE ANY REAL DEPLOYMENT.
-- ---------------------------------------------------------------------

USE `jinjong`;

INSERT INTO `users` (`name`, `username`, `password`, `role`, `phone`, `address`, `is_active`, `created_at`) VALUES
('Site Administrator', 'admin',    '$2y$10$bOshpURqPw8oGziPsFhRdOtKLlQrxjGh.0.NBRyp3pgVOr5XXQpGa', 'admin',    '080-0000-0000', 'HQ', 1, NOW()),
('Demo Customer',      'customer', '$2y$10$dKValp0X5WyGUeqrqKjxYu1usQYmqqBHvWKN00Q70c1dgMG8SW.yu',  'customer', '080-1111-2222', '1-2-3 Shibuya, Tokyo', 1, NOW())
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

INSERT INTO `categories` (`name`, `slug`, `description`, `created_at`) VALUES
('Apparel',     'apparel',     'Shirts, jackets and everyday wear.', NOW()),
('Accessories', 'accessories', 'Bags, belts and small goods.',       NOW()),
('Footwear',    'footwear',    'Sneakers, boots and sandals.',       NOW())
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);

INSERT INTO `products` (`category_id`, `name`, `sku`, `description`, `price`, `stock`, `is_active`, `created_at`) VALUES
(1, 'Heavyweight Cotton Tee',  'APP-001', 'A 240gsm loopwheel cotton t-shirt with a boxy fit and reinforced collar.', 4800.00, 40, 1, NOW()),
(1, 'Selvedge Denim Jacket',   'APP-002', 'Type III jacket in 13oz unwashed selvedge denim. Fades with wear.',       24800.00, 12, 1, NOW()),
(1, 'Merino Crew Knit',        'APP-003', 'Fine-gauge extra-fine merino wool crew neck. Warm without bulk.',        12800.00, 25, 1, NOW()),
(2, 'Waxed Canvas Tote',       'ACC-001', 'Water-resistant waxed canvas tote with leather handles.',                 9800.00, 30, 1, NOW()),
(2, 'Full-Grain Leather Belt', 'ACC-002', 'Vegetable-tanned leather belt with a solid brass buckle.',                7200.00, 50, 1, NOW()),
(2, 'Wool Watch Cap',          'ACC-003', 'Ribbed wool beanie, rolled cuff, made in Japan.',                         3600.00,  0, 1, NOW()),
(3, 'Canvas Court Sneaker',    'FTW-001', 'Vulcanised sole court sneaker in heavy cotton canvas.',                  11800.00, 18, 1, NOW()),
(3, 'Oiled Leather Boot',      'FTW-002', 'Goodyear-welted boot in oiled horsehide. Resoleable.',                   38000.00,  6, 1, NOW()),
(3, 'Suede Desert Boot',       'FTW-003', 'Two-eyelet desert boot on a crepe rubber sole.',                         16800.00, 14, 0, NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
