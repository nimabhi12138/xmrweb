<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'coin_management');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 创建数据表
function createTables($pdo) {
    // 用户表
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // 币种表
    $sql = "CREATE TABLE IF NOT EXISTS coins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        icon VARCHAR(255),
        template TEXT NOT NULL,
        status BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // 自定义字段表
    $sql = "CREATE TABLE IF NOT EXISTS custom_fields (
        id INT AUTO_INCREMENT PRIMARY KEY,
        coin_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        field_type ENUM('text', 'textarea', 'select') NOT NULL,
        placeholder VARCHAR(100) NOT NULL,
        is_required BOOLEAN DEFAULT FALSE,
        options TEXT,
        sort_order INT DEFAULT 0,
        FOREIGN KEY (coin_id) REFERENCES coins(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // 用户提交数据表
    $sql = "CREATE TABLE IF NOT EXISTS user_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        coin_id INT NOT NULL,
        data JSON NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (coin_id) REFERENCES coins(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // 创建默认管理员账户
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT IGNORE INTO users (username, email, password, is_admin) VALUES ('admin', 'admin@example.com', ?, TRUE)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$adminPassword]);
}

// 初始化数据库
createTables($pdo);
?>