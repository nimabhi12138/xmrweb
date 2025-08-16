<?php
if (!$isLoggedIn) {
    header('Location: index.php?page=login');
    exit;
}

$template = $_GET['template'] ?? '';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提交成功 - 币种管理系统</title>
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
        .success-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        .success-header {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .success-body {
            padding: 40px;
        }
        .template-result {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
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
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        .copy-btn {
            background: linear-gradient(45deg, #6c757d, #495057);
            border: none;
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .copy-btn:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="success-container">
                    <div class="success-header">
                        <i class="fas fa-check-circle fa-4x mb-3"></i>
                        <h2 class="mb-2">提交成功！</h2>
                        <p class="mb-0 opacity-75">您的币种配置已成功提交</p>
                    </div>
                    
                    <div class="success-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-rocket fa-2x text-success mb-3"></i>
                            <h5>配置完成</h5>
                            <p class="text-muted">
                                您的币种参数已成功保存，以下是处理后的模板结果：
                            </p>
                        </div>
                        
                        <?php if ($template): ?>
                            <div class="template-result">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="fas fa-code"></i> 处理结果
                                    </h6>
                                    <button class="btn btn-sm copy-btn text-white" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i> 复制
                                    </button>
                                </div>
                                <div class="bg-white p-3 rounded border" id="templateContent">
                                    <pre class="mb-0" style="white-space: pre-wrap; word-break: break-all;"><?= e($template) ?></pre>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 模板处理完成，数据已保存到系统。
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> 返回首页
                            </a>
                            <a href="index.php?page=coin" class="btn btn-success">
                                <i class="fas fa-plus"></i> 继续配置
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard() {
            const content = document.getElementById('templateContent').textContent;
            navigator.clipboard.writeText(content).then(function() {
                const btn = document.querySelector('.copy-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> 已复制';
                btn.style.background = 'linear-gradient(45deg, #28a745, #20c997)';
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.style.background = 'linear-gradient(45deg, #6c757d, #495057)';
                }, 2000);
            }).catch(function(err) {
                console.error('复制失败: ', err);
                alert('复制失败，请手动复制');
            });
        }
    </script>
</body>
</html>