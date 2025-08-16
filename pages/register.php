<?php
$error = '';
$captcha = generateCaptcha();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $captchaInput = $_POST['captcha'] ?? '';
    
    if (!verifyCaptcha($captchaInput)) {
        $error = '验证码错误';
    } elseif ($password !== $confirmPassword) {
        $error = '两次输入的密码不一致';
    } else {
        $passwordErrors = validatePassword($password);
        if (!empty($passwordErrors)) {
            $error = implode('<br>', $passwordErrors);
        } else {
            // 检查用户名和邮箱是否已存在
            $sql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = '用户名或邮箱已存在';
            } else {
                // 创建新用户
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$username, $email, $hashedPassword])) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = false;
                    
                    header('Location: index.php?success=1');
                    exit;
                } else {
                    $error = '注册失败，请重试';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 币种管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .register-body {
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .captcha-container {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 5px;
            user-select: none;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .password-requirements {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .requirement i {
            margin-right: 8px;
            width: 16px;
        }
        .requirement.valid {
            color: #28a745;
        }
        .requirement.invalid {
            color: #dc3545;
        }
        .back-link {
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-container">
                    <div class="register-header">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h2 class="mb-0">用户注册</h2>
                        <p class="mb-0 opacity-75">创建您的币种管理系统账户</p>
                    </div>
                    
                    <div class="register-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="password-requirements">
                            <h6 class="mb-3"><i class="fas fa-shield-alt"></i> 密码要求：</h6>
                            <div class="requirement" id="req-length">
                                <i class="fas fa-circle"></i> 至少8位字符
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <i class="fas fa-circle"></i> 包含大写字母
                            </div>
                            <div class="requirement" id="req-lowercase">
                                <i class="fas fa-circle"></i> 包含小写字母
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="fas fa-circle"></i> 包含数字
                            </div>
                            <div class="requirement" id="req-special">
                                <i class="fas fa-circle"></i> 包含特殊字符
                            </div>
                        </div>
                        
                        <form method="POST" id="registerForm">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> 用户名
                                </label>
                                <input type="text" class="form-control" name="username" value="<?= e($_POST['username'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> 邮箱
                                </label>
                                <input type="email" class="form-control" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> 密码
                                </label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> 确认密码
                                </label>
                                <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-shield-alt"></i> 验证码
                                </label>
                                <div class="row">
                                    <div class="col-8">
                                        <input type="text" class="form-control" name="captcha" placeholder="请输入验证码" required>
                                    </div>
                                    <div class="col-4">
                                        <div class="captcha-container">
                                            <?= $captcha ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> 注册
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-0">
                                    已有账户？ 
                                    <a href="index.php?page=login" class="text-decoration-none fw-bold" style="color: #667eea;">
                                        立即登录
                                    </a>
                                </p>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="back-link">
                                <i class="fas fa-arrow-left"></i> 返回首页
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 密码强度验证
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // 检查密码要求
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };
            
            // 更新要求显示
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById('req-' + req);
                if (requirements[req]) {
                    element.className = 'requirement valid';
                    element.innerHTML = '<i class="fas fa-check"></i>' + element.innerHTML.substring(element.innerHTML.indexOf(' ') + 1);
                } else {
                    element.className = 'requirement invalid';
                    element.innerHTML = '<i class="fas fa-times"></i>' + element.innerHTML.substring(element.innerHTML.indexOf(' ') + 1);
                }
            });
            
            // 检查确认密码
            if (confirmPassword) {
                checkPasswordMatch();
            }
        });
        
        document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const confirmField = document.getElementById('confirmPassword');
            
            if (confirmPassword && password !== confirmPassword) {
                confirmField.setCustomValidity('密码不匹配');
            } else {
                confirmField.setCustomValidity('');
            }
        }
    </script>
</body>
</html>