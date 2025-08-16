<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();

$user = $auth->getCurrentUser();
$userId = $user['id'];

// 获取用户已配置的币种
$userConfigs = $db->query(
    "SELECT ucc.*, c.name, c.symbol, c.icon_path 
     FROM user_currency_configs ucc
     JOIN currencies c ON ucc.currency_id = c.id
     WHERE ucc.user_id = ?
     ORDER BY ucc.created_at DESC",
    [$userId]
);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户仪表盘 - 币种管理系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .welcome-section {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(0, 102, 255, 0.1));
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        .welcome-title {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .crypto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .crypto-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .crypto-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.2);
        }
        .crypto-icon {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        .crypto-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .crypto-symbol {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .config-time {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 15px;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">币种管理系统</a>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">仪表盘</a></li>
                <li><a href="configure.php">配置币种</a></li>
                <li><a href="profile.php">个人资料</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container" style="margin-top: 30px;">
        <div class="welcome-section">
            <h1 class="welcome-title">欢迎回来，<?php echo htmlspecialchars($user['username']); ?>！</h1>
            <p style="color: var(--text-secondary);">在这里管理您的币种配置</p>
        </div>
        
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">我的币种配置</h2>
                <a href="configure.php" class="btn btn-primary">配置新币种</a>
            </div>
            
            <?php if ($userConfigs->rowCount() > 0): ?>
                <div class="crypto-grid">
                    <?php while ($config = $userConfigs->fetch()): ?>
                        <div class="crypto-card">
                            <?php if ($config['icon_path']): ?>
                                <img src="..<?php echo htmlspecialchars($config['icon_path']); ?>" 
                                     class="crypto-icon" alt="<?php echo htmlspecialchars($config['name']); ?>">
                            <?php else: ?>
                                <div class="crypto-icon" style="font-size: 3rem;">💰</div>
                            <?php endif; ?>
                            
                            <div class="crypto-name"><?php echo htmlspecialchars($config['name']); ?></div>
                            <div class="crypto-symbol"><?php echo htmlspecialchars($config['symbol']); ?></div>
                            
                            <div class="action-buttons">
                                <a href="configure.php?currency=<?php echo $config['currency_id']; ?>" 
                                   class="btn btn-sm btn-primary">编辑配置</a>
                                <a href="view-config.php?id=<?php echo $config['id']; ?>" 
                                   class="btn btn-sm btn-secondary">查看详情</a>
                            </div>
                            
                            <div class="config-time">
                                配置时间：<?php echo date('Y-m-d H:i', strtotime($config['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>您还没有配置任何币种</h3>
                    <p>点击下方按钮开始配置您的第一个币种</p>
                    <a href="configure.php" class="btn btn-primary" style="margin-top: 20px;">开始配置</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>