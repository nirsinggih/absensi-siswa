<?php
include 'config.php';

// Sanitasi input dasar
$kelas    = isset($_GET['kelas']) ? mysqli_real_escape_string($conn, $_GET['kelas']) : '';
$semester = isset($_GET['semester']) ? ($_GET['semester'] === '1' || $_GET['semester'] === '2' ? $_GET['semester'] : '') : '';
$tahun    = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Ambil daftar kelas
$kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");

// Ambil siswa aktif (opsional filter kelas)
$siswaQuery = "SELECT * FROM siswa WHERE status='aktif'";
if ($kelas !== '') {
  $siswaQuery .= " AND kelas = '$kelas'";
}
$siswaQuery .= " ORDER BY nama";
$siswaResult = mysqli_query($conn, $siswaQuery);

// Ambil absensi dalam rentang semester terpilih
$absensi = [];
if ($semester !== '') {
  $absensiQuery = "SELECT a.siswa_id, a.tanggal, a.status 
                   FROM absensi a 
                   JOIN siswa s ON a.siswa_id = s.id
                   WHERE s.status='aktif' AND (
                     ('$semester'='1' AND YEAR(a.tanggal) = '$tahun' AND MONTH(a.tanggal) BETWEEN 7 AND 12)
                     OR
                     ('$semester'='2' AND YEAR(a.tanggal) = '".($tahun+1)."' AND MONTH(a.tanggal) BETWEEN 1 AND 6)
                   )";
  if ($kelas !== '') {
    $absensiQuery .= " AND s.kelas = '$kelas'";
  }
  $resultAbsensi = mysqli_query($conn, $absensiQuery);
  while ($row = mysqli_fetch_assoc($resultAbsensi)) {
    $sid = $row['siswa_id'];
    $tglKey = date('Y-m-d', strtotime($row['tanggal']));
    $absensi[$sid][$tglKey] = $row['status'];
  }
}

// Profil & wali kelas
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kepala_sekolah, nip_kepala FROM profil_sekolah LIMIT 1"));
$wali_nama = '....................................';
$wali_nip  = '........................';
if ($kelas !== '') {
  $qWali = mysqli_query($conn, "SELECT nama_wali, nip_wali FROM wali_kelas WHERE kelas = '$kelas' LIMIT 1");
  if ($w = mysqli_fetch_assoc($qWali)) {
    $wali_nama = $w['nama_wali'] ?: $wali_nama;
    $wali_nip  = $w['nip_wali']  ?: $wali_nip;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Rekap Absensi Semester</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{
      --bg:#f8fafc;
      --card:#ffffff;
      --ink:#0f172a;
      --muted:#64748b;
      --brand:#2563eb;
      --ok:#16a34a;
      --warn:#ea580c;
      --bad:#dc2626;
      --ring:#dbeafe;
      --line:#e5e7eb;
      --radius:14px;
      --shadow:0 10px 24px rgba(2,6,23,.06);
    }
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;background:var(--bg);color:var(--ink)}
    body{font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Apple Color Emoji","Segoe UI Emoji"; line-height:1.45}

    .container{
      max-width:1100px; margin:24px auto; padding:0 16px;
    }
    .card{
      background:var(--card); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow);
      padding:16px;
    }
    h2{
      margin:0 0 12px; font-size:clamp(18px,2.5vw,24px)
    }
    .subtitle{color:var(--muted); font-size:14px; margin-bottom:16px}

    .filters{
      display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:10px; align-items:end; margin-bottom:12px
    }
    label{font-size:12px; color:var(--muted); display:block; margin-bottom:6px}
    select,input[type="number"]{
      width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:10px; background:#fff; font-size:14px;
      outline:none; transition:border .2s, box-shadow .2s;
    }
    select:focus, input[type="number"]:focus{
      border-color:var(--brand); box-shadow:0 0 0 4px var(--ring);
    }
    .btn{
      appearance:none; border:none; cursor:pointer; padding:10px 14px; border-radius:10px; font-weight:600; font-size:14px;
      background:var(--brand); color:#fff; transition:filter .2s, transform .02s;
    }
    .btn:active{ transform:translateY(1px) }
    .btn.secondary{ background:#334155 }
    .btn:disabled{ filter:grayscale(.5); opacity:.7; cursor:not-allowed }

    .table-wrap{ width:100%; overflow:auto; border:1px solid var(--line); border-radius:12px; background:#fff }
    table{ border-collapse:separate; border-spacing:0; width:100%; min-width:560px; font-size:14px }
    thead th{
      position:sticky; top:0; background:#f1f5f9; z-index:1; font-weight:700; text-align:center; padding:10px;
      border-bottom:1px solid var(--line);
    }
    th:first-child, td:first-child{ border-left:none }
    tbody td{ padding:10px; text-align:center; border-top:1px solid var(--line) }
    tbody tr:nth-child(odd){ background:#fcfdff }
    tbody tr:hover{ background:#f8fbff }

    .tag{ display:inline-block; min-width:28px; padding:3px 8px; border-radius:999px; font-size:12px; font-weight:700 }
    .tag.h{ background:#ecfdf5; color:#065f46 }
    .tag.s{ background:#fff7ed; color:#9a3412 }
    .tag.i{ background:#f0f9ff; color:#075985 }
    .tag.a{ background:#fef2f2; color:#991b1b }

    .footer-sign{
      display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:18px;
      font-size:14px; text-align:center;
    }
    .sign-card{ background:#fff; border:1px solid var(--line); border-radius:12px; padding:18px }

    /* Mobile tweaks */
    @media (max-width: 720px){
      .filters{ grid-template-columns:1fr 1fr; }
      .filters .wide{ grid-column: span 2 }
      .btn{ width:100% }
      .subtitle{ font-size:13px }
      table{ font-size:13px; min-width:520px }
      .footer-sign{ grid-template-columns:1fr }
    }

    /* Cetak rapi */
    @media print{
      body{ background:#fff }
      .container{ margin:0; max-width:100% }
      .card{ box-shadow:none; border:none; padding:0 }
      .filters, .actions{ display:none !important }
      thead th{ position:static }
      .table-wrap{ overflow:visible; border:none }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>Rekap Absensi Semester</h2>
      <div class="subtitle">Semester 1: Juli–Desember <?= htmlspecialchars($tahun) ?> · Semester 2: Januari–Juni <?= htmlspecialchars($tahun+1) ?></div>

      <form method="get" class="filters">
        <div>
          <label for="kelas">Kelas</label>
          <select id="kelas" name="kelas">
            <option value="">Semua</option>
            <?php while ($k = mysqli_fetch_assoc($kelasList)) {
              $sel = ($k['kelas'] === $kelas) ? 'selected' : '';
              echo "<option value='".htmlspecialchars($k['kelas'],ENT_QUOTES)."' $sel>".htmlspecialchars($k['kelas'])."</option>";
            } ?>
          </select>
        </div>

        <div>
          <label for="tahun">Tahun (awal tahun ajaran)</label>
          <input id="tahun" type="number" name="tahun" value="<?= htmlspecialchars($tahun) ?>" inputmode="numeric" />
        </div>

        <div>
          <label for="semester">Semester</label>
          <select id="semester" name="semester" required>
            <option value="">— Pilih —</option>
            <option value="1" <?= $semester==='1'?'selected':''; ?>>Semester 1 (Juli–Des <?= $tahun ?>)</option>
            <option value="2" <?= $semester==='2'?'selected':''; ?>>Semester 2 (Jan–Jun <?= $tahun+1 ?>)</option>
          </select>
        </div>

        <div class="wide actions" style="display:flex; gap:8px;">
          <button type="submit" class="btn">Tampilkan</button>
          <button type="button" class="btn secondary" onclick="window.print()">Cetak</button>
        </div>
      </form>

      <?php if ($semester !== ''): ?>
      <div class="table-wrap" role="region" aria-label="Tabel Rekap Absensi Semester" tabindex="0">
        <table>
          <thead>
            <tr>
              <th style="width:56px">No</th>
              <th style="width:120px">NIS</th>
              <th style="min-width:200px; text-align:left">Nama</th>
              <th>H</th>
              <th>S</th>
              <th>I</th>
              <th>A</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            // Reset pointer siswaResult kalau sebelumnya dipakai
            mysqli_data_seek($siswaResult, 0);
            while ($siswa = mysqli_fetch_assoc($siswaResult)):
              $sid = $siswa['id'];
              $countH = $countS = $countI = $countA = 0;

              if (isset($absensi[$sid])) {
                foreach ($absensi[$sid] as $tgl => $val) {
                  if     ($val === 'H') $countH++;
                  elseif ($val === 'S') $countS++;
                  elseif ($val === 'I') $countI++;
                  elseif ($val === 'A') $countA++;
                }
              }
            ?>
              <tr>
                <td><?= $no ?></td>
                <td><?= htmlspecialchars($siswa['nis']) ?></td>
                <td style="text-align:left"><?= htmlspecialchars($siswa['nama']) ?></td>
                <td><span class="tag h"><?= $countH ?></span></td>
                <td><span class="tag s"><?= $countS ?></span></td>
                <td><span class="tag i"><?= $countI ?></span></td>
                <td><span class="tag a"><?= $countA ?></span></td>
              </tr>
            <?php $no++; endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <div style="padding:10px; color:var(--muted); font-size:14px">Silakan pilih semester terlebih dahulu untuk menampilkan rekap.</div>
      <?php endif; ?>

      <div class="footer-sign">
        <div class="sign-card">
          Mengetahui,<br>Kepala Sekolah<br><br><br><br>
          <u><?= htmlspecialchars($profil['kepala_sekolah'] ?? '....................................') ?></u><br>
          NIP. <?= htmlspecialchars($profil['nip_kepala'] ?? '........................') ?>
        </div>
        <div class="sign-card">
          <?= date("j F Y") ?><br>
          Wali Kelas <?= $kelas !== '' ? htmlspecialchars($kelas) : '(Semua Kelas)' ?><br><br><br><br>
          <u><?= htmlspecialchars($wali_nama) ?></u><br>
          NIP. <?= htmlspecialchars($wali_nip) ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
