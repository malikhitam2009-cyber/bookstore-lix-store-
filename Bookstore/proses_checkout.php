<?php
header('Content-Type: application/json');
include 'koneksi.php';

$customer_name  = $_POST['customer_name'] ?? $_POST['nama_pembeli'] ?? '';
$book_title     = $_POST['book_title'] ?? $_POST['judul_buku'] ?? '';
$total_price    = $_POST['total_price'] ?? $_POST['harga'] ?? 0;

if (empty($customer_name) || empty($book_title)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nama pembeli atau judul buku kosong!'
    ]);
    exit;
}

// Memasukkan data ke tabel pesanan dengan status awal 'Pending'
$query = "INSERT INTO pesanan (nama_pembeli, judul_buku, total, status) 
          VALUES ('$customer_name', '$book_title', '$total_price', 'Pending')";

if (mysqli_query($conn, $query)) {
    $order_id = mysqli_insert_id($conn);
    echo json_encode([
        'success' => true,
        'order_id' => $order_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database Error: ' . mysqli_error($conn)
    ]);
}
?>