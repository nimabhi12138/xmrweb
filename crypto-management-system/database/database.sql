-- 币种管理系统数据库
CREATE DATABASE IF NOT EXISTS crypto_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crypto_management;

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- 币种表
CREATE TABLE IF NOT EXISTS currencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    icon_path VARCHAR(255),
    json_template TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
);

-- 自定义字段表
CREATE TABLE IF NOT EXISTS currency_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currency_id INT NOT NULL,
    field_title VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'textarea', 'select', 'number') DEFAULT 'text',
    field_placeholder VARCHAR(100),
    field_options TEXT,
    is_required TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE CASCADE,
    INDEX idx_currency (currency_id)
);

-- 用户币种配置表
CREATE TABLE IF NOT EXISTS user_currency_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    currency_id INT NOT NULL,
    field_values JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_currency (user_id, currency_id),
    INDEX idx_user (user_id),
    INDEX idx_currency (currency_id)
);

-- 插入默认管理员账户 (密码: Admin@123456)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@crypto.com', '$2y$10$YKjFp8L7hO3vZ9jN5X8xXuW5KpRJqYvCl7H8mG3YZ5Qm9wXnF3KGa', 'admin');