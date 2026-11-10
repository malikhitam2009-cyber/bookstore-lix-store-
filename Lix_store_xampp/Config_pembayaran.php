<?php
$info_bayar = [
    'BCA Transfer'     => ['judul' => 'Bank BCA',     'nomor' => '1234567890',    'an' => 'LIX STORE'],
    'Mandiri Transfer' => ['judul' => 'Bank Mandiri', 'nomor' => '1230009876543', 'an' => 'LIX STORE'],
    'Gopay / QRIS'     => ['judul' => 'Gopay / QRIS', 'nomor' => '085285851973',  'an' => 'LIX STORE'],
    'DANA'             => ['judul' => 'DANA',         'nomor' => '085285851973',  'an' => 'LIX STORE'],
];
// ====== PAJAK & ONGKIR (ubah angka di sini) ======
$ppn_persen = 11;      // PPN dalam persen, dihitung dari subtotal harga buku
$ongkir     = 15000;   // ongkos kirim flat per pesanan (Rp)