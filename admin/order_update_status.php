<?php
include '../koneksi.php';

// Cek apakah parameter id ada
if (!isset($_GET['id'])) {
    echo "ID pesanan tidak ditemukan.";
    exit;
}

$order_id = $_GET['id'];

// Ambil data pesanan berdasarkan order_id
$query = "SELECT * FROM orders WHERE order_id = '$order_id'";
$result = $conn->query($sql);

if (mysqli_num_rows($result) == 0) {
    echo "Pesanan tidak ditemukan.";
    exit;
}

$order = mysqli_fetch_assoc($result);

// Jika form diedit dan disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_pelanggan = $_POST['user_id'];
    $tanggal = $_POST['order_date'];
    $status = $_POST['status'];

    // Update data pesanan
    $updateQuery = "UPDATE orders SET 
                    user_id = '$nama_pelanggan',
                    order_date = '$tanggal',
                    status = '$status'
                    WHERE order_id = '$order_id'";

    if (mysqli_query($koneksi, $updateQuery)) {
        header("Location: pesanan.php"); // Ganti dengan halaman daftar pesanan kamu
        exit;
    } else {
        echo "Error update: " . mysqli_error($koneksi);
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Pesanan #<?= $order_id ?></h1>

    <form action="" method="POST" class="bg-white p-6 rounded shadow-md max-w-md">
        <label class="block mb-2 font-semibold">Nama Pelanggan:</label>
        <input type="text" name="nama_pelanggan" value="<?= htmlspecialchars($order['nama_pelanggan']) ?>" class="w-full mb-4 p-2 border rounded" required>

        <label class="block mb-2 font-semibold">Tanggal:</label>
        <input type="date" name="tanggal" value="<?= htmlspecialchars($order['tanggal']) ?>" class="w-full mb-4 p-2 border rounded" required>

        <label class="block mb-2 font-semibold">Status:</label>
        <select name="status" class="w-full mb-4 p-2 border rounded" required>
            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="process" <?= $order['status'] == 'process' ? 'selected' : '' ?>>Process</option>
            <option value="complete" <?= $order['status'] == 'complete' ? 'selected' : '' ?>>Complete</option>
        </select>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Simpan Perubahan</button>
        <a href="pesanan.php" class="ml-4 text-gray-600 hover:underline">Batal</a>
    </form>
</div>

</body>
</html>