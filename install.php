<?php
// 安装脚本
session_start();

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// 检查是否已安装
if (file_exists('config/installed.lock') && $step != 'complete') {
    die('系统已安装，如需重新安装请删除 config/installed.lock 文件');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            // 数据库配置
            $host = $_POST['host'] ?? '';
            $dbname = $_POST['dbname'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            try {
                $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 创建数据库
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$dbname`");
                
                // 创建数据表
                $sql = "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    is_admin BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $pdo->exec($sql);

                $sql = "CREATE TABLE IF NOT EXISTS coins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    icon VARCHAR(255),
                    template TEXT NOT NULL,
                    status BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $pdo->exec($sql);

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

                // 创建默认管理员
                $adminPassword = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, TRUE)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['admin_username'], $_POST['admin_email'], $adminPassword]);
                
                // 生成配置文件
                $configContent = "<?php
// 数据库配置
define('DB_HOST', '$host');
define('DB_NAME', '$dbname');
define('DB_USER', '$username');
define('DB_PASS', '$password');

try {
    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\", DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"数据库连接失败: \" . \$e->getMessage());
}
?>";
                
                file_put_contents('config/database.php', $configContent);
                
                // 创建安装锁定文件
                file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
                
                $success = '数据库配置成功！';
                $step = 3;
                
            } catch (Exception $e) {
                $error = '数据库配置失败：' . $e->getMessage();
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>币种管理系统 - 安装向导</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .install-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 20px 0;
        }
        .install-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 40px;
            text-align: center;
            border-radius: 20px 20px 0 0;
        }
        .install-body {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="install-container">
                    <div class="install-header">
                        <i class="fas fa-cogs fa-3x mb-3"></i>
                        <h2 class="mb-2">币种管理系统</h2>
                        <p class="mb-0">安装向导</p>
                    </div>
                    
                    <div class="install-body">
                        <!-- 步骤指示器 -->
                        <div class="step-indicator">
                            <div class="step <?= $step >= 1 ? 'completed' : 'active' ?>">1</div>
                            <div class="step <?= $step >= 2 ? 'completed' : ($step == 2 ? 'active' : '') ?>">2</div>
                            <div class="step <?= $step >= 3 ? 'completed' : ($step == 3 ? 'active' : '') ?>">3</div>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step == 1): ?>
                            <!-- 环境检查 -->
                            <h4 class="mb-4"><i class="fas fa-check-circle"></i> 环境检查</h4>
                            
                            <div class="mb-3">
                                <strong>PHP版本：</strong>
                                <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
                                    <span class="text-success">✓ <?= PHP_VERSION ?></span>
                                <?php else: ?>
                                    <span class="text-danger">✗ <?= PHP_VERSION ?> (需要 7.4+)</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>PDO扩展：</strong>
                                <?php if (extension_loaded('pdo_mysql')): ?>
                                    <span class="text-success">✓ 已安装</span>
                                <?php else: ?>
                                    <span class="text-danger">✗ 未安装</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>uploads目录：</strong>
                                <?php if (is_writable('uploads') || is_writable('.')): ?>
                                    <span class="text-success">✓ 可写</span>
                                <?php else: ?>
                                    <span class="text-danger">✗ 不可写</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid">
                                <a href="?step=2" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> 下一步
                                </a>
                            </div>
                            
                        <?php elseif ($step == 2): ?>
                            <!-- 数据库配置 -->
                            <h4 class="mb-4"><i class="fas fa-database"></i> 数据库配置</h4>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">数据库主机</label>
                                    <input type="text" class="form-control" name="host" value="localhost" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">数据库名</label>
                                    <input type="text" class="form-control" name="dbname" value="coin_management" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">数据库用户名</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">数据库密码</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5 class="mb-3">管理员账户</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">管理员用户名</label>
                                    <input type="text" class="form-control" name="admin_username" value="admin" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">管理员邮箱</label>
                                    <input type="email" class="form-control" name="admin_email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">管理员密码</label>
                                    <input type="password" class="form-control" name="admin_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cog"></i> 开始安装
                                    </button>
                                </div>
                            </form>
                            
                        <?php elseif ($step == 3): ?>
                            <!-- 安装完成 -->
                            <div class="text-center">
                                <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                                <h4 class="mb-3">安装完成！</h4>
                                <p class="text-muted mb-4">
                                    币种管理系统已成功安装，您现在可以开始使用了。
                                </p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <a href="index.php" class="btn btn-primary w-100">
                                            <i class="fas fa-home"></i> 访问前台
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="index.php?page=admin" class="btn btn-success w-100">
                                            <i class="fas fa-cogs"></i> 访问后台
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning mt-4">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>安全提醒：</strong> 安装完成后请删除 install.php 文件！
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>