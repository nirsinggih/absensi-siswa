<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Jika role guru, arahkan ke dashboard guru
if ($_SESSION['role'] === 'guru') {
    header("Location: dashboard_guru.php");
    exit;
}

// Jika bukan admin (misalnya siswa, dll.) kembalikan ke index
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin Absensi QR</title>
  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    body {
      font-family: sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f4f4;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Running text modern */
    .marquee-container {
      background: #ff9800;
      overflow: hidden;
      white-space: nowrap;
      box-sizing: border-box;
      padding: 10px 0;
    }

    .marquee-text {
      display: inline-block;
      padding-left: 100%;
      animation: marquee 15s linear infinite;
      color: white;
      font-weight: bold;
      font-size: 16px;
    }

    @keyframes marquee {
      0%   { transform: translateX(0); }
      100% { transform: translateX(-100%); }
    }

    header {
      background-color: #4CAF50;
      color: white;
      padding: 15px;
      text-align: center;
    }

    h2 {
      margin: 20px;
      text-align: center;
    }

    ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-width: 400px;
      margin: 20px auto;
    }

    li a {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background-color: #fff;
      color: #333;
      text-decoration: none;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: background-color 0.3s ease;
      text-align: center;
      border: 4px solid transparent; /* default */
      font-weight: bold;
    }

    li a:hover {
      background-color: #e0e0e0;
    }

    /* Border warna khusus */
    .siswa { border-color: orange; }
    .scan { border-color: green; }
    .sia { border-color: blue; }
    .jam { border-color: purple; }
    .rekap { border-color: black; }
    .grafik { border-color: black; }
    .prosentase { border-color: black; }
    .libur { border-color: red; }
    .excel { border-color: green; }
    .wa { border-color: green; }
    .profil { border-color: green; }
    .hapus { border-color: red; }
    .restore { border-color: purple; }
    .backup { border-color: blue; }
    .modif { border: 4px dashed blue; /* garis putus-putus biru */}
    .kaih { border: 4px dashed red; /* garis putus-putus merah */}
    .logout { border-color: red; }

    @media (min-width: 600px) {
      ul {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
      }

      li {
        flex: 1 1 40%;
        margin: 5px;
      }
    }

    footer {
      margin-top: auto;
      background: #333;
      color: white;
      text-align: center;
      padding: 10px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <!-- Running Text Modern -->
  <div class="marquee-container">
    <span class="marquee-text">
      Kehadiran Bapak/Ibu Guru membersamai siswa belajar tidak akan pernah dapat digantikan oleh Robot AI
    </span>
  </div>

  <header>
    <h1>Dashboard Admin</h1>
  </header>

  <h2>Menu</h2>
  <ul>
    <li><a href="siswa.php" class="siswa"><i class="fa-solid fa-user-graduate"></i> Data Siswa</a></li>
    <li><a href="scan.php" class="scan"><i class="fa-solid fa-qrcode"></i> SCAN QR</a></li>
	<li><a href="scan_wa.php" class="scan"><i class="fa-solid fa-qrcode"></i> SCAN QR + WA Manual</a></li>
	<li><a href="scan_wa_api.php" class="scan"><i class="fa-solid fa-qrcode"></i> SCAN QR + WA API OTOMATIS</a></li>
	<li><a href="key_wa_sidobe.php" class="scan"><i class="fa-brands fa-whatsapp"></i> Pengaturan Key API WA</a></li>
	<li><a href="belum_absensi.php" class="libur"><i class="fa-solid fa-user"></i> Siswa Belum Hadir</a></li>
    <li><a href="absensi.php" class="sia"><i class="fa-solid fa-clipboard-check"></i> Isi S/I/A</a></li>
<li><a href="siswa_terlambat.php" class="sia"><i class="fa-solid fa-stopwatch"></i> Siswa Terlambat</a></li>
    <li><a href="jam_absensi.php" class="jam"><i class="fa-solid fa-clock"></i> Jam Waktu Absensi</a></li>
    <li><a href="rekap_bulanan.php" class="rekap"><i class="fa-solid fa-calendar-days"></i> Rekap Bulanan</a></li>
    <li><a href="grafik.php" class="grafik"><i class="fa-solid fa-chart-line"></i> Grafik</a></li>
    <li><a href="hadir.php" class="prosentase"><i class="fa-solid fa-chart-pie"></i> Prosentase Kehadiran</a></li>
    <li><a href="libur.php" class="libur"><i class="fa-solid fa-plane"></i> Hari Libur</a></li>
    <li><a href="export.php" class="excel"><i class="fa-solid fa-file-excel"></i> Export Excel</a></li>
    <li><a href="https://wa.me/6281578049508?text=Halo%20mohon%20gabungkan%20ke%20grup%20aplikasi%20absensi%20QR
" class="wa"><i class="fa-brands fa-whatsapp"></i> Grup WA Admin</a></li>
<li><a href="wa-wali-siswa.php" class="wa"><i class="fa-brands fa-whatsapp"></i> Kirim WA Orang Tua/Wali Siswa</a></li>
    <li><a href="profil.php" class="siswa"><i class="fa-solid fa-school"></i> Profil Sekolah</a></li>
<li><a href="wali_kelas.php" class="siswa"><i class="fa-solid fa-chalkboard-teacher"></i> Wali Kelas</a></li>
    <li><a href="kosongkan_data.php" class="hapus"><i class="fa-solid fa-trash"></i> Hapus/Kosongkan Data</a></li>
    <li><a href="restore.php" class="restore"><i class="fa-solid fa-database"></i> Kembalikan Data Contoh</a></li>
    <li><a href="backup_restore.php" class="backup"><i class="fa-solid fa-rotate"></i> Backup Restore Database</a></li>
<li><a href="restore_file.php" class="backup"><i class="fa-solid fa-folder"></i> Backup Restore File</a></li>

	<li><a href="modif.php" class="modif"><i class="fa-solid fa-toolbox"></i> Versi Modif</a></li>
	<li><a href="7kaih/dashboard_7kaih.php" class="kaih"><i class="fa-solid fa-book"></i> Jurnal 7KAIH</a></li>
	<li><a href="pelanggaran/" class="kaih"><i class="fa-solid fa-triangle-exclamation"></i> Pelanggaran Siswa</a></li>
    <li><a href="pengaturan.php" class="backup"><i class="fa-triangfa-gear"></i> Pengaturan</a></li>
    <li><a href="logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
  </ul>

  <footer>
    Versi Aplikasi: 5.3.9
  </footer>
</body>
</html>
