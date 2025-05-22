<?php
require_once 'auth.php';
require_once '../koneksi.php';

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pesanan.php');
    exit;
}

$orderId = (int)$_GET['id'];

// Start transaction
$conn->begin_transaction();

try {
    // First, get order items to log before deletion (optional)
    $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Delete order items (this will trigger your trg_stock_after_delete)
    $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $stmt->close();

    // Then delete the order itself
    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    // Commit transaction
    $conn->commit();

    if ($affectedRows > 0) {
        $_SESSION['success_message'] = "Pesanan #$orderId berhasil dihapus";
    } else {
        $_SESSION['error_message'] = "Pesanan tidak ditemukan";
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error_message'] = "Gagal menghapus pesanan: " . $e->getMessage();
}

header('Location: pesanan.php');
exit;
?>