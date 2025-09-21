<?php
// product_functions.php - clean ASCII start, no BOM, no leading whitespace
require_once __DIR__ . '/connect_db.php';

// ===== Get product by id (alias) =====
function getProductById($id) {
    return getProduct($id);
}

// ===== Update product from POST array =====
function updateProduct($id, array $data): bool {
    $code = trim($data['product_code'] ?? '');
    $name = trim($data['name'] ?? '');
    $qty = isset($data['quantity']) ? (int)$data['quantity'] : 0;
    $price = isset($data['price']) ? (float)$data['price'] : 0.0;
    $expiry = $data['expiry_date'] ?? null;
    if ($code === '' || $name === '') return false;
    return updateProductRaw($id, $code, $name, $qty, $price, $expiry);
}

// ===== Raw update for compatibility =====
function updateProductRaw($id, $code, $name, $qty, $price, $expiry) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE products SET product_code=?, name=?, quantity=?, price=?, expiry_date=? WHERE id=?");
    return $stmt->execute([$code, $name, $qty, $price, $expiry ?: null, $id]);
}



// ===== Metrics =====
function getTotalProducts(): int {
    global $pdo;
    return (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
}
function getTotalStockValue(): float {
    global $pdo;
    return (float)$pdo->query("SELECT COALESCE(SUM(quantity * price),0) FROM products")->fetchColumn();
}
function getExpiringSoonCount(int $days = 30): int {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)");
    $stmt->execute([$days]);
    return (int)$stmt->fetchColumn();
}
function getOutOfStockCount(): int {
    global $pdo;
    return (int)$pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= 0")->fetchColumn();
}

// ===== List with search + pagination =====
function listProducts(string $q = '', int $page = 1, int $perPage = 10, string $orderBy = 'id', string $orderDir = 'ASC'): array {
  global $pdo;

  $page    = max(1, (int)$page);
  $perPage = max(1, min(100, (int)$perPage));
  $offset  = ($page - 1) * $perPage;

  $params = [];
  $where  = '';

  if ($q !== '') {
    $where = "WHERE (product_code LIKE :kw OR name LIKE :kw)";
    $params[':kw'] = "%$q%";
  }

    // sanitize ordering
    $allowed = ['id','product_code','name','quantity','price','expiry_date'];
    if (!in_array($orderBy, $allowed, true)) {
        $orderBy = 'id';
    }
    $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

  // นับทั้งหมด
  $countSql = "SELECT COUNT(*) FROM products $where";
  $countStmt = $pdo->prepare($countSql);
  $countStmt->execute($params);
  $total = (int)$countStmt->fetchColumn();

  // ดึงรายการ — อย่า bind limit/offset เมื่อ emulate ปิด
  $sql = "SELECT * FROM products $where
      ORDER BY $orderBy $orderDir
      LIMIT $perPage OFFSET $offset";

  $stmt = $pdo->prepare($sql);
  if (isset($params[':kw'])) $stmt->bindValue(':kw', $params[':kw'], PDO::PARAM_STR);
  $stmt->execute();

  return [
    'items'   => $stmt->fetchAll(),
    'total'   => $total,
    'page'    => $page,
    'perPage' => $perPage,
    'pages'   => (int)ceil($total / $perPage),
  ];
}


// ===== Existing simple helpers (kept for compatibility) =====
function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
    return $stmt->fetchAll();
}
function getProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
/** ใช้ภายใน: insert และจับ 1062 */
function _insertProduct($code, $name, $qty, $price, $expiry): array {
    global $pdo;
    $sql  = "INSERT INTO products (product_code, name, quantity, price, expiry_date)
             VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    $code  = trim((string)$code);
    $name  = trim((string)$name);
    $qty   = (int)$qty;
    $price = (float)$price;
    $expiry = $expiry ?: null;

    if ($code === '' || $name === '') {
        return [false, 'กรุณากรอก "รหัสสินค้า" และ "ชื่อสินค้า"'];
    }

    try {
        $stmt->execute([$code, $name, $qty, $price, $expiry]);
        return [true, null];
    } catch (PDOException $e) {
        // 1062 = duplicate product_code
        if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
            return [false, 'รหัสสินค้านี้มีอยู่แล้วในระบบ (ห้ามซ้ำ)'];
        }
        return [false, 'บันทึกไม่สำเร็จ: ' . $e->getMessage()];
    }
}

/** สำหรับโค้ดเดิมที่เรียก addProduct แบบเดิม */
function addProduct($code, $name, $qty, $price, $expiry): array {
    return _insertProduct($code, $name, $qty, $price, $expiry);
}

/** รองรับทั้ง createProduct($_POST) และส่งทีละพารามิเตอร์ */
function createProduct($codeOrArray, $name = null, $qty = null, $price = null, $expiry = null): array {
    if (is_array($codeOrArray)) {
        $d = $codeOrArray;
        return _insertProduct(
            $d['product_code'] ?? '',
            $d['name'] ?? '',
            $d['quantity'] ?? 0,
            $d['price'] ?? 0,
            $d['expiry_date'] ?? null
        );
    }
    return _insertProduct($codeOrArray, $name, $qty, $price, $expiry);
}
// (เดิม) updateProductRaw
function deleteProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}
