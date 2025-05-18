/*---------  TRIGGER 1 : AFTER INSERT  ---------*/
DELIMITER $$
CREATE TRIGGER trg_stock_after_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
  UPDATE stock
  SET quantity = quantity - NEW.quantity
  WHERE product_id = NEW.product_id;
END$$
DELIMITER ;

/*---------  TRIGGER 2 : AFTER UPDATE  ---------*/
/* Hitung selisih qty lama vs qty baru */
DELIMITER $$
CREATE TRIGGER trg_stock_after_update
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
  DECLARE diff INT;
  SET diff = NEW.quantity - OLD.quantity;  -- positif artinya tambah beli, stok berkurang
  UPDATE stock
  SET quantity = quantity - diff
  WHERE product_id = NEW.product_id;
END$$
DELIMITER ;

/*---------  TRIGGER 3 : AFTER DELETE  ---------*/
DELIMITER $$
CREATE TRIGGER trg_stock_after_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
  UPDATE stock
  SET quantity = quantity + OLD.quantity
  WHERE product_id = OLD.product_id;
END$$
DELIMITER ;
