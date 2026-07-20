<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
include 'config.php';
require 'vendor/phpqrcode/qrlib.php';
require_once 'phpexcel/Classes/PHPExcel.php'; // pakai PHPExcel klasik

$msg = "";
$berhasil = 0;
$gagal = 0;
$errors = [];

if (isset($_POST['import'])) {
    if (!empty($_FILES['file_excel']['tmp_name'])) {
        $inputFileName = $_FILES['file_excel']['tmp_name'];

        try {
            $excelReader = PHPExcel_IOFactory::createReaderForFile($inputFileName);
            $objPHPExcel = $excelReader->load($inputFileName);
            $sheet = $objPHPExcel->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) { // baris 1 header
                $nis   = trim($sheet->getCellByColumnAndRow(0, $row)->getValue());
                $nisn  = trim($sheet->getCellByColumnAndRow(1, $row)->getValue());
                $nama  = trim($sheet->getCellByColumnAndRow(2, $row)->getValue());
                $kelas = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());

                if ($nis != "" && $nisn != "" && $nama != "" && $kelas != "") {
                    // Validasi
                    if (!preg_match('/^[0-9]{4}$/', $nis)) {
                        $gagal++;
                        $errors[] = "Baris $row: NIS harus 4 digit angka.";
                        continue;
                    }
                    if (!preg_match('/^[0-9]{10}$/', $nisn)) {
                        $gagal++;
                        $errors[] = "Baris $row: NISN harus 10 digit angka.";
                        continue;
                    }
                    if (strlen($kelas) > 7) {
                        $gagal++;
                        $errors[] = "Baris $row: Kelas maksimal 7 karakter.";
                        continue;
                    }

                    // Simpan ke database
                    $sql = "INSERT INTO siswa (nis, nisn, nama, kelas, status) 
                            VALUES ('$nis','$nisn','$nama','$kelas','aktif') 
                            ON DUPLICATE KEY UPDATE nama='$nama', kelas='$kelas', status='aktif'";
                    if (mysqli_query($conn, $sql)) {
                        $berhasil++;

                        // Buat QR Code
                        $qr_dir = "assets/qr/";
                        if (!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);
                        QRcode::png($nisn, $qr_dir . "$nisn.png", QR_ECLEVEL_L, 4);
                    } else {
                        $gagal++;
                        $errors[] = "Baris $row: Gagal disimpan ke database.";
                    }
                }
            }

            $msg = "✅ Import selesai. <br> 
                    Berhasil: <b>$berhasil</b> <br> 
                    Gagal: <b>$gagal</b>";
        } catch (Exception $e) {
            $msg = "❌ Gagal membaca file: " . $e->getMessage();
        }
    } else {
        $msg = "❌ Harap pilih file Excel terlebih dahulu.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Import Data Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

  <h2 class="mb-4 text-center">📥 Import Data Siswa dari Excel</h2>

  <?php if (!empty($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

  <a href="siswa.php" class="btn btn-secondary mb-3">← Kembali</a>

  <form method="post" enctype="multipart/form-data" class="card p-3">
      <div class="mb-3">
          <label class="form-label">Pilih File Excel (.xls / .xlsx)</label>
          <input type="file" name="file_excel" accept=".xls,.xlsx" class="form-control" required>
      </div>
      <button type="submit" name="import" class="btn btn-success">Import</button>
      <a href="template_siswa.xlsx" class="btn btn-link">📂 Unduh Template Excel</a>
  </form>

  <p class="mt-3 text-muted">Format kolom Excel: <b>NIS | NISN | Nama | Kelas</b> (baris pertama header)</p>

  <?php if (!empty($errors)) { ?>
    <div class="card mt-3">
      <div class="card-header bg-danger text-white">❌ Detail Error</div>
      <div class="card-body">
        <ul class="mb-0">
          <?php foreach ($errors as $err) { echo "<li>$err</li>"; } ?>
        </ul>
      </div>
    </div>
  <?php } ?>

</body>
</html>
