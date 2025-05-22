<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$full_name = trim($_POST['full_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$new_password = $_POST['new_password'] ?? '';

if ($full_name && $address) {
    if ($new_password) {
        // Jika password baru diisi
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, address = ?, password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $full_name, $address, $password_hash, $user_id);
    } else {
        // Jika password tidak diubah
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $full_name, $address, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name; // Update nama di session
        header("Location: profile.php?success=1");
        exit;
    } else {
        echo "Gagal update data.";
    }

    $stmt->close();
} else {
    echo "Harap isi semua data.";
}

$conn->close();
?>
