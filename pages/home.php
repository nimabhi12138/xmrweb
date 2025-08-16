<?php
$coins = getCoins($pdo, true);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>币种管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 0;
            margin-bottom: 50px;
        }
        .coin-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        .coin-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .coin-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 20px;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin: 20px 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: #667eea !important;
        }
        .nav-link {
            color: #333 !important;
            font-weight: 500;
            border-radius: 20px;
            padding: 8px 20px !important;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white !important;
        }
        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .stats-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coins"></i> 币种管理系统
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=coin">
                                <i class="fas fa-plus"></i> 提交币种
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=logout">
                                <i class="fas fa-sign-out-alt"></i> 退出登录
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login">
                                <i class="fas fa-sign-in-alt"></i> 登录
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=register">
                                <i class="fas fa-user-plus"></i> 注册
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主要内容 -->
    <div class="container">
        <!-- 英雄区域 -->
        <div class="hero-section text-center text-white">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-rocket"></i> 币种管理系统
                    </h1>
                    <p class="lead mb-4">
                        现代化的数字货币管理平台，支持多种币种配置和用户数据管理
                    </p>
                    <?php if (!$isLoggedIn): ?>
                        <a href="index.php?page=register" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket"></i> 立即开始
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=coin" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> 提交币种
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 统计信息 -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h3 class="text-primary"><?= count($coins) ?></h3>
                    <p class="text-muted">支持币种</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-success"><?= count(array_filter($coins, fn($c) => $c['status'])) ?></h3>
                    <p class="text-muted">活跃币种</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-warning">安全可靠</h3>
                    <p class="text-muted">数据保护</p>
                </div>
            </div>
        </div>

        <!-- 币种列表 -->
        <div class="row">
            <div class="col-12">
                <h2 class="text-white text-center mb-5">
                    <i class="fas fa-list"></i> 可用币种
                </h2>
            </div>
        </div>

        <div class="row">
            <?php if (empty($coins)): ?>
                <div class="col-12 text-center">
                    <div class="coin-card p-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">暂无可用币种</h4>
                        <p class="text-muted">管理员正在配置币种，请稍后再来查看</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($coins as $coin): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="coin-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="coin-icon">
                                    <?php if ($coin['icon']): ?>
                                        <img src="uploads/<?= e($coin['icon']) ?>" width="40" height="40" class="rounded">
                                    <?php else: ?>
                                        <i class="fas fa-coins"></i>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title mb-3"><?= e($coin['name']) ?></h5>
                                <p class="card-text text-muted mb-4">
                                    点击下方按钮开始配置您的币种参数
                                </p>
                                <?php if ($isLoggedIn): ?>
                                    <a href="index.php?page=coin&id=<?= $coin['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-cog"></i> 开始配置
                                    </a>
                                <?php else: ?>
                                    <a href="index.php?page=login" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> 登录后配置
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 功能特色 -->
        <div class="row mt-5">
            <div class="col-12">
                <h2 class="text-white text-center mb-5">
                    <i class="fas fa-star"></i> 功能特色
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="coin-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="coin-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5 class="card-title">灵活配置</h5>
                        <p class="card-text text-muted">
                            支持自定义字段配置，满足不同币种的个性化需求
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="coin-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="coin-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5 class="card-title">响应式设计</h5>
                        <p class="card-text text-muted">
                            完美适配各种设备，提供流畅的用户体验
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="coin-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="coin-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="card-title">数据管理</h5>
                        <p class="card-text text-muted">
                            完善的数据管理功能，轻松查看和管理用户提交信息
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="text-center text-white py-4 mt-5">
        <div class="container">
            <p>&copy; 2024 币种管理系统. 保留所有权利.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>