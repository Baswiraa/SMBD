<?php
session_start();
session_unset();   // hapus semua variabel session
session_destroy(); // hapus session di server
header('Location: index.php');
exit;
?>