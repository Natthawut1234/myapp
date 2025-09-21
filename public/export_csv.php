<?php
require_once __DIR__ . '/../src/product_functions.php';

$q = trim($_GET['q'] ?? '');
    $orderBy = $_GET['orderBy'] ?? 'id';
    $orderDir = $_GET['orderDir'] ?? 'ASC';
    $result = listProducts($q, 1, 10000, $orderBy, $orderDir); // export up to 10k rows
$items = $result['items'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="products_export.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['id','product_code','name','quantity','price','expiry_date']);
foreach ($items as $row) {
    fputcsv($out, [
        $row['id'] ?? '',
        $row['product_code'] ?? '',
        $row['name'] ?? '',
        $row['quantity'] ?? 0,
        $row['price'] ?? 0,
        $row['expiry_date'] ?? '',
    ]);
}
fclose($out);
exit;
