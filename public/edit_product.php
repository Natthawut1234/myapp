<?php
require_once __DIR__ . '/../src/product_functions.php';

$id = (int)($_GET['id'] ?? 0);
$product = getProductById($id);
if (!$product) { http_response_code(404); die('ไม่พบข้อมูล'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (updateProduct($id, $_POST)) {
    header('Location: products.php');
    exit;
  } else {
    $errors[] = 'แก้ไขไม่สำเร็จ (รหัสอาจซ้ำ)';
  }
}

include __DIR__ . '/../templates/header.php';
?>
<div class="container">
  <h1 class="h4 mb-3">Edit Product</h1>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars',$errors)) ?></div>
  <?php endif; ?>
  <div class="card">
    <div class="card-body">
      <form method="post" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">รหัสสินค้า</label>
          <input name="product_code" class="form-control" value="<?= htmlspecialchars($product['product_code']) ?>" required>
        </div>
        <div class="col-md-8">
          <label class="form-label">ชื่อสินค้า</label>
          <input name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">จำนวน</label>
          <input type="number" name="quantity" class="form-control" min="0" value="<?= (int)$product['quantity'] ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">ราคา (บาท)</label>
          <input type="number" step="0.01" name="price" class="form-control" min="0" value="<?= (float)$product['price'] ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">วันหมดอายุ</label>
          <input type="date" name="expiry_date" class="form-control" value="<?= $product['expiry_date'] ?>">
        </div>
        <div class="col-12">
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
          <a href="products.php" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
      </form>
    </div>
  </div>
</div>
