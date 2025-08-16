<?php
$error = '';
$captcha = generateCaptcha();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captchaInput = $_POST['captcha'] ?? '';
    
    if (!verifyCaptcha($captchaInput)) {
        $error = '验证码错误';
    } else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            if ($user['is_admin']) {
                header('Location: index.php?page=admin');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 币种管理系统</title>
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
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .login-body {
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
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="login-header">
                        <i class="fas fa-sign-in-alt fa-3x mb-3"></i>
                        <h2 class="mb-0">用户登录</h2>
                        <p class="mb-0 opacity-75">欢迎回到币种管理系统</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?= e($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> 用户名或邮箱
                                </label>
                                <input type="text" class="form-control" name="username" value="<?= e($_POST['username'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> 密码
                                </label>
                                <input type="password" class="form-control" name="password" required>
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
                                    <i class="fas fa-sign-in-alt"></i> 登录
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-0">
                                    还没有账户？ 
                                    <a href="index.php?page=register" class="text-decoration-none fw-bold" style="color: #667eea;">
                                        立即注册
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
</body>
</html>