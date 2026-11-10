<?php
// Fungsi bantu untuk alur pembayaran pending (dipakai banyak file)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tambah kolom yang dibutuhkan di tabel pesanan kalau belum ada (otomatis, aman dijalankan berulang)
function pastikan_kolom_pesanan($conn) {
    $kolom = [
        'user_id'        => "INT(11) NULL DEFAULT NULL",
        'metode_bayar'   => "VARCHAR(50) NULL DEFAULT NULL",
        'bukti_transfer' => "VARCHAR(255) NULL DEFAULT NULL",
        'token'          => "VARCHAR(64) NULL DEFAULT NULL",
        'nominal_transfer' => "INT(11) NULL DEFAULT NULL",
        'email'          => "VARCHAR(100) NULL DEFAULT NULL",
        'no_telepon'     => "VARCHAR(30) NULL DEFAULT NULL",
        'alamat'         => "TEXT NULL DEFAULT NULL",
        'created_at'     => "DATETIME NULL DEFAULT CURRENT_TIMESTAMP",
        'kode_transaksi' => "VARCHAR(30) NULL DEFAULT NULL",
        'subtotal'       => "INT(11) NULL DEFAULT NULL",   // harga buku sebelum PPN & ongkir
        'ppn'            => "INT(11) NULL DEFAULT NULL",   // nominal PPN (Rp)
        'ongkir'         => "INT(11) NULL DEFAULT NULL",   // ongkos kirim (Rp)
        'dikirim_time'   => "DATETIME NULL DEFAULT NULL",   // kapan status jadi 'Dalam Perjalanan'
        'no_resi_retur'  => "VARCHAR(50) NULL DEFAULT NULL",   // nomor resi paket yang dikembalikan user
        'kurir_retur'    => "VARCHAR(50) NULL DEFAULT NULL",
        'alasan_retur'   => "VARCHAR(255) NULL DEFAULT NULL",
        'selesai_time'   => "DATETIME NULL DEFAULT NULL",   // kapan user konfirmasi selesai / dikembalikan
    ];
    foreach ($kolom as $nama => $definisi) {
        $cek = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE '$nama'");
        if ($cek && mysqli_num_rows($cek) === 0) {
            mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN `$nama` $definisi");
        }
    }
    // Isi kode transaksi untuk pesanan lama yang belum punya (format TL + ddmmyy + -id)
    mysqli_query($conn, "UPDATE pesanan
        SET kode_transaksi = CONCAT('TL', DATE_FORMAT(COALESCE(created_at, NOW()), '%d%m%y'), '-', LPAD(id, 3, '0'))
        WHERE kode_transaksi IS NULL OR kode_transaksi = ''");
}

// Hitung rincian bayar: total = subtotal + PPN + ongkir. Pesanan lama (tanpa rincian) dianggap subtotal = total.
function hitung_biaya($subtotal, $ppn_persen, $ongkir) {
    $subtotal = (int)$subtotal;
    $ppn = (int)round($subtotal * $ppn_persen / 100);
    return ['subtotal' => $subtotal, 'ppn' => $ppn, 'ongkir' => (int)$ongkir, 'total' => $subtotal + $ppn + (int)$ongkir];
}
function rincian_pesanan($p) {
    if ($p['subtotal'] === null || $p['subtotal'] === '') {
        return ['subtotal' => (int)$p['total'], 'ppn' => 0, 'ongkir' => 0, 'total' => (int)$p['total'], 'lama' => true];
    }
    return ['subtotal' => (int)$p['subtotal'], 'ppn' => (int)$p['ppn'], 'ongkir' => (int)$p['ongkir'], 'total' => (int)$p['total'], 'lama' => false];
}

// ==========================================================
// KODE TRANSAKSI
// Format: TL + tanggal(2) + bulan(2) + tahun(2) + '-' + urutan hari itu(3)
// Contoh: pesanan ke-1 pada 9 Nov 2026 => TL091126-001
// 8 karakter pertama (TL091126) = kode hari, dipakai untuk rekap harian admin.
// WAJIB dipanggil di dalam transaksi DB (ada FOR UPDATE).
// ==========================================================
function buat_kode_transaksi($conn) {
    $prefix = 'TL' . date('dmy');
    $like = $prefix . '-%';
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(kode_transaksi, '-', -1) AS UNSIGNED)), 0) AS maks
                                   FROM pesanan WHERE kode_transaksi LIKE ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $prefix . '-' . str_pad(((int)$r['maks']) + 1, 3, '0', STR_PAD_LEFT);
}

// ==========================================================
// ALUR STATUS PESANAN
// Pending -> Approved (dikemas) -> Dalam Perjalanan -> Selesai / Dikembalikan
// (status lama 'Dikirim / Selesai' dianggap sama dengan Selesai)
// ==========================================================
function status_sudah_bayar($st) {
    return in_array(strtolower(trim($st)), ['approved', 'dalam perjalanan', 'selesai', 'dikirim / selesai'], true);
}

function ambil_pesanan_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM pesanan WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Admin menyetujui pembayaran -> status Approved (= sedang dikemas) + email ke user. Timer 24 jam mulai.
function setujui_pesanan($conn, $id) {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "UPDATE pesanan SET status = 'Approved', approval_time = NOW() WHERE id = ? AND LOWER(status) = 'pending'");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_affected_rows($stmt) < 1) return false;

    $p = ambil_pesanan_by_id($conn, $id);
    if ($p && !empty($p['email'])) {
        require_once __DIR__ . '/helper_email.php';
        $isi = "<p>Halo <b>" . htmlspecialchars($p['nama_pembeli']) . "</b>, pembayaran kamu sudah kami terima.</p>
                <p>📦 Pesanan kamu <b>sedang dikemas</b>: " . htmlspecialchars($p['judul_buku']) . "</p>
                <p>Kamu akan mendapat email lagi saat pesanan dikirim ke alamat:<br><i>" . htmlspecialchars($p['alamat'] ?? '-') . "</i></p>";
        kirim_email($conn, $id, $p['email'], 'Pesanan ' . $p['kode_transaksi'] . ' sedang dikemas',
            template_email('Pembayaran diterima, pesanan sedang dikemas', $isi, $p['kode_transaksi']));
    }
    return true;
}

// Approved -> Dalam Perjalanan (dipanggil otomatis setelah 24 jam, atau manual oleh admin) + email ke user.
function kirim_pesanan($conn, $id) {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "UPDATE pesanan SET status = 'Dalam Perjalanan', dikirim_time = NOW() WHERE id = ? AND LOWER(status) = 'approved'");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_affected_rows($stmt) < 1) return false; // sudah diproses sebelumnya -> email tidak dobel

    $p = ambil_pesanan_by_id($conn, $id);
    if ($p && !empty($p['email'])) {
        require_once __DIR__ . '/helper_email.php';
        $isi = "<p>Halo <b>" . htmlspecialchars($p['nama_pembeli']) . "</b>,</p>
                <p>🚚 Pesanan kamu <b>sedang menuju ke lokasi kamu</b>: " . htmlspecialchars($p['judul_buku']) . "</p>
                <p>Alamat tujuan:<br><i>" . htmlspecialchars($p['alamat'] ?? '-') . "</i></p>
                <p>Setelah barang tiba, buka halaman pesanan dan pilih <b>Pengiriman Selesai</b>, atau <b>Kembalikan Barang</b> bila ada masalah.</p>";
        kirim_email($conn, $id, $p['email'], 'Pesanan ' . $p['kode_transaksi'] . ' sedang dalam perjalanan',
            template_email('Pesanan menuju lokasi kamu', $isi, $p['kode_transaksi']));
    }
    return true;
}

// Otomatis: pesanan Approved yang sudah >= 24 jam sejak disetujui -> Dalam Perjalanan.
// Tidak butuh cron: dipanggil tiap halaman penting dibuka. Return jumlah pesanan yang dikirim.
function proses_pengiriman_otomatis($conn, $jam = 24) {
    $jam = (int)$jam; $n = 0;
    try {
        $res = mysqli_query($conn, "SELECT id FROM pesanan
            WHERE LOWER(status) = 'approved' AND approval_time IS NOT NULL
              AND approval_time <= (NOW() - INTERVAL $jam HOUR)");
        if ($res) while ($r = mysqli_fetch_assoc($res)) { if (kirim_pesanan($conn, $r['id'])) $n++; }
    } catch (Throwable $e) {}
    return $n;
}

// Hanya USER yang boleh menyelesaikan: $aksi = 'selesai' atau 'kembali'. Hanya dari status Dalam Perjalanan.
// Kalau 'kembali', nomor resi paket WAJIB diisi (6-30 huruf/angka). Return [true,''] atau [false,'pesan error'].
function konfirmasi_pesanan_user($conn, $id, $aksi, $resi = '', $kurir = '', $alasan = '') {
    $id = (int)$id;
    $baru = ($aksi === 'selesai') ? 'Selesai' : 'Dikembalikan';
    $resi = strtoupper(trim($resi)); $kurir = trim($kurir); $alasan = trim($alasan);
    if ($baru === 'Dikembalikan' && !preg_match('/^[A-Z0-9\-]{6,30}$/', $resi)) {
        return [false, 'Nomor resi wajib diisi (6-30 huruf/angka) untuk pengembalian barang.'];
    }
    if ($baru === 'Selesai') { $resi = null; $kurir = null; $alasan = null; }
    else { $kurir = mb_substr($kurir, 0, 50); $alasan = mb_substr($alasan, 0, 255); }
    try {
        mysqli_begin_transaction($conn);
        $stmt = mysqli_prepare($conn, "UPDATE pesanan SET status = ?, selesai_time = NOW(), no_resi_retur = ?, kurir_retur = ?, alasan_retur = ? WHERE id = ? AND LOWER(status) = 'dalam perjalanan'");
        mysqli_stmt_bind_param($stmt, "ssssi", $baru, $resi, $kurir, $alasan, $id);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) < 1) { mysqli_rollback($conn); return [false, 'Pesanan sudah dikonfirmasi sebelumnya atau belum dalam perjalanan.']; }
        if ($baru === 'Dikembalikan') {
            $p = ambil_pesanan_by_id($conn, $id);
            if ($p) kembalikan_stok($conn, $p['judul_buku']);
        }
        mysqli_commit($conn);
        return [true, ''];
    } catch (Throwable $e) { @mysqli_rollback($conn); return [false, 'Terjadi kesalahan, coba lagi.']; }
}

// Ambil pesanan HANYA jika device/akun ini berhak melihatnya:
// - punya token yang cocok (device yang membuat pesanan), atau
// - user yang login adalah pemilik pesanan, atau
// - admin
function ambil_pesanan_akses($conn, $id, $token = '') {
    $id = (int)$id;
    if ($id <= 0) return null;

    $stmt = mysqli_prepare($conn, "SELECT * FROM pesanan WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$row) return null;

    if (($_SESSION['role'] ?? '') === 'admin') return $row;

    if (!empty($row['user_id']) && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$row['user_id']) {
        return $row;
    }

    if ($token !== '' && !empty($row['token']) && hash_equals($row['token'], $token)) {
        return $row;
    }

    return null;
}

// Simpan file bukti transfer. Return [true, namaFile] atau [false, pesanError]
function simpan_bukti_transfer($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [true, null]; // tidak upload = boleh
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Upload bukti gagal (kode ' . $file['error'] . ').'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return [false, 'Ukuran bukti transfer maksimal 5 MB.'];
    }
    $info = @getimagesize($file['tmp_name']);
    $ekstensi = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($ekstensi[$info[2]])) {
        return [false, 'Bukti transfer harus berupa gambar JPG, PNG, atau WEBP.'];
    }
    $dir = __DIR__ . '/bukti_transfer';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $nama = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $nama)) {
        return [false, 'Gagal menyimpan file bukti transfer.'];
    }
    return [true, $nama];
}

// Kembalikan stok buku dari teks judul_buku, format: "Judul A (2x), Judul B (1x)"
function kembalikan_stok($conn, $judul_buku) {
    if (preg_match_all('/(.+?) \((\d+)x\)(?:, |$)/u', $judul_buku, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $judul = trim($x[1]);
            $qty   = (int)$x[2];
            $stmt = mysqli_prepare($conn, "UPDATE books SET stock = stock + ? WHERE title = ?");
            mysqli_stmt_bind_param($stmt, "is", $qty, $judul);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Batalkan / tolak pesanan yang masih Pending + kembalikan stok.
// $status_baru: 'Rejected' (ditolak admin) atau 'Dibatalkan' (kedaluwarsa).
// Aman dipanggil berulang: hanya pesanan Pending yang diproses, jadi stok tidak pernah dikembalikan dua kali.
function batalkan_pesanan($conn, $id, $status_baru = 'Rejected') {
    $id = (int)$id;
    try {
        mysqli_begin_transaction($conn);

        $stmt = mysqli_prepare($conn, "SELECT judul_buku, status FROM pesanan WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$row || strtolower(trim($row['status'])) !== 'pending') {
            mysqli_rollback($conn);
            return false;
        }

        $stmt = mysqli_prepare($conn, "UPDATE pesanan SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status_baru, $id);
        mysqli_stmt_execute($stmt);

        kembalikan_stok($conn, $row['judul_buku']);

        mysqli_commit($conn);
        return true;
    } catch (Throwable $e) {
        @mysqli_rollback($conn);
        return false;
    }
}

// Batalkan otomatis pesanan non-COD yang belum upload bukti dan sudah lewat $jam jam sejak dibuat.
// Return: jumlah pesanan yang dibatalkan.
function batalkan_pesanan_kedaluwarsa($conn, $jam = 24) {
    $jam = (int)$jam;
    $jumlah = 0;
    try {
        $res = mysqli_query($conn,
            "SELECT id FROM pesanan
             WHERE LOWER(status) = 'pending'
               AND metode_bayar IS NOT NULL AND metode_bayar <> 'COD'
               AND (bukti_transfer IS NULL OR bukti_transfer = '')
               AND created_at IS NOT NULL
               AND created_at < (NOW() - INTERVAL $jam HOUR)");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                if (batalkan_pesanan($conn, $r['id'], 'Dibatalkan')) $jumlah++;
            }
        }
    } catch (Throwable $e) {}
    return $jumlah;
}