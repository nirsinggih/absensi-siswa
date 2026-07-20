<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kumpulan Tombol 7KAIH</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container py-4">
    <div class="text-center mb-4">
      <h3 class="fw-bold"><i class="fa-solid fa-book-open"></i> Kumpulan Menu 7KAIH</h3>
      <p class="text-muted">
        Siswa dapat mengisi <b>Jurnal 7 Kebiasaan</b> dengan login menggunakan <b>Username</b> dan <b>Password</b> masing-masing.
      </p>
    </div>

    <div class="row justify-content-center g-3">
      <div class="col-12 col-md-6">
        <a href="db_7kaih.php" class="btn btn-primary w-100 py-3 shadow-sm">
          <i class="fa-solid fa-database"></i> Buat Database 7KAIH
        </a>
      </div>
      <div class="col-12 col-md-6">
        <a href="lihat_kebiasaan.php" class="btn btn-success w-100 py-3 shadow-sm">
          <i class="fa-solid fa-eye"></i> Lihat Jurnal
        </a>
      </div>
    </div>

    <div class="text-center mt-5">
      <a href="../dashboard.php" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
      </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
