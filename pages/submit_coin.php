<?php
if (!$isLoggedIn) {
    header('Location: index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$coinId = $_POST['coin_id'] ?? null;
if (!$coinId) {
    header('Location: index.php');
    exit;
}

$coin = getCoin($pdo, $coinId);
if (!$coin || !$coin['status']) {
    header('Location: index.php');
    exit;
}

// 收集表单数据
$formData = [];
foreach ($_POST as $key => $value) {
    if ($key !== 'coin_id' && !empty($value)) {
        $formData[$key] = $value;
    }
}

// 处理模板替换
$processedTemplate = processTemplate($coin['template'], $formData);

// 保存用户提交数据
$sql = "INSERT INTO user_submissions (user_id, coin_id, data) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id'], $coinId, json_encode($formData)]);

// 重定向到成功页面
header('Location: index.php?page=success&template=' . urlencode($processedTemplate));
exit;
?>