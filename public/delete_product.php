<?php
require_once __DIR__ . '/../src/product_functions.php';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) deleteProduct($id);
header('Location: products.php');
exit;
