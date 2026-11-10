<?php
// Kartu Invoice / Bukti Pembayaran. Dipanggil dari pembayaran.php (versi lama yang memakai include).
// Butuh variabel: $p, $r, $rp, $kode_trx, $metode, $tgl, $total_fmt, $lunas
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