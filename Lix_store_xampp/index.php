<?php
// Mulai session untuk mengecek status login user
session_start();
// Koneksi ke database
include 'koneksi.php';

// Ambil semua data buku dari tabel database
$query_buku = "SELECT * FROM books ORDER BY id DESC";
$result_buku = mysqli_query($conn, $query_buku);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIX STORE - Digital Book Store</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Quicksand:wght@500;600;700&display=swap');

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Quicksand', sans-serif;
        font-weight: 600;
    }

    body {
        background-color: #D9C3B0;
        color: #2b2b2b;
        padding-bottom: 50px;
    }

    h1, h2, h3, .logo, nav a, .genre-btn, .book-title, .submit-btn {
        font-family: 'Fredoka', cursive;
    }

    /* HEADER */
    header {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px 40px;
    }

    .top-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo {
        width: 50px;
        height: 50px;
        background-color: #fff;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1.5px solid #2b2b2b;
        overflow: hidden;
        cursor: pointer;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .logo:hover {
        transform: scale(1.08);
    }

    .logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .search-box {
        width: 450px;
        background-color: #E8D9CB;
        border: 1px solid #B09B88;
        border-radius: 20px;
        padding: 10px 20px;
        display: flex;
        align-items: center;
    }

    .search-box input {
        width: 100%;
        border: none;
        background: transparent;
        outline: none;
        font-size: 15px;
        color: #2b2b2b;
    }

    .search-box input::placeholder {
        color: #8c7b6d;
    }

    .nav-user {
        display: flex;
        align-items: center;
        gap: 20px;
        font-size: 16px;
        color: #2b2b2b;
        flex-shrink: 0;
    }

    .cart-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .auth-links {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .auth-btn {
        cursor: pointer;
        text-transform: uppercase;
        font-weight: bold;
        font-size: 14px;
        letter-spacing: 0.5px;
        background: #2b2b2b;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .auth-btn:hover {
        background: #444;
    }

    .auth-btn.outline {
        background: transparent;
        border: 1.5px solid #2b2b2b;
        color: #2b2b2b;
    }

    .auth-btn.outline:hover {
        background: #2b2b2b;
        color: #fff;
    }

    /* NAVIGASI TAB */
    nav {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 15px;
    }

    nav a {
        text-decoration: none;
        color: #2b2b2b;
        font-size: 22px;
        cursor: pointer;
        padding-bottom: 2px;
        position: relative;
    }

    nav a.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: #2b2b2b;
    }

    /* BANNER PROMO */
    .banner {
        background-color: #212121;
        color: #fff;
        max-width: 1320px;
        margin: 15px auto 40px auto;
        padding: 45px 50px;
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .banner-text h1 {
        font-size: 52px;
        line-height: 1;
        letter-spacing: 0.5px;
    }

    .banner-books {
        display: flex;
        gap: 16px;
    }

    .banner-books img {
        width: 110px;
        height: 160px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        transition: transform 0.2s ease;
    }

    .banner-books img:hover {
        transform: scale(1.05);
    }

    /* CONTAINER BUKU & GENRE */
    .container {
        max-width: 1320px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .genre-list {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .genre-btn {
        background-color: #2b2b2b;
        color: white;
        padding: 14px 20px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        flex: 1;
        min-width: 160px;
        text-align: center;
    }

    .genre-btn:hover, .genre-btn.active {
        background-color: #4a4a4a;
        transform: translateY(-2px);
    }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 25px 20px;
    }

    .book-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.2s ease;
        background: rgba(255, 255, 255, 0.2);
        padding: 10px;
        border-radius: 10px;
    }

    .book-card:hover {
        transform: translateY(-4px);
        background: rgba(255, 255, 255, 0.4);
    }

    .book-card img {
        width: 100%;
        height: 185px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        cursor: pointer;
    }

    .book-title {
        margin-top: 8px;
        font-size: 13px;
        color: #2b2b2b;
        line-height: 1.2;
        cursor: pointer;
    }

    .book-price-card {
        font-size: 13px;
        font-weight: bold;
        color: #d9534f;
        margin-top: 4px;
    }

    /* ACTION KONTROL PADA CARD BUKU */
    .card-action-box {
        margin-top: 10px;
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: center;
    }

    .card-qty-control {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #E8D9CB;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #B09B88;
    }

    .qty-btn-sm {
        background: #2b2b2b;
        color: #fff;
        border: none;
        width: 22px;
        height: 22px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qty-btn-sm:hover {
        background: #444;
    }

    .btn-add-direct {
        background: #2b2b2b;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        width: 100%;
        transition: background 0.2s;
        font-family: 'Fredoka', cursive;
    }

    .btn-add-direct:hover {
        background: #28a745;
    }

    /* MODAL GLOBAL */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(4px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 16px;
        width: 420px;
        text-align: center;
        position: relative;
        box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    }

    .close-btn {
        position: absolute;
        top: 14px;
        right: 18px;
        font-size: 22px;
        cursor: pointer;
        border: none;
        background: none;
        color: #777;
    }

    .close-btn:hover {
        color: #2b2b2b;
    }

    .input-field {
        width: 100%;
        padding: 12px 14px;
        margin: 8px 0;
        border: 1.5px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
    }

    .input-field:focus {
        border-color: #2b2b2b;
    }

    .submit-btn {
        width: 100%;
        padding: 12px;
        background: #2b2b2b;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 15px;
        transition: all 0.2s ease;
    }

    .submit-btn:hover {
        background: #444;
    }

    .about-info {
        text-align: left;
        margin-top: 15px;
        font-size: 14px;
        line-height: 1.6;
        color: #444;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        background: #f8f8f8;
        padding: 8px 12px;
        border-radius: 8px;
        border-left: 4px solid #2b2b2b;
    }

    .book-modal-body {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .book-modal-body img {
        width: 130px;
        height: 185px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        margin-bottom: 15px;
        user-select: none;
        pointer-events: none;
    }

    .book-modal-desc {
        font-size: 13px;
        color: #555;
        margin: 10px 0;
        line-height: 1.4;
        text-align: justify;
    }

    .book-modal-price {
        font-size: 20px;
        font-family: 'Fredoka', cursive;
        color: #d9534f;
        margin-top: 6px;
    }

    /* QTY CONTROLLER MODAL */
    .qty-control {
        display: flex;
        align-items: center;
        gap: 15px;
        margin: 15px 0;
    }

    .qty-btn {
        background: #2b2b2b;
        color: #fff;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        font-size: 18px;
        cursor: pointer;
    }

    .qty-btn:hover { background: #444; }

    .spinner {
        width: 45px;
        height: 45px;
        border: 5px solid #E8D9CB;
        border-top: 5px solid #2b2b2b;
        border-radius: 50%;
        animation: spin 0.9s linear infinite;
        margin: 20px auto 10px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .receipt-box {
        border: 2px dashed #B09B88;
        padding: 15px;
        background: #FAF6F0;
        border-radius: 8px;
        text-align: left;
        margin-top: 15px;
        font-size: 14px;
    }

    .receipt-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .receipt-divider {
        border-top: 1px dashed #ccc;
        margin: 10px 0;
    }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="top-header">
            <div class="logo" onclick="openAboutModal()" title="About Us & Contact">
                <img src="logo.jpg" alt="Logo">
            </div>

            <div class="search-box">
                <input type="text" id="search-input" placeholder="Cari buku..." onkeyup="searchBook()">
            </div>

            <div class="nav-user">
                <button class="cart-btn" onclick="openCartHistoryModal()" title="Keranjang & Riwayat Belanja" style="background: #2b2b2b; color: white; padding: 6px 12px; border-radius: 8px;">
                    🛒 Keranjang (<span id="cart-count" style="color: white;">0</span>)
                </button>
                
                <div class="auth-links">
                    <?php if(isset($_SESSION['user_name'])): ?>
                        <span style="font-size: 14px; font-weight: bold;">Hai, <?= htmlspecialchars($_SESSION['user_name']); ?></span>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin.php" class="auth-btn" style="background: #28a745;">Admin</a>
                        <?php elseif(isset($_SESSION['role'])): ?>
                            <a href="user.php" class="auth-btn" style="background: #28a745;">Akun Saya</a>
                        <?php endif; ?>
                        <a href="logout.php" class="auth-btn outline" style="border-color: #d9534f; color: #d9534f;">Logout</a>
                    <?php else: ?>
                        <div class="auth-btn" onclick="openLoginModal()">Login</div>
                        <div class="auth-btn outline" onclick="openRegisterModal()">Register</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav>
            <a id="nav-home" class="active" onclick="switchPage('home')">Home</a>
            <a id="nav-books" onclick="switchPage('books')">Books</a>
        </nav>
    </header>

    <!-- BAR PESANAN PENDING (khusus device ini, dari localStorage) -->
    <div id="pending-bar" style="display:none; max-width:1320px; margin:0 auto 15px auto; padding:12px 20px; border-radius:12px; background:#fff3cd; color:#856404; align-items:center; justify-content:space-between; gap:12px; box-shadow:0 2px 8px rgba(0,0,0,.1);">
        <span id="pending-bar-text" style="font-size:14px;"></span>
        <a id="pending-bar-link" href="#" onclick="bersihkanPendingJikaSelesai()" class="auth-btn" style="background:#28a745; white-space:nowrap;">Lanjutkan Pembayaran</a>
    </div>

    <!-- BANNER PROMO -->
    <div id="home-banner" class="banner">
        <div class="banner-text">
            <h1>GET<br>50%<br>DISCOUNT</h1>
        </div>
        <div class="banner-books">
            <img src="SHERLOCK HOLMES.jpg" alt="Sherlock Holmes">
            <img src="Lupin.jpg" alt="Lupin">
            <img src="Madilog.jpg" alt="Madilog">
            <img src="Detektif.jpg" alt="Keigo Higashino">
            <img src="30 Bohong besar.jpg" alt="30 Bohong Besar Soeharto">
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="container">
        <div id="genre-section" style="display: none;">
            <h2 style="margin-bottom: 15px;">Pilih Genre Buku</h2>
            <div class="genre-list">
                <button class="genre-btn" onclick="filterGenre('mystery', this)">Mystery & Detective</button>
                <button class="genre-btn" onclick="filterGenre('history', this)">Sejarah & Filsafat</button>
                <button class="genre-btn" onclick="filterGenre('fiction', this)">Novel & Fiksi</button>
                <button class="genre-btn" onclick="filterGenre('comic', this)">Sains & Komik</button>
            </div>
        </div>

        <div id="books-grid" class="books-grid">
            <?php 
            if ($result_buku && mysqli_num_rows($result_buku) > 0) {
                while ($row = mysqli_fetch_assoc($result_buku)) {
                    $genre = strtolower($row['genre']);
                    $harga_formatted = "Rp " . number_format($row['price'], 0, ',', '.');
                    
                    $title_clean = htmlspecialchars($row['title'], ENT_QUOTES);
                    $image_clean = htmlspecialchars($row['image'], ENT_QUOTES);
                    
                    $desc_safe = str_replace(["\r", "\n"], " ", $row['description']);
                    $desc_clean = htmlspecialchars($desc_safe, ENT_QUOTES);
                    
                    $price_value = (int)$row['price'];
                    $book_id = $row['id'];
            ?>
                    <div class="book-card" data-category="<?= $genre; ?>">
                        <!-- Bagian Cover & Judul (Klik untuk Buka Detail Modal) -->
                        <div style="width: 100%; display: flex; flex-direction: column; align-items: center;" onclick="showBookDetail('<?= addslashes($title_clean); ?>', '<?= addslashes($image_clean); ?>', '<?= addslashes($desc_clean); ?>', '<?= $harga_formatted; ?>', <?= $price_value; ?>)">
                            <img src="<?= $image_clean; ?>" alt="<?= $title_clean; ?>" onerror="this.onerror=null; this.src='logo.jpg';">
                            <div class="book-title"><?= $title_clean; ?></div>
                            <div class="book-price-card"><?= $harga_formatted; ?></div>
                        </div>

                        <!-- Fitur Kontrol Jumlah & Tambah Keranjang Langsung -->
                        <div class="card-action-box" onclick="event.stopPropagation();">
                            <div class="card-qty-control">
                                <button class="qty-btn-sm" onclick="changeCardQty(<?= $book_id; ?>, -1)">-</button>
                                <span id="card-qty-<?= $book_id; ?>" style="font-size: 13px; font-weight: bold;">1</span>
                                <button class="qty-btn-sm" onclick="changeCardQty(<?= $book_id; ?>, 1)">+</button>
                            </div>
                            <button class="btn-add-direct" onclick="addToCartDirect('<?= addslashes($title_clean); ?>', <?= $price_value; ?>, <?= $book_id; ?>)">+ Keranjang</button>
                        </div>
                    </div>
            <?php 
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center;'>Belum ada data buku.</p>";
            }
            ?>
        </div>
    </div>

    <!-- MODAL ABOUT -->
    <div id="about-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeAboutModal()">✕</button>
            <h2 style="margin-bottom: 10px;">About LIX STORE</h2>
            <div class="about-info">
                <p>Selamat datang di <b>LIX STORE</b>! Kami menyediakan berbagai koleksi buku digital berkualitas dengan harga terjangkau.</p>
                <h3 style="margin-top: 15px; font-size: 16px;">Contact Us:</h3>
                <div class="contact-item"><span>📧</span> <span>athallahmalikprayitno@gmail.com</span></div>
                <div class="contact-item"><span>📞</span> <span>+62 852-8585-1973</span></div>
                <div class="contact-item"><span>📍</span> <span>Cipinang Muara, Jakarta, Indonesia</span></div>
            </div>
            <button class="submit-btn" onclick="closeAboutModal()">Tutup</button>
        </div>
    </div>

    <!-- MODAL DETAIL BUKU & QTY -->
    <div id="book-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeBookModal()">✕</button>
            <div class="book-modal-body">
                <img id="modal-book-img" src="" alt="Detail Buku">
                <h3 id="modal-book-title">Judul Buku</h3>
                <p id="modal-book-price" class="book-modal-price">Rp 0</p>
                <p id="modal-book-desc" class="book-modal-desc">Deskripsi buku...</p>
                
                <div class="qty-control">
                    <span>Jumlah:</span>
                    <button class="qty-btn" onclick="changeQty(-1)">-</button>
                    <span id="book-qty" style="font-size: 18px; font-weight: bold;">1</span>
                    <button class="qty-btn" onclick="changeQty(1)">+</button>
                </div>

                <button class="submit-btn" onclick="addToCart()">Masukkan ke Keranjang</button>
            </div>
        </div>
    </div>

    <!-- MODAL KERANJANG & RIWAYAT -->
    <div id="cart-history-modal" class="modal-overlay">
        <div class="modal-content" style="width: 500px; text-align: left;">
            <button class="close-btn" onclick="closeCartHistoryModal()">✕</button>
            <h3 style="margin-bottom: 15px; text-align: center;">Keranjang Belanja & Riwayat</h3>
            
            <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 8px; margin-bottom: 15px;" id="cart-items-container">
                <!-- Item keranjang dirender via JS -->
            </div>

            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-bottom: 15px;">
                <span>Total Keseluruhan:</span>
                <span id="cart-total-price" style="color: #d9534f;">Rp 0</span>
            </div>

            <button class="submit-btn" onclick="proceedToCheckoutFromCart()" style="background: #28a745; margin-top: 0;">Lanjut ke Pembayaran</button>
        </div>
    </div>

    <!-- MODAL LOGIN MANUAL -->
    <div id="login-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeLoginModal()">✕</button>
            <h2 style="margin-bottom: 20px;">Login Akun</h2>
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" class="input-field" required>
                <input type="password" name="password" placeholder="Password" class="input-field" required>
                <button type="submit" class="submit-btn">Login</button>
            </form>
            <p style="font-size: 13px; margin-top: 15px; color: #666;">Belum punya akun? <span style="color: #2b2b2b; cursor: pointer; font-weight: bold;" onclick="closeLoginModal(); openRegisterModal();">Daftar di sini</span></p>
        </div>
    </div>

    <!-- MODAL REGISTER MANUAL -->
    <div id="register-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeRegisterModal()">✕</button>
            <h2 style="margin-bottom: 15px;">Daftar Akun Baru</h2>
            <form action="register.php" method="POST">
                <input type="text" name="nama" placeholder="Nama Lengkap" class="input-field" required>
                <input type="email" name="email" placeholder="Email Aktif" class="input-field" required>
                <input type="password" name="password" placeholder="Password" class="input-field" required>
                <input type="text" name="no_telp" placeholder="Nomor Telepon / WhatsApp" class="input-field" required>
                <textarea name="alamat" placeholder="Alamat Pengiriman Lengkap" class="input-field" rows="3" required style="resize: none;"></textarea>
                <button type="submit" class="submit-btn" style="background: #28a745;">Register</button>
            </form>
        </div>
    </div>

    <!-- MODAL LOADING -->
    <div id="loading-modal" class="modal-overlay">
        <div class="modal-content" style="text-align: center;">
            <h2>Mohon Tunggu Sebentar...</h2>
            <div class="spinner"></div>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">Pesanan kamu sedang dibuat, kamu akan diarahkan ke halaman pembayaran.</p>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        let cartCount = 0;
        let selectedBookTitle = '';
        let selectedBookPrice = 0;
        let currentQty = 1;

        // Array keranjang belanja multi-item
        let cartItems = [];
        try { cartItems = JSON.parse(localStorage.getItem('lix_cart') || '[]') || []; } catch (e) { cartItems = []; }

        function openAboutModal() { document.getElementById('about-modal').style.display = 'flex'; }
        function closeAboutModal() { document.getElementById('about-modal').style.display = 'none'; }
        
        function openLoginModal() { document.getElementById('login-modal').style.display = 'flex'; }
        function closeLoginModal() { document.getElementById('login-modal').style.display = 'none'; }

        function openRegisterModal() { document.getElementById('register-modal').style.display = 'flex'; }
        function closeRegisterModal() { document.getElementById('register-modal').style.display = 'none'; }

        function showBookDetail(title, imgSrc, desc, priceText, priceValue) {
            selectedBookTitle = title;
            selectedBookPrice = priceValue;
            currentQty = 1;
            document.getElementById('book-qty').innerText = currentQty;

            document.getElementById('modal-book-title').innerText = title;
            document.getElementById('modal-book-img').src = imgSrc;
            document.getElementById('modal-book-desc').innerText = desc;
            document.getElementById('modal-book-price').innerText = priceText;
            document.getElementById('book-modal').style.display = 'flex';
        }

        function closeBookModal() { document.getElementById('book-modal').style.display = 'none'; }

        function changeQty(amount) {
            currentQty += amount;
            if (currentQty < 1) currentQty = 1;
            document.getElementById('book-qty').innerText = currentQty;
        }

        function addToCart() {
            const existingIndex = cartItems.findIndex(item => item.title === selectedBookTitle);
            if (existingIndex > -1) {
                cartItems[existingIndex].qty += currentQty;
            } else {
                cartItems.push({
                    title: selectedBookTitle,
                    price: selectedBookPrice,
                    qty: currentQty
                });
            }

            cartCount = cartItems.reduce((sum, item) => sum + item.qty, 0);
            simpanCart();
            document.getElementById('cart-count').innerText = cartCount;
            
            alert(currentQty + ' buku "' + selectedBookTitle + '" berhasil dimasukkan ke keranjang!');
            closeBookModal();
        }

        // FUNGSI UNTUK KONTROL JUMLAH & TAMBAH LANGSUNG DARI CARD BUKU
        function changeCardQty(bookId, amount) {
            const qtyElement = document.getElementById('card-qty-' + bookId);
            let qty = parseInt(qtyElement.innerText) + amount;
            if (qty < 1) qty = 1;
            qtyElement.innerText = qty;
        }

        function addToCartDirect(title, price, bookId) {
            const qtyElement = document.getElementById('card-qty-' + bookId);
            const qty = parseInt(qtyElement.innerText);

            const existingIndex = cartItems.findIndex(item => item.title === title);
            if (existingIndex > -1) {
                cartItems[existingIndex].qty += qty;
            } else {
                cartItems.push({
                    title: title,
                    price: price,
                    qty: qty
                });
            }

            cartCount = cartItems.reduce((sum, item) => sum + item.qty, 0);
            simpanCart();
            document.getElementById('cart-count').innerText = cartCount;

            alert(qty + ' buku "' + title + '" berhasil masuk ke keranjang!');
            qtyElement.innerText = 1; // Reset jumlah input pada card ke 1
        }

        function openCartHistoryModal() {
            const container = document.getElementById('cart-items-container');
            container.innerHTML = '';

            if (cartItems.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #777; padding: 20px;">Keranjang kamu masih kosong.</p>';
                document.getElementById('cart-total-price').innerText = 'Rp 0';
            } else {
                let totalSum = 0;
                cartItems.forEach((item, index) => {
                    let subtotal = item.price * item.qty;
                    totalSum += subtotal;
                    container.innerHTML += `
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #ddd; padding: 8px 0; font-size: 13px;">
                            <div>
                                <b>${item.title}</b><br>
                                <span style="color: #666;">${item.qty} pcs x Rp ${item.price.toLocaleString('id-ID')}</span>
                            </div>
                            <div style="text-align: right;">
                                <span style="color: #d9534f; font-weight: bold;">Rp ${subtotal.toLocaleString('id-ID')}</span><br>
                                <button onclick="removeFromCart(${index})" style="background: none; border: none; color: #d9534f; cursor: pointer; font-size: 11px;">Hapus</button>
                            </div>
                        </div>
                    `;
                });
                document.getElementById('cart-total-price').innerText = 'Rp ' + totalSum.toLocaleString('id-ID');
            }

            document.getElementById('cart-history-modal').style.display = 'flex';
        }

        function closeCartHistoryModal() {
            document.getElementById('cart-history-modal').style.display = 'none';
        }

        function removeFromCart(index) {
            cartItems.splice(index, 1);
            cartCount = cartItems.reduce((sum, item) => sum + item.qty, 0);
            simpanCart();
            document.getElementById('cart-count').innerText = cartCount;
            openCartHistoryModal();
        }

        function simpanCart() {
            try { localStorage.setItem('lix_cart', JSON.stringify(cartItems)); } catch (e) {}
        }

        function proceedToCheckoutFromCart() {
            if (cartItems.length === 0) {
                alert("Keranjang kamu masih kosong!");
                return;
            }
            simpanCart();
            window.location.href = 'checkout.php';
        }

        function switchPage(page) {
            const banner = document.getElementById('home-banner');
            const genreSection = document.getElementById('genre-section');
            const booksGrid = document.getElementById('books-grid');
            const navHome = document.getElementById('nav-home');
            const navBooks = document.getElementById('nav-books');
            const books = document.querySelectorAll('.book-card');

            if (page === 'home') {
                banner.style.display = 'flex';
                genreSection.style.display = 'none';
                booksGrid.style.display = 'grid';
                navHome.classList.add('active');
                navBooks.classList.remove('active');
                books.forEach(book => book.style.display = 'flex');
            } else if (page === 'books') {
                banner.style.display = 'none';
                genreSection.style.display = 'block';
                navBooks.classList.add('active');
                navHome.classList.remove('active');

                const buttons = document.querySelectorAll('.genre-btn');
                buttons.forEach(btn => btn.classList.remove('active'));
                books.forEach(book => book.style.display = 'none');
            }
        }

        function filterGenre(category, btnElement) {
            const buttons = document.querySelectorAll('.genre-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            btnElement.classList.add('active');

            const books = document.querySelectorAll('.book-card');
            books.forEach(book => {
                if (book.getAttribute('data-category') === category) {
                    book.style.display = 'flex';
                } else {
                    book.style.display = 'none';
                }
            });
        }

        function searchBook() {
            const input = document.getElementById('search-input').value.toLowerCase();
            const books = document.querySelectorAll('.book-card');

            books.forEach(book => {
                const title = book.querySelector('.book-title').innerText.toLowerCase();
                if (title.includes(input)) {
                    book.style.display = 'flex';
                } else {
                    book.style.display = 'none';
                }
            });
        }

        // ===== PESANAN PENDING PER DEVICE =====
        // Pesanan yang dibuat di device ini disimpan di localStorage, jadi tiap device
        // hanya melihat pesanannya sendiri dan bisa melanjutkan ke menu pembayaran.
        let pendingSekarang = null;

        async function cekPesananPending() {
            let data = null;
            try { data = JSON.parse(localStorage.getItem('lix_pending_order') || 'null'); } catch (e) {}
            if (!data || !data.id || !data.token) return;

            try {
                const r = await fetch('check_status.php?order_id=' + data.id + '&token=' + encodeURIComponent(data.token), { cache: 'no-store' });
                const d = await r.json();
                if (!d.success) { localStorage.removeItem('lix_pending_order'); return; }

                pendingSekarang = data;
                const link = 'pembayaran.php?id=' + data.id + '&t=' + encodeURIComponent(data.token);
                const bar = document.getElementById('pending-bar');
                const teks = document.getElementById('pending-bar-text');
                const tombol = document.getElementById('pending-bar-link');
                tombol.href = link;

                if (d.status.toLowerCase() === 'pending') {
                    teks.innerHTML = '⏳ Pesanan <b>#' + data.id + '</b> masih <b>pending</b> (menunggu pembayaran / persetujuan admin).';
                    tombol.innerText = 'Lanjutkan Pembayaran';
                } else if (d.status.toLowerCase() === 'rejected' || d.status.toLowerCase() === 'dibatalkan') {
                    const ditolak = d.status.toLowerCase() === 'rejected';
                    teks.innerHTML = '❌ Pesanan <b>#' + data.id + '</b> ' + (ditolak ? 'ditolak admin.' : 'dibatalkan karena melewati batas waktu pembayaran.');
                    tombol.innerText = 'Lihat Detail';
                    bar.style.background = '#f8d7da';
                    bar.style.color = '#721c24';
                } else {
                    teks.innerHTML = '✅ Pesanan <b>#' + data.id + '</b> sudah disetujui admin.';
                    tombol.innerText = 'Lihat Struk';
                    bar.style.background = '#d4edda';
                    bar.style.color = '#155724';
                }
                bar.style.display = 'flex';
            } catch (e) {}
        }

        function bersihkanPendingJikaSelesai() {
            // Pesanan yang sudah disetujui dihapus dari device setelah struk dibuka
            if (document.getElementById('pending-bar-link').innerText !== 'Lanjutkan Pembayaran') {
                try { localStorage.removeItem('lix_pending_order'); } catch (e) {}
            }
        }

        // Pulihkan jumlah keranjang dari device ini
        cartCount = cartItems.reduce((sum, item) => sum + item.qty, 0);
        document.getElementById('cart-count').innerText = cartCount;

        cekPesananPending();
        setInterval(cekPesananPending, 10000);
    </script>
</body>
</html>