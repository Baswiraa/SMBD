<?php
$conn = mysqli_connect("localhost", "root", "", "db_shoes");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
} else {
    echo "Koneksi Berhasil";
}
?>