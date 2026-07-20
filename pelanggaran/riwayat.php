<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

// Ambil filter siswa
$filter_siswa = isset($_GET['siswa_id']) ? $_GET['siswa_id'] : "";

// Ambil daftar siswa untuk dropdown
$siswa_q = $conn->query("SELECT id, nama FROM siswa ORDER BY nama ASC");

// Query riwayat
$sql = "SELECT pl.id, s.nama AS siswa, p.nama_pelanggaran, p.poin, pl.keterangan, pl.tanggal, u.nama AS pelapor
        FROM pelanggaran_log pl
        JOIN siswa s ON pl.siswa_id = s.id
        JOIN pelanggaran p ON pl.pelanggaran_id = p.id
        JOIN users u ON pl.user_id = u.id";
if ($filter_siswa) {
    $sql .= " WHERE s.id = '$filter_siswa'";
}
$sql .= " ORDER BY pl.tanggal DESC";
$result = $conn->query($sql);

// Query rekap poin per siswa
$rekap_sql = "SELECT s.nama, SUM(p.poin) AS total_poin
              FROM pelanggaran_log pl
              JOIN siswa s ON pl.siswa_id = s.id
              JOIN pelanggaran p ON pl.pelanggaran_id = p.id
              GROUP BY s.id ORDER BY total_poin DESC";
$rekap = $conn->query($rekap_sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Riwayat Pelanggaran</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h2>📖 Riwayat Pelanggaran</h2>

    <!-- Filter Siswa -->
    <form method="get" action="">
        <label>Pilih Siswa:</label>
        <select name="siswa_id" onchange="this.form.submit()">
            <option value="">-- Semua Siswa --</option>
            <?php while($s = $siswa_q->fetch_assoc()) { ?>
                <option value="<?= $s['id'] ?>" <?= ($filter_siswa==$s['id'])?'selected':'' ?>>
                    <?= $s['nama'] ?>
                </option>
            <?php } ?>
        </select>
        <a href="riwayat.php">Reset</a>
    </form>

    <?php if(isset($_GET['success'])) echo "<p style='color:green'>Data berhasil disimpan.</p>"; ?>

    <!-- Tabel Riwayat -->
    <div class="card">
        <h3>Riwayat Pelanggaran</h3>
        <table border="1" width="100%" cellpadding="8">
            <tr style="background:#eee">
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Pelanggaran</th>
                <th>Poin</th>
                <th>Keterangan</th>
                <th>Tanggal</th>
                <th>Pelapor</th>
            </tr>
            <?php 
            $no=1; 
            while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['siswa'] ?></td>
                <td><?= $row['nama_pelanggaran'] ?></td>
                <td><?= $row['poin'] ?></td>
                <td><?= $row['keterangan'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['pelapor'] ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Rekap Poin -->
    <div class="card">
        <h3>📊 Rekap Poin Per Siswa</h3>
        <table border="1" width="100%" cellpadding="8">
            <tr style="background:#eee">
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Total Poin</th>
            </tr>
            <?php $no=1; while($r = $rekap->fetch_assoc()) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $r['nama'] ?></td>
                <td><?= $r['total_poin'] ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Export ke PDF -->
    <p>
        <a href="export_pdf.php" target="_blank">📄 Export Rekap ke PDF</a>
    </p>
</div>
</body>
</html>
