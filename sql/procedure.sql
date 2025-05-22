DELIMITER //

-- Buat order lengkap (header+detail+update stok)
CREATE PROCEDURE sp_create_order(
  IN p_user_id INT,
  IN p_product_id INT,
  IN p_qty INT,
  OUT p_order_id INT
)
BEGIN
  DECLARE v_price DECIMAL(10,2);
  DECLARE v_total DECIMAL(10,2);

  SELECT price INTO v_price FROM products WHERE product_id = p_product_id;
  SET v_total = v_price * p_qty;

  INSERT INTO orders(user_id,total_amount) VALUES(p_user_id,v_total);
  SET p_order_id = LAST_INSERT_ID();

  INSERT INTO order_items(order_id,product_id,quantity,price_each)
  VALUES(p_order_id,p_product_id,p_qty,v_price);

  UPDATE stock SET quantity = quantity - p_qty
  WHERE product_id = p_product_id;
END//

-- Tambah stok
CREATE PROCEDURE sp_add_stock(IN p_product_id INT, IN p_qty INT)
BEGIN
  INSERT INTO stock(product_id,quantity) VALUES(p_product_id,p_qty)
  ON DUPLICATE KEY UPDATE quantity = quantity + p_qty;
END//

-- Ubah status order
CREATE PROCEDURE sp_update_order_status(IN p_order_id INT, IN p_status VARCHAR(50))
BEGIN
  UPDATE orders SET STATUS = p_status WHERE order_id = p_order_id;
END//

-- Hapus user & semua pesanan-nya
CREATE PROCEDURE sp_delete_user(IN p_user_id INT)
BEGIN
  DELETE FROM users WHERE user_id = p_user_id;
END//

-- Ambil laporan sales periode
CREATE PROCEDURE sp_report_sales(IN p_from DATE, IN p_to DATE)
BEGIN
  SELECT DATE(o.order_date) AS order_day,
         SUM(o.total_amount) AS daily_total
  FROM orders o
  WHERE o.order_date BETWEEN p_from AND p_to
  GROUP BY order_day;
END//

-- Panggil semua produk
CREATE PROCEDURE get_all_products()
BEGIN
    SELECT id, NAME, price, image FROM products;
END//


DELIMITER ;

-- Membuat STORED PROCEDURE create_order
DELIMITER //
CREATE PROCEDURE create_order(
    IN p_user_id INT,
    IN p_product_id INT,
    IN p_quantity INT,
    IN p_price_each DECIMAL(10, 2),
    IN p_total_amount DECIMAL(10, 2)
)
BEGIN
    INSERT INTO orders (user_id, total_amount) VALUES (p_user_id, p_total_amount);
    SET @order_id = LAST_INSERT_ID();

    INSERT INTO order_items (order_id, product_id, quantity, price_each)
    VALUES (@order_id, p_product_id, p_quantity, p_price_each);
    UPDATE stock SET quantity = quantity - p_quantity WHERE product_id = p_product_id;
    SELECT @order_id AS order_id;

END //
DELIMITER ;

DELIMITER //

CREATE PROCEDURE top_selling_products()
BEGIN
  SELECT 
    p.product_id,
    p.product_name,
    p.image_url,
    p.price,
    b.brand_name,
    c.category_name,
    SUM(oi.quantity) AS qty_sold
  FROM order_items oi
  JOIN products p ON oi.product_id = p.product_id
  LEFT JOIN brands b ON p.brand_id = b.brand_id
  LEFT JOIN categories c ON p.category_id = c.category_id
  GROUP BY p.product_id
  ORDER BY qty_sold DESC
  LIMIT 8;
END //

DELIMITER ;






