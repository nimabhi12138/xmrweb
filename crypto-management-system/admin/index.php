<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();
$db = Database::getInstance();

// 获取统计数据
$userCount = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch()['count'];
$currencyCount = $db->query("SELECT COUNT(*) as count FROM currencies")->fetch()['count'];
$configCount = $db->query("SELECT COUNT(*) as count FROM user_currency_configs")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - 币种管理系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        .stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.1;
        }
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">币种管理系统 - 管理后台</a>
            <ul class="navbar-menu">
                <li><a href="index.php">仪表盘</a></li>
                <li><a href="currencies.php">币种管理</a></li>
                <li><a href="users.php">用户管理</a></li>
                <li><a href="user-configs.php">用户配置</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container" style="margin-top: 30px;">
        <h1 style="margin-bottom: 30px;">欢迎回来，<?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $userCount; ?></div>
                <div class="stat-label">注册用户</div>
                <div class="stat-icon">👥</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $currencyCount; ?></div>
                <div class="stat-label">币种数量</div>
                <div class="stat-icon">💰</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $configCount; ?></div>
                <div class="stat-label">用户配置</div>
                <div class="stat-icon">⚙️</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">快速操作</h2>
            </div>
            <div class="quick-actions">
                <a href="currencies.php?action=add" class="btn btn-primary">添加新币种</a>
                <a href="currencies.php" class="btn btn-secondary">管理币种</a>
                <a href="users.php" class="btn btn-secondary">查看用户</a>
                <a href="user-configs.php" class="btn btn-secondary">用户配置</a>
            </div>
        </div>
        
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h2 class="card-title">最近添加的币种</h2>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>币种名称</th>
                            <th>符号</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currencies = $db->query("SELECT * FROM currencies ORDER BY created_at DESC LIMIT 5");
                        while ($currency = $currencies->fetch()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($currency['name']); ?></td>
                            <td><?php echo htmlspecialchars($currency['symbol']); ?></td>
                            <td>
                                <?php if ($currency['status']): ?>
                                    <span class="badge badge-success">启用</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">禁用</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($currency['created_at'])); ?></td>
                            <td>
                                <a href="currencies.php?action=edit&id=<?php echo $currency['id']; ?>" 
                                   class="btn btn-sm btn-primary">编辑</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>