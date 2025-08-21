<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/captcha.php';

$auth = new Auth();
$message = '';
$messageType = '';

// 如果已登录，跳转到相应页面
if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    
    // 验证验证码
    if (!Captcha::verify($captcha)) {
        $message = '验证码错误';
        $messageType = 'danger';
    } else {
        $result = $auth->login($username, $password);
        if ($result['success']) {
            if ($result['role'] === 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录 - 币种管理系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
        }
        .captcha-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .captcha-image {
            height: 40px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            cursor: pointer;
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
        .login-icon {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="card auth-card">
            <div class="login-icon">🔐</div>
            <div class="card-header">
                <h2 class="card-title">欢迎回来</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">用户名或邮箱</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="请输入用户名或邮箱" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">密码</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="请输入密码" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="captcha">验证码</label>
                    <div class="captcha-group">
                        <input type="text" class="form-control" id="captcha" name="captcha" 
                               placeholder="请输入验证码" required style="flex: 1;">
                        <img src="../includes/captcha.php" alt="验证码" class="captcha-image" 
                             onclick="this.src='../includes/captcha.php?'+Math.random()">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">登录系统</button>
            </form>
            
            <div class="auth-links">
                还没有账户？ <a href="register.php">立即注册</a>
            </div>
            
            <div class="auth-links" style="margin-top: 10px; color: var(--text-secondary); font-size: 0.9rem;">
                管理员账户：admin / Admin@123456
            </div>
        </div>
    </div>
</body>
</html>