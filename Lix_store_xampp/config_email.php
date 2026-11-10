<?php
// ====== PENGATURAN EMAIL (SMTP) ======
// Isi dengan akun email toko. Contoh Gmail:
//  1. Aktifkan "Verifikasi 2 langkah" di akun Google
//  2. Buat "App Password" (Akun Google > Keamanan > Sandi aplikasi)
//  3. Tempel 16 karakter App Password di 'pass' (BUKAN password login Gmail)
return [
    'aktif'     => true,                    // false = email tidak dikirim, hanya dicatat di tabel email_log
    'host'      => 'smtp.gmail.com',
    'port'      => 587,                     // STARTTLS
    'user'      => 'emailtoko@gmail.com',   // <-- GANTI
    'pass'      => 'xxxxxxxxxxxxxxxx',      // <-- GANTI (App Password)
    'from'      => 'emailtoko@gmail.com',   // <-- GANTI
    'from_name' => 'Toko Lix',
];