<?php
// Halaman checkout: isi data pembeli, lihat rekening toko, pilih metode bayar, lalu buat pesanan (Pending).
session_start();
include 'koneksi.php';
include 'config_pembayaran.php';

// Isi otomatis dari akun kalau pembeli sudah login
$pre = ['nama' => '', 'email' => '', 'telp' => '', 'alamat' => ''];
$lengkap = false;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "SELECT nama, email, no_telp, alamat FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($u) {
        $pre = ['nama' => $u['nama'] ?? '', 'email' => $u['email'] ?? '', 'telp' => $u['no_telp'] ?? '', 'alamat' => $u['alamat'] ?? ''];
    }
}
// Data akun lengkap (dari register) -> tampil ringkas, pembeli cukup pilih metode pembayaran
$lengkap = isset($_SESSION['user_id'])
    && trim($pre['nama']) !== '' && trim($pre['email']) !== ''
    && strlen(preg_replace('/\D/', '', $pre['telp'])) >= 8
    && mb_strlen(trim($pre['alamat'])) >= 10;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - LIX STORE</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Quicksand:wght@500;600;700&display=swap');
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Quicksand', sans-serif; font-weight: 600; }
    h1, h2, h3, .btn { font-family: 'Fredoka', cursive; }
    body { background: #D9C3B0; color: #2b2b2b; padding-bottom: 50px; }
    .topbar { background: #2b2b2b; color: #fff; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
    .brand { display: flex; align-items: center; gap: 10px; font-family: 'Fredoka', cursive; letter-spacing: 1px; }
    .brand img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
    .topbar a { color: #fff; text-decoration: none; background: #28a745; padding: 8px 14px; border-radius: 8px; font-size: 14px; }
    .wrap { max-width: 600px; margin: 24px auto; padding: 0 16px; }
    .card { background: #fff; border-radius: 16px; padding: 22px; margin-bottom: 18px; box-shadow: 0 6px 18px rgba(0,0,0,.1); }
    .card h3 { margin-bottom: 12px; }
    .step { display: inline-block; background: #2b2b2b; color: #fff; width: 24px; height: 24px; line-height: 24px; text-align: center; border-radius: 50%; font-size: 13px; margin-right: 8px; }
    label.lbl { display: block; font-size: 13px; margin: 12px 0 4px; }
    .input { width: 100%; padding: 10px; border: 1.5px solid #ddd; border-radius: 8px; background: #fff; font-size: 14px; }
    textarea.input { resize: vertical; min-height: 80px; }
    .item { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed #ddd; font-size: 14px; }
    .item:last-child { border-bottom: none; }
    .total-row { display: flex; justify-content: space-between; font-size: 18px; margin-top: 12px; }
    .total-row b { color: #d9534f; }
    .rek { background: #FAF6F0; border: 2px dashed #B09B88; border-radius: 10px; padding: 12px 14px; margin-top: 10px; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .rek .nm { font-size: 12px; color: #777; }
    .rek .no { font-family: 'Fredoka', cursive; font-size: 19px; letter-spacing: 1px; }
    .rek .an { font-size: 12px; }
    .btn { display: block; width: 100%; padding: 13px; border: none; border-radius: 10px; color: #fff; background: #28a745; font-size: 16px; cursor: pointer; margin-top: 16px; text-align: center; text-decoration: none; }
    .btn:disabled { opacity: .6; cursor: wait; }
    .btn.small { display: inline-block; width: auto; padding: 6px 12px; font-size: 12px; margin: 0; background: #2b2b2b; white-space: nowrap; }
    .metode { display: flex; align-items: center; gap: 10px; padding: 11px 12px; border: 2px solid #ddd; border-radius: 10px; margin-top: 8px; cursor: pointer; font-size: 14px; }
    .metode:has(input:checked) { border-color: #28a745; background: #f1fbf4; }
    .metode input { accent-color: #28a745; }
    .msg-err { background: #f8d7da; color: #721c24; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-top: 12px; display: none; }
    .kosong { text-align: center; padding: 30px 10px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand"><img src="logo.jpg" alt="logo"> LIX STORE</div>
        <a href="index.php">← Kembali ke Toko</a>
    </div>

    <div class="wrap">

        <div id="kosong" class="card kosong" style="display:none;">
            <h3>Keranjang kamu kosong 🛒</h3>
            <p style="font-size:14px; color:#555; margin-top:6px;">Pilih buku dulu di toko, lalu kembali ke sini.</p>
            <a class="btn" href="index.php">Belanja Sekarang</a>
        </div>

        <form id="form-checkout" style="display:none;" onsubmit="buatPesanan(event)">

            <!-- 1. RINGKASAN KERANJANG -->
            <div class="card">
                <h3><span class="step">1</span>Pesanan Kamu</h3>
                <div id="daftar-item"></div>
                <div class="item" style="border-bottom:none;"><span>Subtotal</span><b id="subtotal-harga">Rp 0</b></div>
                <div class="item" style="border-bottom:none;"><span>PPN (<?= (float)$ppn_persen; ?>%)</span><b id="ppn-harga">Rp 0</b></div>
                <div class="item"><span>Ongkos Kirim</span><b id="ongkir-harga">Rp 0</b></div>
                <div class="total-row"><span>Total Bayar</span><b id="total-harga">Rp 0</b></div>
            </div>

            <!-- 2. DATA PEMBELI -->
            <div class="card">
                <h3><span class="step">2</span>Data Pembeli</h3>

                <?php if ($lengkap): ?>
                <!-- Data diambil dari akun (saat register), pembeli tinggal pilih pembayaran -->
                <div id="ringkas-data" style="font-size:14px; line-height:1.7; background:#FAF6F0; border:2px dashed #B09B88; border-radius:10px; padding:12px 14px;">
                    <div><b><?= htmlspecialchars($pre['nama']); ?></b></div>
                    <div>✉️ <?= htmlspecialchars($pre['email']); ?></div>
                    <div>📞 <?= htmlspecialchars($pre['telp']); ?></div>
                    <div>📍 <?= nl2br(htmlspecialchars($pre['alamat'])); ?></div>
                    <p style="font-size:12px; color:#777; margin-top:6px;">Pesanan akan dikirim ke alamat ini. Notifikasi dikirim ke email di atas.</p>
                    <button type="button" class="btn small" onclick="document.getElementById('edit-data').style.display='block'; document.getElementById('ringkas-data').style.display='none';">Ubah data</button>
                </div>
                <?php endif; ?>

                <div id="edit-data" style="<?= $lengkap ? 'display:none;' : ''; ?>">

                <label class="lbl" for="nama">Nama Lengkap</label>
                <input class="input" type="text" id="nama" name="customer_name" value="<?= htmlspecialchars($pre['nama']); ?>" placeholder="Nama kamu" required maxlength="100">

                <label class="lbl" for="email">Email</label>
                <input class="input" type="email" id="email" name="email" value="<?= htmlspecialchars($pre['email']); ?>" placeholder="contoh@email.com" required maxlength="100">

                <label class="lbl" for="telp">Nomor Telepon / WhatsApp</label>
                <input class="input" type="tel" id="telp" name="no_telepon" value="<?= htmlspecialchars($pre['telp']); ?>" placeholder="08xxxxxxxxxx" required maxlength="20" pattern="[0-9+\-\s]{8,20}" title="Isi nomor telepon yang valid (8-20 angka)">

                <label class="lbl" for="alamat">Alamat Lengkap Pengiriman</label>
                <textarea class="input" id="alamat" name="alamat" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos" required minlength="10" maxlength="500"><?= htmlspecialchars($pre['alamat']); ?></textarea>
                </div>
            </div>

            <!-- 3. REKENING TOKO -->
            <div class="card">
                <h3><span class="step">3</span>Nomor Rekening Toko</h3>
                <p style="font-size:13px; color:#555;">Transfer ke salah satu rekening di bawah sesuai metode yang kamu pilih. Bukti transfer diupload setelah pesanan dibuat.</p>
                <?php foreach ($info_bayar as $nama_metode => $info): ?>
                <div class="rek">
                    <div>
                        <div class="nm"><?= htmlspecialchars($info['judul']); ?></div>
                        <div class="no"><?= htmlspecialchars($info['nomor']); ?></div>
                        <div class="an">a.n. <?= htmlspecialchars($info['an']); ?></div>
                    </div>
                    <button type="button" class="btn small" onclick="salin('<?= htmlspecialchars($info['nomor'], ENT_QUOTES); ?>', this)">Salin</button>
                </div>
                <?php endforeach; ?>
                <?php if (file_exists(__DIR__ . '/qris.jpg')): ?>
                    <div style="text-align:center; margin-top:12px;"><img src="qris.jpg" alt="QRIS" style="max-width:100%; border-radius:8px;"></div>
                <?php endif; ?>
            </div>

            <!-- 4. METODE PEMBAYARAN -->
            <div class="card">
                <h3><span class="step">4</span>Bayar Via Apa?</h3>
                <?php foreach ($info_bayar as $nama_metode => $info): ?>
                    <label class="metode"><input type="radio" name="payment_method" value="<?= htmlspecialchars($nama_metode); ?>" required> <?= htmlspecialchars($info['judul']); ?><?= strpos($nama_metode, 'Transfer') !== false ? ' (Transfer)' : ''; ?></label>
                <?php endforeach; ?>
                <label class="metode"><input type="radio" name="payment_method" value="COD" required> Bayar di Tempat (COD)</label>

                <div id="pesan-error" class="msg-err"></div>
                <button type="submit" id="btn-pesan" class="btn">Buat Pesanan & Lanjut Bayar</button>
                <p style="font-size:12px; color:#777; text-align:center; margin-top:10px;">Pesanan akan berstatus <b>Pending</b> sampai bukti pembayaran dikirim dan disetujui admin.</p>
            </div>
        </form>
    </div>

    <script>
        const PPN_PERSEN = <?= (float)$ppn_persen; ?>;
        const ONGKIR = <?= (int)$ongkir; ?>;
        let cart = [];
        try { cart = JSON.parse(localStorage.getItem('lix_cart') || '[]') || []; } catch (e) { cart = []; }

        function rupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

        function tampilkanKeranjang() {
            if (!cart.length) {
                document.getElementById('kosong').style.display = 'block';
                return;
            }
            document.getElementById('form-checkout').style.display = 'block';
            const box = document.getElementById('daftar-item');
            let total = 0;
            cart.forEach(item => {
                const sub = (Number(item.price) || 0) * item.qty;
                total += sub;
                const row = document.createElement('div');
                row.className = 'item';
                const kiri = document.createElement('span');
                kiri.textContent = item.title + ' (' + item.qty + 'x)';
                const kanan = document.createElement('b');
                kanan.textContent = rupiah(sub);
                row.appendChild(kiri); row.appendChild(kanan);
                box.appendChild(row);
            });
            const ppn = Math.round(total * PPN_PERSEN / 100);
            document.getElementById('subtotal-harga').textContent = rupiah(total);
            document.getElementById('ppn-harga').textContent = rupiah(ppn);
            document.getElementById('ongkir-harga').textContent = rupiah(ONGKIR);
            document.getElementById('total-harga').textContent = rupiah(total + ppn + ONGKIR);
        }

        function salin(teks, btn) {
            const selesai = () => { const t = btn.innerText; btn.innerText = 'Tersalin ✓'; setTimeout(() => btn.innerText = t, 1500); };
            if (navigator.clipboard) { navigator.clipboard.writeText(teks).then(selesai); }
            else { const i = document.createElement('input'); i.value = teks; document.body.appendChild(i); i.select(); document.execCommand('copy'); i.remove(); selesai(); }
        }

        async function buatPesanan(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-pesan');
            const err = document.getElementById('pesan-error');
            err.style.display = 'none';
            btn.disabled = true;
            btn.textContent = 'Membuat pesanan...';

            const fd = new FormData(document.getElementById('form-checkout'));
            fd.append('cart_json', JSON.stringify(cart.map(i => ({ title: i.title, qty: i.qty }))));

            try {
                const res = await fetch('proses_checkout.php', { method: 'POST', body: fd });
                const teks = await res.text();
                let data = null;
                try { data = JSON.parse(teks); } catch (x) {}

                if (data && data.success) {
                    try {
                        localStorage.setItem('lix_pending_order', JSON.stringify({ id: data.order_id, token: data.token }));
                        localStorage.removeItem('lix_cart');
                    } catch (x) {}
                    window.location.href = data.redirect;
                    return;
                }
                if (data) {
                    err.textContent = 'Gagal: ' + (data.message || 'Error server');
                } else {
                    // Server membalas selain JSON (biasanya error PHP) -> tampilkan isinya supaya mudah dicari penyebabnya
                    const bersih = teks.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 400);
                    err.textContent = 'Server membalas bukan JSON (HTTP ' + res.status + '): ' + (bersih || '(kosong)');
                }
            } catch (x) {
                err.textContent = 'Tidak bisa menghubungi server (' + x.message + '). Pastikan Apache XAMPP menyala.';
            }
            err.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Buat Pesanan & Lanjut Bayar';
        }

        tampilkanKeranjang();
    </script>
</body>
</html>