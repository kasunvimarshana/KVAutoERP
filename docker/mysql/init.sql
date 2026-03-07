CREATE DATABASE IF NOT EXISTS product_service;
CREATE DATABASE IF NOT EXISTS inventory_service;
CREATE DATABASE IF NOT EXISTS order_service;
CREATE DATABASE IF NOT EXISTS user_service;
CREATE DATABASE IF NOT EXISTS keycloak_db;

GRANT ALL PRIVILEGES ON product_service.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON inventory_service.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON order_service.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON user_service.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON keycloak_db.* TO 'root'@'%';
FLUSH PRIVILEGES;
