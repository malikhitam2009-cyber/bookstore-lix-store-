<?php
// Koneksi database untuk XAMPP (localhost)
$host = "localhost";
$user = "root";
$pass = "";              // default XAMPP: password kosong
$db   = "lix_store";     // nama database (import file lix_store.sql dulu)

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Zona waktu WIB supaya kode transaksi (tanggal/bulan/tahun) & timer 24 jam akurat
date_default_timezone_set('Asia/Jakarta');
mysqli_query($conn, "SET time_zone = '+07:00'");
?>