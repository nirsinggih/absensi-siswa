<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pengaturan</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background: #f8f9fa;
    }
    .menu-btn {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 10px;
      font-size: 1rem;
      font-weight: 600;
      padding: 14px;
      border-radius: 12px;
    }
    .menu-btn i {
      font-size: 1.2rem;
      width: 24px;
      text-align: center;
    }
  </style>
</head>
<body>
  <header class="bg-white shadow-sm sticky-top">
    <div class="container py-3 d-flex justify-content-between align-items-center">
      <h1 class="h5 m-0">Pengaturan</h1>
      <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-house"></i>
      </a>
    </div>
  </header>

  <main class="container py-4">
    <div class="alert alert-info small" style="border-radius: 10px;">
      <i class="fa-solid fa-circle-info"></i> 
      Sebelum melakukan tindakan penting, sebaiknya lakukan <strong>Backup</strong> terlebih dahulu.
    </div>

    <div class="d-grid gap-3">
      <a href="backup_restore.php" class="btn btn-light border menu-btn">
        <i class="fa-solid fa-database text-primary"></i> Backup & Restore
      </a>
      <a href="server-info.php" class="btn btn-light border menu-btn">
        <i class="fa-solid fa-code text-success"></i> Cek Info Server
      </a>
      <a href="update_db.php" class="btn btn-light border menu-btn">
        <i class="fa-solid fa-database text-warning"></i> Update Versi Database
      </a>
      <a href="cek_update.php" class="btn btn-light border menu-btn">
        <i class="fa-solid fa-rotate text-danger"></i> Cek Update Versi Aplikasi 
      </a>
    </div>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
