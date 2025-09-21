-- ตารางสินค้า (รองรับภาษาไทย)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  price DECIMAL(10,2) NOT NULL,
  expiry_date DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตัวอย่างข้อมูล
INSERT INTO products (product_code, name, quantity, price, expiry_date) VALUES
('P-1001','เมล็ดกาแฟอาราบิก้าพรีเมี่ยม 250 กรัม', 30, 240.00, DATE_ADD(CURDATE(), INTERVAL 120 DAY)),
('P-1002','ผงมัทฉะ 100 กรัม',        12, 180.00, DATE_ADD(CURDATE(), INTERVAL 45 DAY)),
('P-1003','ดาร์กช็อกโกแลต 70% 80 กรัม',    0,  85.00,  DATE_ADD(CURDATE(), INTERVAL 15 DAY)),
('P-1004','นมอัลมอนด์ 1 ลิตร',            8,  95.00,  DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
('P-1005','กราโนล่า ออริจินัล 500 กรัม',     55, 210.00, DATE_ADD(CURDATE(), INTERVAL 300 DAY)),
('P-1006','ชาไทยสูตรโบราณ 200 กรัม',        25, 150.00, DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
('P-1007','น้ำผึ้งป่าแท้ 1 กิโลกรัม',       40, 320.00, DATE_ADD(CURDATE(), INTERVAL 180 DAY)),
('P-1008','ข้าวกล้องหอมมะลิ 5 กิโลกรัม',    20, 250.00, DATE_ADD(CURDATE(), INTERVAL 365 DAY));
