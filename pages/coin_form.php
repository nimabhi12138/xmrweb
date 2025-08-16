<?php
if (!$isLoggedIn) {
    header('Location: index.php?page=login');
    exit;
}

$coinId = $_GET['id'] ?? null;
if (!$coinId) {
    header('Location: index.php');
    exit;
}

$coin = getCoin($pdo, $coinId);
if (!$coin || !$coin['status']) {
    header('Location: index.php');
    exit;
}

$customFields = getCustomFields($pdo, $coinId);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($coin['name']) ?> - 币种配置</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 20px 0;
        }
        .form-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
        }
        .form-body {
            padding: 40px;
        }
        .form-control, .form-select, .form-textarea {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
        }
        .form-control:focus, .form-select:focus, .form-textarea:focus {
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
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #495057);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
        }
        .coin-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
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
            margin-right: 20px;
        }
        .field-group {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .required-badge {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 10px;
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
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user"></i> <?= e($_SESSION['username']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> 首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=logout">
                            <i class="fas fa-sign-out-alt"></i> 退出登录
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <div class="d-flex align-items-center">
                    <div class="coin-icon">
                        <?php if ($coin['icon']): ?>
                            <img src="uploads/<?= e($coin['icon']) ?>" width="40" height="40" class="rounded">
                        <?php else: ?>
                            <i class="fas fa-coins"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="mb-1"><?= e($coin['name']) ?></h2>
                        <p class="mb-0 opacity-75">配置您的币种参数</p>
                    </div>
                </div>
            </div>
            
            <div class="form-body">
                <div class="coin-info">
                    <h5><i class="fas fa-info-circle"></i> 币种信息</h5>
                    <p class="mb-0 text-muted">
                        请根据下方表单填写您的币种配置信息。所有必填字段都需要完成才能提交。
                    </p>
                </div>
                
                <form method="POST" action="index.php?page=submit">
                    <input type="hidden" name="coin_id" value="<?= $coin['id'] ?>">
                    
                    <?php if (empty($customFields)): ?>
                        <!-- 默认字段 -->
                        <div class="field-group">
                            <h6>
                                <i class="fas fa-wallet"></i> 钱包地址
                                <span class="required-badge">必填</span>
                            </h6>
                            <input type="text" class="form-control" name="WALLET" placeholder="请输入您的钱包地址" required>
                        </div>
                        
                        <div class="field-group">
                            <h6>
                                <i class="fas fa-coins"></i> 数量
                                <span class="required-badge">必填</span>
                            </h6>
                            <input type="text" class="form-control" name="AMOUNT" placeholder="请输入数量" required>
                        </div>
                    <?php else: ?>
                        <!-- 自定义字段 -->
                        <?php foreach ($customFields as $field): ?>
                            <div class="field-group">
                                <h6>
                                    <i class="fas fa-<?= $field['field_type'] === 'select' ? 'list' : ($field['field_type'] === 'textarea' ? 'align-left' : 'edit') ?>"></i>
                                    <?= e($field['title']) ?>
                                    <?php if ($field['is_required']): ?>
                                        <span class="required-badge">必填</span>
                                    <?php endif; ?>
                                </h6>
                                
                                <?php if ($field['field_type'] === 'text'): ?>
                                    <input type="text" class="form-control" 
                                           name="<?= e($field['placeholder']) ?>" 
                                           placeholder="请输入<?= e($field['title']) ?>"
                                           <?= $field['is_required'] ? 'required' : '' ?>>
                                <?php elseif ($field['field_type'] === 'textarea'): ?>
                                    <textarea class="form-control" rows="3" 
                                              name="<?= e($field['placeholder']) ?>" 
                                              placeholder="请输入<?= e($field['title']) ?>"
                                              <?= $field['is_required'] ? 'required' : '' ?>></textarea>
                                <?php elseif ($field['field_type'] === 'select'): ?>
                                    <select class="form-select" 
                                            name="<?= e($field['placeholder']) ?>"
                                            <?= $field['is_required'] ? 'required' : '' ?>>
                                        <option value="">请选择<?= e($field['title']) ?></option>
                                        <?php 
                                        $options = json_decode($field['options'], true) ?: [];
                                        foreach ($options as $option): 
                                        ?>
                                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 返回首页
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> 提交配置
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>