<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$currencyId = $_GET['currency_id'] ?? '';

if (!$currencyId) {
    echo json_encode(['error' => 'Currency ID required']);
    exit;
}

// 获取币种信息
$stmt = $db->query("SELECT * FROM currencies WHERE id = ? AND status = 1", [$currencyId]);
$currency = $stmt->fetch();

if (!$currency) {
    echo json_encode(['error' => 'Currency not found']);
    exit;
}

// 获取字段列表
$fields = $db->query(
    "SELECT * FROM currency_fields WHERE currency_id = ? ORDER BY sort_order",
    [$currencyId]
);

$fieldList = [];
while ($field = $fields->fetch()) {
    $fieldList[] = $field;
}

echo json_encode([
    'currency' => $currency,
    'fields' => $fieldList
]);