<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (strpos($email, 'admin') !== false) {
        header("Location: admin.php");
        exit();
    } else {
        header("Location: Bookstore.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIX STORE - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #D2C1B0;
            color: #333;
            position: relative;
        }

        header {
            max-width: 1320px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 400px;
            margin: 0 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid #C4B09C;
            background-color: #E8DEC9;
            outline: none;
            font-size: 14px;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            color: #d9534f;
        }

        .cart-count {
            background-color: #d9534f;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            position: absolute;
            top: -8px;
            right: -10px;
        }

        .container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .banner {
            background-color: #2B2A2A;
            color: white;
            border-radius: 15px;
            padding: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .banner-text h1 {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
        }

        .banner-books {
            display: flex;
            gap: 12px;
        }

        .banner-books img {
            width: 90px;
            height: 130px;
            object-fit: cover;
            border-radius: 6px;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .book-card {
            text-align: center;
        }

        .book-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .book-card h4 {
            font-size: 13px;
            font-weight: 600;
            color: #222;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .login-modal {
            background-color: #ffffff;
            width: 100%;
            max-width: 380px;
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
            text-align: center;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 22px;
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: #000;
        }

        .login-modal h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #222;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid #E5ECF4;
            background-color: #EBF3FA;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-group input:focus {
            border-color: #2B2A2A;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #2B2A2A;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        .submit-btn:hover {
            background-color: #1a1a1a;
        }
    </style>
</head>
<body>

    <div class="modal-overlay">
        <div class="login-modal">
            <a href="Bookstore.php" class="close-btn">&times;</a>
            <h2>Login</h2>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
    </div>

    <header>
        <div class="logo"><img src="logo.jpg" alt="LIX STORE Logo" onerror="this.src='https://via.placeholder.com/45'"></div>
        <div class="search-bar"><input type="text" placeholder="Cari buku..."></div>
        <div class="user-nav">
            <div class="cart-icon"><i class="fa-solid fa-cart-shopping"></i><span class="cart-count">0</span></div>
            <a href="#" class="login-btn">LOG IN</a>
        </div>
    </header>

    <div class="container">
        <div class="banner">
            <div class="banner-text"><h1>GET<br>50%<br>DISCOUNT</h1></div>
            <div class="banner-books">
                <img src="SHERLOCK HOLMES.jpg" onerror="this.src='https://via.placeholder.com/90x130'">
                <img src="Lupin.jpg" onerror="this.src='https://via.placeholder.com/90x130'">
                <img src="Madilog.jpg" onerror="this.src='https://via.placeholder.com/90x130'">
                <img src="Detektif.jpg" onerror="this.src='https://via.placeholder.com/90x130'">
                <img src="30 Bohong besar.jpg" onerror="this.src='https://via.placeholder.com/90x130'">
            </div>
        </div>

        <div class="book-grid">
            <div class="book-card"><img src="Jack.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Jack the ripper</h4></div>
            <div class="book-card"><img src="Gamera.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Gamera</h4></div>
            <div class="book-card"><img src="Wingchun.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Traditional Wing Chun</h4></div>
            <div class="book-card"><img src="The love next door.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>The Lover Next Door</h4></div>
            <div class="book-card"><img src="Megalodon.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Megalodon</h4></div>
            <div class="book-card"><img src="Moby Dick.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Moby Dick</h4></div>
            <div class="book-card"><img src="Pulang.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Pulang - Tere Liye</h4></div>
            <div class="book-card"><img src="Sejarah dunia singkat.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Sejarah Singkat Dunia</h4></div>
            <div class="book-card"><img src="30 Bohong besar.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>30 Bohong Besar Soeharto</h4></div>
            <div class="book-card"><img src="Madilog.jpg" onerror="this.src='https://via.placeholder.com/140x200'"><h4>Madilog</h4></div>
        </div>
    </div>

</body>
</html>