-- ตารางสินค้า
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  price DECIMAL(10,2) NOT NULL,
  expiry_date DATE
);

-- ตัวอย่างข้อมูล
INSERT INTO products (product_code, name, quantity, price, expiry_date) VALUES
('P-1001','Premium Arabica Beans 250g', 30, 240.00, DATE_ADD(CURDATE(), INTERVAL 120 DAY)),
('P-1002','Matcha Powder 100g',        12, 180.00, DATE_ADD(CURDATE(), INTERVAL 45 DAY)),
('P-1003','Dark Chocolate 70% 80g',    0,  85.00,  DATE_ADD(CURDATE(), INTERVAL 15 DAY)),
('P-1004','Almond Milk 1L',            8,  95.00,  DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
('P-1005','Granola Original 500g',     55, 210.00, DATE_ADD(CURDATE(), INTERVAL 300 DAY));
