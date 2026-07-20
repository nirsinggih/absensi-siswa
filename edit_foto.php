<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$q = mysqli_query($conn, "SELECT nama, foto_siswa FROM siswa WHERE id = $id");
$siswa = mysqli_fetch_assoc($q);

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['foto_siswa']) && $_FILES['foto_siswa']['error'] === UPLOAD_ERR_OK) {

        $file_tmp  = $_FILES['foto_siswa']['tmp_name'];
        $file_name = $_FILES['foto_siswa']['name'];
        $file_size = $_FILES['foto_siswa']['size'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 🔒 1. Batasi ukuran maksimal 2MB sebelum compress
        if ($file_size > 2 * 1024 * 1024) {
            $msg = "<div class='alert alert-danger'>Ukuran file terlalu besar (maks 2MB).</div>";
            exit;
        }

        // 🔒 2. Blokir jika nama file mengandung php
        if (preg_match('/\.php/i', $file_name)) {
            $msg = "<div class='alert alert-danger'>File mencurigakan terdeteksi.</div>";
            exit;
        }

        // 🔒 3. Validasi ekstensi
        if ($ext !== "jpg" && $ext !== "jpeg") {
            $msg = "<div class='alert alert-danger'>Foto harus berformat JPG/JPEG.</div>";
            exit;
        }

        // 🔒 4. Validasi MIME type asli file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if ($mime !== 'image/jpeg') {
            $msg = "<div class='alert alert-danger'>File bukan gambar JPEG valid.</div>";
            exit;
        }

        // 🔒 5. Validasi benar-benar gambar
        $check = getimagesize($file_tmp);
        if ($check === false) {
            $msg = "<div class='alert alert-danger'>File bukan gambar valid.</div>";
            exit;
        }

        // Buat nama file baru (hindari pakai nama asli)
        $new_name = "siswa_" . $id . "_" . time() . ".jpg";
        $dest = "uploads/" . $new_name;

        if (!is_dir("uploads")) {
            mkdir("uploads", 0755, true);
        }

        // 🔄 Proses compress
        $src = imagecreatefromjpeg($file_tmp);
        if ($src) {
            $quality = 90;
            do {
                ob_start();
                imagejpeg($src, null, $quality);
                $data = ob_get_clean();
                $size = strlen($data);
                $quality -= 5;
            } while ($size > 200 * 1024 && $quality > 10);

            file_put_contents($dest, $data);
            imagedestroy($src);

            mysqli_query($conn, "UPDATE siswa SET foto_siswa = '$new_name' WHERE id = $id");

            $msg = "<div class='alert alert-success'>Foto berhasil diperbarui!</div>";
            $q = mysqli_query($conn, "SELECT nama, foto_siswa FROM siswa WHERE id = $id");
            $siswa = mysqli_fetch_assoc($q);
        } else {
            $msg = "<div class='alert alert-danger'>Gagal memproses gambar.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Foto Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

  <h3>Edit Foto - <?= htmlspecialchars($siswa['nama']) ?></h3>
  <?= $msg ?>

  <?php if (!empty($siswa['foto_siswa'])): ?>
    <img src="uploads/<?= htmlspecialchars($siswa['foto_siswa']) ?>" alt="Foto" class="mb-3 rounded" style="max-width:120px;">
  <?php else: ?>
    <p class="text-muted">Belum ada foto.</p>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Upload Foto Baru (JPG max 200KB, otomatis compress)</label>
      <input type="file" name="foto_siswa" class="form-control" accept="image/jpeg" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="foto_siswa.php" class="btn btn-secondary">Kembali</a>
  </form>

</body>
</html>
