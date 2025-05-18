-- View stok rendah
CREATE OR REPLACE VIEW v_low_stock AS
SELECT p.product_id, p.product_name, s.quantity
FROM products p
JOIN stock s ON s.product_id = p.product_id
WHERE s.quantity < 5;

-- View produk + brand + kategori ringkas
CREATE OR REPLACE VIEW v_product_catalog AS
SELECT p.*, b.brand_name, c.category_name
FROM products p
JOIN brands b ON b.brand_id = p.brand_id
JOIN categories c ON c.category_id = p.category_id;

-- View total order per user
CREATE OR REPLACE VIEW v_user_order_total AS
SELECT u.user_id, u.full_name,
       COUNT(o.order_id) AS order_count,
       SUM(o.total_amount) AS total_spent
FROM users u
LEFT JOIN orders o ON o.user_id = u.user_id
GROUP BY u.user_id;

-- View penjualan per-produk
CREATE OR REPLACE VIEW v_sales_per_product AS
SELECT p.product_id, p.product_name,
       SUM(oi.quantity) AS qty_sold,
       SUM(oi.quantity * oi.price_each) AS revenue
FROM products p
JOIN order_items oi ON oi.product_id = p.product_id
GROUP BY p.product_id;

-- View 10 produk terlaris
CREATE OR REPLACE VIEW v_top_selling AS
SELECT * FROM v_sales_per_product
ORDER BY qty_sold DESC
LIMIT 10;
