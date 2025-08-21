<?php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($username, $email, $password) {
        // 验证密码强度
        if (!$this->validatePassword($password)) {
            return ['success' => false, 'message' => '密码必须包含大小写字母、数字和特殊字符，且长度至少8位'];
        }
        
        // 检查用户名是否存在
        $stmt = $this->db->query("SELECT id FROM users WHERE username = :username", ['username' => $username]);
        if ($stmt && $stmt->fetch()) {
            return ['success' => false, 'message' => '用户名已存在'];
        }
        
        // 检查邮箱是否存在
        $stmt = $this->db->query("SELECT id FROM users WHERE email = :email", ['email' => $email]);
        if ($stmt && $stmt->fetch()) {
            return ['success' => false, 'message' => '邮箱已被注册'];
        }
        
        // 加密密码并插入用户
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'user'
        ]);
        
        if ($userId) {
            return ['success' => true, 'message' => '注册成功'];
        }
        
        return ['success' => false, 'message' => '注册失败，请重试'];
    }
    
    public function login($username, $password) {
        $stmt = $this->db->query(
            "SELECT id, username, email, password, role FROM users WHERE username = :username OR email = :username",
            ['username' => $username]
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => '登录失败'];
        }
        
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => '用户名或密码错误'];
        }
        
        // 设置会话
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        return ['success' => true, 'message' => '登录成功', 'role' => $user['role']];
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /crypto-management-system/user/login.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: /crypto-management-system/user/login.php');
            exit;
        }
    }
    
    private function validatePassword($password) {
        // 至少8个字符，包含大写字母、小写字母、数字和特殊字符
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
}