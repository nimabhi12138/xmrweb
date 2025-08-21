<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = '';

// 处理币种状态切换
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $db->query("SELECT status FROM currencies WHERE id = ?", [$id]);
    if ($currency = $stmt->fetch()) {
        $newStatus = $currency['status'] ? 0 : 1;
        $db->update('currencies', ['status' => $newStatus], 'id = ?', [$id]);
        header('Location: currencies.php');
        exit;
    }
}

// 处理删除
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->delete('currencies', 'id = ?', [$id]);
    header('Location: currencies.php');
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $symbol = $_POST['symbol'] ?? '';
    $json_template = $_POST['json_template'] ?? '';
    $status = isset($_POST['status']) ? 1 : 0;
    
    // 处理图标上传
    $icon_path = '';
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/png', 'image/svg+xml', 'image/jpeg'];
        if (in_array($_FILES['icon']['type'], $allowed)) {
            $extension = pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $upload_path = UPLOAD_PATH . $filename;
            
            if (move_uploaded_file($_FILES['icon']['tmp_name'], $upload_path)) {
                $icon_path = '/assets/uploads/' . $filename;
            }
        }
    }
    
    if ($action === 'add') {
        $data = [
            'name' => $name,
            'symbol' => $symbol,
            'json_template' => $json_template,
            'status' => $status
        ];
        
        if ($icon_path) {
            $data['icon_path'] = $icon_path;
        }
        
        $currencyId = $db->insert('currencies', $data);
        
        if ($currencyId) {
            // 处理自定义字段
            if (isset($_POST['fields'])) {
                foreach ($_POST['fields'] as $index => $field) {
                    if (!empty($field['title'])) {
                        $db->insert('currency_fields', [
                            'currency_id' => $currencyId,
                            'field_title' => $field['title'],
                            'field_type' => $field['type'],
                            'field_placeholder' => $field['placeholder'],
                            'field_options' => $field['options'] ?? '',
                            'is_required' => isset($field['required']) ? 1 : 0,
                            'sort_order' => $index
                        ]);
                    }
                }
            }
            
            $message = '币种添加成功';
            $messageType = 'success';
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $data = [
            'name' => $name,
            'symbol' => $symbol,
            'json_template' => $json_template,
            'status' => $status
        ];
        
        if ($icon_path) {
            $data['icon_path'] = $icon_path;
        }
        
        $db->update('currencies', $data, 'id = ?', [$id]);
        
        // 删除旧的字段
        $db->delete('currency_fields', 'currency_id = ?', [$id]);
        
        // 添加新的字段
        if (isset($_POST['fields'])) {
            foreach ($_POST['fields'] as $index => $field) {
                if (!empty($field['title'])) {
                    $db->insert('currency_fields', [
                        'currency_id' => $id,
                        'field_title' => $field['title'],
                        'field_type' => $field['type'],
                        'field_placeholder' => $field['placeholder'],
                        'field_options' => $field['options'] ?? '',
                        'is_required' => isset($field['required']) ? 1 : 0,
                        'sort_order' => $index
                    ]);
                }
            }
        }
        
        $message = '币种更新成功';
        $messageType = 'success';
    }
}

// 获取编辑数据
$editData = null;
$editFields = [];
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->query("SELECT * FROM currencies WHERE id = ?", [$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->query("SELECT * FROM currency_fields WHERE currency_id = ? ORDER BY sort_order", [$id]);
    while ($field = $stmt->fetch()) {
        $editFields[] = $field;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>币种管理 - 管理后台</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .field-row {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr 1fr auto auto;
            gap: 10px;
            align-items: center;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .field-row input, .field-row select {
            width: 100%;
        }
        .add-field-btn {
            margin-top: 10px;
        }
        .remove-field-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .json-editor {
            font-family: 'Courier New', monospace;
            background: rgba(0, 0, 0, 0.5);
        }
        .icon-preview {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">币种管理系统 - 管理后台</a>
            <ul class="navbar-menu">
                <li><a href="index.php">仪表盘</a></li>
                <li><a href="currencies.php" class="active">币种管理</a></li>
                <li><a href="users.php">用户管理</a></li>
                <li><a href="user-configs.php">用户配置</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container" style="margin-top: 30px;">
        <?php if ($action === 'list'): ?>
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title">币种列表</h2>
                    <a href="?action=add" class="btn btn-primary">添加新币种</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>图标</th>
                                <th>币种名称</th>
                                <th>符号</th>
                                <th>状态</th>
                                <th>字段数量</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $currencies = $db->query("SELECT c.*, COUNT(cf.id) as field_count 
                                                      FROM currencies c 
                                                      LEFT JOIN currency_fields cf ON c.id = cf.currency_id 
                                                      GROUP BY c.id 
                                                      ORDER BY c.created_at DESC");
                            while ($currency = $currencies->fetch()):
                            ?>
                            <tr>
                                <td><?php echo $currency['id']; ?></td>
                                <td>
                                    <?php if ($currency['icon_path']): ?>
                                        <img src="..<?php echo htmlspecialchars($currency['icon_path']); ?>" 
                                             style="width: 30px; height: 30px; object-fit: contain;">
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">无</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($currency['name']); ?></td>
                                <td><?php echo htmlspecialchars($currency['symbol']); ?></td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" <?php echo $currency['status'] ? 'checked' : ''; ?>
                                               onchange="window.location.href='?toggle=<?php echo $currency['id']; ?>'">
                                        <span class="switch-slider"></span>
                                    </label>
                                </td>
                                <td><?php echo $currency['field_count']; ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($currency['created_at'])); ?></td>
                                <td>
                                    <a href="?action=edit&id=<?php echo $currency['id']; ?>" 
                                       class="btn btn-sm btn-primary">编辑</a>
                                    <a href="?delete=<?php echo $currency['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('确定要删除这个币种吗？')">删除</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <?php echo $action === 'add' ? '添加新币种' : '编辑币种'; ?>
                    </h2>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($editData): ?>
                        <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label" for="name">币种名称</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $editData ? htmlspecialchars($editData['name']) : ''; ?>"
                               placeholder="例如：Bitcoin" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="symbol">币种符号</label>
                        <input type="text" class="form-control" id="symbol" name="symbol" 
                               value="<?php echo $editData ? htmlspecialchars($editData['symbol']) : ''; ?>"
                               placeholder="例如：BTC" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="icon">币种图标</label>
                        <input type="file" class="form-control" id="icon" name="icon" 
                               accept="image/png,image/jpeg,image/svg+xml">
                        <?php if ($editData && $editData['icon_path']): ?>
                            <img src="..<?php echo htmlspecialchars($editData['icon_path']); ?>" 
                                 class="icon-preview">
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="json_template">JSON模板</label>
                        <textarea class="form-control json-editor" id="json_template" name="json_template" 
                                  rows="10" placeholder='{"wallet": "{{WALLET}}", "network": "{{NETWORK}}"}'><?php echo $editData ? htmlspecialchars($editData['json_template']) : ''; ?></textarea>
                        <small style="color: var(--text-secondary);">
                            使用占位符如 {{WALLET}}, {{NETWORK}} 等，将被用户输入的值替换
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="status" value="1" 
                                   <?php echo (!$editData || $editData['status']) ? 'checked' : ''; ?>>
                            启用此币种
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">自定义字段</label>
                        <div id="fields-container">
                            <?php if ($editFields): ?>
                                <?php foreach ($editFields as $field): ?>
                                <div class="field-row">
                                    <input type="text" name="fields[][title]" placeholder="字段标题" 
                                           value="<?php echo htmlspecialchars($field['field_title']); ?>">
                                    <select name="fields[][type]">
                                        <option value="text" <?php echo $field['field_type'] === 'text' ? 'selected' : ''; ?>>文本框</option>
                                        <option value="textarea" <?php echo $field['field_type'] === 'textarea' ? 'selected' : ''; ?>>多行文本</option>
                                        <option value="number" <?php echo $field['field_type'] === 'number' ? 'selected' : ''; ?>>数字</option>
                                        <option value="select" <?php echo $field['field_type'] === 'select' ? 'selected' : ''; ?>>下拉选择</option>
                                    </select>
                                    <input type="text" name="fields[][placeholder]" placeholder="占位符 (如 {{WALLET}})" 
                                           value="<?php echo htmlspecialchars($field['field_placeholder']); ?>">
                                    <input type="text" name="fields[][options]" placeholder="选项 (用逗号分隔)" 
                                           value="<?php echo htmlspecialchars($field['field_options']); ?>">
                                    <label>
                                        <input type="checkbox" name="fields[][required]" value="1" 
                                               <?php echo $field['is_required'] ? 'checked' : ''; ?>>
                                        必填
                                    </label>
                                    <button type="button" class="remove-field-btn" onclick="removeField(this)">删除</button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-secondary add-field-btn" onclick="addField()">
                            添加字段
                        </button>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $action === 'add' ? '添加币种' : '更新币种'; ?>
                        </button>
                        <a href="currencies.php" class="btn btn-secondary">返回列表</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        let fieldIndex = <?php echo count($editFields); ?>;
        
        function addField() {
            const container = document.getElementById('fields-container');
            const fieldRow = document.createElement('div');
            fieldRow.className = 'field-row';
            fieldRow.innerHTML = `
                <input type="text" name="fields[${fieldIndex}][title]" placeholder="字段标题">
                <select name="fields[${fieldIndex}][type]">
                    <option value="text">文本框</option>
                    <option value="textarea">多行文本</option>
                    <option value="number">数字</option>
                    <option value="select">下拉选择</option>
                </select>
                <input type="text" name="fields[${fieldIndex}][placeholder]" placeholder="占位符 (如 {{WALLET}})">
                <input type="text" name="fields[${fieldIndex}][options]" placeholder="选项 (用逗号分隔)">
                <label>
                    <input type="checkbox" name="fields[${fieldIndex}][required]" value="1">
                    必填
                </label>
                <button type="button" class="remove-field-btn" onclick="removeField(this)">删除</button>
            `;
            container.appendChild(fieldRow);
            fieldIndex++;
        }
        
        function removeField(button) {
            button.parentElement.remove();
        }
        
        // 如果没有字段，添加一个默认字段
        if (document.getElementById('fields-container').children.length === 0) {
            addField();
        }
    </script>
</body>
</html>