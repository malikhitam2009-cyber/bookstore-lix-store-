<?php
include 'koneksi.php';

// ==========================================
// 1. LOGIK PERSETUJUAN PESANAN (APPROVAL)
// ==========================================
if (isset($_GET['approve_id'])) {
    $approve_id = intval($_GET['approve_id']);
    $update_query = "UPDATE pesanan SET status = 'Approved' WHERE id = $approve_id";
    mysqli_query($conn, $update_query);
    header("Location: admin.php");
    exit;
}

// ==========================================
// 2. LOGIK KELOLA BUKU (TAMBAH / EDIT / HAPUS)
// ==========================================

// Variable penampung form edit
$edit_mode = false;
$edit_id = 0;
$edit_title = '';
$edit_category = '';
$edit_price = '';
$edit_image = '';
$edit_desc = '';

// a. Hapus Buku
if (isset($_GET['delete_book_id'])) {
    $del_id = intval($_GET['delete_book_id']);
    mysqli_query($conn, "DELETE FROM books WHERE id = $del_id");
    header("Location: admin.php");
    exit;
}

// b. Persiapan Edit Buku (Ambil data buku yang mau diedit)
if (isset($_GET['edit_book_id'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit_book_id']);
    $res_edit = mysqli_query($conn, "SELECT * FROM books WHERE id = $edit_id");
    if ($res_edit && mysqli_num_rows($res_edit) > 0) {
        $data_edit = mysqli_fetch_assoc($res_edit);
        $edit_title = $data_edit['title'];
        $edit_category = $data_edit['category'];
        $edit_price = $data_edit['price'];
        $edit_image = $data_edit['image'];
        $edit_desc = $data_edit['description'];
    }
}

// c. Simpan / Update Buku (Submit Form)
if (isset($_POST['save_book'])) {
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $price       = intval($_POST['price']);
    $image       = mysqli_real_escape_string($conn, $_POST['image']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $book_id     = intval($_POST['book_id'] ?? 0);

    if ($book_id > 0) {
        // Update Buku
        $query_save = "UPDATE books SET 
                        title = '$title', 
                        category = '$category', 
                        price = '$price', 
                        image = '$image', 
                        description = '$description' 
                       WHERE id = $book_id";
    } else {
        // Tambah Buku Baru
        $query_save = "INSERT INTO books (title, category, price, image, description) 
                       VALUES ('$title', '$category', '$price', '$image', '$description')";
    }

    mysqli_query($conn, $query_save);
    header("Location: admin.php");
    exit;
}

// ==========================================
// QUERY DATA DARI DATABASE
// ==========================================
// Ambil daftar pesanan
$query_pesanan = "SELECT * FROM pesanan ORDER BY id DESC";
$result_pesanan = mysqli_query($conn, $query_pesanan);

// Ambil daftar buku
$query_buku = "SELECT * FROM books ORDER BY id DESC";
$result_buku = mysqli_query($conn, $query_buku);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - LIX STORE</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Quicksand', sans-serif;
        }

        body {
            background-color: #f4f6f9;
            padding: 30px;
            color: #333;
        }

        h1 {
            margin-bottom: 25px;
            font-size: 28px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card h2 {
            font-size: 18px;
            margin-bottom: 20px;
            border-bottom: 2px solid #f4f6f9;
            padding-bottom: 10px;
        }

        /* STYLES TABEL */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background-color: #343a40;
            color: white;
            padding: 12px 15px;
            font-size: 14px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            vertical-align: middle;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }

        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-approved {
            background-color: #d4edda;
            color: #155724;
        }

        /* BUTTONS */
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }

        .btn-approve { background-color: #28a745; color: white; }
        .btn-approve:hover { background-color: #218838; }

        .btn-edit { background-color: #ffc107; color: #212529; margin-right: 5px; }
        .btn-edit:hover { background-color: #e0a800; }

        .btn-delete { background-color: #dc3545; color: white; }
        .btn-delete:hover { background-color: #c82333; }

        .btn-submit { background-color: #007bff; color: white; padding: 10px 20px; font-size: 15px; }
        .btn-submit:hover { background-color: #0069d9; }

        .btn-cancel { background-color: #6c757d; color: white; padding: 10px 20px; font-size: 15px; text-decoration: none; }

        /* FORM BUKU */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 13px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .book-img-thumb {
            width: 45px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .text-muted { color: #888; font-size: 13px; }
    </style>
</head>
<body>

    <h1>Dashboard Admin - LIX STORE</h1>

    <!-- SECTION 1: PERSETUJUAN PESANAN (APPROVAL) -->
    <div class="card">
        <h2>Persetujuan Pesanan Buku</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pembeli</th>
                    <th>Judul Buku</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi Approval</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result_pesanan && mysqli_num_rows($result_pesanan) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result_pesanan)) {
                        $status = $row['status'] ?? 'Pending';
                        $is_approved = (strtolower($status) === 'approved');
                ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><b><?= htmlspecialchars($row['nama_pembeli']); ?></b></td>
                            <td><?= htmlspecialchars($row['judul_buku']); ?></td>
                            <td>Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($is_approved): ?>
                                    <span class="badge badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$is_approved): ?>
                                    <a href="admin.php?approve_id=<?= $row['id']; ?>" class="btn btn-approve">Approve</a>
                                <?php else: ?>
                                    <span class="text-muted">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>Belum ada pesanan masuk.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- SECTION 2: FORM TAMBAH / EDIT BUKU -->
    <div class="card" id="form-buku">
        <h2><?= $edit_mode ? 'Edit Buku' : 'Tambah Buku Baru'; ?></h2>
        <form action="admin.php" method="POST">
            <input type="hidden" name="book_id" value="<?= $edit_id; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($edit_title); ?>">
                </div>
                <div class="form-group">
                    <label>Genre / Kategori</label>
                    <select name="category" class="form-control" required>
                        <option value="mystery" <?= $edit_category == 'mystery' ? 'selected' : ''; ?>>Mystery & Detective</option>
                        <option value="history" <?= $edit_category == 'history' ? 'selected' : ''; ?>>Sejarah & Filsafat</option>
                        <option value="fiction" <?= $edit_category == 'fiction' ? 'selected' : ''; ?>>Novel & Fiksi</option>
                        <option value="comic" <?= $edit_category == 'comic' ? 'selected' : ''; ?>>Sains & Komik</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="price" class="form-control" required value="<?= $edit_price; ?>">
                </div>
                <div class="form-group">
                    <label>File / Path Gambar (misal: Lupin.jpg)</label>
                    <input type="text" name="image" class="form-control" required value="<?= htmlspecialchars($edit_image); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi Buku</label>
                <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($edit_desc); ?></textarea>
            </div>

            <button type="submit" name="save_book" class="btn btn-submit"><?= $edit_mode ? 'Update Buku' : 'Simpan Buku Baru'; ?></button>
            <?php if ($edit_mode): ?>
                <a href="admin.php" class="btn btn-cancel">Batal Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- SECTION 3: DAFTAR KATALOG BUKU (EDIT & HAPUS) -->
    <div class="card">
        <h2>Katalog Buku Terdaftar</h2>
        <table>
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul Buku</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result_buku && mysqli_num_rows($result_buku) > 0) {
                    while ($buku = mysqli_fetch_assoc($result_buku)) {
                ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($buku['image']); ?>" class="book-img-thumb" alt="Cover" onerror="this.src='https://via.placeholder.com/45x60';">
                            </td>
                            <td><b><?= htmlspecialchars($buku['title']); ?></b></td>
                            <td><span class="badge" style="background:#e9ecef; color:#495057;"><?= ucfirst($buku['category']); ?></span></td>
                            <td>Rp <?= number_format($buku['price'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="admin.php?edit_book_id=<?= $buku['id']; ?>#form-buku" class="btn btn-edit">Edit</a>
                                <a href="admin.php?delete_book_id=<?= $buku['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus buku ini?');">Hapus</a>
                            </td>
                        </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data buku.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>