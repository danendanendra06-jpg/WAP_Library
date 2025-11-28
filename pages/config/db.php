<?php
$host = "db";       // <-- HARUS 'db' (nama layanan di docker-compose)
$user = "root";     // <-- 'root' (default, atau buat pengguna baru)
$pass = "root";     // <-- HARUS 'root' (sesuai dengan docker-compose)
$db   = "projek_perpus"; // <-- Nama database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>