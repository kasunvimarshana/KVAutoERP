-- Inventory Service MySQL initialization
CREATE DATABASE IF NOT EXISTS ims_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON ims_inventory.* TO 'inventory_user'@'%';
