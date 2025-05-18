CREATE DATABASE db_shoes;
USE db_shoes;

CREATE TABLE brands (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    brand_id INT NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    DESCRIPTION TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE stock (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2),
    STATUS VARCHAR(50) DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_each DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE ADMIN (
	admin_id INT AUTO_INCREMENT PRIMARY KEY,
	admin_name VARCHAR(50) NOT NULL,
	admin_pass VARCHAR(50) NOT NULL
);

INSERT INTO brands (brand_name) VALUES
('Nike'),
('Adidas'),
('Puma'),
('Reebok'),
('Under Armour');

INSERT INTO categories (category_name) VALUES
('Running'),
('Basketball'),
('Soccer'),
('Training'),
('Casual');

INSERT INTO products (product_name, brand_id, category_id, price, DESCRIPTION, image_url) VALUES
('Nike Air Zoom Pegasus 40', 1, 1, 120.00, 'Lightweight running shoes with Zoom cushioning.', 'images/nike_pegasus_40.jpg'),
('Adidas Ultraboost 23', 2, 1, 180.00, 'High-performance running shoes with responsive cushioning.', 'images/adidas_ultraboost_23.jpg'),
('Puma MB.02 Basketball Shoes', 3, 2, 130.00, 'Stylish and supportive for the court.', 'images/puma_mb02.jpg'),
('Reebok Nano X3 Training', 4, 4, 110.00, 'Versatile training shoes for gym workouts.', 'images/reebok_nano_x3.jpg'),
('Under Armour Curry Flow 10', 5, 2, 160.00, 'Basketball shoes designed for agility and comfort.', 'images/ua_curry_flow_10.jpg');

INSERT INTO stock (product_id, quantity) VALUES
(1, 25),
(2, 30),
(3, 15),
(4, 20),
(5, 10);

INSERT INTO users (full_name, email, password_hash, address) VALUES
('Faiz Bekasi', 'faiz@example.com', 'faiz1', '123 Elm St, New York, NY'),
('Fuad Ngawi', 'fuad@example.com', 'fuad2', '456 Oak Ave, Los Angeles, CA');

INSERT INTO orders (user_id, total_amount, STATUS) VALUES
(1, 240.00, 'Completed'),
(2, 130.00, 'Processing');

INSERT INTO order_items (order_id, product_id, quantity, price_each) VALUES
(1, 1, 1, 120.00),
(1, 2, 1, 120.00),
(2, 3, 1, 130.00);

