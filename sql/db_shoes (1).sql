-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2025 at 01:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_shoes`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `create_order` (IN `p_user_id` INT, IN `p_product_id` INT, IN `p_quantity` INT, IN `p_price_each` DECIMAL(10,2), IN `p_total_amount` DECIMAL(10,2))   BEGIN
    -- Insert ke tabel orders
    INSERT INTO orders (user_id, total_amount) VALUES (p_user_id, p_total_amount);
    SET @order_id = LAST_INSERT_ID();

    -- Insert ke tabel order_items
    INSERT INTO order_items (order_id, product_id, quantity, price_each)
    VALUES (@order_id, p_product_id, p_quantity, p_price_each);

    -- Kurangi stok
    UPDATE stock SET quantity = quantity - p_quantity WHERE product_id = p_product_id;

    -- Mengembalikan order_id
    SELECT @order_id AS order_id;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `show_all_products` (IN `search_term` VARCHAR(100))   BEGIN
  SELECT p.product_id, p.product_name, p.price, p.image_url,
         b.brand_name, c.category_name
  FROM products p
  JOIN brands b ON p.brand_id = b.brand_id
  JOIN categories c ON p.category_id = c.category_id
  WHERE search_term IS NULL OR p.product_name LIKE CONCAT('%', search_term, '%')
  ORDER BY p.product_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `show_all_products_with_filter` (IN `search_term` VARCHAR(100), IN `in_brand_id` INT, IN `in_category_id` INT)   BEGIN
  SELECT p.product_id, p.product_name, p.price, p.image_url,
         b.brand_name, c.category_name
  FROM products p
  JOIN brands b ON p.brand_id = b.brand_id
  JOIN categories c ON p.category_id = c.category_id
  WHERE (search_term IS NULL OR p.product_name LIKE CONCAT('%', search_term, '%'))
    AND (in_brand_id IS NULL OR p.brand_id = in_brand_id)
    AND (in_category_id IS NULL OR p.category_id = in_category_id)
  ORDER BY p.product_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_add_stock` (IN `p_product_id` INT, IN `p_qty` INT)   BEGIN
  INSERT INTO stock(product_id,quantity) VALUES(p_product_id,p_qty)
  ON DUPLICATE KEY UPDATE quantity = quantity + p_qty;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_order` (IN `p_user_id` INT, IN `p_product_id` INT, IN `p_qty` INT, OUT `p_order_id` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_delete_user` (IN `p_user_id` INT)   BEGIN
  DELETE FROM users WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_report_sales` (IN `p_from` DATE, IN `p_to` DATE)   BEGIN
  SELECT DATE(o.order_date) AS order_day,
         SUM(o.total_amount) AS daily_total
  FROM orders o
  WHERE o.order_date BETWEEN p_from AND p_to
  GROUP BY order_day;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_order_status` (IN `p_order_id` INT, IN `p_status` VARCHAR(50))   BEGIN
  UPDATE orders SET STATUS = p_status WHERE order_id = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `top_selling_products` ()   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_hash` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `email`, `password_hash`) VALUES
(1, 'admin1', 'admin1@gmail.com', '$2y$10$zBpxtuTKVX13C7p50G2R/.sdGKQaBg6DSz7/fP.ldhECCNVVq2ACG');

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_order_list_view`
-- (See below for the actual view)
--
CREATE TABLE `admin_order_list_view` (
`order_id` int(11)
,`order_date` timestamp
,`total_amount` decimal(10,2)
,`status` varchar(50)
,`full_name` varchar(150)
,`item_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_product_list_view`
-- (See below for the actual view)
--
CREATE TABLE `admin_product_list_view` (
`product_id` int(11)
,`product_name` varchar(150)
,`price` decimal(10,2)
,`brand_name` varchar(100)
,`category_name` varchar(100)
,`stock` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_user_list_view`
-- (See below for the actual view)
--
CREATE TABLE `admin_user_list_view` (
`user_id` int(11)
,`full_name` varchar(150)
,`email` varchar(150)
,`address` text
,`order_count` bigint(21)
,`total_spent` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`) VALUES
(2, 'Adidas'),
(1, 'Nike'),
(3, 'Puma'),
(4, 'Reebok'),
(5, 'Under Armour');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(2, 'Basketball'),
(5, 'Casual'),
(1, 'Running'),
(3, 'Soccer'),
(4, 'Training');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`) VALUES
(1, 1, '2025-05-18 04:16:36', 240.00, 'Completed'),
(2, 2, '2025-05-18 04:16:36', 130.00, 'Processing'),
(3, 1, '2025-05-18 14:31:24', 130.00, 'Pending'),
(4, 1, '2025-05-18 17:18:51', 180.00, 'Pending'),
(5, 1, '2025-05-18 18:15:02', 180.00, 'Pending'),
(6, 3, '2025-05-18 19:05:37', 180.00, 'Pending'),
(7, 3, '2025-05-21 07:57:43', 110.00, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_each` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_each`) VALUES
(1, 1, 1, 1, 120.00),
(2, 1, 2, 1, 120.00),
(3, 2, 3, 1, 130.00),
(4, 3, 3, 1, 130.00),
(5, 4, 2, 1, 180.00),
(6, 5, 2, 1, 180.00),
(7, 6, 2, 1, 180.00),
(8, 7, 4, 1, 110.00);

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_stock_after_delete` AFTER DELETE ON `order_items` FOR EACH ROW BEGIN
  UPDATE stock
  SET quantity = quantity + OLD.quantity
  WHERE product_id = OLD.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_stock_after_insert` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
  UPDATE stock
  SET quantity = quantity - NEW.quantity
  WHERE product_id = NEW.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_stock_after_update` AFTER UPDATE ON `order_items` FOR EACH ROW BEGIN
  DECLARE diff INT;
  SET diff = NEW.quantity - OLD.quantity;  -- positif artinya tambah beli, stok berkurang
  UPDATE stock
  SET quantity = quantity - diff
  WHERE product_id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `brand_id`, `category_id`, `price`, `description`, `image_url`, `created_at`) VALUES
(1, 'Nike Air Zoom Pegasus 40', 1, 1, 120.00, 'Lightweight running shoes with Zoom cushioning.', 'https://www.misterrunning.com/images/2023-media-03/nike-air-zoom-pegasus-40-scarpe-da-running-uomo-midnight-navy-dv3853-400_A.jpg', '2025-05-18 04:14:55'),
(2, 'Adidas Ultraboost 23', 2, 1, 180.00, 'High-performance running shoes with responsive cushioning.', 'https://assets.adidas.com/images/w_1880,f_auto,q_auto/bca8a92780e14b80919cf604d364995d_9366/IG8279_01_standard.jpg', '2025-05-18 04:14:55'),
(3, 'Puma MB.02 Basketball Shoes', 3, 2, 130.00, 'Stylish and supportive for the court.', 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_600,h_600/global/377590/01/sv03/fnd/GBR/fmt/png/MB.02-HONEYCOMB-Basketball-Shoes', '2025-05-18 04:14:55'),
(4, 'Reebok Nano X3 Training', 4, 4, 110.00, 'Versatile training shoes for gym workouts.', 'https://static3.tcdn.com.br/img/img_prod/719636/tenis_reebok_nano_x3_masculino_preto_10987_1_2d105e280f7c354ff359900dcdc7b31b.jpg', '2025-05-18 04:14:55'),
(5, 'Under Armour Curry Flow 10', 5, 2, 160.00, 'Basketball shoes designed for agility and comfort.', 'https://www.youngsneaker.com/bmz_cache/2/2206ua-3026949-curry-flow-10jpg.image.301x320.jpg', '2025-05-18 04:14:55'),
(7, 'Nike InfinityRN 4', 1, 1, 150.00, 'Cushioned support for everyday runners.', 'https://www.runningxpert.com/media/catalog/product/cache/e1bfa30f5f000aa573b2ee969a7a0fde/n/i/nike_infinity_run_4.jpg', '2025-05-22 11:13:17'),
(8, 'Adidas Harden Vol. 7', 2, 2, 160.00, 'James Harden’s signature basketball shoes.', 'https://filebroker-cdn.lazada.co.id/kf/Sbcc1b89f27434a78bcf1c7a452bf7e5eL.jpg', '2025-05-22 11:15:59'),
(9, 'Puma Velocity Nitro 2', 3, 1, 125.00, 'Lightweight daily trainers with Nitro Foam.', 'https://shop4runners.eu/media/catalog/product/cache/2ff9c1584197bbc18b51650a1d955587/p/u/puma-velocity-nitro-2-gtx-damen-schwarz-377508-01_1_4.jpg', '2025-05-22 11:17:19'),
(10, 'Reebok Floatride Energy 5', 4, 1, 100.00, 'Lightweight and responsive running shoes.', 'https://www.running-point.com/dw/image/v2/BBDP_PRD/on/demandware.static/-/Sites-master-catalog/default/dw517f3b25/images/080/162/00451000_0_1.jpg?q=80&sw=2000', '2025-05-22 11:18:21'),
(11, 'Under Armour HOVR Phantom 3', 5, 1, 140.00, 'High-cushioning shoes for intense running performance.', 'https://underarmour.scene7.com/is/image/Underarmour/3026582-100_DEFAULT?rp=standard-30pad|pdpZoomDesktop&scl=0.50&fmt=jpg&qlt=85&resMode=sharp2&cache=on,on&bgc=f0f0f0&wid=1836&hei=1950&size=850,850', '2025-05-22 11:19:13');

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_detail_view`
-- (See below for the actual view)
--
CREATE TABLE `product_detail_view` (
`product_id` int(11)
,`product_name` varchar(150)
,`description` text
,`price` decimal(10,2)
,`image_url` varchar(255)
,`brand_name` varchar(100)
,`category_name` varchar(100)
,`stock` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_stock_view`
-- (See below for the actual view)
--
CREATE TABLE `product_stock_view` (
`product_id` int(11)
,`price` decimal(10,2)
,`product_name` varchar(150)
,`stock` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `stock_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`stock_id`, `product_id`, `quantity`) VALUES
(1, 1, 25),
(2, 2, 24),
(3, 3, 14),
(4, 4, 20),
(5, 5, 10),
(7, 7, 20),
(8, 8, 25),
(9, 9, 15),
(10, 10, 30),
(11, 11, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `address`, `created_at`) VALUES
(1, 'Faiz Bekasi', 'faiz@gmail.com', 'faiz123', '123 Elm St, New York, NY', '2025-05-18 04:16:13'),
(2, 'Fuad Ngawi', 'fuad@gmail.com', 'fuad123', '456 Oak Ave, Los Angeles, CA', '2025-05-18 04:16:13'),
(3, 'Sagab', 'sagab@gmail.com', '$2y$10$/6xKWMxFQ6lZ9fizlkWfRO3Oy.zOaFBf9u1JVZrctvdtv0TZHn1vu', 'Jomokerto Nomer 15', '2025-05-18 18:03:37'),
(4, 'bagas', 'bagas@gmail.com', '$2y$10$Ql0nUIr24SZrv6WfYJYDfeEE8lWX6P6kKavrQhX2fROeeZLLT0BXi', 'bejirrr', '2025-05-21 15:15:09');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_low_stock`
-- (See below for the actual view)
--
CREATE TABLE `v_low_stock` (
`product_id` int(11)
,`product_name` varchar(150)
,`quantity` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_product_catalog`
-- (See below for the actual view)
--
CREATE TABLE `v_product_catalog` (
`product_id` int(11)
,`product_name` varchar(150)
,`brand_id` int(11)
,`category_id` int(11)
,`price` decimal(10,2)
,`description` text
,`image_url` varchar(255)
,`created_at` timestamp
,`brand_name` varchar(100)
,`category_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_sales_per_product`
-- (See below for the actual view)
--
CREATE TABLE `v_sales_per_product` (
`product_id` int(11)
,`product_name` varchar(150)
,`qty_sold` decimal(32,0)
,`revenue` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_top_selling`
-- (See below for the actual view)
--
CREATE TABLE `v_top_selling` (
`product_id` int(11)
,`product_name` varchar(150)
,`qty_sold` decimal(32,0)
,`revenue` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_user_order_total`
-- (See below for the actual view)
--
CREATE TABLE `v_user_order_total` (
`user_id` int(11)
,`full_name` varchar(150)
,`order_count` bigint(21)
,`total_spent` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Structure for view `admin_order_list_view`
--
DROP TABLE IF EXISTS `admin_order_list_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_order_list_view`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`order_date` AS `order_date`, `o`.`total_amount` AS `total_amount`, `o`.`status` AS `status`, `u`.`full_name` AS `full_name`, count(`oi`.`order_item_id`) AS `item_count` FROM ((`orders` `o` join `users` `u` on(`u`.`user_id` = `o`.`user_id`)) left join `order_items` `oi` on(`oi`.`order_id` = `o`.`order_id`)) GROUP BY `o`.`order_id` ;

-- --------------------------------------------------------

--
-- Structure for view `admin_product_list_view`
--
DROP TABLE IF EXISTS `admin_product_list_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_product_list_view`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, `p`.`price` AS `price`, `b`.`brand_name` AS `brand_name`, `c`.`category_name` AS `category_name`, coalesce(`s`.`quantity`,0) AS `stock` FROM (((`products` `p` join `brands` `b` on(`p`.`brand_id` = `b`.`brand_id`)) join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) left join `stock` `s` on(`s`.`product_id` = `p`.`product_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `admin_user_list_view`
--
DROP TABLE IF EXISTS `admin_user_list_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_user_list_view`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`, `u`.`email` AS `email`, `u`.`address` AS `address`, count(`o`.`order_id`) AS `order_count`, sum(`o`.`total_amount`) AS `total_spent` FROM (`users` `u` left join `orders` `o` on(`o`.`user_id` = `u`.`user_id`)) GROUP BY `u`.`user_id` ;

-- --------------------------------------------------------

--
-- Structure for view `product_detail_view`
--
DROP TABLE IF EXISTS `product_detail_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_detail_view`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, `p`.`description` AS `description`, `p`.`price` AS `price`, `p`.`image_url` AS `image_url`, `b`.`brand_name` AS `brand_name`, `c`.`category_name` AS `category_name`, coalesce(`s`.`quantity`,0) AS `stock` FROM (((`products` `p` join `brands` `b` on(`b`.`brand_id` = `p`.`brand_id`)) join `categories` `c` on(`c`.`category_id` = `p`.`category_id`)) left join `stock` `s` on(`s`.`product_id` = `p`.`product_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `product_stock_view`
--
DROP TABLE IF EXISTS `product_stock_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_stock_view`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`price` AS `price`, `p`.`product_name` AS `product_name`, coalesce(`s`.`quantity`,0) AS `stock` FROM (`products` `p` left join `stock` `s` on(`s`.`product_id` = `p`.`product_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_low_stock`
--
DROP TABLE IF EXISTS `v_low_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_low_stock`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, `s`.`quantity` AS `quantity` FROM (`products` `p` join `stock` `s` on(`s`.`product_id` = `p`.`product_id`)) WHERE `s`.`quantity` < 5 ;

-- --------------------------------------------------------

--
-- Structure for view `v_product_catalog`
--
DROP TABLE IF EXISTS `v_product_catalog`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_product_catalog`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, `p`.`brand_id` AS `brand_id`, `p`.`category_id` AS `category_id`, `p`.`price` AS `price`, `p`.`description` AS `description`, `p`.`image_url` AS `image_url`, `p`.`created_at` AS `created_at`, `b`.`brand_name` AS `brand_name`, `c`.`category_name` AS `category_name` FROM ((`products` `p` join `brands` `b` on(`b`.`brand_id` = `p`.`brand_id`)) join `categories` `c` on(`c`.`category_id` = `p`.`category_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_sales_per_product`
--
DROP TABLE IF EXISTS `v_sales_per_product`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_sales_per_product`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, sum(`oi`.`quantity`) AS `qty_sold`, sum(`oi`.`quantity` * `oi`.`price_each`) AS `revenue` FROM (`products` `p` join `order_items` `oi` on(`oi`.`product_id` = `p`.`product_id`)) GROUP BY `p`.`product_id` ;

-- --------------------------------------------------------

--
-- Structure for view `v_top_selling`
--
DROP TABLE IF EXISTS `v_top_selling`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_top_selling`  AS SELECT `v_sales_per_product`.`product_id` AS `product_id`, `v_sales_per_product`.`product_name` AS `product_name`, `v_sales_per_product`.`qty_sold` AS `qty_sold`, `v_sales_per_product`.`revenue` AS `revenue` FROM `v_sales_per_product` ORDER BY `v_sales_per_product`.`qty_sold` DESC LIMIT 0, 10 ;

-- --------------------------------------------------------

--
-- Structure for view `v_user_order_total`
--
DROP TABLE IF EXISTS `v_user_order_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user_order_total`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`, count(`o`.`order_id`) AS `order_count`, sum(`o`.`total_amount`) AS `total_spent` FROM (`users` `u` left join `orders` `o` on(`o`.`user_id` = `u`.`user_id`)) GROUP BY `u`.`user_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`stock_id`),
  ADD UNIQUE KEY `stock_id` (`stock_id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
