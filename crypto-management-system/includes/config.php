<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'crypto_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// 站点配置
define('SITE_NAME', '币种管理系统');
define('SITE_URL', 'http://localhost/crypto-management-system/');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// 会话配置
session_start();

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告（生产环境请关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);