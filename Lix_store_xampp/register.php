<?php
// Mulai session
session_start();
// Panggil file koneksi database
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form register
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, strtolower(trim($_POST['email'])));
    $password = $_POST['password']; // Jangan di-hash dulu kalau mau plain, atau pakai password_hash jika standar keamanan tinggi
    $no_telp  = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);

    // Cek apakah email sudah terdaftar sebelumnya di database
    $cek_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>
                alert('Email sudah terdaftar! Silakan gunakan email lain atau langsung login.');
                window.location.href = 'index.php';
              </script>";
        exit;
    }

    // Query untuk menyimpan user baru ke tabel users (role default: 'user')
    $query = "INSERT INTO users (nama, email, password, no_telp, alamat, role) 
              VALUES ('$nama', '$email', '$password', '$no_telp', '$alamat', 'user')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Registrasi Berhasil! Silakan Login menggunakan akun baru kamu.');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Registrasi Gagal: " . mysqli_error($conn) . "');
                window.location.href = 'index.php';
              </script>";
    }
} else {
    header("Location: index.php");
    exit;
}
?>