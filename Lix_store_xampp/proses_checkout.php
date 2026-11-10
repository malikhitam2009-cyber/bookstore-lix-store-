<?php
// Backend checkout: simpan pesanan dengan status PENDING, lalu arahkan pembeli ke halaman pembayaran di device-nya.
session_start();
header('Content-Type: application/json; charset=utf-8');
include 'koneksi.php';
include 'helper_pesanan.php';
include 'config_pembayaran.php'; // $ppn_persen & $ongkir

function keluar($data) {
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    keluar(['success' => false, 'message' => 'Metode request tidak valid.']);
}

try {
    pastikan_kolom_pesanan($conn);
    batalkan_pesanan_kedaluwarsa($conn); // bebaskan stok dari pesanan kedaluwarsa dulu

    $nama   = trim($_POST['customer_name'] ?? '');
    $metode = trim($_POST['payment_method'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $telp   = trim($_POST['no_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $cart   = json_decode($_POST['cart_json'] ?? '[]', true);

    $metode_valid = ['BCA Transfer', 'Mandiri Transfer', 'Gopay / QRIS', 'DANA', 'COD'];

    if ($nama === '' || mb_strlen($nama) > 255) {
        keluar(['success' => false, 'message' => 'Nama pembeli wajib diisi.']);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        keluar(['success' => false, 'message' => 'Email tidak valid.']);
    }
    $digit_telp = preg_replace('/\D/', '', $telp);
    if (!preg_match('/^[0-9+\-\s]{8,20}$/', $telp) || strlen($digit_telp) < 8) {
        keluar(['success' => false, 'message' => 'Nomor telepon tidak valid (8-20 angka).']);
    }
    if (mb_strlen($alamat) < 10 || mb_strlen($alamat) > 500) {
        keluar(['success' => false, 'message' => 'Alamat pengiriman wajib diisi lengkap (minimal 10 karakter).']);
    }
    if (!in_array($metode, $metode_valid, true)) {
        keluar(['success' => false, 'message' => 'Metode pembayaran tidak valid.']);
    }
    if (!is_array($cart) || count($cart) === 0) {
        keluar(['success' => false, 'message' => 'Keranjang kosong.']);
    }

    // Bukti transfer TIDAK diupload di sini: diupload di halaman pembayaran supaya nominalnya bisa dicek.
    $bukti = null;

    mysqli_begin_transaction($conn);

    // Hitung total dari harga di database (bukan dari browser) + cek stok
    $total = 0;
    $label = [];
    $potong = []; // book_id => qty
    foreach ($cart as $item) {
        $judul = html_entity_decode(trim($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $qty   = (int)($item['qty'] ?? 0);
        if ($judul === '' || $qty < 1 || $qty > 99) {
            throw new Exception('Data keranjang tidak valid.');
        }
        $stmt = mysqli_prepare($conn, "SELECT id, price, stock FROM books WHERE title = ? LIMIT 1 FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "s", $judul);
        mysqli_stmt_execute($stmt);
        $buku = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$buku) throw new Exception("Buku \"$judul\" tidak ditemukan.");
        if ((int)$buku['stock'] < $qty) throw new Exception("Stok \"$judul\" tidak cukup (sisa {$buku['stock']}).");

        $total += (int)$buku['price'] * $qty;
        $label[] = "$judul ({$qty}x)";
        $potong[(int)$buku['id']] = ($potong[(int)$buku['id']] ?? 0) + $qty;
    }
    $judul_buku = implode(', ', $label);

    // Total akhir = harga buku + PPN + ongkir (dihitung di server, bukan dari browser)
    $biaya    = hitung_biaya($total, $ppn_persen, $ongkir);
    $subtotal = $biaya['subtotal']; $ppn = $biaya['ppn']; $ongkir_bayar = $biaya['ongkir'];
    $total    = $biaya['total'];

    $kode_transaksi = buat_kode_transaksi($conn); // TL + tgl + bln + thn + urutan, mis. TL091126-001
    $token   = bin2hex(random_bytes(16));
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $stmt = mysqli_prepare($conn,
        "INSERT INTO pesanan (nama_pembeli, judul_buku, total, status, user_id, metode_bayar, bukti_transfer, token, email, alamat, no_telepon, kode_transaksi, subtotal, ppn, ongkir)
         VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiisssssssiii", $nama, $judul_buku, $total, $user_id, $metode, $bukti, $token, $email, $alamat, $telp, $kode_transaksi, $subtotal, $ppn, $ongkir_bayar);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);

    foreach ($potong as $book_id => $qty) {
        $stmt = mysqli_prepare($conn, "UPDATE books SET stock = stock - ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $qty, $book_id);
        mysqli_stmt_execute($stmt);
    }

    mysqli_commit($conn);

    keluar([
        'success'  => true,
        'order_id' => $order_id,
        'kode_transaksi' => $kode_transaksi,
        'token'    => $token,
        'redirect' => 'pembayaran.php?id=' . $order_id . '&t=' . $token,
    ]);
} catch (Throwable $e) {
    @mysqli_rollback($conn);
    keluar(['success' => false, 'message' => $e->getMessage()]);
}