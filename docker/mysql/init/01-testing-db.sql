-- Isolated database for the test suite (AC-000-02): tests never touch development data.
CREATE DATABASE IF NOT EXISTS `products_api_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `products_api_testing`.* TO 'products_api'@'%';
FLUSH PRIVILEGES;
