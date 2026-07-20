<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
// Konfirmasi penghapusan
if (!isset($_GET['confirm']) || $_GET['confirm'] != 'yes') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Konfirmasi Hapus Data</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f8f9fa;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .container {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                max-width: 420px;
                width: 90%;
                text-align: center;
            }
            h2 {
                color: #dc3545;
                margin-bottom: 10px;
            }
            p {
                color: #333;
                font-size: 16px;
                margin: 8px 0;
            }
            .btn {
                display: inline-block;
                padding: 10px 18px;
                margin: 10px 5px 0;
                font-size: 15px;
                font-weight: bold;
                border-radius: 5px;
                text-decoration: none;
                color: white;
                transition: 0.3s;
            }
            .btn-danger {
                background: #dc3545;
            }
            .btn-danger:hover {
                background: #c82333;
            }
            .btn-secondary {
                background: #6c757d;
            }
            .btn-secondary:hover {
                background: #5a6268;
            }
            @media (max-width: 500px) {
                .container {
                    padding: 15px;
                }
                h2 {
                    font-size: 20px;
                }
                p, .btn {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>⚠ PERINGATAN!</h2>
            <p>Ini akan menghapus semua data di tabel <b>absensi</b>, <b>hari_libur</b>, dan <b>siswa</b>.</p>
            <p><b>Tindakan ini tidak dapat dibatalkan!</b></p>
            <p style="color:#d63384;">👉 Silahkan <a href="backup_restore.php">backup data dulu</a> sebelum melanjutkan.</p>
            
            <a href="?confirm=ya" class="btn btn-danger">Ya, Hapus Semua Data</a>
            <a href="dashboard.php" class="btn btn-secondary">Batal</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Ambil koneksi database dari config.php
require_once 'config.php'; // Pastikan config.php punya $conn (mysqli)

// Nonaktifkan cek foreign key
$conn->query("SET FOREIGN_KEY_CHECKS=0");

// Kosongkan tabel
$conn->query("TRUNCATE TABLE absensi");
$conn->query("TRUNCATE TABLE hari_libur");
$conn->query("TRUNCATE TABLE siswa");

// Aktifkan kembali cek foreign key
$conn->query("SET FOREIGN_KEY_CHECKS=1");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Terhapus</title>
    <!-- Arahkan otomatis setelah 4 detik -->
    <meta http-equiv="refresh" content="4;url=dashboard.php">
</head>
<body style="font-family:Arial;text-align:center;margin-top:50px;">
    <h2 style="color:green;">✅ Semua data berhasil dihapus.</h2>
    <p>Anda akan diarahkan ke <b>Dashboard</b> dalam 4 detik...</p>
    <p><a href="dashboard.php">Klik di sini</a> jika tidak otomatis.</p>
</body>
</html>
