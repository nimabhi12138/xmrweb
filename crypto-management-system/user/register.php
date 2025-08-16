<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/captcha.php';

$auth = new Auth();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    
    // 验证验证码
    if (!Captcha::verify($captcha)) {
        $message = '验证码错误';
        $messageType = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = '两次密码输入不一致';
        $messageType = 'danger';
    } else {
        $result = $auth->register($username, $email, $password);
        if ($result['success']) {
            $message = $result['message'] . '，请登录';
            $messageType = 'success';
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
    <title>用户注册 - 币种管理系统</title>
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
        .password-requirements {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 5px;
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
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="card auth-card">
            <div class="card-header">
                <h2 class="card-title">创建新账户</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">用户名</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="请输入用户名" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">邮箱地址</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="请输入邮箱地址" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">密码</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="请输入密码" required>
                    <div class="password-requirements">
                        密码需包含大小写字母、数字和特殊字符，至少8位
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">确认密码</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="请再次输入密码" required>
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
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">注册账户</button>
            </form>
            
            <div class="auth-links">
                已有账户？ <a href="login.php">立即登录</a>
            </div>
        </div>
    </div>
    
    <script>
        // 密码强度验证
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            
            if (password && !pattern.test(password)) {
                e.target.style.borderColor = 'var(--danger-color)';
            } else {
                e.target.style.borderColor = '';
            }
        });
        
        // 确认密码验证
        document.getElementById('confirm_password').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = e.target.value;
            
            if (confirmPassword && password !== confirmPassword) {
                e.target.style.borderColor = 'var(--danger-color)';
            } else {
                e.target.style.borderColor = '';
            }
        });
    </script>
</body>
</html>