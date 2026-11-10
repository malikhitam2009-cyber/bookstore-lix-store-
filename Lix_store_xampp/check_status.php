<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
include 'koneksi.php';
include 'helper_pesanan.php';

error_reporting(0);

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$token    = isset($_GET['token']) ? trim($_GET['token']) : '';

// Hanya device pemilik pesanan (token cocok) / user pemilik / admin yang boleh cek status
$row = ambil_pesanan_akses($conn, $order_id, $token);

if ($row) {
    echo json_encode([
        'success' => true,
        'status'  => trim($row['status']),
        'bukti'   => !empty($row['bukti_transfer']),
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'status'  => 'Pending'
]);
?>