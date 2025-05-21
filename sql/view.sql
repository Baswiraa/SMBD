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

-- Membuat VIEW product_stock_view
CREATE VIEW product_stock_view AS
SELECT
    p.product_id,
    p.price,
    p.product_name,
    COALESCE(s.quantity, 0) AS stock
FROM
    products p
LEFT JOIN
    stock s ON s.product_id = p.product_id;

CREATE VIEW product_detail_view AS
SELECT
    p.product_id,
    p.product_name,
    p.description,
    p.price,
    p.image_url,
    b.brand_name,
    c.category_name,
    COALESCE(s.quantity, 0) AS stock
FROM
    products p
JOIN
    brands b ON b.brand_id = p.brand_id
JOIN
    categories c ON c.category_id = p.category_id
LEFT JOIN
    stock s ON s.product_id = p.product_id;

CREATE VIEW admin_product_list_view AS
SELECT
    p.product_id,
    p.product_name,
    p.price,
    b.brand_name,
    c.category_name,
    COALESCE(s.quantity, 0) AS stock
FROM
    products p
JOIN
    brands b ON p.brand_id = b.brand_id
JOIN
    categories c ON p.category_id = c.category_id
LEFT JOIN
    stock s ON s.product_id = p.product_id;

CREATE VIEW admin_order_list_view AS
SELECT
    o.order_id,
    o.order_date,
    o.total_amount,
    o.status,
    u.full_name,
    COUNT(oi.order_item_id) AS item_count
FROM
    orders o
JOIN
    users u ON u.user_id = o.user_id
LEFT JOIN
    order_items oi ON oi.order_id = o.order_id
GROUP BY
    o.order_id;

CREATE VIEW admin_user_list_view AS
SELECT
    u.user_id,
    u.full_name,
    u.email,
    u.address,
    COUNT(o.order_id) AS order_count,
    SUM(o.total_amount) AS total_spent
FROM
    users u
LEFT JOIN
    orders o ON o.user_id = u.user_id
GROUP BY
    u.user_id;