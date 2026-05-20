-- Hospital Inventory Management System SQL Schema
-- Created for CodeIgniter 3 and MySQL XAMPP setup

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Create users table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'staff') DEFAULT 'staff',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create audit_logs table
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `username` VARCHAR(50) DEFAULT 'Guest',
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed users table
-- Password for 'admin' is 'admin' (bcrypt)
INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES
('admin', '$2y$10$3Xhfb.GCbHUtlu0haHSTTOR7X75o9CkiE.EvsWqRs6BNXJNmQPGxe', 'Hospital Administrator', 'admin'),
('staff_juan', '$2y$10$yN85s.cOL9FPbeGm9dLh5OqxNOoan/0qeaPzhcdtAIbsZnz/69NjS', 'Juan Dela Cruz (Pharmacy Staff)', 'staff');

-- 4. Seed initial audit log entries
INSERT INTO `audit_logs` (`user_id`, `username`, `action`, `module`, `description`, `ip_address`) VALUES
(1, 'admin', 'SYSTEM_INIT', 'System', 'Database initialized and default administrative account seeded.', '127.0.0.1');

-- 5. Create items table
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `department` ENUM('LAB', 'PHARMA', 'SUPPLIES', 'OR/DR COMPLEX') NOT NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `unit` VARCHAR(50) NOT NULL DEFAULT 'pcs',
    `min_stock` INT NOT NULL DEFAULT 5,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Seed initial items for departments
INSERT INTO `items` (`item_code`, `name`, `description`, `department`, `quantity`, `unit`, `min_stock`) VALUES
-- LAB
('LAB-001', 'Blood Collection Tube (Red Top)', 'Silicone-coated tubes for serum determinations.', 'LAB', 150, 'pcs', 20),
('LAB-002', 'Microscope Glass Slides', 'Pre-cleaned glass slides for lab microscopy.', 'LAB', 80, 'box', 10),
('LAB-003', 'Rapid Antigen Test Kits', 'Rapid diagnostic test kits for infectious disease screening.', 'LAB', 4, 'pcs', 15), -- Low stock

-- PHARMA
('PHA-001', 'Paracetamol 500mg Tablets', 'Analgesic and antipyretic tablets.', 'PHARMA', 1200, 'pcs', 100),
('PHA-002', 'Amoxicillin 250mg Capsules', 'Broad-spectrum antibiotic medication.', 'PHARMA', 500, 'pcs', 50),
('PHA-003', 'Ibuprofen 400mg Tablets', 'Nonsteroidal anti-inflammatory drug.', 'PHARMA', 3, 'pcs', 20), -- Low stock

-- SUPPLIES
('SUP-001', 'Surgical Gloves (Size 7.5)', 'Sterile latex powder-free surgical gloves.', 'SUPPLIES', 300, 'pairs', 50),
('SUP-002', 'N95 Respirator Masks', 'Particulate respirator mask for medical protection.', 'SUPPLIES', 120, 'pcs', 30),
('SUP-003', 'Adhesive Bandages (Assorted)', 'Elastic strip bandages for wound care.', 'SUPPLIES', 2, 'box', 5), -- Low stock

-- OR/DR COMPLEX
('ORD-001', 'Suture Silk 3-0', 'Non-absorbable sterile surgical suture.', 'OR/DR COMPLEX', 90, 'box', 15),
('ORD-002', 'Scalpel Blade No. 10', 'High-grade carbon steel surgical scalpel blades.', 'OR/DR COMPLEX', 150, 'pcs', 20),
('ORD-003', 'Sterile Drape Pack', 'Complete disposable sterile surgical drape kit.', 'OR/DR COMPLEX', 1, 'pcs', 10); -- Low stock

-- 7. Create departments table
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Seed departments table
INSERT INTO `departments` (`code`, `name`, `description`) VALUES
('LAB', 'Laboratory Services', 'Clinical laboratory and diagnostic testing services.'),
('PHARMA', 'Pharmacy Department', 'Medication dispensing and pharmaceutical care services.'),
('SUPPLIES', 'Central Supplies', 'General hospital medical supplies and distribution.'),
('OR/DR COMPLEX', 'OR/DR Complex', 'Operating Room and Delivery Room specialized services.');

-- 9. Add department relation to users
ALTER TABLE `users` ADD COLUMN `department_id` INT NULL AFTER `role`;
ALTER TABLE `users` ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

-- Assign staff_juan (Juan Dela Cruz) to the PHARMA department (id = 2)
UPDATE `users` SET `department_id` = 2 WHERE `username` = 'staff_juan';

