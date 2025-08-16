<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// 路由处理
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';

// 检查用户登录状态
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// 页面路由
switch ($page) {
    case 'admin':
        if (!$isAdmin) {
            header('Location: index.php?page=login');
            exit;
        }
        include 'admin/dashboard.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php');
        exit;
    case 'coin':
        include 'pages/coin_form.php';
        break;
    case 'submit':
        include 'pages/submit_coin.php';
        break;
    case 'success':
        include 'pages/success.php';
        break;
    default:
        include 'pages/home.php';
        break;
}
?>