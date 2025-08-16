<?php
// 处理币种管理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_coin':
            $name = $_POST['name'] ?? '';
            $template = $_POST['template'] ?? '';
            $status = isset($_POST['status']) ? 1 : 0;
            
            // 处理图标上传
            $icon = '';
            if (isset($_FILES['icon']) && $_FILES['icon']['error'] === 0) {
                $uploadResult = uploadFile($_FILES['icon']);
                if (isset($uploadResult['success'])) {
                    $icon = $uploadResult['filename'];
                }
            }
            
            $sql = "INSERT INTO coins (name, icon, template, status) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $icon, $template, $status]);
            
            header('Location: index.php?page=admin&action=coins');
            exit;
            break;
            
        case 'update_coin':
            $id = $_POST['id'] ?? '';
            $name = $_POST['name'] ?? '';
            $template = $_POST['template'] ?? '';
            $status = isset($_POST['status']) ? 1 : 0;
            
            $sql = "UPDATE coins SET name = ?, template = ?, status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $template, $status, $id]);
            
            header('Location: index.php?page=admin&action=coins');
            exit;
            break;
            
        case 'delete_coin':
            $id = $_POST['id'] ?? '';
            $sql = "DELETE FROM coins WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            header('Location: index.php?page=admin&action=coins');
            exit;
            break;
    }
}

$action = $_GET['action'] ?? 'dashboard';
$coins = getCoins($pdo, false);
$submissions = getUserSubmissions($pdo);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>币种管理系统 - 管理后台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .admin-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            border-radius: 15px 0 0 15px;
        }
        .nav-link {
            color: #ecf0f1;
            border-radius: 10px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
        }
        .btn-danger {
            background: linear-gradient(45deg, #ff6b6b, #ee5a52);
            border: none;
            border-radius: 25px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-3">
                <div class="admin-container h-100">
                    <div class="sidebar p-4 h-100">
                        <h4 class="text-white mb-4">
                            <i class="fas fa-cogs"></i> 管理后台
                        </h4>
                        <nav class="nav flex-column">
                            <a class="nav-link <?= $action === 'dashboard' ? 'active' : '' ?>" href="index.php?page=admin&action=dashboard">
                                <i class="fas fa-tachometer-alt"></i> 仪表盘
                            </a>
                            <a class="nav-link <?= $action === 'coins' ? 'active' : '' ?>" href="index.php?page=admin&action=coins">
                                <i class="fas fa-coins"></i> 币种管理
                            </a>
                            <a class="nav-link <?= $action === 'fields' ? 'active' : '' ?>" href="index.php?page=admin&action=fields">
                                <i class="fas fa-list"></i> 字段管理
                            </a>
                            <a class="nav-link <?= $action === 'submissions' ? 'active' : '' ?>" href="index.php?page=admin&action=submissions">
                                <i class="fas fa-database"></i> 用户数据
                            </a>
                            <a class="nav-link" href="index.php?page=logout">
                                <i class="fas fa-sign-out-alt"></i> 退出登录
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="admin-container p-4">
                    <?php if ($action === 'dashboard'): ?>
                        <!-- 仪表盘 -->
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-coins fa-3x text-primary mb-3"></i>
                                        <h5 class="card-title">总币种数</h5>
                                        <h2 class="text-primary"><?= count($coins) ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                                        <h5 class="card-title">活跃币种</h5>
                                        <h2 class="text-success"><?= count(array_filter($coins, fn($c) => $c['status'])) ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-database fa-3x text-warning mb-3"></i>
                                        <h5 class="card-title">用户提交</h5>
                                        <h2 class="text-warning"><?= count($submissions) ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-chart-line"></i> 最近活动</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>用户</th>
                                                        <th>币种</th>
                                                        <th>提交时间</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($submissions, 0, 10) as $submission): ?>
                                                    <tr>
                                                        <td><?= e($submission['username']) ?></td>
                                                        <td><?= e($submission['coin_name']) ?></td>
                                                        <td><?= date('Y-m-d H:i:s', strtotime($submission['created_at'])) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($action === 'coins'): ?>
                        <!-- 币种管理 -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4><i class="fas fa-coins"></i> 币种管理</h4>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCoinModal">
                                <i class="fas fa-plus"></i> 添加币种
                            </button>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>图标</th>
                                                <th>名称</th>
                                                <th>状态</th>
                                                <th>创建时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($coins as $coin): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($coin['icon']): ?>
                                                        <img src="uploads/<?= e($coin['icon']) ?>" width="30" height="30" class="rounded">
                                                    <?php else: ?>
                                                        <i class="fas fa-coins text-muted"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= e($coin['name']) ?></td>
                                                <td>
                                                    <span class="badge <?= $coin['status'] ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $coin['status'] ? '启用' : '禁用' ?>
                                                    </span>
                                                </td>
                                                <td><?= date('Y-m-d H:i', strtotime($coin['created_at'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editCoin(<?= $coin['id'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCoin(<?= $coin['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($action === 'submissions'): ?>
                        <!-- 用户数据 -->
                        <h4 class="mb-4"><i class="fas fa-database"></i> 用户提交数据</h4>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>用户</th>
                                                <th>币种</th>
                                                <th>提交数据</th>
                                                <th>提交时间</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($submissions as $submission): ?>
                                            <tr>
                                                <td><?= e($submission['username']) ?></td>
                                                <td><?= e($submission['coin_name']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewData(<?= $submission['id'] ?>)">
                                                        查看数据
                                                    </button>
                                                </td>
                                                <td><?= date('Y-m-d H:i:s', strtotime($submission['created_at'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加币种模态框 -->
    <div class="modal fade" id="addCoinModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">添加币种</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_coin">
                        
                        <div class="mb-3">
                            <label class="form-label">币种名称</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">图标</label>
                            <input type="file" class="form-control" name="icon" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">JSON模板</label>
                            <textarea class="form-control" name="template" rows="5" placeholder='{"wallet": "{{WALLET}}", "amount": "{{AMOUNT}}"}'
                                      required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status" checked>
                                <label class="form-check-label">启用</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCoin(id) {
            // 实现编辑功能
            alert('编辑功能待实现');
        }
        
        function deleteCoin(id) {
            if (confirm('确定要删除这个币种吗？')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_coin">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function viewData(id) {
            // 实现查看数据功能
            alert('查看数据功能待实现');
        }
    </script>
</body>
</html>