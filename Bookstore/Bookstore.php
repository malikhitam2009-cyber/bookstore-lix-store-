<?php
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
    <!-- Google Sign-In SDK -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
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

    /* HEADER - UKURAN WIDE & PROPORSIONAL */
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

    .cart-badge {
        background: #d9534f;
        color: white;
        border-radius: 50%;
        padding: 2px 8px;
        font-size: 12px;
    }

    .login-btn {
        cursor: pointer;
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 0.5px;
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

    /* BANNER PROMO - EXPANDED */
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
        grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
        gap: 25px 20px;
    }

    .book-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .book-card:hover {
        transform: translateY(-4px);
    }

    .book-card img {
        width: 100%;
        height: 195px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    }

    .book-title {
        margin-top: 10px;
        font-size: 13px;
        color: #2b2b2b;
        line-height: 1.2;
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

    /* STYLING KHUSUS ABOUT & CONTACT MODAL */
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

    /* DETAIL BUKU POPUP STYLES */
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
        -webkit-user-select: none;
        -moz-user-select: none;
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

    /* SPINNER ANIMATION */
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

    /* STRUK STYLE */
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
    @media print {
    body * {
        visibility: hidden;
    }
    #printable-receipt, #printable-receipt * {
        visibility: visible;
    }
    #printable-receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 20px;
        box-shadow: none !important;
        border: none !important;
    }
    .no-print {
        display: none !important;
    }
}
    </style>
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="top-header">
            <!-- LOGO (DIKLIK UNTUK BUKA ABOUT & CONTACT) -->
            <div class="logo" onclick="openAboutModal()" title="About Us & Contact">
                <img src="logo.jpg" alt="Logo">
            </div>

            <!-- SEARCH BAR -->
            <div class="search-box">
                <input type="text" placeholder="Cari buku...">
            </div>

            <!-- KERANJANG & LOG IN -->
            <div class="nav-user">
                <button class="cart-btn" onclick="openCartCheckout()" title="Keranjang">
                    🛒 <span id="cart-count" class="cart-badge">0</span>
                </button>
                <div class="login-btn" onclick="openLoginModal()">LOG IN</div>
            </div>
        </div>

        <!-- NAVIGASI TAB -->
        <nav>
            <a id="nav-home" class="active" onclick="switchPage('home')">Home</a>
            <a id="nav-books" onclick="switchPage('books')">Books</a>
        </nav>
    </header>

    <!-- BANNER PROMO (MUNCUL DI HOME) -->
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
        
        <!-- PILIHAN GENRE -->
        <div id="genre-section" style="display: none;">
            <h2 style="margin-bottom: 15px;">Pilih Genre Buku</h2>
            <div class="genre-list">
                <button class="genre-btn" onclick="filterGenre('mystery', this)">Mystery & Detective</button>
                <button class="genre-btn" onclick="filterGenre('history', this)">Sejarah & Filsafat</button>
                <button class="genre-btn" onclick="filterGenre('fiction', this)">Novel & Fiksi</button>
                <button class="genre-btn" onclick="filterGenre('comic', this)">Sains & Komik</button>
            </div>
        </div>

        <!-- DAFTAR BUKU (DINAMIS DATABASE) -->
        <div id="books-grid" class="books-grid">
            <?php 
            if ($result_buku && mysqli_num_rows($result_buku) > 0) {
                while ($row = mysqli_fetch_assoc($result_buku)) {
                    $category = strtolower($row['category']);
                    $harga_formatted = "Rp " . number_format($row['price'], 0, ',', '.');
                    
                    $title_js = htmlspecialchars($row['title'], ENT_QUOTES);
                    $image_js = htmlspecialchars($row['image'], ENT_QUOTES);
                    $desc_js = htmlspecialchars($row['description'], ENT_QUOTES);
                    $price_value = (int)$row['price'];
            ?>
                    <div class="book-card" data-category="<?= $category; ?>" onclick="showBookDetail('<?= $title_js; ?>', '<?= $image_js; ?>', '<?= $desc_js; ?>', '<?= $harga_formatted; ?>', <?= $price_value; ?>)">
                        <img src="<?= $image_js; ?>" alt="<?= $title_js; ?>">
                        <div class="book-title"><?= htmlspecialchars($row['title']); ?></div>
                    </div>
            <?php 
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center;'>Belum ada data buku.</p>";
            }
            ?>
        </div>
    </div>

    <!-- MODAL ABOUT US & CONTACT US -->
    <div id="about-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeAboutModal()">✕</button>
            <h2 style="margin-bottom: 10px;">About LIX STORE</h2>
            
            <div class="about-info">
                <p>Selamat datang di <b>LIX STORE</b>! Kami menyediakan berbagai koleksi buku digital mulai dari fiksi, detektif, hingga sejarah & filsafat berkualitas dengan harga terjangkau.</p>
                
                <h3 style="margin-top: 15px; font-size: 16px;">Contact Us:</h3>
                
                <div class="contact-item">
                    <span></span>
                    <span>athallahmalikprayitno@gmail.com</span>
                </div>
                <div class="contact-item">
                    <span></span>
                    <span>+62 852-8585-1973</span>
                </div>
                <div class="contact-item">
                    <span></span>
                    <span>Cipinang muara, Jakarta, Indonesia</span>
                </div>
            </div>

            <button class="submit-btn" onclick="closeAboutModal()">Tutup</button>
        </div>
    </div>

    <!-- MODAL DETAIL BUKU -->
    <div id="book-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeBookModal()">✕</button>
            <div class="book-modal-body">
                <img id="modal-book-img" src="" alt="Detail Buku">
                <h3 id="modal-book-title">Judul Buku</h3>
                <p id="modal-book-price" class="book-modal-price">Rp 0</p>
                <p id="modal-book-desc" class="book-modal-desc">Deskripsi buku...</p>
                <button class="submit-btn" onclick="addToCart()">Masukkan ke Keranjang</button>
            </div>
        </div>
    </div>

    <!-- MODAL CHECKOUT & METODE PEMBAYARAN -->
    <!-- MODAL CHECKOUT & METODE PEMBAYARAN -->
<div id="checkout-modal" class="modal-overlay">
    <div class="modal-content" style="text-align: left;">
        <button class="close-btn" onclick="closeCheckoutModal()">✕</button>
        <h3 style="margin-bottom: 15px; text-align: center;">Checkout & Pembayaran</h3>
        
        <!-- Hilangkan action="", kita serahkan penuh ke submit JavaScript -->
        <form id="form-checkout" onsubmit="handleCheckout(event)">
            <input type="hidden" name="book_title" id="pay-title">
            <input type="hidden" name="total_price" id="pay-price">

            <p style="margin-bottom: 8px;">Buku: <b id="display-title">-</b></p>
            <p style="margin-bottom: 15px;">Total Price: <b id="display-price" style="color:#d9534f;">-</b></p>

            <label style="font-size: 12px; font-weight: bold;">Nama Lengkap Pembeli:</label>
            <input type="text" name="customer_name" placeholder="Masukkan nama kamu" required class="input-field">

            <label style="font-size: 12px; font-weight: bold; margin-top: 10px; display: block;">Pilih Metode Pembayaran:</label>
            <select name="payment_method" required class="input-field" style="background: white;">
                <option value="">-- Pilih Pembayaran --</option>
                <option value="BCA Transfer">Bank BCA (Transfer)</option>
                <option value="Mandiri Transfer">Bank Mandiri (Transfer)</option>
                <option value="Gopay / QRIS">Gopay / QRIS</option>
                <option value="DANA">DANA</option>
                <option value="COD">Bayar di Tempat (COD)</option>
            </select>

            <!-- PENTING: Jangan pakai name="checkout" agar tidak memicu submit bawaan browser -->
            <button type="submit" class="submit-btn" style="background: #28a745;">Bayar Sekarang</button>
        </form>
    </div>
</div>

    <!-- MODAL POPUP LOGIN -->
    <div id="login-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeLoginModal()">✕</button>
            <h2 style="margin-bottom: 20px;">Login</h2>
            
            <div id="g_id_onload"
                data-client_id="309071684213-ccjaq623482up39kdhu4h15dsr8l8f6i.apps.googleusercontent.com"
                data-callback="handleCredentialResponse">
            </div>
            <div class="g_id_signin" 
                data-type="standard" 
                data-size="large" 
                data-theme="outline" 
                data-text="sign_in_with" 
                data-shape="rectangular" 
                data-logo_alignment="left"
                style="display: flex; justify-content: center; margin-bottom: 15px;">
            </div>

            <p style="font-size: 12px; color: #666; margin: 10px 0;">Atau login manual:</p>
            
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" class="input-field" required>
                <input type="password" name="password" placeholder="Password" class="input-field" required>
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
    </div>

    <!-- MODAL LOADING WAITING ADMIN -->
    <div id="loading-modal" class="modal-overlay">
        <div class="modal-content" style="text-align: center;">
            <h2>Mohon Tunggu Sebentar...</h2>
            <div class="spinner"></div>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">Pesanan kamu sedang menunggu persetujuan (Approval) Admin.</p>
        </div>
    </div>

    <!-- MODAL STRUK BELANJA -->
    <div id="receipt-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-btn" onclick="closeReceiptModal()">✕</button>
            <h2 style="color: #28a745;"> Pembayaran Disetujui!</h2>
            <p style="font-size: 13px; color: #555;">Terima kasih telah berbelanja di LIX STORE</p>

            <div class="receipt-box">
                <div class="receipt-row"><span>No. Transaksi:</span> <b id="st-id">#000</b></div>
                <div class="receipt-row"><span>Nama Pembeli:</span> <b id="st-nama">-</b></div>
                <div class="receipt-row"><span>Buku:</span> <b id="st-buku">-</b></div>
                <div class="receipt-divider"></div>
                <div class="receipt-row"><span>Harga Buku:</span> <span id="st-harga">Rp 0</span></div>
                <div class="receipt-row"><span>PPN (11%):</span> <span id="st-ppn">Rp 0</span></div>
                <div class="receipt-divider"></div>
                <div class="receipt-row" style="font-size: 16px; font-weight: bold; color: #2b2b2b;">
                    <span>Total Bayar:</span> <span id="st-total">Rp 0</span>
                </div>
            </div>

            <button class="submit-btn" onclick="window.print()" style="background: #2b2b2b; margin-top: 15px;"> Cetak Struk</button>
            <button class="submit-btn" onclick="closeReceiptModal()" style="background: #666; margin-top: 8px;">Selesai</button>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
   <script>
        // 1. VARIABEL GLOBAL
        let cartCount = 0;
        let selectedBookTitle = '';
        let selectedBookPrice = 0;
        let checkApprovalInterval = null;

        // 2. FUNGSI PENGECEKAN STATUS APPROVAL (DITARUH DI ATAS AGAR ALWAYS DEFINED)
        async function checkAdminApproval(orderId, customerName) {
            if (!orderId) return;

            try {
                const response = await fetch('check_status.php?order_id=' + orderId);
                const data = await response.json();

                console.log("Status Pesanan saat ini:", data.status);

                const currentStatus = data.status ? data.status.toString().toLowerCase().trim() : '';

                if (currentStatus === 'approved' || currentStatus === 'success' || currentStatus === 'selesai') {
                    
                    // Stop polling timer
                    if (checkApprovalInterval) {
                        clearInterval(checkApprovalInterval);
                        checkApprovalInterval = null;
                    }

                    // Sembunyikan Loading Modal
                    const loadingModal = document.getElementById('loading-modal');
                    if (loadingModal) loadingModal.style.display = 'none';

                    // Set Data Struk (Receipt)
                    if (document.getElementById('st-id')) document.getElementById('st-id').innerText = '#' + orderId;
                    if (document.getElementById('st-nama')) document.getElementById('st-nama').innerText = customerName;
                    if (document.getElementById('st-buku')) document.getElementById('st-buku').innerText = selectedBookTitle;

                    const harga = Number(selectedBookPrice) || 0;
                    const ppn = harga * 0.11;
                    const total = harga + ppn;

                    if (document.getElementById('st-harga')) document.getElementById('st-harga').innerText = 'Rp ' + harga.toLocaleString('id-ID');
                    if (document.getElementById('st-ppn')) document.getElementById('st-ppn').innerText = 'Rp ' + ppn.toLocaleString('id-ID');
                    if (document.getElementById('st-total')) document.getElementById('st-total').innerText = 'Rp ' + total.toLocaleString('id-ID');

                    // Reset Keranjang Belanja
                    cartCount = 0;
                    const cartCountEl = document.getElementById('cart-count');
                    if (cartCountEl) cartCountEl.innerText = 0;

                    // Tampilkan Struk
                    const receiptModal = document.getElementById('receipt-modal');
                    if (receiptModal) receiptModal.style.display = 'flex';
                }
            } catch (error) {
                console.error("Gagal melakukan pengecekan status:", error);
            }
        }

        // 3. FUNGSI CHECKOUT
        async function handleCheckout(event) {
            event.preventDefault();

            const form = document.getElementById('form-checkout');
            const formData = new FormData(form);

            closeCheckoutModal();
            document.getElementById('loading-modal').style.display = 'flex';

            try {
                const response = await fetch('proses_checkout.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    const orderId = data.order_id;
                    const customerName = formData.get('customer_name');

                    if (checkApprovalInterval) clearInterval(checkApprovalInterval);

                    // Panggil polling setiap 2 detik
                    checkApprovalInterval = setInterval(() => {
                        checkAdminApproval(orderId, customerName);
                    }, 2000);

                } else {
                    document.getElementById('loading-modal').style.display = 'none';
                    alert('Gagal memproses pesanan: ' + (data.message || 'Error Server'));
                }
            } catch (error) {
                document.getElementById('loading-modal').style.display = 'none';
                console.error('Error Checkout:', error);
                alert('Terjadi kesalahan koneksi ke server.');
            }
        }

        // 4. HANDLERS LAINNYA
        function openAboutModal() {
            document.getElementById('about-modal').style.display = 'flex';
        }

        function closeAboutModal() {
            document.getElementById('about-modal').style.display = 'none';
        }

        function handleCredentialResponse(response) {
            alert("Login Berhasil!");
            closeLoginModal();
        }

        function openLoginModal() {
            document.getElementById('login-modal').style.display = 'flex';
        }

        function closeLoginModal() {
            document.getElementById('login-modal').style.display = 'none';
        }

        function showBookDetail(title, imgSrc, desc, priceText, priceValue) {
            selectedBookTitle = title;
            selectedBookPrice = priceValue;

            document.getElementById('modal-book-title').innerText = title;
            document.getElementById('modal-book-img').src = imgSrc;
            document.getElementById('modal-book-desc').innerText = desc;
            document.getElementById('modal-book-price').innerText = priceText;
            document.getElementById('book-modal').style.display = 'flex';
        }

        function closeBookModal() {
            document.getElementById('book-modal').style.display = 'none';
        }

        function addToCart() {
            cartCount++;
            document.getElementById('cart-count').innerText = cartCount;
            alert('Buku "' + selectedBookTitle + '" berhasil dimasukkan ke keranjang!');
            closeBookModal();
        }

        function openCartCheckout() {
            if (cartCount === 0) {
                alert("Keranjang kamu masih kosong! Silakan pilih buku terlebih dahulu.");
                return;
            }

            document.getElementById('pay-title').value = selectedBookTitle;
            document.getElementById('pay-price').value = selectedBookPrice;
            document.getElementById('display-title').innerText = selectedBookTitle;
            document.getElementById('display-price').innerText = 'Rp ' + Number(selectedBookPrice).toLocaleString('id-ID');
            
            document.getElementById('checkout-modal').style.display = 'flex';
        }

        function closeCheckoutModal() {
            document.getElementById('checkout-modal').style.display = 'none';
        }

        function closeReceiptModal() {
            document.getElementById('receipt-modal').style.display = 'none';
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
    </script>
</body>
</html>