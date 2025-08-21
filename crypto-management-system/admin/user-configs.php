<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();
$db = Database::getInstance();

$currency_filter = $_GET['currency'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户配置监控 - 管理后台</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filter-section {
            margin-bottom: 20px;
        }
        .config-data {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
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
                <li><a href="user-configs.php" class="active">用户配置</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container" style="margin-top: 30px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">用户币种配置监控</h2>
            </div>
            
            <div class="filter-section">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <label class="form-label" style="margin: 0;">筛选币种：</label>
                    <select name="currency" class="form-control" style="width: auto;">
                        <option value="">全部币种</option>
                        <?php
                        $currencies = $db->query("SELECT id, name, symbol FROM currencies ORDER BY name");
                        while ($currency = $currencies->fetch()):
                        ?>
                        <option value="<?php echo $currency['id']; ?>" 
                                <?php echo $currency_filter == $currency['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($currency['name'] . ' (' . $currency['symbol'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">筛选</button>
                    <?php if ($currency_filter): ?>
                        <a href="user-configs.php" class="btn btn-secondary">清除筛选</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户</th>
                            <th>币种</th>
                            <th>配置数据</th>
                            <th>配置时间</th>
                            <th>最后更新</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT ucc.*, u.username, u.email, c.name as currency_name, c.symbol 
                                FROM user_currency_configs ucc
                                JOIN users u ON ucc.user_id = u.id
                                JOIN currencies c ON ucc.currency_id = c.id";
                        
                        if ($currency_filter) {
                            $sql .= " WHERE ucc.currency_id = :currency_id";
                        }
                        
                        $sql .= " ORDER BY ucc.created_at DESC";
                        
                        $params = $currency_filter ? ['currency_id' => $currency_filter] : [];
                        $configs = $db->query($sql, $params);
                        
                        while ($config = $configs->fetch()):
                            $fieldValues = json_decode($config['field_values'], true) ?: [];
                        ?>
                        <tr>
                            <td><?php echo $config['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($config['username']); ?></strong><br>
                                <small style="color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($config['email']); ?>
                                </small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($config['currency_name']); ?><br>
                                <small style="color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($config['symbol']); ?>
                                </small>
                            </td>
                            <td>
                                <div class="config-data">
                                    <?php
                                    if (!empty($fieldValues)) {
                                        echo htmlspecialchars(json_encode($fieldValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                    } else {
                                        echo '<span style="color: var(--text-secondary);">无数据</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($config['created_at'])); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($config['updated_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>