<?php
// Kirim email lewat SMTP (tanpa library tambahan, jalan di XAMPP biasa).
// Setiap email dicatat ke tabel email_log (status terkirim/gagal) supaya admin bisa cek.

function pastikan_tabel_email($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS email_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pesanan_id INT NULL,
        tujuan VARCHAR(150) NOT NULL,
        subjek VARCHAR(255) NOT NULL,
        status VARCHAR(20) NOT NULL,
        keterangan VARCHAR(255) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smtp_baca($fp) {
    $out = '';
    while (($line = fgets($fp, 515)) !== false) {
        $out .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    return $out;
}

function smtp_kirim_mentah($cfg, $tujuan, $subjek, $html) {
    $fp = @stream_socket_client("tcp://{$cfg['host']}:{$cfg['port']}", $errno, $errstr, 15);
    if (!$fp) return [false, "Tidak bisa konek SMTP: $errstr"];
    stream_set_timeout($fp, 15);
    smtp_baca($fp);
    $cmd = function ($c, $harap) use ($fp) {
        fwrite($fp, $c . "\r\n");
        $r = smtp_baca($fp);
        return strpos($r, (string)$harap) === 0 ? [true, $r] : [false, trim($r)];
    };
    [$ok, $r] = $cmd('EHLO localhost', 250);           if (!$ok) return [false, $r];
    [$ok, $r] = $cmd('STARTTLS', 220);                 if (!$ok) return [false, $r];
    if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) return [false, 'Gagal TLS'];
    [$ok, $r] = $cmd('EHLO localhost', 250);           if (!$ok) return [false, $r];
    [$ok, $r] = $cmd('AUTH LOGIN', 334);               if (!$ok) return [false, $r];
    [$ok, $r] = $cmd(base64_encode($cfg['user']), 334); if (!$ok) return [false, $r];
    [$ok, $r] = $cmd(base64_encode($cfg['pass']), 235); if (!$ok) return [false, 'Login SMTP gagal: ' . $r];
    [$ok, $r] = $cmd('MAIL FROM:<' . $cfg['from'] . '>', 250); if (!$ok) return [false, $r];
    [$ok, $r] = $cmd('RCPT TO:<' . $tujuan . '>', 250);        if (!$ok) return [false, $r];
    [$ok, $r] = $cmd('DATA', 354);                     if (!$ok) return [false, $r];

    $header  = "From: =?UTF-8?B?" . base64_encode($cfg['from_name']) . "?= <{$cfg['from']}>\r\n";
    $header .= "To: <$tujuan>\r\n";
    $header .= "Subject: =?UTF-8?B?" . base64_encode($subjek) . "?=\r\n";
    $header .= "Date: " . date('r') . "\r\nMIME-Version: 1.0\r\n";
    $header .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $body = chunk_split(base64_encode($html));
    fwrite($fp, $header . $body . "\r\n.\r\n");
    $r = smtp_baca($fp);
    $cmd('QUIT', 221);
    fclose($fp);
    return strpos($r, '250') === 0 ? [true, 'OK'] : [false, trim($r)];
}

// Return true kalau berhasil terkirim. Tidak pernah melempar error (agar alur pesanan tidak rusak).
function kirim_email($conn, $pesanan_id, $tujuan, $subjek, $html) {
    pastikan_tabel_email($conn);
    $status = 'gagal'; $ket = '';
    try {
        $cfg = include __DIR__ . '/config_email.php';
        if (!filter_var($tujuan, FILTER_VALIDATE_EMAIL)) {
            $ket = 'Email tujuan tidak valid';
        } elseif (empty($cfg['aktif'])) {
            $status = 'dilewati'; $ket = 'Email dinonaktifkan di config_email.php';
        } else {
            [$ok, $ket] = smtp_kirim_mentah($cfg, $tujuan, $subjek, $html);
            $status = $ok ? 'terkirim' : 'gagal';
        }
    } catch (Throwable $e) { $ket = $e->getMessage(); }

    $pid = $pesanan_id ? (int)$pesanan_id : null;
    $ket = mb_substr($ket, 0, 250);
    $stmt = mysqli_prepare($conn, "INSERT INTO email_log (pesanan_id, tujuan, subjek, status, keterangan) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "issss", $pid, $tujuan, $subjek, $status, $ket);
    mysqli_stmt_execute($stmt);
    return $status === 'terkirim';
}

function template_email($judul, $isi_html, $kode) {
    return "<div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;border:1px solid #ddd;border-radius:10px;overflow:hidden'>
      <div style='background:#2b2b2b;color:#fff;padding:14px 20px;font-size:18px'>TOKO LIX</div>
      <div style='padding:20px;color:#333;font-size:14px;line-height:1.6'>
        <h3 style='margin:0 0 10px'>" . htmlspecialchars($judul) . "</h3>
        <p>Kode transaksi: <b>" . htmlspecialchars($kode) . "</b></p>
        $isi_html
        <p style='color:#888;font-size:12px;margin-top:20px'>Email ini dikirim otomatis, mohon tidak dibalas.</p>
      </div></div>";
}