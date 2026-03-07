-- Create the Keycloak database for the saas_user
CREATE DATABASE IF NOT EXISTS keycloak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON keycloak.* TO 'saas_user'@'%';
FLUSH PRIVILEGES;
