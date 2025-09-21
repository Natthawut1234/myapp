<?php
require_once __DIR__ . '/../src/product_functions.php';
include __DIR__ . '/../templates/header.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$orderBy = $_GET['orderBy'] ?? 'id';
$orderDir = $_GET['orderDir'] ?? 'ASC';
$result = listProducts($q, $page, $perPage, $orderBy, $orderDir);
$items = $result['items'];
$pages = $result['pages'];
?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Products</h1>
    <div>
      <a href="export_csv.php?q=<?= urlencode($q) ?>&orderBy=<?= urlencode($orderBy) ?>&orderDir=<?= urlencode($orderDir) ?>" class="btn btn-outline-secondary me-2"><i class="bi bi-download"></i> Export CSV</a>
      <a href="add_product.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>เพิ่มสินค้า</a>
    </div>
  </div>

  <form class="row g-2 mb-3" method="get" id="search-form">
    <div class="col-auto">
      <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="ค้นหาด้วยชื่อหรือรหัสสินค้า" id="search-input">
    </div>
    <div class="col-auto">
      <select name="orderBy" id="orderBy" class="form-select">
        <?php
        $cols = ['id' => 'ID', 'product_code' => 'รหัส', 'name' => 'ชื่อ', 'quantity' => 'คงเหลือ', 'price' => 'ราคา', 'expiry_date' => 'หมดอายุ'];
        foreach ($cols as $key => $label) {
            $sel = ($orderBy === $key) ? 'selected' : '';
            echo "<option value=\"{$key}\" {$sel}>{$label}</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-auto">
      <select name="orderDir" id="orderDir" class="form-select">
        <option value="ASC" <?= $orderDir === 'ASC' ? 'selected' : '' ?>>น้อย→มาก</option>
        <option value="DESC" <?= $orderDir === 'DESC' ? 'selected' : '' ?>>มาก→น้อย</option>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> ค้นหา</button>
    </div>
  </form>

  <div class="card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>รหัส</th>
            <th>ชื่อสินค้า</th>
            <th class="text-center">คงเหลือ</th>
            <th class="text-end">ราคา</th>
            <th>หมดอายุ</th>
            <th class="text-end">การจัดการ</th>
          </tr>
        </thead>
  <tbody id="products-table-body">
        <?php if (!$items): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">ไม่พบรายการ</td></tr>
        <?php else: foreach ($items as $i => $p): ?>
          <tr>
            <td><?= (($page-1)*$perPage) + $i + 1 ?></td>
            <td><span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($p['product_code']) ?></span></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td class="text-center">
              <?php if ((int)$p['quantity'] <= 0): ?>
                <span class="badge bg-danger">หมด</span>
              <?php elseif ((int)$p['quantity'] <= 5): ?>
                <span class="badge bg-warning text-dark"><?= (int)$p['quantity'] ?></span>
              <?php else: ?>
                <span class="badge bg-success-subtle text-success"><?= (int)$p['quantity'] ?></span>
              <?php endif; ?>
            </td>
            <td class="text-end">฿<?= number_format((float)$p['price'], 2) ?></td>
            <td><?= $p['expiry_date'] ?: '-' ?></td>
            <td class="text-end">
              <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
              <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบสินค้า?');"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($pages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php for ($p=1; $p<=$pages; $p++): ?>
        <li class="page-item <?= $p===$page?'active':'' ?>">
          <a class="page-link" href="?q=<?= urlencode($q) ?>&page=<?= $p ?>"><?= $p ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>
