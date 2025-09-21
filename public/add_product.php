<?php
require_once __DIR__ . '/../src/product_functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $err] = createProduct($_POST);
    if ($ok) {
        header('Location: products.php');
        exit;
    } else {
        $errors[] = $err ?: 'บันทึกไม่สำเร็จ';
    }
}

include __DIR__ . '/../templates/header.php';
?>
<div class="container">
  <h1 class="h4 mb-3">เพิ่มสินค้า</h1>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(implode("\n", $errors)) ?></div>
  <?php endif; ?>
  <div class="card">
    <div class="card-body">
      <form method="post" class="row g-3">
        <!-- ฟิลด์ฟอร์ม -->
        <div class="col-md-4">
          <label class="form-label">รหัสสินค้า</label>
          <input name="product_code" class="form-control" required>
        </div>
        <div class="col-md-8">
          <label class="form-label">ชื่อสินค้า</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">จำนวน</label>
          <input type="number" name="quantity" class="form-control" min="0" value="0" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">ราคา (บาท)</label>
          <input type="number" step="0.01" name="price" class="form-control" min="0" value="0" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">วันหมดอายุ</label>
          <input type="date" name="expiry_date" class="form-control">
        </div>
        <div class="col-12">
          <button class="btn btn-primary">บันทึก</button>
          <a href="products.php" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
      </form>
    </div>
  </div>
</div>

