<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();

$user = $auth->getCurrentUser();
$userId = $user['id'];
$message = '';
$messageType = '';

$selectedCurrency = $_GET['currency'] ?? '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currencyId = $_POST['currency_id'] ?? '';
    $fieldValues = [];
    
    // 获取币种信息和字段
    $stmt = $db->query("SELECT * FROM currencies WHERE id = ? AND status = 1", [$currencyId]);
    $currency = $stmt->fetch();
    
    if ($currency) {
        // 获取字段定义
        $fields = $db->query(
            "SELECT * FROM currency_fields WHERE currency_id = ? ORDER BY sort_order",
            [$currencyId]
        );
        
        // 收集字段值
        while ($field = $fields->fetch()) {
            $fieldKey = 'field_' . $field['id'];
            if (isset($_POST[$fieldKey])) {
                $fieldValues[$field['field_placeholder']] = $_POST[$fieldKey];
            }
        }
        
        // 处理JSON模板替换
        $jsonTemplate = $currency['json_template'];
        $processedJson = $jsonTemplate;
        
        foreach ($fieldValues as $placeholder => $value) {
            $processedJson = str_replace($placeholder, $value, $processedJson);
        }
        
        // 检查是否已存在配置
        $stmt = $db->query(
            "SELECT id FROM user_currency_configs WHERE user_id = ? AND currency_id = ?",
            [$userId, $currencyId]
        );
        
        if ($existing = $stmt->fetch()) {
            // 更新现有配置
            $db->update(
                'user_currency_configs',
                ['field_values' => json_encode($fieldValues)],
                'id = ?',
                [$existing['id']]
            );
            $message = '配置更新成功！';
        } else {
            // 创建新配置
            $db->insert('user_currency_configs', [
                'user_id' => $userId,
                'currency_id' => $currencyId,
                'field_values' => json_encode($fieldValues)
            ]);
            $message = '配置保存成功！';
        }
        
        $messageType = 'success';
    } else {
        $message = '无效的币种';
        $messageType = 'danger';
    }
}

// 获取现有配置（如果有）
$existingConfig = null;
if ($selectedCurrency) {
    $stmt = $db->query(
        "SELECT * FROM user_currency_configs WHERE user_id = ? AND currency_id = ?",
        [$userId, $selectedCurrency]
    );
    $existingConfig = $stmt->fetch();
}

// 获取可用币种列表
$currencies = $db->query("SELECT * FROM currencies WHERE status = 1 ORDER BY name");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>配置币种 - 币种管理系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .currency-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .currency-option {
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .currency-option:hover {
            border-color: var(--primary-color);
            background: rgba(0, 212, 255, 0.05);
        }
        .currency-option.selected {
            border-color: var(--primary-color);
            background: rgba(0, 212, 255, 0.1);
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
        }
        .currency-option img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .currency-option-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .currency-option-symbol {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .field-section {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .field-help {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-top: 5px;
        }
        .result-section {
            margin-top: 30px;
            padding: 20px;
            background: rgba(0, 212, 255, 0.05);
            border: 1px solid var(--primary-color);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">币种管理系统</a>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">仪表盘</a></li>
                <li><a href="configure.php" class="active">配置币种</a></li>
                <li><a href="profile.php">个人资料</a></li>
                <li><a href="logout.php">退出登录</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container" style="margin-top: 30px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">配置币种参数</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                    <?php if ($messageType === 'success'): ?>
                        <a href="dashboard.php" style="margin-left: 10px;">返回仪表盘</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="configForm">
                <div class="form-group">
                    <label class="form-label">选择币种</label>
                    <div class="currency-selector">
                        <?php while ($currency = $currencies->fetch()): ?>
                            <div class="currency-option <?php echo $selectedCurrency == $currency['id'] ? 'selected' : ''; ?>"
                                 onclick="selectCurrency(<?php echo $currency['id']; ?>)">
                                <?php if ($currency['icon_path']): ?>
                                    <img src="..<?php echo htmlspecialchars($currency['icon_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($currency['name']); ?>">
                                <?php else: ?>
                                    <div style="font-size: 2.5rem; margin-bottom: 10px;">💰</div>
                                <?php endif; ?>
                                <div class="currency-option-name"><?php echo htmlspecialchars($currency['name']); ?></div>
                                <div class="currency-option-symbol"><?php echo htmlspecialchars($currency['symbol']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <div id="fieldsContainer" style="display: none;">
                    <input type="hidden" name="currency_id" id="currencyId">
                    
                    <div class="field-section">
                        <h3 style="margin-bottom: 20px;">填写配置参数</h3>
                        <div id="dynamicFields"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                        保存配置
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let selectedCurrencyId = <?php echo $selectedCurrency ?: 'null'; ?>;
        let existingValues = <?php echo $existingConfig ? $existingConfig['field_values'] : '{}'; ?>;
        
        function selectCurrency(currencyId) {
            // 更新选中状态
            document.querySelectorAll('.currency-option').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            selectedCurrencyId = currencyId;
            document.getElementById('currencyId').value = currencyId;
            
            // 加载字段
            loadFields(currencyId);
        }
        
        function loadFields(currencyId) {
            fetch(`../api/get-fields.php?currency_id=${currencyId}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('dynamicFields');
                    container.innerHTML = '';
                    
                    if (data.fields && data.fields.length > 0) {
                        data.fields.forEach(field => {
                            const fieldGroup = document.createElement('div');
                            fieldGroup.className = 'form-group';
                            
                            let fieldHtml = `
                                <label class="form-label">
                                    ${field.field_title}
                                    ${field.is_required ? '<span style="color: var(--danger-color);">*</span>' : ''}
                                </label>
                            `;
                            
                            const fieldName = `field_${field.id}`;
                            const existingValue = existingValues[field.field_placeholder] || '';
                            
                            switch(field.field_type) {
                                case 'textarea':
                                    fieldHtml += `
                                        <textarea class="form-control" name="${fieldName}" 
                                                  ${field.is_required ? 'required' : ''}
                                                  placeholder="${field.field_title}">${existingValue}</textarea>
                                    `;
                                    break;
                                case 'select':
                                    const options = field.field_options ? field.field_options.split(',') : [];
                                    fieldHtml += `
                                        <select class="form-control" name="${fieldName}" 
                                                ${field.is_required ? 'required' : ''}>
                                            <option value="">请选择</option>
                                            ${options.map(opt => `
                                                <option value="${opt.trim()}" 
                                                        ${existingValue === opt.trim() ? 'selected' : ''}>
                                                    ${opt.trim()}
                                                </option>
                                            `).join('')}
                                        </select>
                                    `;
                                    break;
                                case 'number':
                                    fieldHtml += `
                                        <input type="number" class="form-control" name="${fieldName}" 
                                               value="${existingValue}"
                                               ${field.is_required ? 'required' : ''}
                                               placeholder="${field.field_title}">
                                    `;
                                    break;
                                default:
                                    fieldHtml += `
                                        <input type="text" class="form-control" name="${fieldName}" 
                                               value="${existingValue}"
                                               ${field.is_required ? 'required' : ''}
                                               placeholder="${field.field_title}">
                                    `;
                            }
                            
                            if (field.field_placeholder) {
                                fieldHtml += `
                                    <div class="field-help">
                                        此字段将替换模板中的 ${field.field_placeholder}
                                    </div>
                                `;
                            }
                            
                            fieldGroup.innerHTML = fieldHtml;
                            container.appendChild(fieldGroup);
                        });
                        
                        document.getElementById('fieldsContainer').style.display = 'block';
                    } else {
                        container.innerHTML = '<p style="color: var(--text-secondary);">此币种暂无配置字段</p>';
                        document.getElementById('fieldsContainer').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error loading fields:', error);
                });
        }
        
        // 如果有预选币种，加载其字段
        if (selectedCurrencyId) {
            loadFields(selectedCurrencyId);
        }
    </script>
</body>
</html>