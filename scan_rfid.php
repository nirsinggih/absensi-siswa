<?php
include "config.php";
date_default_timezone_set("Asia/Jakarta");

if (isset($_GET['rfid_uid'])) {
    $uid = $conn->real_escape_string($_GET['rfid_uid']);

    // Cari UID di tabel siswa
    $q = $conn->query("SELECT * FROM siswa WHERE rfid_uid='$uid'");
    if ($q->num_rows > 0) {
        $user = $q->fetch_assoc();
        $userId = $user['id'];
        $today  = date("Y-m-d");
        $jam    = date("H:i:s");

        // Cek apakah hari ini libur
        $cekLibur = $conn->query("SELECT * FROM hari_libur WHERE tanggal='$today'");
        if ($cekLibur->num_rows > 0) {
            echo "<div class='alert alert-danger'>⛔ Hari ini libur!</div>";
            exit;
        }

        // Cek apakah sudah ada absensi hari ini
        $cek = $conn->query("SELECT * FROM absensi WHERE siswa_id='$userId' AND tanggal='$today'");
        if ($cek->num_rows == 0) {
            // Belum ada → catat jam masuk
            $conn->query("INSERT INTO absensi (siswa_id, tanggal, jam, status) 
                          VALUES ('$userId', '$today', '$jam', 'H')");
            echo "<div class='alert alert-success'>✅ Selamat datang, {$user['nama']} ({$user['kelas']})!<br>🕒 Jam hadir: $jam</div>";
        } else {
            // Sudah ada, cek apakah jam pulang terisi
            $row = $cek->fetch_assoc();

            if (is_null($row['jam_pulang']) && $jam >= "09:00:00") {
                // Update jam pulang
                $conn->query("UPDATE absensi SET jam_pulang='$jam' WHERE id={$row['id']}");
                echo "<div class='alert alert-warning'>👋 Sampai jumpa, {$user['nama']} ({$user['kelas']})!<br>🕒 Jam pulang: $jam</div>";
            } else {
                // Sudah absen masuk & pulang
                echo "<div class='alert alert-info'>ℹ️ {$user['nama']} sudah absen hari ini.<br>🕒 Jam hadir: {$row['jam']}";
                if (!is_null($row['jam_pulang'])) {
                    echo "<br>🕒 Jam pulang: {$row['jam_pulang']}";
                }
                echo "</div>";
            }
        }
    } else {
        echo "<div class='alert alert-danger'>❌ UID tidak dikenal!</div>";
    }
} else {
    echo "<div class='alert alert-danger'>❌ UID kosong!</div>";
}
?>
