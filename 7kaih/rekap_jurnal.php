<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . "/../config.php"; 

// Cek role siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['siswa_id'])) {
    die("⚠️ Data siswa tidak ditemukan, silakan login ulang.");
}

$siswa_id = intval($_SESSION['siswa_id']);

// Pilih bulan & tahun
$bulan = $_GET['bulan'] ?? date("m");
$tahun = $_GET['tahun'] ?? date("Y");

// Ambil data jurnal bulan ini
$sql = "SELECT * FROM jurnal_kebiasaan 
        WHERE siswa_id = ? 
        AND MONTH(tanggal_input) = ? 
        AND YEAR(tanggal_input) = ?
        ORDER BY tanggal_input ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("❌ Query error: " . $conn->error);
}
$stmt->bind_param("iii", $siswa_id, $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rekap Bulanan Jurnal Kebiasaan</title>
<style>
    body { font-family: Arial, sans-serif; background:#f8f9fa; margin:20px;}
    .container { max-width: 100%; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.1);}
    h2 { text-align:center; margin-bottom:20px; }
    form { text-align:center; margin-bottom:15px; }
    select, button { padding:6px; margin:5px; }
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
    <h2>📅 Rekap Jurnal Bulan <?= $bulan . "-" . $tahun; ?></h2>
    
    <form method="get">
        <label>Pilih Bulan: 
            <select name="bulan">
                <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= ($m==$bulan)?"selected":""; ?>>
                        <?= date("F", mktime(0,0,0,$m,1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </label>
        <label>Pilih Tahun: 
            <select name="tahun">
                <?php for($y=date("Y")-2; $y<=date("Y"); $y++): ?>
                    <option value="<?= $y ?>" <?= ($y==$tahun)?"selected":""; ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </label>
        <button type="submit">Tampilkan</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
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
                        <td><?= date("d-m-Y", strtotime($row['tanggal_input'])) ?></td>
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
                <tr><td colspan="10">❌ Belum ada data di bulan ini</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="text-align:center; margin-top:15px;">
        <a href="../dashboard_siswa.php">⬅ Kembali ke Dashboard</a>
    </p>
</div>
</body>
</html>
