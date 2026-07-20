<?php
session_start();

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard Pelanggaran</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h2>📋 Kredit Poin Pelanggaran</h2>

    <div class="card">
        <a href="tambah.php"><button>➕ Tambah Pelanggaran</button></a>
    </div>

    <div class="card">
        <a href="riwayat.php"><button>📖 Riwayat Pelanggaran</button></a>
    </div>
<div class="card">
        <a href="buat_database.php"><button>Buat Database</button></a>
    </div>
<div class="card">
        <a href="pelanggaran.php"><button>Daftar Pelanggaran dan Poin</button></a>
    </div>
</div>
</body>
</html>
