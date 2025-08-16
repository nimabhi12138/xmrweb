<?php
// 通用函数库

// 生成验证码
function generateCaptcha() {
    $code = '';
    for ($i = 0; $i < 4; $i++) {
        $code .= rand(0, 9);
    }
    $_SESSION['captcha'] = $code;
    return $code;
}

// 验证验证码
function verifyCaptcha($input) {
    return isset($_SESSION['captcha']) && $_SESSION['captcha'] === $input;
}

// 密码强度验证
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "密码长度至少8位";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "密码必须包含大写字母";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "密码必须包含小写字母";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "密码必须包含数字";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "密码必须包含特殊字符";
    }
    
    return $errors;
}

// 文件上传处理
function uploadFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg']) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = $file['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['error' => '不支持的文件类型'];
    }
    
    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $uploadPath];
    } else {
        return ['error' => '文件上传失败'];
    }
}

// 获取币种列表
function getCoins($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM coins";
    if ($activeOnly) {
        $sql .= " WHERE status = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// 获取币种详情
function getCoin($pdo, $id) {
    $sql = "SELECT * FROM coins WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// 获取币种的自定义字段
function getCustomFields($pdo, $coinId) {
    $sql = "SELECT * FROM custom_fields WHERE coin_id = ? ORDER BY sort_order ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$coinId]);
    return $stmt->fetchAll();
}

// 处理模板变量替换
function processTemplate($template, $data) {
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . strtoupper($key) . '}}', $value, $template);
    }
    return $template;
}

// 获取用户提交数据
function getUserSubmissions($pdo, $coinId = null) {
    $sql = "SELECT us.*, u.username, c.name as coin_name 
            FROM user_submissions us 
            JOIN users u ON us.user_id = u.id 
            JOIN coins c ON us.coin_id = c.id";
    
    if ($coinId) {
        $sql .= " WHERE us.coin_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$coinId]);
    } else {
        $sql .= " ORDER BY us.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

// 安全输出
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 生成CSRF令牌
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 验证CSRF令牌
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>