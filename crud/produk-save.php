<?php
require '../koneksi.php';

$product_id = $_POST['product_id'] ?? '';
$product_name = $_POST['product_name'] ?? '';
$brand_id = $_POST['brand_id'] ?? '';
$category_id = $_POST['category_id'] ?? '';
$price = $_POST['price'] ?? 0;
$quantity = $_POST['quantity'] ?? 0;
$image_url = $_POST['image_url'] ?? '';
$description = $_POST['description'] ?? '';

if ($product_id) {
    // --- UPDATE produk utama ---
    $stmt = $conn->prepare("UPDATE products SET product_name=?, brand_id=?, category_id=?, price=?, image_url=?, description=? WHERE product_id=?");
    $stmt->bind_param("siidssi", $product_name, $brand_id, $category_id, $price, $image_url, $description, $product_id);
    $stmt->execute();
    $stmt->close();

    // --- UPDATE stok di tabel stocks ---
    $stmt = $conn->prepare("UPDATE stock SET quantity=? WHERE product_id=?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

} else {
    // --- INSERT ke products ---
    $stmt = $conn->prepare("INSERT INTO products (product_name, brand_id, category_id, price, image_url, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siidss", $product_name, $brand_id, $category_id, $price, $image_url, $description);
    $stmt->execute();

    // Ambil ID produk yang baru ditambahkan
    $new_product_id = $stmt->insert_id;
    $stmt->close();

    // --- INSERT stok ke tabel stocks ---
    $stmt = $conn->prepare("INSERT INTO stock (product_id, quantity) VALUES (?, ?)");
    $stmt->bind_param("ii", $new_product_id, $quantity);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: ../admin/dashboard.php");
exit();
?>
