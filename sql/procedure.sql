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
