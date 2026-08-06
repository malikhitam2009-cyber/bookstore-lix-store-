<?php
header('Content-Type: application/json');
include 'koneksi.php';

error_reporting(0);

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    $query = "SELECT status FROM pesanan WHERE id = $order_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'status'  => trim($row['status'])
        ]);
        exit;
    }
}

echo json_encode([
    'success' => false,
    'status'  => 'Pending'
]);
?>