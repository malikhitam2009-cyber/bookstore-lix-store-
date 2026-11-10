<?php
// Halaman pembayaran: dibuka di device pembeli yang membuat pesanan (pakai token), atau oleh user pemilik / admin.
session_start();
include 'koneksi.php';
include 'helper_pesanan.php';
pastikan_kolom_pesanan($conn);
batalkan_pesanan_kedaluwarsa($conn, 24); // pesanan non-COD tanpa bukti > 24 jam otomatis dibatalkan
proses_pengiriman_otomatis($conn, 24);   // dikemas 24 jam -> otomatis 'Dalam Perjalanan' + email ke pembeli

include 'config_pembayaran.php'; // data rekening toko (ubah di file itu)

$id    = (int)($_GET['id'] ?? 0);
$token = trim($_GET['t'] ?? '');
$p     = ambil_pesanan_akses($conn, $id, $token);

if (!$p) {
    http_response_code(403);
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>LIX STORE</title></head>
    <body style='font-family:sans-serif;background:#D9C3B0;text-align:center;padding:60px 20px;'>
    <h2>Pesanan tidak ditemukan</h2>
    <p style='margin:12px 0 24px;'>Link pembayaran ini hanya bisa dibuka dari device yang membuat pesanan, atau login dengan akun pemilik pesanan.</p>
    <a href='index.php' style='background:#2b2b2b;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;'>Kembali ke Toko</a></body></html>";
    exit;
}

// Upload bukti transfer dari halaman ini
$pesan_upload = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'upload_bukti' && strtolower($p['status']) === 'pending') {
    // Nominal yang diisi pembeli harus SAMA PERSIS dengan total pesanan (dicek dari database)
    $nominal = (int)preg_replace('/\D/', '', $_POST['nominal'] ?? '');
    $total_pesanan = (int)$p['total'];

    if ($nominal !== $total_pesanan) {
        $pesan_upload = 'Upload GAGAL: nominal transfer (Rp ' . number_format($nominal, 0, ',', '.') .
                        ') tidak sesuai dengan total pesanan (Rp ' . number_format($total_pesanan, 0, ',', '.') .
                        '). Transfer dan isi nominal harus sesuai harga buku yang dibayar.';
    } else {
        [$ok, $hasil] = simpan_bukti_transfer($_FILES['bukti_transfer'] ?? null);
        if ($ok && $hasil) {
            $stmt = mysqli_prepare($conn, "UPDATE pesanan SET bukti_transfer = ?, nominal_transfer = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sii", $hasil, $nominal, $id);
            mysqli_stmt_execute($stmt);
            header("Location: pembayaran.php?id=$id&t=" . urlencode($token) . "&upload=ok");
            exit;
        }
        $pesan_upload = $ok ? 'Pilih file bukti transfer dulu.' : $hasil;
    }
}
if (($_GET['upload'] ?? '') === 'ok') {
    $pesan_upload = 'ok';
}

// Konfirmasi selesai / dikembalikan dilakukan PEMBELI di halaman konfirmasi_pesanan.php (admin tidak bisa).
$pesan_konfirmasi = '';
if (($_GET['konfirmasi'] ?? '') === 'selesai')  $pesan_konfirmasi = 'ok-selesai';
if (($_GET['konfirmasi'] ?? '') === 'kembali')  $pesan_konfirmasi = 'ok-kembali';

$status   = strtolower(trim($p['status']));
$lunas    = status_sudah_bayar($status);   // approved / dalam perjalanan / selesai
$dikembalikan = ($status === 'dikembalikan');
$ditutup  = in_array($status, ['rejected', 'dibatalkan'], true);
$pending  = !$lunas && !$ditutup && !$dikembalikan;
$kode_trx = $p['kode_transaksi'] ?: ('#' . $id);
$r = rincian_pesanan($p);
$rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
$html_rincian = '<div class="row"><span>Harga Buku</span><b>' . $rp($r['subtotal']) . '</b></div>'
    . '<div class="row"><span>PPN' . (($r['subtotal'] > 0 && !$r['lama']) ? ' (' . round($r['ppn'] / $r['subtotal'] * 100) . '%)' : '') . '</span><b>' . $rp($r['ppn']) . '</b></div>'
    . '<div class="row"><span>Ongkos Kirim</span><b>' . $rp($r['ongkir']) . '</b></div>'
    . '<div class="divider"></div>'
    . '<div class="row"><span>Total Bayar</span><b class="total">' . $rp($r['total']) . '</b></div>';
$dalam_perjalanan = ($status === 'dalam perjalanan');
$selesai  = in_array($status, ['selesai', 'dikirim / selesai'], true);
$tahap    = $selesai ? 4 : ($dalam_perjalanan ? 3 : ($lunas ? 2 : 1)); // 1 menunggu bayar, 2 dikemas, 3 perjalanan, 4 selesai
$kirim_pada = (!empty($p['approval_time']) && $status === 'approved') ? date('d M Y H:i', strtotime($p['approval_time'] . ' +24 hours')) : null;
$metode   = $p['metode_bayar'] ?: '-';
$is_cod   = ($metode === 'COD');
$ada_bukti = !empty($p['bukti_transfer']);
$bayar    = $info_bayar[$metode] ?? null;
$total_fmt = 'Rp ' . number_format($p['total'], 0, ',', '.');
$batas    = !empty($p['created_at']) ? date('d M Y H:i', strtotime($p['created_at'] . ' +24 hours')) : null;
$tgl      = !empty($p['created_at']) ? date('d M Y H:i', strtotime($p['created_at'])) : '-';
$tampil_invoice = $lunas || ($pending && $ada_bukti && !$is_cod); // invoice tampil setelah bukti diupload / setelah lunas

// Kartu Invoice / Bukti Pembayaran (digabung di file ini, tidak perlu file tambahan)
function tampilkan_invoice($p, $r, $rp, $kode_trx, $metode, $tgl, $total_fmt, $lunas) {
    $tgl_bayar = !empty($p['approval_time']) ? date('d M Y H:i', strtotime($p['approval_time'])) : null;
?>
        <div class="card">
            <div id="receipt-print-area" style="background:#fff; padding:14px; border-radius:8px; text-align:center;">
                <div style="font-family:'Fredoka',cursive; font-size:20px; letter-spacing:1px;">TOKO LIX</div>
                <div style="font-size:12px; color:#777; margin-bottom:8px;">INVOICE / BUKTI PEMBAYARAN</div>
                <?php if ($lunas): ?>
                    <div style="display:inline-block; background:#d4edda; color:#155724; padding:4px 14px; border-radius:20px; font-size:13px;">✅ LUNAS</div>
                <?php else: ?>
                    <div style="display:inline-block; background:#fff3cd; color:#856404; padding:4px 14px; border-radius:20px; font-size:13px;">⏳ MENUNGGU VERIFIKASI ADMIN</div>
                <?php endif; ?>

                <div class="receipt-box" style="text-align:left;">
                    <div class="receipt-row"><span>No. Invoice:</span> <b><?= htmlspecialchars($kode_trx); ?></b></div>
                    <div class="receipt-row"><span>Tanggal Pesan:</span> <span><?= $tgl; ?></span></div>
                    <?php if ($lunas && $tgl_bayar): ?><div class="receipt-row"><span>Disetujui:</span> <span><?= $tgl_bayar; ?></span></div><?php endif; ?>
                    <div class="receipt-divider"></div>
                    <div class="receipt-row"><span>Nama Pembeli:</span> <b><?= htmlspecialchars($p['nama_pembeli']); ?></b></div>
                    <?php if (!empty($p['email'])): ?><div class="receipt-row"><span>Email:</span> <span><?= htmlspecialchars($p['email']); ?></span></div><?php endif; ?>
                    <?php if (!empty($p['no_telepon'])): ?><div class="receipt-row"><span>Telepon:</span> <span><?= htmlspecialchars($p['no_telepon']); ?></span></div><?php endif; ?>
                    <?php if (!empty($p['alamat'])): ?><div class="receipt-row"><span>Alamat Kirim:</span> <span style="text-align:right;"><?= htmlspecialchars($p['alamat']); ?></span></div><?php endif; ?>
                    <div class="receipt-divider"></div>
                    <div class="receipt-row"><span>Buku:</span> <b style="text-align:right;"><?= htmlspecialchars($p['judul_buku']); ?></b></div>
                    <div class="receipt-row"><span>Metode Bayar:</span> <span><?= htmlspecialchars($metode); ?></span></div>
                    <div class="receipt-divider"></div>
                    <div class="receipt-row"><span>Harga Buku:</span> <span><?= $rp($r['subtotal']); ?></span></div>
                    <div class="receipt-row"><span>PPN<?= (!$r['lama'] && $r['subtotal'] > 0) ? ' (' . round($r['ppn'] / $r['subtotal'] * 100) . '%)' : ''; ?>:</span> <span><?= $rp($r['ppn']); ?></span></div>
                    <div class="receipt-row"><span>Ongkos Kirim:</span> <span><?= $rp($r['ongkir']); ?></span></div>
                    <div class="receipt-divider"></div>
                    <div class="receipt-row" style="font-size:16px; font-weight:bold;"><span>Total Bayar:</span> <span><?= $total_fmt; ?></span></div>
                    <?php if (!empty($p['nominal_transfer'])): ?><div class="receipt-row"><span>Nominal Ditransfer:</span> <span><?= $rp($p['nominal_transfer']); ?></span></div><?php endif; ?>
                </div>
                <p style="font-size:11px; color:#999; margin-top:10px;">Terima kasih telah berbelanja di Toko Lix.<?= $lunas ? '' : ' Invoice ini menjadi bukti lunas setelah pembayaran diverifikasi admin.'; ?></p>
            </div>
            <button class="btn" onclick="printReceipt()">🖨️ Cetak Invoice</button>
            <button class="btn red" onclick="downloadReceiptPDF()">📄 Download Invoice (PDF)</button>
            <button class="btn blue" onclick="downloadReceiptJPG()">📥 Download Invoice (JPG)</button>
            <?php if ($lunas): ?><a class="btn grey" href="index.php">Selesai</a><?php endif; ?>
        </div>
<?php
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan #<?= $id; ?> - LIX STORE</title>
    <?php if ($tampil_invoice): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <?php endif; ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Quicksand:wght@500;600;700&display=swap');
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Quicksand', sans-serif; font-weight: 600; }
    h1, h2, h3, .btn { font-family: 'Fredoka', cursive; }
    body { background: #D9C3B0; color: #2b2b2b; padding-bottom: 50px; }
    .topbar { background: #2b2b2b; color: #fff; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
    .brand { display: flex; align-items: center; gap: 10px; font-family: 'Fredoka', cursive; letter-spacing: 1px; }
    .brand img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
    .topbar a { color: #fff; text-decoration: none; background: #28a745; padding: 8px 14px; border-radius: 8px; font-size: 14px; }
    .wrap { max-width: 560px; margin: 24px auto; padding: 0 16px; }
    .card { background: #fff; border-radius: 16px; padding: 22px; margin-bottom: 18px; box-shadow: 0 6px 18px rgba(0,0,0,.1); }
    .status { text-align: center; }
    .status h2 { font-size: 22px; margin: 8px 0 4px; }
    .status p { font-size: 14px; color: #555; }
    .status.pending h2 { color: #b8860b; }
    .status.ok h2 { color: #28a745; }
    .spinner { width: 42px; height: 42px; border: 5px solid #E8D9CB; border-top-color: #2b2b2b; border-radius: 50%; animation: spin .9s linear infinite; margin: 4px auto; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .row { display: flex; justify-content: space-between; gap: 14px; padding: 7px 0; font-size: 14px; }
    .row span:first-child { color: #777; flex-shrink: 0; }
    .row b { text-align: right; }
    .divider { border-top: 1px dashed #ccc; margin: 8px 0; }
    .total { font-size: 18px; color: #d9534f; }
    .rek { background: #FAF6F0; border: 2px dashed #B09B88; border-radius: 10px; padding: 14px; margin-top: 10px; text-align: center; }
    .rek .no { font-family: 'Fredoka', cursive; font-size: 24px; letter-spacing: 1px; margin: 4px 0; }
    .btn { display: block; width: 100%; padding: 12px; border: none; border-radius: 10px; color: #fff; background: #2b2b2b; font-size: 15px; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; }
    .btn.small { display: inline-block; width: auto; padding: 6px 14px; font-size: 13px; margin-top: 6px; }
    .btn.green { background: #28a745; } .btn.red { background: #d9534f; } .btn.blue { background: #17a2b8; } .btn.grey { background: #666; }
    .input { width: 100%; padding: 10px; border: 1.5px solid #ddd; border-radius: 8px; background: #fff; font-size: 14px; }
    .msg { padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 10px; }
    .msg.ok { background: #d4edda; color: #155724; } .msg.err { background: #f8d7da; color: #721c24; }
    .steps { font-size: 13px; color: #555; line-height: 1.7; margin-top: 8px; padding-left: 18px; }
    .receipt-box { border: 2px dashed #B09B88; padding: 15px; background: #FAF6F0; border-radius: 8px; margin-top: 12px; font-size: 14px; }
    .receipt-row { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
    .receipt-divider { border-top: 1px dashed #ccc; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand"><img src="logo.jpg" alt="logo"> LIX STORE</div>
        <a href="index.php">Kembali ke Toko</a>
    </div>

    <div class="wrap">

        <?php if ($lunas): ?>
        <!-- ============ STATUS PENGIRIMAN ============ -->
        <div class="card status">
            <?php if ($pesan_konfirmasi === 'ok-selesai'): ?><div class="msg ok">Terima kasih! Pengiriman ditandai selesai.</div><?php endif; ?>
            <?php if ($pesan_konfirmasi !== '' && strpos($pesan_konfirmasi, 'ok-') !== 0): ?><div class="msg err"><?= htmlspecialchars($pesan_konfirmasi); ?></div><?php endif; ?>
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:14px;">
                <?php foreach ([1 => 'Dibayar', 2 => 'Dikemas', 3 => 'Perjalanan', 4 => 'Selesai'] as $n => $lb):
                    $aktif = ($tahap >= ($n === 1 ? 1 : $n)); ?>
                    <div style="flex:1; text-align:center; color:<?= $aktif ? '#28a745' : '#aaa'; ?>;">
                        <div style="width:26px;height:26px;line-height:26px;border-radius:50%;margin:0 auto 4px;background:<?= $aktif ? '#28a745' : '#ddd'; ?>;color:#fff;"><?= $aktif ? '✓' : $n; ?></div><?= $lb; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($status === 'approved'): ?>
                <h2 style="color:#b8860b;">📦 Pesanan Sedang Dikemas</h2>
                <p>Pembayaran sudah diterima. Kami kirim email saat barang berangkat<?= $kirim_pada ? ' (perkiraan ' . $kirim_pada . ')' : ''; ?>.</p>
            <?php elseif ($dalam_perjalanan): ?>
                <h2 style="color:#084298;">🚚 Barang Menuju Lokasi Kamu</h2>
                <p>Barang sedang dalam perjalanan. Tunggu sampai paket tiba di alamat kamu.</p>
                <p style="font-size:12px; color:#777; margin-top:6px;">Sudah menerima paket atau ada masalah? Buka menu konfirmasi di bawah.</p>
                <a class="btn" href="konfirmasi_pesanan.php?id=<?= $id; ?>&t=<?= urlencode($token); ?>">📋 Konfirmasi Penerimaan Pesanan</a>
            <?php elseif ($selesai): ?>
                <h2 style="color:#28a745;">🎉 Pesanan Selesai</h2>
                <p>Terima kasih sudah berbelanja di Toko Lix.</p>
            <?php endif; ?>
        </div>
        <?php elseif ($dikembalikan): ?>
        <div class="card status">
            <h2 style="color:#6f42c1;">↩ Barang Dikembalikan</h2>
            <p>Kamu memilih mengembalikan barang untuk transaksi <b><?= htmlspecialchars($kode_trx); ?></b>.</p>
            <?php if (!empty($p['no_resi_retur'])): ?><p style="margin-top:6px;">Nomor resi pengembalian: <b style="font-family:monospace;"><?= htmlspecialchars($p['no_resi_retur']); ?></b><?= !empty($p['kurir_retur']) ? ' (' . htmlspecialchars($p['kurir_retur']) . ')' : ''; ?></p><?php endif; ?>
            <p style="margin-top:6px;">Admin akan menghubungi kamu lewat email/telepon.</p>
            <a class="btn" href="index.php">Kembali ke Toko</a>
        </div>
        <?php endif; ?>

        <?php if ($lunas): ?>
        <!-- ============ SUDAH DISETUJUI: TAMPILKAN STRUK ============ -->
        <?php tampilkan_invoice($p, $r, $rp, $kode_trx, $metode, $tgl, $total_fmt, $lunas); ?>

        <?php elseif ($dikembalikan): ?>
        <?php /* kartu info sudah ditampilkan di atas */ ?>
        <?php elseif ($ditutup): ?>
        <!-- ============ DITOLAK / DIBATALKAN ============ -->
        <div class="card status">
            <?php if ($status === 'rejected'): ?>
                <h2 style="color:#dc3545;">❌ Pesanan Ditolak</h2>
                <p>Maaf, pesanan ini ditolak oleh admin. Stok buku sudah dikembalikan. Silakan hubungi admin atau buat pesanan baru.</p>
            <?php else: ?>
                <h2 style="color:#dc3545;">⏰ Pesanan Dibatalkan</h2>
                <p>Pesanan dibatalkan otomatis karena pembayaran tidak diterima dalam 24 jam. Silakan buat pesanan baru.</p>
            <?php endif; ?>
            <a class="btn" href="index.php">Kembali ke Toko</a>
        </div>

        <?php else: ?>
        <!-- ============ MASIH PENDING ============ -->
        <div class="card status pending">
            <div class="spinner"></div>
            <?php if ($is_cod): ?>
                <h2>⏳ Menunggu Persetujuan Admin</h2>
                <p>Pesanan COD kamu sudah masuk. Siapkan uang tunai <b><?= $total_fmt; ?></b> saat pesanan tiba.</p>
            <?php elseif (!$ada_bukti): ?>
                <h2>⏳ Menunggu Pembayaran</h2>
                <p>Selesaikan pembayaran sesuai petunjuk di bawah, lalu upload bukti transfer.</p>
                <?php if ($batas): ?>
                    <p style="font-size:12px; color:#b8860b; margin-top:6px;">Bayar sebelum <b><?= $batas; ?></b>. Lewat dari itu pesanan dibatalkan otomatis.</p>
                <?php endif; ?>
            <?php else: ?>
                <h2>⏳ Menunggu Verifikasi Admin</h2>
                <p>Bukti pembayaran sudah diterima. Halaman ini otomatis berubah saat admin menyetujui.</p>
            <?php endif; ?>
        </div>

        <?php if ($bayar && !$is_cod): ?>
        <div class="card">
            <h3 style="margin-bottom:6px;">Petunjuk Pembayaran</h3>
            <p style="font-size:14px; color:#555;"><?= htmlspecialchars($bayar['judul']); ?></p>
            <div class="rek">
                <div style="font-size:12px; color:#777;">Nomor Tujuan</div>
                <div class="no" id="no-rek"><?= htmlspecialchars($bayar['nomor']); ?></div>
                <div style="font-size:13px;">a.n. <?= htmlspecialchars($bayar['an']); ?></div>
                <button class="btn small" onclick="salin('<?= htmlspecialchars($bayar['nomor']); ?>', this)">Salin Nomor</button>
                <?php if ($metode === 'Gopay / QRIS' && file_exists(__DIR__ . '/qris.jpg')): ?>
                    <div style="margin-top:12px;"><img src="qris.jpg" alt="QRIS" style="max-width:100%; border-radius:8px;"></div>
                <?php endif; ?>
            </div>
            <div class="rek" style="margin-top:10px;">
                <div style="font-size:12px; color:#777;">Jumlah yang harus dibayar</div>
                <div class="no" style="color:#d9534f;"><?= $total_fmt; ?></div>
            </div>
            <ol class="steps">
                <li>Transfer sesuai nominal di atas.</li>
                <li>Upload bukti transfer di bawah ini.</li>
                <li>Tunggu admin menyetujui, halaman ini otomatis ter-update.</li>
            </ol>
        </div>

        <?php if (!$is_cod): ?>
        <div class="card">
            <h3 style="margin-bottom:8px;">Rincian Pembayaran</h3>
            <?= $html_rincian; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom:10px;">Bukti Transfer</h3>
            <?php if ($pesan_upload === 'ok'): ?>
                <div class="msg ok">Bukti transfer berhasil diupload.</div>
            <?php elseif ($pesan_upload !== ''): ?>
                <div class="msg err"><?= htmlspecialchars($pesan_upload); ?></div>
            <?php endif; ?>

            <?php if ($ada_bukti): ?>
                <a href="bukti_transfer/<?= rawurlencode($p['bukti_transfer']); ?>" target="_blank">
                    <img src="bukti_transfer/<?= rawurlencode($p['bukti_transfer']); ?>" alt="Bukti transfer" style="max-width:100%; max-height:260px; border-radius:8px; border:1px solid #ddd; display:block; margin:0 auto 10px;">
                </a>
                <p style="font-size:12px; color:#777; text-align:center; margin-bottom:8px;">Salah kirim? Upload ulang untuk mengganti.</p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="upload_bukti">
                <label style="font-size:13px;">Nominal yang kamu transfer (Rp)</label>
                <input type="text" name="nominal" inputmode="numeric" class="input" placeholder="contoh: <?= (int)$p['total']; ?>" required style="margin-bottom:10px;">
                <p style="font-size:12px; color:#777; margin:-4px 0 10px;">Harus sama persis dengan <b><?= $total_fmt; ?></b>. Kalau tidak sesuai, upload otomatis gagal.</p>
                <label style="font-size:13px;">Foto bukti transfer</label>
                <input type="file" name="bukti_transfer" accept="image/*" class="input" required>
                <button type="submit" class="btn green"><?= $ada_bukti ? 'Ganti Bukti Transfer' : 'Upload Bukti Transfer'; ?></button>
            </form>
        </div>
        <?php if ($tampil_invoice) { tampilkan_invoice($p, $r, $rp, $kode_trx, $metode, $tgl, $total_fmt, $lunas); } ?>
        <?php endif; ?>
        <?php endif; ?>

        <!-- RINGKASAN PESANAN (selalu tampil) -->
        <div class="card">
            <h3 style="margin-bottom:8px;">Ringkasan Pesanan</h3>
            <div class="row"><span>Kode Transaksi</span><b><?= htmlspecialchars($kode_trx); ?></b></div>
            <div class="row"><span>Pembeli</span><b><?= htmlspecialchars($p['nama_pembeli']); ?></b></div>
            <div class="row"><span>Buku</span><b><?= htmlspecialchars($p['judul_buku']); ?></b></div>
            <?php if (!empty($p['email'])): ?><div class="row"><span>Email</span><b><?= htmlspecialchars($p['email']); ?></b></div><?php endif; ?>
            <?php if (!empty($p['no_telepon'])): ?><div class="row"><span>Telepon</span><b><?= htmlspecialchars($p['no_telepon']); ?></b></div><?php endif; ?>
            <?php if (!empty($p['alamat'])): ?><div class="row"><span>Alamat</span><b><?= htmlspecialchars($p['alamat']); ?></b></div><?php endif; ?>
            <div class="row"><span>Metode</span><b><?= htmlspecialchars($metode); ?></b></div>
            <div class="row"><span>Waktu Pesan</span><b><?= $tgl; ?></b></div>
            <div class="divider"></div>
            <?= $html_rincian; ?>
        </div>
    </div>

    <script>
        const ORDER_ID = <?= $id; ?>;
        const TOKEN = <?= json_encode($token); ?>;
        const MASIH_PENDING = <?= $pending ? 'true' : 'false'; ?>;
        const STATUS_AWAL = <?= json_encode($status); ?>;
        const PERLU_POLLING = <?= json_encode(!in_array($status, ['selesai', 'dikirim / selesai', 'dikembalikan', 'rejected', 'dibatalkan'], true)); ?>;

        // Simpan pesanan ini di device ini agar bisa dilanjutkan dari halaman toko
        try {
            if (!MASIH_PENDING) {
                localStorage.removeItem('lix_pending_order');
            } else if (TOKEN) {
                localStorage.setItem('lix_pending_order', JSON.stringify({ id: ORDER_ID, token: TOKEN }));
            }
        } catch (e) {}

        function salin(teks, btn) {
            const selesai = () => { const t = btn.innerText; btn.innerText = 'Tersalin ✓'; setTimeout(() => btn.innerText = t, 1500); };
            if (navigator.clipboard) { navigator.clipboard.writeText(teks).then(selesai); }
            else { const i = document.createElement('input'); i.value = teks; document.body.appendChild(i); i.select(); document.execCommand('copy'); i.remove(); selesai(); }
        }

        // Cek status ke server tiap 3 detik selama masih Pending
        if (PERLU_POLLING) {
            setInterval(async () => {
                try {
                    const r = await fetch('check_status.php?order_id=' + ORDER_ID + '&token=' + encodeURIComponent(TOKEN), { cache: 'no-store' });
                    const d = await r.json();
                    if (d.success && d.status.toLowerCase() !== STATUS_AWAL) {
                        location.reload();
                    }
                } catch (e) {}
            }, 3000);
        }

        <?php if ($tampil_invoice): ?>
        function ambilCanvasStruk() {
            return html2canvas(document.getElementById('receipt-print-area'), {
                scale: 2, backgroundColor: '#ffffff', useCORS: true, scrollX: 0, scrollY: -window.scrollY
            });
        }

        function downloadReceiptPDF() {
            if (!window.jspdf || typeof html2canvas === 'undefined') {
                alert('Library PDF belum termuat. Cek koneksi internet lalu refresh halaman (Ctrl+F5).');
                return;
            }
            ambilCanvasStruk().then(canvas => {
                const { jsPDF } = window.jspdf;
                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a5' }); // A5 = 148 x 210 mm
                const pageW = pdf.internal.pageSize.getWidth();
                const pageH = pdf.internal.pageSize.getHeight();
                let imgW = 100;
                let imgH = (canvas.height * imgW) / canvas.width;
                if (imgH > pageH - 20) { // kalau terlalu tinggi, kecilkan supaya muat 1 halaman
                    imgH = pageH - 20;
                    imgW = (canvas.width * imgH) / canvas.height;
                }
                pdf.addImage(imgData, 'JPEG', (pageW - imgW) / 2, 10, imgW, imgH);
                pdf.save('Invoice-LixStore-<?= $id; ?>.pdf');
            }).catch(err => { console.error(err); alert('Gagal membuat PDF. Coba pakai tombol Cetak Invoice.'); });
        }

        function downloadReceiptJPG() {
            ambilCanvasStruk().then(canvas => {
                const link = document.createElement('a');
                link.download = 'Invoice-LixStore-<?= $id; ?>.jpg';
                link.href = canvas.toDataURL('image/jpeg', 0.9);
                link.click();
            });
        }

        function printReceipt() {
            const isi = document.getElementById('receipt-print-area').innerHTML;
            const w = window.open('', '_blank', 'width=480,height=700');
            if (!w) { alert('Pop-up diblokir browser. Izinkan pop-up lalu coba lagi.'); return; }
            w.document.write(`<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Invoice LIX STORE</title>
                <style>
                    * { box-sizing: border-box; }
                    body { font-family: Arial, Helvetica, sans-serif; padding: 20px; color: #222; max-width: 380px; margin: 0 auto; }
                    h2 { color: #28a745; text-align: center; font-size: 18px; margin-bottom: 6px; }
                    p { text-align: center; font-size: 13px; color: #555; }
                    .receipt-box { border: 2px dashed #B09B88; padding: 15px; background: #FAF6F0; border-radius: 8px; margin-top: 15px; font-size: 14px; }
                    .receipt-row { display: flex; justify-content: space-between; gap: 12px; margin: 6px 0; }
                    .receipt-divider { border-top: 1px dashed #B09B88; margin: 10px 0; }
                    @page { size: A5 portrait; margin: 10mm; }
                    @media print { body { padding: 0; } }
                </style></head><body>${isi}</body></html>`);
            w.document.close();
            w.focus();
            setTimeout(() => { w.print(); }, 400);
        }
        <?php endif; ?>
    </script>
</body>
</html>