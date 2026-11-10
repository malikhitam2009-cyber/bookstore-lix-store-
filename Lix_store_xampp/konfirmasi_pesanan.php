<?php
// Menu konfirmasi penerimaan: HANYA pembeli yang bisa memilih "Pesanan Selesai" atau "Barang Dikembalikan".
// Kalau dikembalikan, nomor resi paket wajib diisi.
session_start();
include 'koneksi.php';
include 'helper_pesanan.php';
pastikan_kolom_pesanan($conn);

$id    = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$token = trim($_GET['t'] ?? $_POST['t'] ?? '');
$p     = ambil_pesanan_akses($conn, $id, $token);
$kembali_url = 'pembayaran.php?id=' . $id . '&t=' . urlencode($token);

function halaman_pesan($judul, $isi, $url) {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>LIX STORE</title></head>
    <body style='font-family:sans-serif;background:#D9C3B0;text-align:center;padding:60px 20px;'>
    <h2>" . htmlspecialchars($judul) . "</h2><p style='margin:12px 0 24px;'>" . htmlspecialchars($isi) . "</p>
    <a href='" . htmlspecialchars($url, ENT_QUOTES) . "' style='background:#2b2b2b;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;'>Kembali</a></body></html>";
    exit;
}

if (!$p) { http_response_code(403); halaman_pesan('Pesanan tidak ditemukan', 'Halaman ini hanya bisa dibuka oleh pembeli pemilik pesanan.', 'index.php'); }
if (($_SESSION['role'] ?? '') === 'admin') {
    halaman_pesan('Khusus pembeli', 'Admin tidak dapat mengonfirmasi penerimaan. Admin hanya menunggu jawaban dari pembeli.', $kembali_url);
}
if (strtolower(trim($p['status'])) !== 'dalam perjalanan') {
    halaman_pesan('Belum bisa dikonfirmasi', 'Konfirmasi hanya tersedia saat pesanan berstatus Dalam Perjalanan.', $kembali_url);
}

$error = '';
$pilihan = $_POST['pilihan'] ?? '';
$resi = $_POST['no_resi'] ?? ''; $kurir = $_POST['kurir'] ?? ''; $alasan = $_POST['alasan'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($pilihan, ['selesai', 'kembali'], true)) {
    [$ok, $pesan] = konfirmasi_pesanan_user($conn, $id, $pilihan, $resi, $kurir, $alasan);
    if ($ok) {
        header('Location: ' . $kembali_url . '&konfirmasi=' . $pilihan);
        exit;
    }
    $error = $pesan;
}
$kode = $p['kode_transaksi'] ?: ('#' . $id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konfirmasi Pesanan - LIX STORE</title>
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
.pilih { display: flex; gap: 12px; align-items: flex-start; padding: 14px; border: 2px solid #ddd; border-radius: 12px; margin-top: 10px; cursor: pointer; }
.pilih:has(input:checked) { border-color: #28a745; background: #f1fbf4; }
.pilih.merah:has(input:checked) { border-color: #d9534f; background: #fdf1f1; }
.pilih small { display: block; color: #777; font-size: 12px; margin-top: 2px; }
.lbl { display: block; font-size: 13px; margin: 12px 0 4px; }
.input { width: 100%; padding: 10px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 14px; }
.btn { display: block; width: 100%; padding: 13px; border: none; border-radius: 10px; color: #fff; background: #28a745; font-size: 16px; cursor: pointer; margin-top: 16px; text-align: center; text-decoration: none; }
.btn.grey { background: #666; }
.msg-err { background: #f8d7da; color: #721c24; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 12px; }
#box-resi { display: none; margin-top: 8px; padding: 12px; background: #FAF6F0; border: 2px dashed #B09B88; border-radius: 10px; }
</style>
</head>
<body>
<div class="topbar">
    <div class="brand"><img src="logo.jpg" alt="logo"> LIX STORE</div>
    <a href="<?= htmlspecialchars($kembali_url); ?>">← Kembali ke Pesanan</a>
</div>
<div class="wrap">
    <div class="card">
        <h3>Konfirmasi Pesanan</h3>
        <p style="font-size:13px; color:#555; margin-top:4px;">Transaksi <b><?= htmlspecialchars($kode); ?></b> &bull; <?= htmlspecialchars($p['judul_buku']); ?></p>
    </div>

    <form method="POST" class="card" onsubmit="return cekForm();">
        <input type="hidden" name="id" value="<?= $id; ?>">
        <input type="hidden" name="t" value="<?= htmlspecialchars($token); ?>">
        <h3>Bagaimana pesanan kamu?</h3>
        <?php if ($error): ?><div class="msg-err" style="margin-top:10px;"><?= htmlspecialchars($error); ?></div><?php endif; ?>

        <label class="pilih">
            <input type="radio" name="pilihan" value="selesai" <?= $pilihan === 'selesai' ? 'checked' : ''; ?> onchange="ubahPilihan()">
            <div>✅ Pengiriman telah selesai<small>Barang sudah kuterima dengan baik.</small></div>
        </label>
        <label class="pilih merah">
            <input type="radio" name="pilihan" value="kembali" <?= $pilihan === 'kembali' ? 'checked' : ''; ?> onchange="ubahPilihan()">
            <div>↩ Barang harus dikembalikan<small>Wajib mengisi nomor resi paket pengembalian.</small></div>
        </label>

        <div id="box-resi">
            <label class="lbl" for="no_resi">Nomor Resi Paket *</label>
            <input class="input" type="text" id="no_resi" name="no_resi" maxlength="30" placeholder="contoh: JP1234567890" value="<?= htmlspecialchars($resi); ?>">
            <label class="lbl" for="kurir">Nama Kurir / Ekspedisi</label>
            <input class="input" type="text" id="kurir" name="kurir" maxlength="50" placeholder="contoh: JNE, J&T, SiCepat" value="<?= htmlspecialchars($kurir); ?>">
            <label class="lbl" for="alasan">Alasan Pengembalian</label>
            <input class="input" type="text" id="alasan" name="alasan" maxlength="255" placeholder="contoh: buku rusak / salah kirim" value="<?= htmlspecialchars($alasan); ?>">
        </div>

        <button type="submit" class="btn">Kirim Konfirmasi</button>
        <a class="btn grey" href="<?= htmlspecialchars($kembali_url); ?>">Batal</a>
        <p style="font-size:12px; color:#777; text-align:center; margin-top:10px;">Pilihan tidak bisa diubah setelah dikirim.</p>
    </form>
</div>
<script>
function ubahPilihan() {
    const kembali = document.querySelector('input[name=pilihan][value=kembali]').checked;
    document.getElementById('box-resi').style.display = kembali ? 'block' : 'none';
    document.getElementById('no_resi').required = kembali;
}
function cekForm() {
    const p = document.querySelector('input[name=pilihan]:checked');
    if (!p) { alert('Pilih salah satu dulu.'); return false; }
    if (p.value === 'kembali') {
        const r = document.getElementById('no_resi').value.trim();
        if (!/^[A-Za-z0-9\-]{6,30}$/.test(r)) { alert('Nomor resi wajib diisi (6-30 huruf/angka).'); return false; }
        return confirm('Kirim konfirmasi pengembalian barang? Tidak bisa diubah lagi.');
    }
    return confirm('Konfirmasi pesanan selesai? Tidak bisa diubah lagi.');
}
ubahPilihan();
</script>
</body>
</html>