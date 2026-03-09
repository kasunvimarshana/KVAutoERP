-- Auth Service MySQL initialization
CREATE DATABASE IF NOT EXISTS ims_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON ims_auth.* TO 'auth_user'@'%';

-- Enable event scheduler for token cleanup
SET GLOBAL event_scheduler = ON;
