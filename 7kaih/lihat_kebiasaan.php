<?php

session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . "/../config.php"; 


// Default tanggal hari ini
$tanggal = $_GET['tanggal'] ?? date("Y-m-d");

// Ambil data jurnal semua siswa pada tanggal tersebut
$sql = "SELECT j.*, s.nama, s.kelas 
        FROM jurnal_kebiasaan j
        JOIN siswa s ON j.siswa_id = s.id
        WHERE DATE(j.tanggal_input) = ?
        ORDER BY s.kelas ASC, s.nama ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("❌ Query error: " . $conn->error);
}
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Harian Jurnal Kebiasaan</title>
<style>
    body { font-family: Arial, sans-serif; background:#f8f9fa; margin:20px;}
    .container { max-width: 100%; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.1);}
    h2 { text-align:center; margin-bottom:20px; }
    form { text-align:center; margin-bottom:15px; }
    input, button { padding:6px; margin:5px; }
    button { background:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer; }
    table { width:100%; border-collapse: collapse; font-size:13px; margin-top:15px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:center; }
    th { background:#007bff; color:#fff; }
    tr:nth-child(even){ background:#f9f9f9; }
    img { max-width:50px; border-radius:4px; }
    @media(max-width:768px){
        table { font-size:12px; }
        th, td { padding:6px; }
    }
</style>
</head>
<body>
<div class="container">
    <h2>📅 Laporan Jurnal Harian (<?= date("d-m-Y", strtotime($tanggal)) ?>)</h2>
    
    <form method="get">
        <label>Pilih Tanggal: 
            <input type="date" name="tanggal" value="<?= $tanggal ?>">
        </label>
        <button type="submit">Tampilkan</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Bangun Pagi</th>
                <th>Ibadah</th>
                <th>Olahraga</th>
                <th>Makanan</th>
                <th>Belajar</th>
                <th>Bermasyarakat</th>
                <th>Tidur</th>
                <th>Keterangan</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= htmlspecialchars($row['jam_bangun']) ?></td>
                        <td><?= htmlspecialchars($row['beribadah']) ?></td>
                        <td><?= htmlspecialchars($row['jam_olahraga']) ?></td>
                        <td><?= htmlspecialchars($row['makanan_sehat']) ?></td>
                        <td><?= htmlspecialchars($row['jam_belajar']) ?></td>
                        <td><?= htmlspecialchars($row['bermasyarakat']) ?></td>
                        <td><?= htmlspecialchars($row['jam_tidur']) ?></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td>
                            <?php if($row['foto']): ?>
                                <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="foto">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="11">❌ Belum ada data pada tanggal ini</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="text-align:center; margin-top:15px;">
        <a href="../dashboard.php">⬅ Kembali ke Dashboard</a>
    </p>
</div>
</body>
</html>
