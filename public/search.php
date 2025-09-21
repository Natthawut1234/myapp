<?php
require_once __DIR__ . '/../src/product_functions.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$orderBy = $_GET['orderBy'] ?? 'id';
$orderDir = $_GET['orderDir'] ?? 'ASC';
$result = listProducts($q, $page, $perPage, $orderBy, $orderDir);
$items = $result['items'];

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['items' => $items]);
