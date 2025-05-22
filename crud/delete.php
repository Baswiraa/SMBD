<?php
require '../koneksi.php';

// Ambil ID dari parameter GET
$id = $_GET['id'] ?? '';

if ($id) {
    // Pastikan ID adalah integer untuk keamanan
    $id = (int)$id;

    // Hapus stok terlebih dahulu (jika ada tabel stok terpisah)
    $stmt = $conn->prepare("DELETE FROM stock WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Hapus data produk
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Redirect kembali ke halaman dashboard
header("Location: ../admin/dashboard.php");
exit();
?>
