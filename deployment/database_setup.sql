-- Database setup for fernandozucula.com
-- Create database and user for the Laravel application

-- Create database
CREATE DATABASE IF NOT EXISTS fernandozucula_video CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create database user
CREATE USER IF NOT EXISTS 'fernandozucula_user'@'localhost' IDENTIFIED BY 'ALTERAR_PARA_PASSWORD_FORTE_PRODUCAO';

-- Grant privileges
GRANT ALL PRIVILEGES ON fernandozucula_video.* TO 'fernandozucula_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Use the database
USE fernandozucula_video;

-- Show tables (after migrations)
SHOW TABLES;
