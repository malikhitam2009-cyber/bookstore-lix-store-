<?php
session_start();
include 'koneksi.php';
include 'helper_pesanan.php';
pastikan_kolom_pesanan($conn);

// Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href='login.php';</script>";
    exit;
}

// Batalkan otomatis pesanan yang tidak dibayar dalam 24 jam (stok dikembalikan)
$jumlah_batal_otomatis = batalkan_pesanan_kedaluwarsa($conn, 24);

// Pesanan yang sudah 24 jam dikemas otomatis berubah jadi 'Dalam Perjalanan' + email ke user
$jumlah_kirim_otomatis = proses_pengiriman_otomatis($conn, 24);

// Ambil halaman aktif dari URL, default ke 'pesanan'
$halaman = $_GET['halaman'] ?? 'pesanan';

// ==========================================
// 1. LOGIK APPROVAL PESANAN & TIMER 24 JAM
// ==========================================
if (isset($_GET['approve_id'])) {
    $approve_id = intval($_GET['approve_id']);
    setujui_pesanan($conn, $approve_id); // status Approved (dikemas) + email "sedang dikemas" ke user
    header("Location: admin.php?halaman=pesanan");
    exit;
}

if (isset($_GET['stop_id'])) {
    $stop_id = intval($_GET['stop_id']);
    kirim_pesanan($conn, $stop_id); // lewati sisa timer: langsung 'Dalam Perjalanan' + email ke user
    header("Location: admin.php?halaman=pesanan");
    exit;
}

// Tolak pesanan (POST) -> status Rejected + stok dikembalikan
$notif_tolak = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tolak_id'])) {
    $ok_tolak = batalkan_pesanan($conn, intval($_POST['tolak_id']), 'Rejected');
    header("Location: admin.php?halaman=pesanan&tolak=" . ($ok_tolak ? 'ok' : 'gagal'));
    exit;
}

// ==========================================
// 2. LOGIK CRUD BUKU + STOK
// ==========================================
$edit_book_mode = false;
$b_id = $b_title = $b_category = $b_price = $b_image = $b_desc = '';
$b_stock = 60; // Default stok 60 per bulan

if (isset($_GET['delete_book_id'])) {
    $del_id = intval($_GET['delete_book_id']);
    mysqli_query($conn, "DELETE FROM books WHERE id = $del_id");
    header("Location: admin.php?halaman=buku");
    exit;
}

if (isset($_GET['edit_book_id'])) {
    $edit_book_mode = true;
    $eb_id = intval($_GET['edit_book_id']);
    $res_eb = mysqli_query($conn, "SELECT * FROM books WHERE id = $eb_id");
    if ($res_eb && mysqli_num_rows($res_eb) > 0) {
        $d_eb = mysqli_fetch_assoc($res_eb);
        $b_id       = $d_eb['id'];
        $b_title    = $d_eb['title'];
        $b_category = $d_eb['genre'];
        $b_price    = $d_eb['price'];
        $b_stock    = $d_eb['stock'] ?? 60;
        $b_image    = $d_eb['image'];
        $b_desc     = $d_eb['description'];
    }
}

if (isset($_POST['save_book'])) {
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $price       = intval($_POST['price']);
    $stock       = intval($_POST['stock'] ?? 60);
    $image       = mysqli_real_escape_string($conn, $_POST['image']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $book_id     = intval($_POST['book_id'] ?? 0);

    if ($book_id > 0) {
        mysqli_query($conn, "UPDATE books SET title='$title', genre='$category', price='$price', stock='$stock', image='$image', description='$description' WHERE id=$book_id");
    } else {
        mysqli_query($conn, "INSERT INTO books (title, genre, price, stock, image, description) VALUES ('$title', '$category', '$price', '$stock', '$image', '$description')");
    }
    header("Location: admin.php?halaman=buku");
    exit;
}

// Fitur Reset Stok Bulanan ke Minimal 60
if (isset($_POST['reset_monthly_stock'])) {
    mysqli_query($conn, "UPDATE books SET stock = 60 WHERE stock < 60");
    header("Location: admin.php?halaman=buku");
    exit;
}

// ==========================================
// 3. LOGIK CRUD GENRE
// ==========================================
$edit_genre_mode = false;
$g_id = $g_name = '';

if (isset($_GET['delete_genre_id'])) {
    $dg_id = intval($_GET['delete_genre_id']);
    mysqli_query($conn, "DELETE FROM genres WHERE id_genre = $dg_id");
    header("Location: admin.php?halaman=genre");
    exit;
}

if (isset($_GET['edit_genre_id'])) {
    $edit_genre_mode = true;
    $eg_id = intval($_GET['edit_genre_id']);
    $res_eg = mysqli_query($conn, "SELECT * FROM genres WHERE id_genre = $eg_id");
    if ($res_eg && mysqli_num_rows($res_eg) > 0) {
        $d_eg = mysqli_fetch_assoc($res_eg);
        $g_id = $d_eg['id_genre'];
        $g_name = $d_eg['nama_genre'] ?? $d_eg['genre'] ?? '';
    }
}

if (isset($_POST['save_genre'])) {
    $nama_genre = mysqli_real_escape_string($conn, $_POST['nama_genre']);
    $genre_id   = intval($_POST['genre_id'] ?? 0);

    if ($genre_id > 0) {
        mysqli_query($conn, "UPDATE genres SET nama_genre = '$nama_genre' WHERE id_genre = $genre_id");
    } else {
        mysqli_query($conn, "INSERT INTO genres (nama_genre) VALUES ('$nama_genre')");
    }
    header("Location: admin.php?halaman=genre");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - LIX STORE</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Quicksand', sans-serif; }
        body { background-color: #f4f6f9; color: #333; padding-bottom: 40px; }

        .admin-navbar {
            background-color: #212529;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .admin-navbar h2 { font-size: 20px; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a {
            color: #adb5bd;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.2s;
        }
        .nav-links a:hover, .nav-links a.active {
            color: white;
            background-color: #343a40;
        }
        .nav-links a.logout { background-color: #dc3545; color: white; }
        .nav-links a.logout:hover { background-color: #c82333; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .card h2 { font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 10px; }
        th { background-color: #343a40; color: white; padding: 12px 15px; font-size: 14px; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }

        .badge { padding: 5px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; display: inline-block; }
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .badge-approved { background-color: #d4edda; color: #155724; }

        .btn { padding: 6px 12px; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: bold; display: inline-block; cursor: pointer; border: none; transition: 0.2s; }
        .btn-approve { background-color: #28a745; color: white; }
        .btn-reject { background-color: #dc3545; color: white; }
        .badge-rejected { background-color: #f8d7da; color: #721c24; }
        .notif { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 14px; }
        .notif.ok { background: #d4edda; color: #155724; }
        .notif.warn { background: #fff3cd; color: #856404; }
        .btn-edit { background-color: #ffc107; color: #212529; margin-right: 5px; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-submit { background-color: #007bff; color: white; padding: 10px 20px; font-size: 14px; }
        .btn-cancel { background-color: #6c757d; color: white; padding: 10px 20px; font-size: 14px; text-decoration: none; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 13px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        .book-img-thumb { width: 45px; height: 60px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="admin-navbar">
        <h2>LIX STORE - Admin Panel</h2>
        <div class="nav-links">
            <a href="admin.php?halaman=pesanan" class="<?= $halaman == 'pesanan' ? 'active' : ''; ?>">Kelola Pesanan</a>
            <a href="admin.php?halaman=rekap" class="<?= $halaman == 'rekap' ? 'active' : ''; ?>">Rekap Harian</a>
            <a href="admin.php?halaman=buku" class="<?= $halaman == 'buku' ? 'active' : ''; ?>">Kelola Buku</a>
            <a href="admin.php?halaman=genre" class="<?= $halaman == 'genre' ? 'active' : ''; ?>">Kelola Genre</a>
            <a href="index.php" target="_blank">Lihat Toko</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">

        <!-- ================= KONTEN 1: HALAMAN PESANAN ================= -->
        <?php if ($halaman == 'pesanan'): ?>
        <div class="card">
            <h2>Persetujuan Pesanan Buku & Timer 24 Jam</h2>
            <?php if (($_GET['tolak'] ?? '') === 'ok'): ?>
                <div class="notif ok">Pesanan ditolak dan stok buku sudah dikembalikan.</div>
            <?php elseif (($_GET['tolak'] ?? '') === 'gagal'): ?>
                <div class="notif warn">Pesanan tidak bisa ditolak (hanya pesanan berstatus Pending yang bisa ditolak).</div>
            <?php endif; ?>
            <?php if ($jumlah_kirim_otomatis > 0): ?>
                <div class="notif ok">🚚 <?= (int)$jumlah_kirim_otomatis; ?> pesanan sudah 24 jam dikemas, otomatis berubah ke <b>Dalam Perjalanan</b> dan email sudah dikirim ke pembeli.</div>
            <?php endif; ?>
            <?php
            $res_jalan = mysqli_query($conn, "SELECT COUNT(*) c FROM pesanan WHERE LOWER(status) = 'dalam perjalanan'");
            $n_jalan = $res_jalan ? (int)mysqli_fetch_assoc($res_jalan)['c'] : 0;
            if ($n_jalan > 0): ?>
                <div class="notif warn">📦 <?= $n_jalan; ?> barang masih di perjalanan. Admin menunggu konfirmasi pembeli (Pengiriman Selesai / Barang Dikembalikan).</div>
            <?php endif; ?>
            <?php if ($jumlah_batal_otomatis > 0): ?>
                <div class="notif warn"><?= (int)$jumlah_batal_otomatis; ?> pesanan dibatalkan otomatis karena tidak dibayar lebih dari 24 jam. Stok sudah dikembalikan.</div>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Nama Pembeli</th>
                        <th>Judul Buku</th>
                        <th>Total</th>
                        <th>Metode / Bukti</th>
                        <th>Status</th>
                        <th>Aksi / Timer Countdown</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $res_p = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY id DESC");
                    if ($res_p && mysqli_num_rows($res_p) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($res_p)) {
                            $st = $row['status'] ?? 'Pending';
                            $stl = strtolower(trim($st));
                            $is_app = ($stl === 'approved');
                            $is_jalan = ($stl === 'dalam perjalanan');
                            $is_selesai = in_array($stl, ['selesai', 'dikirim / selesai'], true);
                            $is_kembali = ($stl === 'dikembalikan');
                            $is_rej = in_array($stl, ['rejected', 'dibatalkan'], true);
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><b style="font-family:monospace;"><?= htmlspecialchars($row['kode_transaksi'] ?? '-'); ?></b></td>
                            <td>
                                <b><?= htmlspecialchars($row['nama_pembeli'] ?? '-'); ?></b>
                                <?php if (!empty($row['email']) || !empty($row['no_telepon'])): ?>
                                    <br><small style="color:#555;"><?= htmlspecialchars(trim(($row['email'] ?? '') . ' • ' . ($row['no_telepon'] ?? ''), ' •')); ?></small>
                                <?php endif; ?>
                                <?php if (!empty($row['alamat'])): ?>
                                    <br><small style="color:#777;">📍 <?= htmlspecialchars($row['alamat']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['judul_buku'] ?? '-'); ?></td>
                            <td>Rp <?= number_format($row['total'] ?? 0, 0, ',', '.'); ?></td>
                            <td style="font-size:12px;">
                                <?= htmlspecialchars($row['metode_bayar'] ?? '-'); ?><br>
                                <?php if (!empty($row['bukti_transfer'])): ?>
                                    <a href="bukti_transfer/<?= rawurlencode($row['bukti_transfer']); ?>" target="_blank">Lihat bukti</a>
                                    <?php if (!empty($row['nominal_transfer'])): ?><br><span style="color:#28a745;">Nominal: Rp <?= number_format($row['nominal_transfer'], 0, ',', '.'); ?> ✓</span><?php endif; ?>
                                <?php elseif (($row['metode_bayar'] ?? '') === 'COD'): ?>
                                    <span style="color:#777;">Bayar di tempat</span>
                                <?php else: ?>
                                    <span style="color:#d9534f;">Belum ada bukti</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if ($is_rej)          { $lbl = ($stl === 'rejected') ? 'Ditolak' : 'Dibatalkan'; $sty = ''; $cls = 'badge-rejected'; }
                                elseif ($is_app)      { $lbl = 'Sedang Dikemas'; $sty = ''; $cls = 'badge-approved'; }
                                elseif ($is_jalan)    { $lbl = 'Dalam Perjalanan'; $sty = 'background:#cfe2ff;color:#084298;'; $cls = ''; }
                                elseif ($is_selesai)  { $lbl = 'Selesai'; $sty = 'background:#17a2b8;color:#fff;'; $cls = ''; }
                                elseif ($is_kembali)  { $lbl = 'Dikembalikan'; $sty = 'background:#e2d9f3;color:#432874;'; $cls = ''; }
                                else                  { $lbl = ucfirst($st); $sty = ''; $cls = 'badge-pending'; }
                                ?>
                                <span class="badge <?= $cls; ?>" style="<?= $sty; ?>"><?= htmlspecialchars($lbl); ?></span>
                            </td>
                            <td>
                                <?php if ($is_app && !empty($row['approval_time'])): 
                                    $target_time = date('Y-m-d H:i:s', strtotime($row['approval_time'] . ' + 24 hours'));
                                ?>
                                    <div id="timer-<?= $row['id']; ?>" style="font-weight: bold; color: #d9534f; margin-bottom: 5px; font-size: 13px;">Menghitung...</div>
                                    <a href="admin.php?halaman=pesanan&stop_id=<?= $row['id']; ?>" class="btn" style="background:#343a40; color:white; padding: 5px 10px; font-size:11px;" onclick="return confirm('Hentikan timer dan kirim sekarang? Pembeli akan menerima email bahwa barang menuju lokasinya.');">Kirim Sekarang</a>

                                    <script>
                                        const targetDate_<?= $row['id']; ?> = new Date("<?= $target_time; ?>").getTime();
                                        
                                        const x_<?= $row['id']; ?> = setInterval(function() {
                                            const now = new Date().getTime();
                                            const distance = targetDate_<?= $row['id']; ?> - now;

                                            if (distance < 0) {
                                                clearInterval(x_<?= $row['id']; ?>);
                                                document.getElementById("timer-<?= $row['id']; ?>").innerHTML = "Mengirim... muat ulang halaman"; setTimeout(function(){ location.reload(); }, 2000);
                                            } else {
                                                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                                
                                                document.getElementById("timer-<?= $row['id']; ?>").innerHTML = 
                                                    hours + "j " + minutes + "m " + seconds + "d";
                                            }
                                        }, 1000);
                                    </script>

                                <?php elseif ($is_jalan): ?>
                                    <span style="color:#084298; font-size:13px; font-weight:bold;">🚚 Barang masih di perjalanan</span><br>
                                    <small style="color:#666;">Menunggu konfirmasi pembeli<?= !empty($row['dikirim_time']) ? ' (berangkat ' . date('d M H:i', strtotime($row['dikirim_time'])) . ')' : ''; ?></small>
                                <?php elseif ($is_selesai): ?>
                                    <span style="color: #28a745; font-size: 13px; font-weight: bold;">✅ Diterima pembeli</span>
                                <?php elseif ($is_kembali): ?>
                                    <span style="color: #6f42c1; font-size: 13px; font-weight: bold;">↩ Barang dikembalikan pembeli</span><br><small style="color:#666;">Stok sudah dikembalikan</small>
                                    <?php if (!empty($row['no_resi_retur'])): ?><br><small>📮 Resi: <b style="font-family:monospace;"><?= htmlspecialchars($row['no_resi_retur']); ?></b><?= !empty($row['kurir_retur']) ? ' (' . htmlspecialchars($row['kurir_retur']) . ')' : ''; ?></small><?php endif; ?>
                                    <?php if (!empty($row['alasan_retur'])): ?><br><small style="color:#777;">Alasan: <?= htmlspecialchars($row['alasan_retur']); ?></small><?php endif; ?>
                                <?php elseif ($is_rej): ?>
                                    <span style="color: #dc3545; font-size: 13px; font-weight: bold;">Stok dikembalikan</span>
                                <?php else: ?>
                                    <a href="admin.php?halaman=pesanan&approve_id=<?= $row['id']; ?>" class="btn btn-approve">Approve</a>
                                    <?php if (strtolower($st) === 'pending'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Tolak pesanan ini? Stok buku akan dikembalikan.');">
                                        <input type="hidden" name="tolak_id" value="<?= (int)$row['id']; ?>">
                                        <button type="submit" class="btn btn-reject">Tolak</button>
                                    </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center;'>Belum ada pesanan masuk.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>


        <!-- ================= KONTEN REKAP HARIAN ================= -->
        <?php if ($halaman == 'rekap'):
            $paid_sql = "LOWER(status) IN ('approved','dalam perjalanan','selesai','dikirim / selesai')";
            $kode_hari = trim($_GET['kode'] ?? '');
        ?>
        <div class="card">
            <h2>Rekap Penjualan Harian</h2>
            <p style="font-size:13px; color:#666; margin-bottom:12px;">
                Kode transaksi: <b>TL</b> (Toko Lix) + <b>tanggal</b> + <b>bulan</b> + <b>tahun</b> + nomor urut, contoh <b>TL091126-001</b> = pesanan ke-1 pada 9 November 2026.
                Hanya pesanan yang sudah dibayar &amp; disetujui yang dihitung (Pending, Ditolak, Dibatalkan, dan Dikembalikan tidak masuk).
            </p>
            <table>
                <thead>
                    <tr><th>Kode Hari</th><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Penjualan</th><th>Dikembalikan</th><th>Detail</th></tr>
                </thead>
                <tbody>
                <?php
                $q = mysqli_query($conn, "SELECT LEFT(kode_transaksi, 8) AS kode_hari,
                        MIN(DATE(created_at)) AS tgl,
                        SUM(CASE WHEN $paid_sql THEN 1 ELSE 0 END) AS jml,
                        SUM(CASE WHEN $paid_sql THEN total ELSE 0 END) AS omzet,
                        SUM(CASE WHEN LOWER(status) = 'dikembalikan' THEN total ELSE 0 END) AS retur
                    FROM pesanan WHERE kode_transaksi IS NOT NULL AND kode_transaksi <> ''
                    GROUP BY LEFT(kode_transaksi, 8) ORDER BY tgl DESC, kode_hari DESC");
                $grand = 0; $grand_jml = 0;
                if ($q && mysqli_num_rows($q) > 0) {
                    while ($r = mysqli_fetch_assoc($q)) {
                        $grand += (int)$r['omzet']; $grand_jml += (int)$r['jml'];
                ?>
                    <tr>
                        <td><b style="font-family:monospace;"><?= htmlspecialchars($r['kode_hari']); ?></b></td>
                        <td><?= $r['tgl'] ? date('d M Y', strtotime($r['tgl'])) : '-'; ?></td>
                        <td><?= (int)$r['jml']; ?></td>
                        <td><b>Rp <?= number_format($r['omzet'], 0, ',', '.'); ?></b></td>
                        <td><?= $r['retur'] > 0 ? 'Rp ' . number_format($r['retur'], 0, ',', '.') : '-'; ?></td>
                        <td><a class="btn btn-edit" href="admin.php?halaman=rekap&kode=<?= urlencode($r['kode_hari']); ?>#detail">Lihat</a></td>
                    </tr>
                <?php } } else { echo "<tr><td colspan='6' style='text-align:center;'>Belum ada transaksi.</td></tr>"; } ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="2"><b>TOTAL KESELURUHAN</b></td><td><b><?= $grand_jml; ?></b></td><td colspan="3"><b>Rp <?= number_format($grand, 0, ',', '.'); ?></b></td></tr>
                </tfoot>
            </table>
        </div>

        <?php if ($kode_hari !== ''): ?>
        <div class="card" id="detail">
            <h2>Detail Transaksi <?= htmlspecialchars($kode_hari); ?></h2>
            <table>
                <thead><tr><th>Kode Transaksi</th><th>Pembeli</th><th>Buku</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php
                $like = $kode_hari . '-%';
                $stmt = mysqli_prepare($conn, "SELECT * FROM pesanan WHERE kode_transaksi LIKE ? ORDER BY id ASC");
                mysqli_stmt_bind_param($stmt, "s", $like);
                mysqli_stmt_execute($stmt);
                $qd = mysqli_stmt_get_result($stmt);
                $sum = 0;
                while ($d = mysqli_fetch_assoc($qd)) {
                    $dibayar = status_sudah_bayar($d['status']);
                    if ($dibayar) $sum += (int)$d['total'];
                ?>
                    <tr style="<?= $dibayar ? '' : 'opacity:.55;'; ?>">
                        <td style="font-family:monospace;"><?= htmlspecialchars($d['kode_transaksi']); ?></td>
                        <td><?= htmlspecialchars($d['nama_pembeli']); ?></td>
                        <td><?= htmlspecialchars($d['judul_buku']); ?></td>
                        <td>Rp <?= number_format($d['total'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($d['status']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot><tr><td colspan="3"><b>Total penjualan hari ini (yang sudah dibayar)</b></td><td colspan="2"><b>Rp <?= number_format($sum, 0, ',', '.'); ?></b></td></tr></tfoot>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>


        <!-- ================= KONTEN 2: HALAMAN BUKU + STOK ================= -->
        <?php if ($halaman == 'buku'): ?>
        
        <!-- Tombol Reset Stok Bulanan -->
        <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size:15px;">📦 Reset Stok Minimal Bulanan</h3>
                <p style="font-size: 13px; color: #666;">Kembalikan stok seluruh buku yang di bawah 60 pcs menjadi 60 pcs.</p>
            </div>
            <form method="POST" onsubmit="return confirm('Reset semua stok buku menjadi 60 kembali?');">
                <button type="submit" name="reset_monthly_stock" class="btn" style="background:#28a745; color:white; padding:8px 15px;">🔄 Reset Stok Bulanan (60 pcs)</button>
            </form>
        </div>

        <!-- Form Tambah / Edit Buku -->
        <div class="card" id="form-buku">
            <h2><?= $edit_book_mode ? 'Edit Buku' : 'Tambah Buku Baru'; ?></h2>
            <form action="admin.php?halaman=buku" method="POST">
                <input type="hidden" name="book_id" value="<?= $b_id; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Judul Buku</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($b_title); ?>">
                    </div>
                    <div class="form-group">
                        <label>Genre / Kategori</label>
                        <input type="text" name="category" class="form-control" required placeholder="Contoh: mystery / history" value="<?= htmlspecialchars($b_category); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="price" class="form-control" required value="<?= $b_price; ?>">
                    </div>
                    <div class="form-group">
                        <label>Stok Buku (Minimal 60/Bulan)</label>
                        <input type="number" name="stock" class="form-control" required value="<?= $b_stock; ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama File Gambar (misal: Lupin.jpg)</label>
                        <input type="text" name="image" class="form-control" required value="<?= htmlspecialchars($b_image); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi Buku</label>
                    <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($b_desc); ?></textarea>
                </div>

                <button type="submit" name="save_book" class="btn btn-submit"><?= $edit_book_mode ? 'Update Buku' : 'Simpan Buku Baru'; ?></button>
                <?php if ($edit_book_mode): ?>
                    <a href="admin.php?halaman=buku" class="btn btn-cancel">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabel Katalog Buku -->
        <div class="card">
            <h2>Katalog Buku Terdaftar</h2>
            <table>
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Judul Buku</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $res_b = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC");
                    if ($res_b && mysqli_num_rows($res_b) > 0) {
                        while ($buku = mysqli_fetch_assoc($res_b)) {
                            $stok = $buku['stock'] ?? 60;
                    ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($buku['image']); ?>" class="book-img-thumb" onerror="this.src='https://via.placeholder.com/45x60';"></td>
                            <td><b><?= htmlspecialchars($buku['title']); ?></b></td>
                            <td><span class="badge" style="background:#e9ecef; color:#495057;"><?= ucfirst($buku['genre']); ?></span></td>
                            <td>Rp <?= number_format($buku['price'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge <?= $stok < 10 ? 'badge-pending' : 'badge-approved'; ?>">
                                    <?= $stok; ?> Pcs
                                </span>
                            </td>
                            <td>
                                <a href="admin.php?halaman=buku&edit_book_id=<?= $buku['id']; ?>#form-buku" class="btn btn-edit">Edit</a>
                                <a href="admin.php?halaman=buku&delete_book_id=<?= $buku['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus buku ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>Belum ada data buku.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>


        <!-- ================= KONTEN 3: HALAMAN GENRE ================= -->
        <?php if ($halaman == 'genre'): ?>
        
        <div class="card" id="form-genre">
            <h2><?= $edit_genre_mode ? 'Edit Genre' : 'Tambah Genre Baru'; ?></h2>
            <form action="admin.php?halaman=genre" method="POST">
                <input type="hidden" name="genre_id" value="<?= $g_id; ?>">

                <div class="form-group">
                    <label>Nama Genre</label>
                    <input type="text" name="nama_genre" class="form-control" required placeholder="Contoh: Mystery, Sejarah, Fiksi" value="<?= htmlspecialchars($g_name); ?>">
                </div>

                <button type="submit" name="save_genre" class="btn btn-submit"><?= $edit_genre_mode ? 'Update Genre' : 'Simpan Genre'; ?></button>
                <?php if ($edit_genre_mode): ?>
                    <a href="admin.php?halaman=genre" class="btn btn-cancel">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>Daftar Kategori Genre</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Genre</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $res_g = mysqli_query($conn, "SELECT * FROM genres ORDER BY id_genre DESC");
                    if ($res_g && mysqli_num_rows($res_g) > 0) {
                        $no = 1;
                        while ($genre = mysqli_fetch_assoc($res_g)) {
                            $g_val = $genre['nama_genre'] ?? $genre['genre'] ?? '-';
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><b><?= htmlspecialchars($g_val); ?></b></td>
                            <td>
                                <a href="admin.php?halaman=genre&edit_genre_id=<?= $genre['id_genre']; ?>#form-genre" class="btn btn-edit">Edit</a>
                                <a href="admin.php?halaman=genre&delete_genre_id=<?= $genre['id_genre']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus genre ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;'>Belum ada data genre.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>

</body>
</html>